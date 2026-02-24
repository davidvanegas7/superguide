# Plugs y Middleware en Phoenix

## Introducción

Los Plugs son el corazón del manejo de peticiones HTTP en Phoenix. Cada plug recibe una conexión (`Plug.Conn`), la transforma y la devuelve. Son componentes composables que forman el pipeline de procesamiento.

## Plug.Conn

La estructura `%Plug.Conn{}` representa la conexión HTTP completa:

```elixir
# Campos importantes de Plug.Conn
%Plug.Conn{
  host: "localhost",
  method: "GET",
  path_info: ["api", "usuarios"],
  request_path: "/api/usuarios",
  query_params: %{"page" => "1"},
  body_params: %{},
  assigns: %{},
  status: nil,
  resp_body: nil,
  halted: false
}
```

Podemos inspeccionar y modificar la conexión con funciones del módulo `Plug.Conn`:

```elixir
conn
|> put_status(200)
|> put_resp_content_type("application/json")
|> assign(:usuario_actual, usuario)
|> send_resp(200, Jason.encode!(%{ok: true}))
```

## Function Plugs

Los function plugs son funciones simples que reciben `conn` y `opts`:

```elixir
defmodule MyAppWeb.ProductoController do
  use MyAppWeb, :controller

  plug :validar_admin when action in [:create, :update, :delete]

  def index(conn, _params) do
    render(conn, :index, productos: Catalogo.list_productos())
  end

  defp validar_admin(conn, _opts) do
    if conn.assigns[:usuario_actual] && conn.assigns.usuario_actual.rol == :admin do
      conn
    else
      conn
      |> put_status(:forbidden)
      |> put_view(json: MyAppWeb.ErrorJSON)
      |> render(:"403")
      |> halt()
    end
  end
end
```

## Module Plugs: init/1 y call/2

Los module plugs implementan dos callbacks: `init/1` (compilación) y `call/2` (ejecución):

```elixir
defmodule MyApp.Plugs.Locale do
  import Plug.Conn

  def init(default_locale), do: default_locale

  def call(conn, default_locale) do
    locale =
      conn
      |> get_req_header("accept-language")
      |> List.first()
      |> parse_locale()
      |> Kernel.||(default_locale)

    Gettext.put_locale(MyAppWeb.Gettext, locale)
    assign(conn, :locale, locale)
  end

  defp parse_locale(nil), do: nil
  defp parse_locale(header), do: header |> String.slice(0, 2)
end
```

## Plug.Builder

`Plug.Builder` permite componer múltiples plugs en un módulo:

```elixir
defmodule MyApp.Plugs.APISetup do
  use Plug.Builder

  plug Plug.Logger
  plug Plug.Parsers,
    parsers: [:json],
    json_decoder: Jason
  plug :set_formato

  defp set_formato(conn, _opts) do
    put_resp_content_type(conn, "application/json")
  end
end
```

## Composición en Pipelines

Las pipelines del router componen plugs de forma declarativa:

```elixir
pipeline :auth_api do
  plug :accepts, ["json"]
  plug MyApp.Plugs.AuthenticateToken
  plug MyApp.Plugs.Locale, "es"
  plug MyApp.Plugs.RateLimiter, max_requests: 100, window_ms: 60_000
end

scope "/api", MyAppWeb do
  pipe_through [:auth_api]
  resources "/cursos", CursoController
end
```

## Custom Auth Plug

Un plug de autenticación basado en tokens Bearer:

```elixir
defmodule MyApp.Plugs.AuthenticateToken do
  import Plug.Conn

  def init(opts), do: opts

  def call(conn, _opts) do
    with ["Bearer " <> token] <- get_req_header(conn, "authorization"),
         {:ok, usuario} <- MyApp.Cuentas.verificar_token(token) do
      assign(conn, :usuario_actual, usuario)
    else
      _ ->
        conn
        |> put_status(:unauthorized)
        |> Phoenix.Controller.json(%{error: "Token inválido o ausente"})
        |> halt()
    end
  end
end
```

## Rate Limiting Plug

Un plug para limitar la tasa de peticiones:

```elixir
defmodule MyApp.Plugs.RateLimiter do
  import Plug.Conn

  def init(opts) do
    %{
      max_requests: Keyword.get(opts, :max_requests, 60),
      window_ms: Keyword.get(opts, :window_ms, 60_000)
    }
  end

  def call(conn, %{max_requests: max, window_ms: window}) do
    key = "rate_limit:#{client_ip(conn)}"

    case MyApp.RateStore.check_rate(key, max, window) do
      {:ok, count} ->
        conn
        |> put_resp_header("x-ratelimit-limit", "#{max}")
        |> put_resp_header("x-ratelimit-remaining", "#{max - count}")

      {:error, :rate_exceeded} ->
        conn
        |> put_status(:too_many_requests)
        |> Phoenix.Controller.json(%{error: "Límite de peticiones excedido"})
        |> halt()
    end
  end

  defp client_ip(conn), do: conn.remote_ip |> :inet.ntoa() |> to_string()
end
```

## Resumen

Los Plugs son el mecanismo fundamental de Phoenix para procesar peticiones HTTP. Existen como funciones simples o módulos con `init/call`, se componen con `Plug.Builder` y pipelines del router. Permiten crear middleware reutilizable para autenticación, rate limiting, localización y cualquier transformación de la conexión.
