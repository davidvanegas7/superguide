# Mix y Gestión de Proyectos en Elixir

Mix es la herramienta oficial de construcción de Elixir. Gestiona la creación de proyectos, compilación, dependencias, ejecución de tests, generación de documentación y cualquier tarea automatizable. Dominar Mix es esencial para trabajar de forma productiva con Elixir.

## Crear un Proyecto con mix new

```elixir
# Proyecto básico
# mix new mi_app

# Proyecto con supervisor (Application)
# mix new mi_app --sup

# Proyecto Umbrella (monorepo)
# mix new mi_plataforma --umbrella

# Estructura generada con --sup:
# mi_app/
#   lib/
#     mi_app.ex
#     mi_app/
#       application.ex
#   test/
#     mi_app_test.exs
#     test_helper.exs
#   mix.exs
#   .formatter.exs
#   .gitignore
#   README.md
```

## El Archivo mix.exs

`mix.exs` es el archivo de configuración central del proyecto:

```elixir
defmodule MiApp.MixProject do
  use Mix.Project

  def project do
    [
      app: :mi_app,
      version: "0.1.0",
      elixir: "~> 1.16",
      start_permanent: Mix.env() == :prod,
      deps: deps(),
      aliases: aliases(),

      # Configuración de tests
      test_coverage: [tool: ExCoveralls],
      preferred_cli_env: [coveralls: :test],

      # Documentación
      name: "MiApp",
      source_url: "https://github.com/usuario/mi_app",
      docs: [main: "MiApp", extras: ["README.md"]]
    ]
  end

  def application do
    [
      mod: {MiApp.Application, []},
      extra_applications: [:logger, :runtime_tools]
    ]
  end

  defp deps do
    [
      {:phoenix, "~> 1.7"},
      {:ecto_sql, "~> 3.10"},
      {:postgrex, ">= 0.0.0"},
      {:jason, "~> 1.4"},
      {:ex_doc, "~> 0.31", only: :dev, runtime: false},
      {:credo, "~> 1.7", only: [:dev, :test], runtime: false},
      {:excoveralls, "~> 0.18", only: :test}
    ]
  end

  defp aliases do
    [
      setup: ["deps.get", "ecto.setup"],
      "ecto.setup": ["ecto.create", "ecto.migrate", "run priv/repo/seeds.exs"],
      "ecto.reset": ["ecto.drop", "ecto.setup"],
      test: ["ecto.create --quiet", "ecto.migrate --quiet", "test"]
    ]
  end
end
```

## Gestión de Dependencias

Las dependencias se definen en la función `deps/0` y se gestionan con Hex (el gestor de paquetes de Elixir):

```elixir
defp deps do
  [
    # Desde Hex (registro público)
    {:phoenix, "~> 1.7"},

    # Versión exacta
    {:jason, "1.4.1"},

    # Desde GitHub
    {:mi_lib, github: "usuario/mi_lib", branch: "main"},

    # Dependencia local
    {:utils, path: "../utils"},

    # Solo para ciertos entornos
    {:dialyxir, "~> 1.4", only: :dev, runtime: false},

    # Dependencia opcional
    {:plug_cowboy, "~> 2.7", optional: true}
  ]
end
```

Comandos de dependencias:

```elixir
# mix deps.get       — Descargar dependencias
# mix deps.update     — Actualizar dependencias
# mix deps.tree       — Ver árbol de dependencias
# mix deps.clean      — Limpiar dependencias
# mix hex.info jason  — Info de un paquete en Hex
```

## Configuración y Entornos

Elixir soporta tres entornos por defecto: `:dev`, `:test` y `:prod`:

```elixir
# config/config.exs — Configuración compartida
import Config

config :mi_app,
  nombre: "Mi Aplicación",
  version: "1.0.0"

config :logger, level: :info

# Importar configuración específica del entorno
import_config "#{config_env()}.exs"
```

```elixir
# config/dev.exs
import Config

config :mi_app, MiApp.Repo,
  database: "mi_app_dev",
  hostname: "localhost",
  pool_size: 10

config :logger, level: :debug
```

```elixir
# config/runtime.exs — Se ejecuta en runtime
import Config

if config_env() == :prod do
  config :mi_app, MiApp.Repo,
    url: System.get_env("DATABASE_URL"),
    pool_size: String.to_integer(System.get_env("POOL_SIZE") || "10")
end
```

## Tareas Mix

Mix incluye muchas tareas útiles y permite crear las propias:

```elixir
# Tareas comunes:
# mix compile          — Compilar el proyecto
# mix test             — Ejecutar tests
# mix format           — Formatear código
# mix credo            — Análisis estático
# mix dialyzer         — Verificación de tipos
# mix docs             — Generar documentación
# mix run archivo.exs  — Ejecutar script
# mix phx.server       — Iniciar servidor Phoenix

# Crear una tarea personalizada
defmodule Mix.Tasks.MiApp.Seed do
  @moduledoc "Poblar la base de datos con datos de prueba"
  use Mix.Task

  @shortdoc "Ejecuta los seeds de la base de datos"

  @impl Mix.Task
  def run(args) do
    Mix.Task.run("app.start")

    case args do
      ["--reset"] ->
        IO.puts("Reseteando y sembrando...")
        MiApp.Seeds.reset_and_seed()
      _ ->
        IO.puts("Sembrando datos...")
        MiApp.Seeds.seed()
    end
  end
end

# Uso: mix mi_app.seed --reset
```

## Hex: El Gestor de Paquetes

Hex es el registro de paquetes para el ecosistema Erlang/Elixir:

```elixir
# Buscar paquetes
# mix hex.search json

# Información de un paquete
# mix hex.info phoenix

# Publicar un paquete propio
# mix hex.publish

# Ver paquetes desactualizados
# mix hex.outdated
```

## El Formateador

Elixir incluye un formateador oficial configurado en `.formatter.exs`:

```elixir
# .formatter.exs
[
  inputs: [
    "{mix,.formatter}.exs",
    "{config,lib,test}/**/*.{ex,exs}"
  ],
  line_length: 100,
  import_deps: [:ecto, :phoenix]
]

# Formatear todo el proyecto
# mix format

# Verificar sin modificar
# mix format --check-formatted
```

## Resumen

Mix es una herramienta completa que centraliza todas las operaciones del ciclo de desarrollo en Elixir. Desde la creación del proyecto con `mix new` hasta el deploy, pasando por gestión de dependencias con Hex, configuración por entornos, tareas personalizadas y formateo automático del código. Dominar Mix no solo mejora la productividad sino que asegura que los proyectos sigan las convenciones del ecosistema Elixir.
