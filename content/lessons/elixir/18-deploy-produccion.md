# Deploy y Producción en Elixir

Llevar una aplicación Elixir a producción requiere comprender el sistema de releases, la configuración en runtime, el empaquetado con Docker y las estrategias de monitoreo. La BEAM VM ofrece ventajas únicas como hot code upgrades y observabilidad integrada que hacen del deploy una experiencia diferente a otros ecosistemas.

## Mix Releases

Desde Elixir 1.9, el sistema de releases está integrado en Mix. Un release empaqueta la aplicación compilada, sus dependencias y la BEAM VM:

```elixir
# mix.exs
def project do
  [
    app: :mi_app,
    version: "1.0.0",
    releases: [
      mi_app: [
        include_executables_for: [:unix],
        applications: [runtime_tools: :permanent]
      ]
    ]
  ]
end

# Generar release
# MIX_ENV=prod mix release

# Estructura generada:
# _build/prod/rel/mi_app/
#   bin/
#     mi_app        # Script de arranque
#     server        # Inicia como servidor
#   lib/            # Código compilado
#   releases/
#     1.0.0/
#       elixir
#       start.boot
```

Comandos del release:

```elixir
# Iniciar la aplicación
# _build/prod/rel/mi_app/bin/mi_app start

# Iniciar en foreground
# _build/prod/rel/mi_app/bin/mi_app start_iex

# Modo daemon (background)
# _build/prod/rel/mi_app/bin/mi_app daemon

# Conectarse a un nodo en ejecución
# _build/prod/rel/mi_app/bin/mi_app remote

# Detener
# _build/prod/rel/mi_app/bin/mi_app stop

# Ejecutar migraciones
# _build/prod/rel/mi_app/bin/mi_app eval "MiApp.Release.migrate()"
```

## Configuración en Runtime

`config/runtime.exs` se ejecuta cada vez que inicia la aplicación, ideal para leer variables de entorno:

```elixir
# config/runtime.exs
import Config

if config_env() == :prod do
  database_url =
    System.get_env("DATABASE_URL") ||
      raise "DATABASE_URL no está configurada"

  config :mi_app, MiApp.Repo,
    url: database_url,
    pool_size: String.to_integer(System.get_env("POOL_SIZE") || "10"),
    ssl: true

  secret_key_base =
    System.get_env("SECRET_KEY_BASE") ||
      raise "SECRET_KEY_BASE no está configurada"

  config :mi_app, MiAppWeb.Endpoint,
    http: [port: String.to_integer(System.get_env("PORT") || "4000")],
    secret_key_base: secret_key_base,
    server: true
end
```

## Módulo Release para Migraciones

Es necesario crear un módulo para ejecutar migraciones sin Mix (que no está disponible en producción):

```elixir
defmodule MiApp.Release do
  @moduledoc """
  Tareas de release que se ejecutan sin Mix.
  """
  @app :mi_app

  def migrate do
    load_app()

    for repo <- repos() do
      {:ok, _, _} =
        Ecto.Migrator.with_repo(repo, &Ecto.Migrator.run(&1, :up, all: true))
    end
  end

  def rollback(repo, version) do
    load_app()
    {:ok, _, _} = Ecto.Migrator.with_repo(repo, &Ecto.Migrator.run(&1, :down, to: version))
  end

  defp repos do
    Application.fetch_env!(@app, :ecto_repos)
  end

  defp load_app do
    Application.ensure_all_started(:ssl)
    Application.load(@app)
  end
end
```

## Docker

Un Dockerfile multi-stage optimizado para Elixir:

