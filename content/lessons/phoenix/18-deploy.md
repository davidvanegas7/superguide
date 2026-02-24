# Deploy y Producción en Phoenix

## Introducción

Phoenix ofrece múltiples estrategias de deploy gracias a Mix Releases y la BEAM VM. Desde contenedores Docker hasta plataformas especializadas como Fly.io, el ecosistema facilita llevar aplicaciones a producción.

## Mix Release

Las releases empaquetan la aplicación en un binario autónomo:

```elixir
# mix.exs
def project do
  [
    app: :my_app,
    releases: [
      my_app: [
        include_executables_for: [:unix],
        steps: [:assemble, :tar]
      ]
    ]
  ]
end
```

Generar y ejecutar una release:

```elixir
# Compilar assets y crear release
MIX_ENV=prod mix assets.deploy
MIX_ENV=prod mix release

# Ejecutar
_build/prod/rel/my_app/bin/my_app start
```

## Runtime Config

La configuración en producción se lee en tiempo de ejecución:

```elixir
# config/runtime.exs
import Config

if config_env() == :prod do
  database_url = System.fetch_env!("DATABASE_URL")
  secret_key_base = System.fetch_env!("SECRET_KEY_BASE")

  config :my_app, MyApp.Repo,
    url: database_url,
    pool_size: String.to_integer(System.get_env("POOL_SIZE") || "10"),
    ssl: true,
    ssl_opts: [verify: :verify_none]

  config :my_app, MyAppWeb.Endpoint,
    url: [host: System.fetch_env!("PHX_HOST"), port: 443, scheme: "https"],
    http: [port: String.to_integer(System.get_env("PORT") || "4000")],
    secret_key_base: secret_key_base,
    server: true
end
```

## Docker Multistage

Dockerfile optimizado con build multietapa:

```elixir
# Dockerfile
FROM hexpm/elixir:1.16.1-erlang-26.2.2-debian-bookworm-20240130-slim AS build

RUN apt-get update && apt-get install -y build-essential git nodejs npm
WORKDIR /app

ENV MIX_ENV=prod
COPY mix.exs mix.lock ./
RUN mix deps.get --only prod && mix deps.compile

COPY assets/ assets/
COPY lib/ lib/
COPY priv/ priv/
COPY config/ config/

RUN mix assets.deploy && mix compile && mix release

# --- Runtime ---
FROM debian:bookworm-slim AS runtime

RUN apt-get update && apt-get install -y libssl3 libstdc++6 locales \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY --from=build /app/_build/prod/rel/my_app ./

ENV LANG=es_ES.UTF-8
EXPOSE 4000
CMD ["bin/my_app", "start"]
```

## Deploy en Fly.io

Fly.io es ideal para apps Phoenix con soporte nativo:

```elixir
# Instalar flyctl e inicializar
fly launch

# fly.toml generado
[env]
  PHX_HOST = "mi-app.fly.dev"
  PORT = "8080"

[http_service]
  internal_port = 8080
  force_https = true

[[services.ports]]
  handlers = ["http"]
  port = 80

[[services.ports]]
  handlers = ["tls", "http"]
  port = 443
```

Comandos de deploy:

```elixir
# Crear base de datos
fly postgres create --name mi-app-db
fly postgres attach mi-app-db

# Configurar secretos
fly secrets set SECRET_KEY_BASE=$(mix phx.gen.secret)
fly secrets set DATABASE_URL=postgres://...

# Deploy
fly deploy

# Ejecutar migraciones
fly ssh console -C "/app/bin/my_app eval 'MyApp.Release.migrate()'"
```

## Gigalixir

Plataforma PaaS especializada en Elixir:

```elixir
# Instalar CLI y crear app
pip install gigalixir
gigalixir create -n mi-app

# Configurar
gigalixir config:set SECRET_KEY_BASE=$(mix phx.gen.secret)
gigalixir pg:create --free

# Deploy via git
git remote add gigalixir https://git.gigalixir.com/mi-app.git
git push gigalixir main

# Migraciones
gigalixir run mix ecto.migrate
```

## CI/CD con GitHub Actions

Pipeline automatizado de testing y deploy:

```elixir
# .github/workflows/ci.yml
name: CI/CD
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_PASSWORD: postgres
        ports: ["5432:5432"]
    steps:
      - uses: actions/checkout@v4
      - uses: erlef/setup-beam@v1
        with:
          elixir-version: "1.16"
          otp-version: "26"
      - run: mix deps.get
      - run: mix test

  deploy:
    needs: test
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: superfly/flyctl-actions/setup-flyctl@master
      - run: flyctl deploy --remote-only
        env:
          FLY_API_TOKEN: ${{ secrets.FLY_API_TOKEN }}
```

## Clustering con libcluster

Conectar nodos BEAM para distribución:

```elixir
# mix.exs
{:libcluster, "~> 3.3"}

# config/runtime.exs
config :libcluster,
  topologies: [
    fly6pn: [
      strategy: Cluster.Strategy.DNSPoll,
      config: [
        polling_interval: 5_000,
        query: System.get_env("FLY_APP_NAME") <> ".internal",
        node_basename: System.get_env("FLY_APP_NAME")
      ]
    ]
  ]

# application.ex
children = [
  {Cluster.Supervisor, [topologies, [name: MyApp.ClusterSupervisor]]},
  MyApp.Repo,
  MyAppWeb.Endpoint
]
```

## Monitoring con Telemetry

Instrumentación y métricas en producción:

```elixir
defmodule MyAppWeb.Telemetry do
  use Supervisor
  import Telemetry.Metrics

  def metrics do
    [
      summary("phoenix.endpoint.start.system_time", unit: {:native, :millisecond}),
      summary("phoenix.router_dispatch.stop.duration", unit: {:native, :millisecond}),
      counter("phoenix.endpoint.stop.duration"),
      summary("my_app.repo.query.total_time", unit: {:native, :millisecond}),
      last_value("vm.memory.total", unit: :byte),
      last_value("vm.total_run_queue_lengths.total")
    ]
  end
end
```

## Resumen

El deploy de Phoenix abarca Mix Releases para binarios autónomos, Docker multistage para contenedores, plataformas como Fly.io y Gigalixir, CI/CD automatizado con GitHub Actions, clustering con libcluster para distribución y monitoring con Telemetry. La BEAM VM garantiza alta disponibilidad y hot code upgrades en producción.
