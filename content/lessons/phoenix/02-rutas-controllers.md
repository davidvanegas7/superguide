# Rutas y Controllers en Phoenix

El sistema de enrutamiento de Phoenix es potente y expresivo. Permite definir rutas RESTful, agruparlas en scopes y aplicar pipelines de plugs para transformar las solicitudes antes de que lleguen a los controllers.

## El Archivo router.ex

El router es el corazón del enrutamiento en Phoenix. Define cómo las URLs se mapean a acciones de controllers:

```elixir
defmodule MiAppWeb.Router do
  use MiAppWeb, :router

  pipeline :browser do
    plug :accepts, ["html"]
    plug :fetch_session
    plug :fetch_live_flash
    plug :put_root_layout, html: {MiAppWeb.Layouts, :root}
    plug :protect_from_forgery
    plug :put_secure_browser_headers
  end

  pipeline :api do
    plug :accepts, ["json"]
  end

  scope "/", MiAppWeb do
    pipe_through :browser
    get "/", PageController, :index
  end
end
```

## La Macro resources

La macro `resources` genera automáticamente las siete rutas RESTful estándar para un recurso:

```elixir
scope "/", MiAppWeb do
  pipe_through :browser

  resources "/usuarios", UsuarioController
  # Genera:
  # GET     /usuarios           -> :index
  # GET     /usuarios/new       -> :new
  # POST    /usuarios           -> :create
  # GET     /usuarios/:id       -> :show
  # GET     /usuarios/:id/edit  -> :edit
  # PUT     /usuarios/:id       -> :update
  # DELETE  /usuarios/:id       -> :delete

  # También puedes limitar las acciones generadas
  resources "/posts", PostController, only: [:index, :show]
  resources "/comentarios", ComentarioController, except: [:delete]
end
```

## Pipelines :browser y :api

Las pipelines son conjuntos de plugs que se aplican a grupos de rutas según el tipo de solicitud:

```elixir
# Pipeline para solicitudes del navegador (HTML)
pipeline :browser do
  plug :accepts, ["html"]
  plug :fetch_session
  plug :fetch_live_flash
  plug :protect_from_forgery
  plug :put_secure_browser_headers
end

# Pipeline para solicitudes de API (JSON)
pipeline :api do
  plug :accepts, ["json"]
  plug MiAppWeb.Plugs.VerificarApiKey
end

# Puedes crear pipelines personalizadas
pipeline :admin do
  plug MiAppWeb.Plugs.RequiereAdmin
end
```

## Scopes

Los scopes agrupan rutas bajo un prefijo de URL y un módulo base:

```elixir
# Scope para páginas públicas
scope "/", MiAppWeb do
  pipe_through :browser
  get "/", PageController, :index
end

# Scope para API versionada
scope "/api/v1", MiAppWeb.API.V1 do
  pipe_through :api
  resources "/productos", ProductoController
  resources "/pedidos", PedidoController
end

# Scope para administración con pipeline extra
scope "/admin", MiAppWeb.Admin do
  pipe_through [:browser, :admin]
  resources "/usuarios", UsuarioController
  resources "/reportes", ReporteController
end
```

## Acciones del Controller

Los controllers agrupan funciones (acciones) que manejan solicitudes HTTP:

```elixir
defmodule MiAppWeb.ProductoController do
  use MiAppWeb, :controller

  alias MiApp.Catalogo

  def index(conn, _params) do
    productos = Catalogo.listar_productos()
    render(conn, :index, productos: productos)
  end

  def show(conn, %{"id" => id}) do
    producto = Catalogo.obtener_producto!(id)
    render(conn, :show, producto: producto)
  end

  def create(conn, %{"producto" => producto_params}) do
    case Catalogo.crear_producto(producto_params) do
      {:ok, producto} ->
        conn
        |> put_flash(:info, "Producto creado exitosamente.")
        |> redirect(to: ~p"/productos/#{producto}")

      {:error, %Ecto.Changeset{} = changeset} ->
        render(conn, :new, changeset: changeset)
    end
  end
end
```

## El Struct conn y params

El struct `conn` (`Plug.Conn`) contiene toda la información de la solicitud y la respuesta:

```elixir
def show(conn, params) do
  # params contiene los parámetros de la URL y query string
  id = params["id"]

  # conn tiene información sobre la solicitud
  metodo = conn.method          # "GET", "POST", etc.
  host = conn.host              # "localhost"
  ruta = conn.request_path      # "/productos/1"
  cabeceras = conn.req_headers  # lista de tuplas {clave, valor}

  # conn también almacena datos asignados
  usuario = conn.assigns[:usuario_actual]

  render(conn, :show, id: id)
end
```

## Render, Redirect y JSON

Phoenix ofrece varias formas de generar respuestas desde un controller:

```elixir
defmodule MiAppWeb.EjemploController do
  use MiAppWeb, :controller

  # Renderizar una plantilla HTML
  def pagina(conn, _params) do
    render(conn, :pagina, titulo: "Mi Página")
  end

  # Redirigir a otra ruta
  def redirigir(conn, _params) do
    conn
    |> put_flash(:info, "Redirigido correctamente")
    |> redirect(to: ~p"/destino")
  end

  # Responder con JSON (para APIs)
  def datos(conn, _params) do
    datos = %{nombre: "Phoenix", version: "1.7"}
    json(conn, datos)
  end

  # Enviar respuesta con status personalizado
  def no_encontrado(conn, _params) do
    conn
    |> put_status(:not_found)
    |> json(%{error: "Recurso no encontrado"})
  end
end
```

## Resumen

El sistema de rutas de Phoenix es flexible y expresivo. El `router.ex` define cómo las URLs se mapean a controllers usando macros como `resources`, scopes para agrupar rutas y pipelines para aplicar transformaciones. Los controllers reciben el struct `conn` con toda la información de la solicitud y los `params` extraídos de la URL, y pueden responder con `render`, `redirect` o `json` según las necesidades de la aplicación.
