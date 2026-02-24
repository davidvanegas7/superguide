# Ecto y Bases de Datos en Elixir

Ecto es la biblioteca principal de Elixir para interactuar con bases de datos. No es un ORM tradicional sino un toolkit que proporciona esquemas, changesets para validación, un DSL de queries composable y migraciones. Ecto sigue la filosofía explícita de Elixir: nada sucede por arte de magia.

## Configuración del Repo

El Repo es el punto de entrada para todas las operaciones con la base de datos:

```elixir
# lib/mi_app/repo.ex
defmodule MiApp.Repo do
  use Ecto.Repo,
    otp_app: :mi_app,
    adapter: Ecto.Adapters.Postgres
end

# config/dev.exs
config :mi_app, MiApp.Repo,
  username: "postgres",
  password: "postgres",
  hostname: "localhost",
  database: "mi_app_dev",
  pool_size: 10
```

Comandos de base de datos:

```elixir
# mix ecto.create    — Crear la base de datos
# mix ecto.drop      — Eliminar la base de datos
# mix ecto.migrate   — Ejecutar migraciones
# mix ecto.rollback  — Revertir última migración
# mix ecto.reset     — Drop + create + migrate
```

## Schemas

Los schemas mapean tablas de la base de datos a structs de Elixir:

```elixir
defmodule MiApp.Cuentas.Usuario do
  use Ecto.Schema
  import Ecto.Changeset

  schema "usuarios" do
    field :nombre, :string
    field :email, :string
    field :edad, :integer
    field :activo, :boolean, default: true
    field :password_hash, :string
    field :password, :string, virtual: true  # No se persiste

    has_many :posts, MiApp.Blog.Post
    belongs_to :empresa, MiApp.Empresas.Empresa
    many_to_many :roles, MiApp.Cuentas.Rol, join_through: "usuarios_roles"

    timestamps()  # Agrega inserted_at y updated_at
  end
end
```

## Changesets

Los changesets validan y transforman datos antes de persistirlos:

```elixir
defmodule MiApp.Cuentas.Usuario do
  use Ecto.Schema
  import Ecto.Changeset

  schema "usuarios" do
    field :nombre, :string
    field :email, :string
    field :edad, :integer
    field :bio, :string
    timestamps()
  end

  def changeset(usuario, attrs) do
    usuario
    |> cast(attrs, [:nombre, :email, :edad, :bio])
    |> validate_required([:nombre, :email])
    |> validate_format(:email, ~r/@/)
    |> validate_length(:nombre, min: 2, max: 100)
    |> validate_number(:edad, greater_than: 0, less_than: 150)
    |> validate_length(:bio, max: 500)
    |> unique_constraint(:email)
  end

  def changeset_registro(usuario, attrs) do
    usuario
    |> changeset(attrs)
    |> validate_required([:edad])
    |> put_change(:activo, true)
  end
end

# Uso
changeset = Usuario.changeset(%Usuario{}, %{nombre: "Ana", email: "ana@mail.com"})
changeset.valid?  # => true

changeset = Usuario.changeset(%Usuario{}, %{nombre: ""})
changeset.valid?  # => false
changeset.errors  # => [nombre: {"can't be blank", ...}, email: {"can't be blank", ...}]
```

## Queries

Ecto proporciona un DSL de queries composable y seguro:

```elixir
import Ecto.Query

# Query básica
MiApp.Repo.all(from u in Usuario, where: u.activo == true)

# Query con selección
from u in Usuario,
  where: u.edad >= 18,
  select: {u.nombre, u.email},
  order_by: [asc: u.nombre]

# Queries composables
defmodule MiApp.Cuentas do
  def listar_usuarios(filtros \\ %{}) do
    Usuario
    |> filtrar_activos(filtros)
    |> filtrar_edad(filtros)
    |> ordenar(filtros)
    |> MiApp.Repo.all()
  end

  defp filtrar_activos(query, %{activos: true}) do
    from u in query, where: u.activo == true
  end
  defp filtrar_activos(query, _), do: query

  defp filtrar_edad(query, %{edad_minima: edad}) do
    from u in query, where: u.edad >= ^edad
  end
  defp filtrar_edad(query, _), do: query

  defp ordenar(query, %{orden: campo}) do
    from u in query, order_by: [asc: ^campo]
  end
  defp ordenar(query, _), do: query
end

# Queries con joins y preloads
from u in Usuario,
  join: p in assoc(u, :posts),
  where: p.publicado == true,
  preload: [posts: p]
```

## Operaciones CRUD

```elixir
defmodule MiApp.Cuentas do
  alias MiApp.{Repo, Cuentas.Usuario}

  def crear_usuario(attrs) do
    %Usuario{}
    |> Usuario.changeset(attrs)
    |> Repo.insert()
  end

  def obtener_usuario(id) do
    Repo.get(Usuario, id)
  end

  def actualizar_usuario(%Usuario{} = usuario, attrs) do
    usuario
    |> Usuario.changeset(attrs)
    |> Repo.update()
  end

  def eliminar_usuario(%Usuario{} = usuario) do
    Repo.delete(usuario)
  end

  # Uso
  # {:ok, usuario} = crear_usuario(%{nombre: "Ana", email: "ana@mail.com"})
  # {:error, changeset} = crear_usuario(%{nombre: ""})
end
```

## Migraciones

Las migraciones definen cambios en la estructura de la base de datos:

```elixir
# mix ecto.gen.migration crear_usuarios
defmodule MiApp.Repo.Migrations.CrearUsuarios do
  use Ecto.Migration

  def change do
    create table(:usuarios) do
      add :nombre, :string, null: false
      add :email, :string, null: false
      add :edad, :integer
      add :activo, :boolean, default: true
      add :empresa_id, references(:empresas, on_delete: :nilify_all)

      timestamps()
    end

    create unique_index(:usuarios, [:email])
    create index(:usuarios, [:empresa_id])
  end
end
```

## Asociaciones y Preloads

```elixir
# Cargar asociaciones
usuario = Repo.get(Usuario, 1) |> Repo.preload(:posts)
usuario = Repo.get(Usuario, 1) |> Repo.preload(posts: :comentarios)

# Preload en query
from u in Usuario,
  preload: [:posts, :roles]

# Insertar con asociación
Ecto.build_assoc(usuario, :posts, %{titulo: "Mi Post"})
|> Repo.insert()
```

## Resumen

Ecto es un toolkit completo para trabajar con bases de datos en Elixir. Los schemas definen la estructura, los changesets validan y transforman datos de forma explícita, el DSL de queries permite construir consultas composables y seguras, y las migraciones gestionan la evolución del esquema. A diferencia de los ORMs tradicionales, Ecto favorece la claridad sobre la conveniencia, lo que resulta en código predecible y mantenible.