```elixir
# Dockerfile
# --- Etapa de build ---
FROM hexpm/elixir:1.16.0-erlang-26.2-debian-bookworm-20231009 AS build

RUN apt-get update && apt-get install -y build-essential git
WORKDIR /app

ENV MIX_ENV=prod

# Instalar dependencias primero (mejor caché)
COPY mix.exs mix.lock ./
RUN mix deps.get --only prod
RUN mix deps.compile

# Copiar código y compilar
COPY lib lib
COPY priv priv
COPY config config
RUN mix compile

# Assets (si es Phoenix)
COPY assets assets
RUN mix assets.deploy

# Release
RUN mix release

# --- Etapa de runtime ---
FROM debian:bookworm-slim

RUN apt-get update && \
    apt-get install -y libstdc++6 openssl libncurses5 locales && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

ENV LANG=es_ES.UTF-8
RUN sed -i '/es_ES.UTF-8/s/^# //g' /etc/locale.gen && locale-gen

WORKDIR /app
COPY --from=build /app/_build/prod/rel/mi_app ./

ENV HOME=/app
ENV PORT=4000

EXPOSE 4000

CMD ["bin/mi_app", "start"]
```

## CI/CD

Ejemplo de pipeline con GitHub Actions:

```elixir
# .github/workflows/ci.yml
# name: CI
#
# on:
#   push:
#     branches: [main]
#   pull_request:
#     branches: [main]
#
# jobs:
#   test:
#     runs-on: ubuntu-latest
#     services:
#       postgres:
#         image: postgres:16
#         env:
#           POSTGRES_PASSWORD: postgres
#         ports: ['5432:5432']
#
#     steps:
#       - uses: actions/checkout@v4
#       - uses: erlef/setup-beam@v1
#         with:
#           elixir-version: '1.16'
#           otp-version: '26'
#       - run: mix deps.get
#       - run: mix format --check-formatted
#       - run: mix credo --strict
#       - run: mix test
#
#   deploy:
#     needs: test
#     if: github.ref == 'refs/heads/main'
#     runs-on: ubuntu-latest
#     steps:
#       - uses: actions/checkout@v4
#       - run: docker build -t mi_app .
#       - run: docker push registry.example.com/mi_app:latest
```

## Monitoreo y Observabilidad

La BEAM ofrece herramientas de observabilidad integradas:

```elixir
# Observer: interfaz gráfica de monitoreo
# :observer.start()

# Telemetry: sistema de eventos para métricas
defmodule MiApp.Telemetria do
  def setup do
    :telemetry.attach_many(
      "mi-app-handler",
      [
        [:mi_app, :repo, :query],
        [:mi_app, :endpoint, :request],
        [:mi_app, :worker, :ejecutar]
      ],
      &manejar_evento/4,
      nil
    )
  end

  defp manejar_evento([:mi_app, :repo, :query], medidas, metadata, _config) do
    IO.puts("Query #{metadata.source}: #{medidas.total_time / 1_000_000}ms")
  end

  defp manejar_evento(nombre, medidas, _metadata, _config) do
    IO.puts("#{inspect(nombre)}: #{inspect(medidas)}")
  end
end

# Logger estructurado
require Logger
Logger.info("Usuario creado", usuario_id: 123, accion: "registro")
```

## Health Checks

```elixir
defmodule MiAppWeb.HealthController do
  use MiAppWeb, :controller

  def check(conn, _params) do
    checks = %{
      database: check_database(),
      memoria: check_memoria(),
      procesos: check_procesos()
    }

    status = if Enum.all?(Map.values(checks), &(&1 == :ok)), do: 200, else: 503
    json(conn, %{status: status, checks: checks})
  end

  defp check_database do
    case Ecto.Adapters.SQL.query(MiApp.Repo, "SELECT 1") do
      {:ok, _} -> :ok
      _ -> :error
    end
  end

  defp check_memoria do
    memoria_mb = :erlang.memory(:total) / 1_048_576
    if memoria_mb < 1024, do: :ok, else: :warning
  end

  defp check_procesos do
    if length(Process.list()) < 100_000, do: :ok, else: :warning
  end
end
```

## Resumen

El deploy de aplicaciones Elixir aprovecha el sistema de releases de Mix para crear paquetes autocontenidos, configuración en runtime con variables de entorno para diferentes ambientes, y Docker para contenedorización reproducible. La BEAM VM ofrece capacidades únicas de monitoreo con Observer y Telemetry, y la posibilidad de conectarse a nodos en producción para debugging en vivo. Un pipeline de CI/CD bien configurado con tests, formateo y análisis estático garantiza la calidad antes de cada despliegue.
