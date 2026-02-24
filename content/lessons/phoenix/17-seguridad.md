# Seguridad en Phoenix

## Introducción

Phoenix incluye protecciones de seguridad de forma predeterminada y facilita la implementación de medidas adicionales. Desde CSRF hasta CSP headers, la seguridad es una prioridad del framework.

## Protección CSRF

Phoenix protege automáticamente contra Cross-Site Request Forgery:

```elixir
# En el endpoint (habilitado por defecto)
plug Plug.CSRFProtection

# En formularios HTML, el token se incluye automáticamente
<.form for={@form} action={~p"/productos"} method="post">
  <!-- csrf_token se agrega automáticamente -->
  <.input field={@form[:nombre]} label="Nombre" />
  <.button>Guardar</.button>
</.form>

# Para APIs, se desactiva en la pipeline :api
pipeline :api do
  plug :accepts, ["json"]
  # No incluye Plug.CSRFProtection
end
```

Para peticiones AJAX, incluimos el token en los headers:

```elixir
# En app.js
let csrfToken = document.querySelector("meta[name='csrf-token']").getAttribute("content")

let liveSocket = new LiveSocket("/live", Socket, {
  params: {_csrf_token: csrfToken}
})
```

## CSP Headers

Content Security Policy previene inyección de scripts maliciosos:

```elixir
defmodule MyApp.Plugs.SecurityHeaders do
  import Plug.Conn

  def init(opts), do: opts

  def call(conn, _opts) do
    nonce = Base.encode64(:crypto.strong_rand_bytes(16))

    conn
    |> assign(:csp_nonce, nonce)
    |> put_resp_header("content-security-policy",
      "default-src 'self'; " <>
      "script-src 'self' 'nonce-#{nonce}'; " <>
      "style-src 'self' 'unsafe-inline'; " <>
      "img-src 'self' data: https:; " <>
      "font-src 'self'; " <>
      "connect-src 'self' wss://#{conn.host}; " <>
      "frame-ancestors 'none'")
    |> put_resp_header("x-content-type-options", "nosniff")
    |> put_resp_header("x-frame-options", "DENY")
    |> put_resp_header("x-xss-protection", "1; mode=block")
    |> put_resp_header("referrer-policy", "strict-origin-when-cross-origin")
    |> put_resp_header("permissions-policy", "camera=(), microphone=(), geolocation=()")
  end
end
```

## Rate Limiting

Limitación de tasa para prevenir abuso:

```elixir
defmodule MyApp.Plugs.RateLimiter do
  import Plug.Conn
  alias MyApp.RateStore

  def init(opts), do: Map.new(opts)

  def call(conn, %{max: max, window: window, by: by_fn}) do
    key = by_fn.(conn)
    bucket = "rate:#{key}:#{div(System.system_time(:second), window)}"

    case RateStore.increment(bucket, window) do
      count when count <= max ->
        conn
        |> put_resp_header("x-ratelimit-limit", to_string(max))
        |> put_resp_header("x-ratelimit-remaining", to_string(max - count))

      _ ->
        conn
        |> put_status(429)
        |> Phoenix.Controller.json(%{error: "Demasiadas peticiones"})
        |> halt()
    end
  end
end

# Uso en router
pipeline :rate_limited do
  plug MyApp.Plugs.RateLimiter,
    max: 100,
    window: 60,
    by: &("#{:inet.ntoa(&1.remote_ip)}")
end
```

## CORS con cors_plug

Configurar Cross-Origin Resource Sharing:

```elixir
# mix.exs
{:cors_plug, "~> 3.0"}

# En el endpoint o router
plug CORSPlug,
  origin: ["https://miapp.com", "https://admin.miapp.com"],
  methods: ["GET", "POST", "PUT", "DELETE"],
  headers: ["Authorization", "Content-Type"],
  max_age: 86400

# O configuración dinámica
plug CORSPlug, origin: &MyApp.CORSConfig.allowed_origins/0
```

## Gestión de Secretos

Manejo seguro de credenciales y configuración sensible:

```elixir
# config/runtime.exs - secretos desde variables de entorno
config :my_app, MyAppWeb.Endpoint,
  secret_key_base: System.fetch_env!("SECRET_KEY_BASE")

config :my_app, MyApp.Repo,
  url: System.fetch_env!("DATABASE_URL"),
  pool_size: String.to_integer(System.get_env("POOL_SIZE") || "10")

config :my_app, MyApp.Mailer,
  api_key: System.fetch_env!("SENDGRID_API_KEY")
```

Nunca hardcodear secretos en el código:

```elixir
# MAL - nunca hacer esto
config :my_app, api_key: "sk_live_abc123secreto"

# BIEN - usar variables de entorno
config :my_app, api_key: System.fetch_env!("API_KEY")
```

## Configuración SSL

Habilitar HTTPS en producción:

```elixir
# config/runtime.exs
config :my_app, MyAppWeb.Endpoint,
  url: [host: "miapp.com", port: 443, scheme: "https"],
  https: [
    port: 443,
    cipher_suite: :strong,
    keyfile: System.get_env("SSL_KEY_PATH"),
    certfile: System.get_env("SSL_CERT_PATH")
  ]

# Forzar HTTPS con plug
plug Plug.SSL,
  rewrite_on: [:x_forwarded_proto],
  hsts: true,
  expires: 31_536_000
```

## Checklist de Seguridad

Verificaciones esenciales antes de ir a producción:

```elixir
# 1. Validar y sanitizar inputs
def changeset(struct, params) do
  struct
  |> cast(params, [:nombre, :email])
  |> validate_required([:nombre, :email])
  |> validate_format(:email, ~r/^[^\s]+@[^\s]+\.[^\s]+$/)
  |> validate_length(:nombre, max: 100)
  |> unique_constraint(:email)
end

# 2. Usar consultas parametrizadas (Ecto lo hace por defecto)
# BIEN
Repo.all(from u in User, where: u.email == ^email)

# MAL - nunca interpolar directamente
# Repo.query("SELECT * FROM users WHERE email = '#{email}'")

# 3. Hash de passwords con bcrypt
def registrar(attrs) do
  %Usuario{}
  |> cast(attrs, [:email, :password])
  |> validate_length(:password, min: 12)
  |> put_pass_hash()
  |> Repo.insert()
end

defp put_pass_hash(%{valid?: true, changes: %{password: pw}} = cs) do
  put_change(cs, :password_hash, Bcrypt.hash_pwd_salt(pw))
end
```

## Resumen

La seguridad en Phoenix abarca CSRF automático, CSP headers personalizados, rate limiting, CORS configurado, gestión de secretos vía variables de entorno, SSL/HTTPS y un checklist que incluye validación de inputs, consultas parametrizadas y hashing de passwords. Phoenix facilita implementar estas medidas con su arquitectura de plugs.
