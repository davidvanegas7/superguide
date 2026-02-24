# Introducción a Phoenix Framework

Phoenix es un framework web escrito en Elixir que aprovecha la máquina virtual BEAM para construir aplicaciones web de alto rendimiento, escalables y tolerantes a fallos. Su diseño está inspirado en frameworks como Ruby on Rails, pero con las ventajas únicas del ecosistema Erlang/Elixir.

## ¿Qué es Phoenix?

Phoenix es un framework MVC (Model-View-Controller) que facilita el desarrollo de aplicaciones web modernas. Combina productividad del desarrollador con rendimiento excepcional gracias a la concurrencia nativa de BEAM.

```elixir
# Phoenix permite manejar millones de conexiones simultáneas
# gracias al modelo de actores de la BEAM
defmodule MiAppWeb.PageController do
  use MiAppWeb, :controller

  def index(conn, _params) do
    render(conn, :index)
  end
end
```

## Ventajas de BEAM para la Web

La máquina virtual BEAM ofrece características únicas para aplicaciones web:

- **Concurrencia masiva**: cada conexión se maneja en un proceso ligero independiente.
- **Tolerancia a fallos**: los supervisores reinician procesos fallidos automáticamente.
- **Hot code reloading**: actualizar código sin detener el servidor.
- **Baja latencia**: tiempos de respuesta predecibles bajo carga.

```elixir
# Cada request se ejecuta en su propio proceso BEAM
# Si un proceso falla, no afecta a los demás
# El supervisor se encarga de reiniciarlo
defmodule MiApp.Application do
  use Application

  def start(_type, _args) do
    children = [
      MiAppWeb.Telemetry,
      MiApp.Repo,
      MiAppWeb.Endpoint
    ]

    opts = [strategy: :one_for_one, name: MiApp.Supervisor]
    Supervisor.start_link(children, opts)
  end
end
```

## Crear un Proyecto con mix phx.new

Para crear un nuevo proyecto Phoenix, usamos el generador oficial:

```elixir
# Instalar el generador de Phoenix
mix archive.install hex phx_new

# Crear un nuevo proyecto
mix phx.new mi_app

# Crear proyecto sin base de datos
mix phx.new mi_app --no-ecto

# Crear proyecto solo como API (sin HTML ni assets)
mix phx.new mi_app --no-html --no-assets
```

Después de crear el proyecto, configuramos la base de datos y arrancamos el servidor:

```elixir
cd mi_app
mix setup        # Instala dependencias, crea y migra la BD
mix phx.server   # Inicia el servidor en localhost:4000
```

## Estructura del Proyecto

Un proyecto Phoenix tiene una estructura bien organizada:

```elixir
mi_app/
├── lib/
│   ├── mi_app/           # Lógica de negocio (contextos, schemas)
│   │   ├── application.ex
│   │   └── repo.ex
│   └── mi_app_web/       # Capa web (controllers, views, templates)
│       ├── endpoint.ex
│       ├── router.ex
│       ├── controllers/
│       ├── components/
│       └── layouts/
├── priv/
│   ├── repo/migrations/  # Migraciones de base de datos
│   └── static/           # Archivos estáticos
├── config/               # Configuración por entorno
├── test/                 # Tests
└── mix.exs               # Dependencias y configuración del proyecto
```

## Ciclo de Vida de un Request

Cada solicitud HTTP en Phoenix sigue un flujo bien definido a través de varias capas:

```elixir
# 1. Endpoint: punto de entrada, aplica plugs globales
# 2. Router: determina qué controller manejar la solicitud
# 3. Pipeline: aplica plugs específicos (autenticación, formato)
# 4. Controller: procesa la solicitud y prepara la respuesta
# 5. View/Component: renderiza la respuesta final

# El flujo completo:
# Request HTTP → Endpoint → Router → Pipeline → Controller → View → Response
```

## Endpoint, Router, Controller y View

Estos son los cuatro componentes principales de la capa web:

```elixir
# Endpoint (lib/mi_app_web/endpoint.ex)
# Punto de entrada de todas las solicitudes
defmodule MiAppWeb.Endpoint do
  use Phoenix.Endpoint, otp_app: :mi_app
  plug Plug.Static, at: "/", from: :mi_app
  plug Plug.Parsers, parsers: [:urlencoded, :json]
  plug MiAppWeb.Router
end

# Router (lib/mi_app_web/router.ex)
defmodule MiAppWeb.Router do
  use MiAppWeb, :router

  scope "/", MiAppWeb do
    pipe_through :browser
    get "/", PageController, :index
  end
end

# Controller (lib/mi_app_web/controllers/page_controller.ex)
defmodule MiAppWeb.PageController do
  use MiAppWeb, :controller

  def index(conn, _params) do
    render(conn, :index, mensaje: "¡Bienvenido a Phoenix!")
  end
end
```

## Resumen

Phoenix Framework es una herramienta poderosa para desarrollo web que combina la productividad de un framework moderno con el rendimiento de la BEAM. Su arquitectura basada en Endpoint, Router, Controller y View facilita la organización del código. El generador `mix phx.new` permite arrancar proyectos rápidamente, y el ciclo de vida del request garantiza un flujo predecible y extensible para cada solicitud HTTP.
