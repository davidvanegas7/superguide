# Autenticación en Phoenix

Phoenix incluye un generador de autenticación completo que crea todo el sistema de registro, login y gestión de sesiones. Este generador produce código que vive en tu proyecto, permitiéndote personalizarlo completamente según tus necesidades.

## mix phx.gen.auth

El generador `mix phx.gen.auth` crea un sistema de autenticación completo basado en sesiones:

```elixir
# Generar el sistema de autenticación
mix phx.gen.auth Cuentas Usuario usuarios

# Este comando genera:
# - Schema de Usuario con campos email y hashed_password
# - Contexto Cuentas con funciones de registro, login, etc.
# - Controller de sesión (UserSessionController)
# - LiveViews para registro y login
# - Migraciones para la tabla usuarios y tokens
# - Plugs para autenticación
# - Tests completos

# Ejecutar la migración generada
mix ecto.migrate
```

## User Schema

El schema de usuario generado incluye campos para email y contraseña hasheada:

```elixir
defmodule MiApp.Cuentas.Usuario do
  use Ecto.Schema
  import Ecto.Changeset

  schema "usuarios" do
    field :email, :string
    field :password, :string, virtual: true, redact: true
    field :hashed_password, :string, redact: true
    field :confirmed_at, :naive_datetime
    timestamps()
  end

  def registration_changeset(usuario, attrs, opts \\ []) do
    usuario
    |> cast(attrs, [:email, :password])
    |> validate_email()
    |> validate_password(opts)
  end

  defp validate_email(changeset) do
    changeset
    |> validate_required([:email])
    |> validate_format(:email, ~r/^[^\s]+@[^\s]+$/)
    |> validate_length(:email, max: 160)
    |> unsafe_validate_unique(:email, MiApp.Repo)
    |> unique_constraint(:email)
  end

  defp validate_password(changeset, opts) do
    changeset
    |> validate_required([:password])
    |> validate_length(:password, min: 12, max: 72)
    |> maybe_hash_password(opts)
  end

  defp maybe_hash_password(changeset, opts) do
    if hash = Keyword.get(opts, :hash_password, true) && changeset.valid? do
      changeset
      |> put_change(:hashed_password, Bcrypt.hash_pwd_salt(get_change(changeset, :password)))
      |> delete_change(:password)
    else
      changeset
    end
  end
end
```

## Session Controller

El controller de sesión maneja el login y logout de usuarios:

```elixir
defmodule MiAppWeb.UsuarioSessionController do
  use MiAppWeb, :controller

  alias MiApp.Cuentas

  def create(conn, %{"usuario" => usuario_params}) do
    %{"email" => email, "password" => password} = usuario_params

    if usuario = Cuentas.get_user_by_email_and_password(email, password) do
      conn
      |> put_flash(:info, "Bienvenido de vuelta.")
      |> MiAppWeb.UsuarioAuth.log_in_user(usuario, usuario_params)
    else
      conn
      |> put_flash(:error, "Email o contraseña inválidos.")
      |> redirect(to: ~p"/usuarios/log_in")
    end
  end

  def delete(conn, _params) do
    conn
    |> put_flash(:info, "Sesión cerrada exitosamente.")
    |> MiAppWeb.UsuarioAuth.log_out_user()
  end
end
```

## Autenticación Basada en Tokens

Para APIs, es común usar autenticación basada en tokens en lugar de sesiones:

```elixir
defmodule MiApp.Cuentas do
  alias MiApp.Cuentas.UsuarioToken

  def crear_token_api(usuario) do
    {token_encoded, usuario_token} = UsuarioToken.build_email_token(usuario, "api")
    MiApp.Repo.insert!(usuario_token)
    token_encoded
  end

  def obtener_usuario_por_token_api(token) do
    case UsuarioToken.verify_email_token_query(token, "api") do
      {:ok, query} -> MiApp.Repo.one(query)
      _ -> nil
    end
  end
end

# Controller de API para generar tokens
defmodule MiAppWeb.API.AuthController do
  use MiAppWeb, :controller

  alias MiApp.Cuentas

  def login(conn, %{"email" => email, "password" => password}) do
    case Cuentas.get_user_by_email_and_password(email, password) do
      nil ->
        conn |> put_status(:unauthorized) |> json(%{error: "Credenciales inválidas"})

      usuario ->
        token = Cuentas.crear_token_api(usuario)
        json(conn, %{token: token, usuario_id: usuario.id})
    end
  end
end
```

