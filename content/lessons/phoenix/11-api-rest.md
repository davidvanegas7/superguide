# APIs REST y JSON en Phoenix

## Introducción

Phoenix ofrece herramientas poderosas para construir APIs REST robustas y eficientes. Gracias a su pipeline `:api` y sus vistas JSON, podemos crear servicios backend de alto rendimiento.

## Pipeline :api

El router de Phoenix permite definir pipelines específicas para APIs:

```elixir
pipeline :api do
  plug :accepts, ["json"]
  plug :fetch_session
  plug MyApp.Plugs.AuthenticateAPI
end

scope "/api", MyAppWeb do
  pipe_through :api

  resources "/productos", ProductoController, except: [:new, :edit]
  resources "/usuarios", UsuarioController, only: [:index, :show, :create]
end
```

## JSON Views y Render

Phoenix usa vistas dedicadas para serializar datos a JSON:

```elixir
defmodule MyAppWeb.ProductoJSON do
  def index(%{productos: productos}) do
    %{data: for(producto <- productos, do: data(producto))}
  end

  def show(%{producto: producto}) do
    %{data: data(producto)}
  end

  defp data(producto) do
    %{
      id: producto.id,
      nombre: producto.nombre,
      precio: producto.precio,
      categoria: producto.categoria,
      en_stock: producto.en_stock
    }
  end
end
```

En el controlador renderizamos así:

```elixir
defmodule MyAppWeb.ProductoController do
  use MyAppWeb, :controller

  alias MyApp.Catalogo

  def index(conn, _params) do
    productos = Catalogo.list_productos()
    render(conn, :index, productos: productos)
  end

  def show(conn, %{"id" => id}) do
    producto = Catalogo.get_producto!(id)
    render(conn, :show, producto: producto)
  end

  def create(conn, %{"producto" => producto_params}) do
    with {:ok, producto} <- Catalogo.create_producto(producto_params) do
      conn
      |> put_status(:created)
      |> put_resp_header("location", ~p"/api/productos/#{producto}")
      |> render(:show, producto: producto)
    end
  end
end
```

## Fallback Controllers

Los fallback controllers centralizan el manejo de errores:

```elixir
defmodule MyAppWeb.FallbackController do
  use MyAppWeb, :controller

  def call(conn, {:error, :not_found}) do
    conn
    |> put_status(:not_found)
    |> put_view(json: MyAppWeb.ErrorJSON)
    |> render(:"404")
  end

  def call(conn, {:error, %Ecto.Changeset{} = changeset}) do
    conn
    |> put_status(:unprocessable_entity)
    |> put_view(json: MyAppWeb.ChangesetJSON)
    |> render(:error, changeset: changeset)
  end
end
```

Luego en el controlador usamos `action_fallback`:

```elixir
defmodule MyAppWeb.ProductoController do
  use MyAppWeb, :controller

  action_fallback MyAppWeb.FallbackController

  def show(conn, %{"id" => id}) do
    with {:ok, producto} <- Catalogo.fetch_producto(id) do
      render(conn, :show, producto: producto)
    end
  end
end
```

## Versionado de API

Organizamos versiones con scopes y módulos separados:

```elixir
scope "/api/v1", MyAppWeb.V1 do
  pipe_through :api
  resources "/productos", ProductoController
end

scope "/api/v2", MyAppWeb.V2 do
  pipe_through [:api, :v2_transforms]
  resources "/productos", ProductoController
end
```

## Paginación

Implementamos paginación con parámetros de query:

```elixir
def list_productos(params) do
  page = Map.get(params, "page", "1") |> String.to_integer()
  per_page = Map.get(params, "per_page", "20") |> String.to_integer()

  query = from p in Producto, order_by: [desc: p.inserted_at]

  total = Repo.aggregate(query, :count)
  productos = query |> limit(^per_page) |> offset(^((page - 1) * per_page)) |> Repo.all()

  %{data: productos, meta: %{page: page, per_page: per_page, total: total}}
end
```

## Respuestas de Error Estandarizadas

```elixir
defmodule MyAppWeb.ErrorJSON do
  def render("404.json", _assigns) do
    %{errors: %{detail: "Recurso no encontrado"}}
  end

  def render("500.json", _assigns) do
    %{errors: %{detail: "Error interno del servidor"}}
  end
end
```

## Resumen

Las APIs REST en Phoenix se construyen con pipelines dedicadas, vistas JSON para serialización, fallback controllers para errores centralizados, versionado mediante scopes y paginación eficiente. Esta arquitectura produce APIs limpias, mantenibles y de alto rendimiento.