## Plugs de Autenticación

Los plugs de autenticación se integran en el pipeline del router para proteger rutas:

```elixir
defmodule MiAppWeb.UsuarioAuth do
  import Plug.Conn
  import Phoenix.Controller

  def log_in_user(conn, usuario, params \\ %{}) do
    token = MiApp.Cuentas.generate_user_session_token(usuario)

    conn
    |> renew_session()
    |> put_session(:user_token, token)
    |> put_session(:live_socket_id, "users_sessions:#{Base.url_encode64(token)}")
    |> maybe_write_remember_me_cookie(token, params)
    |> redirect(to: signed_in_path(conn))
  end

  def fetch_current_user(conn, _opts) do
    {user_token, conn} = ensure_user_token(conn)
    user = user_token && MiApp.Cuentas.get_user_by_session_token(user_token)
    assign(conn, :current_user, user)
  end

  def log_out_user(conn) do
    if user_token = get_session(conn, :user_token) do
      MiApp.Cuentas.delete_user_session_token(user_token)
    end

    conn
    |> renew_session()
    |> redirect(to: ~p"/")
  end
end
```

## require_authenticated_user

Este plug asegura que solo usuarios autenticados accedan a ciertas rutas:

```elixir
defmodule MiAppWeb.UsuarioAuth do
  def require_authenticated_user(conn, _opts) do
    if conn.assigns[:current_user] do
      conn
    else
      conn
      |> put_flash(:error, "Debes iniciar sesión para acceder a esta página.")
      |> maybe_store_return_to()
      |> redirect(to: ~p"/usuarios/log_in")
      |> halt()
    end
  end
end

# En el router
defmodule MiAppWeb.Router do
  use MiAppWeb, :router

  pipeline :browser do
    # ... plugs estándar
    plug :fetch_current_user
  end

  # Rutas que requieren autenticación
  scope "/", MiAppWeb do
    pipe_through [:browser, :require_authenticated_user]
    resources "/configuracion", ConfiguracionController
    live "/dashboard", DashboardLive
  end

  # Rutas públicas
  scope "/", MiAppWeb do
    pipe_through :browser
    get "/", PageController, :index
    live "/usuarios/log_in", UsuarioLoginLive
    live "/usuarios/register", UsuarioRegistrationLive
  end
end
```

## Guardian Overview

Guardian es una biblioteca externa popular para autenticación basada en JWT:

```elixir
# Agregar Guardian en mix.exs
defp deps do
  [{:guardian, "~> 2.3"}]
end

# Configurar el módulo Guardian
defmodule MiApp.Guardian do
  use Guardian, otp_app: :mi_app

  def subject_for_token(usuario, _claims) do
    {:ok, to_string(usuario.id)}
  end

  def resource_from_claims(%{"sub" => id}) do
    case MiApp.Cuentas.obtener_usuario(id) do
      nil -> {:error, :recurso_no_encontrado}
      usuario -> {:ok, usuario}
    end
  end
end

# Generar y verificar tokens JWT
{:ok, token, _claims} = MiApp.Guardian.encode_and_sign(usuario)
{:ok, claims} = MiApp.Guardian.decode_and_verify(token)
{:ok, usuario} = MiApp.Guardian.resource_from_claims(claims)
```

## Resumen

Phoenix ofrece `mix phx.gen.auth` para generar un sistema de autenticación completo basado en sesiones. El schema de usuario maneja hashing seguro de contraseñas, el session controller gestiona login/logout, y los plugs como `require_authenticated_user` protegen rutas. Para APIs, se puede implementar autenticación basada en tokens, y para casos más avanzados con JWT, Guardian es la biblioteca más usada en el ecosistema Elixir.
