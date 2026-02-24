# Ecto y Modelos en Phoenix

Ecto es la biblioteca oficial de Elixir para interactuar con bases de datos. En Phoenix, Ecto proporciona schemas para definir la estructura de los datos, changesets para validar y transformar datos, migraciones para gestionar el esquema de la base de datos y un DSL de consultas poderoso y composable.

## Schemas

Los schemas definen la estructura de los datos y su mapeo a tablas de la base de datos:

```elixir
defmodule MiApp.Catalogo.Producto do
  use Ecto.Schema
  import Ecto.Changeset

  schema "productos" do
    field :nombre, :string
    field :descripcion, :string
    field :precio, :decimal
    field :stock, :integer, default: 0
    field :activo, :boolean, default: true
    field :publicado_en, :naive_datetime

    belongs_to :categoria, MiApp.Catalogo.Categoria
    has_many :resenas, MiApp.Catalogo.Resena
    many_to_many :etiquetas, MiApp.Catalogo.Etiqueta, join_through: "productos_etiquetas"

    timestamps()
  end
end
```

## Tipos de Campo

Ecto soporta una variedad de tipos de campo para representar distintos datos:

```elixir
schema "ejemplo" do
  field :texto, :string
  field :numero_entero, :integer
  field :numero_decimal, :decimal
  field :flotante, :float
  field :activo, :boolean
  field :fecha, :date
  field :hora, :time
  field :fecha_hora, :naive_datetime
  field :fecha_hora_utc, :utc_datetime
  field :identificador, :binary_id       # UUID
  field :datos, :map                     # JSON/mapa
  field :lista_tags, {:array, :string}   # Array de strings
  field :estado, Ecto.Enum, values: [:borrador, :publicado, :archivado]
end
```

## Changesets

Los changesets son el mecanismo central de Ecto para validar y transformar datos antes de insertarlos o actualizarlos:

```elixir
defmodule MiApp.Catalogo.Producto do
  use Ecto.Schema
  import Ecto.Changeset

  schema "productos" do
    field :nombre, :string
    field :precio, :decimal
    field :stock, :integer
    field :email_contacto, :string
    timestamps()
  end

  def changeset(producto, attrs) do
    producto
    |> cast(attrs, [:nombre, :precio, :stock, :email_contacto])
    |> validate_required([:nombre, :precio])
    |> validate_length(:nombre, min: 3, max: 100)
    |> validate_number(:precio, greater_than: 0)
    |> validate_number(:stock, greater_than_or_equal_to: 0)
    |> validate_format(:email_contacto, ~r/@/)
    |> unique_constraint(:nombre)
  end
end
```

## Cast y Validate

Las funciones `cast` y `validate_*` forman la base del sistema de validación:

```elixir
def changeset(usuario, attrs) do
  usuario
  # cast filtra y convierte los campos permitidos
  |> cast(attrs, [:nombre, :email, :edad, :rol])
  # Validaciones disponibles
  |> validate_required([:nombre, :email])
  |> validate_length(:nombre, min: 2, max: 50)
  |> validate_format(:email, ~r/^[\w.]+@[\w.]+$/)
  |> validate_inclusion(:rol, ["admin", "usuario", "moderador"])
  |> validate_number(:edad, greater_than: 0, less_than: 150)
  |> validate_confirmation(:password, message: "las contraseñas no coinciden")
  |> unique_constraint(:email)
  |> put_change(:email, String.downcase(attrs["email"] || ""))
end
```

## Migraciones

Las migraciones gestionan los cambios en el esquema de la base de datos de forma versionada:

```elixir
# Generar una migración
# mix ecto.gen.migration crear_productos

defmodule MiApp.Repo.Migrations.CrearProductos do
  use Ecto.Migration

  def change do
    create table(:productos) do
      add :nombre, :string, null: false
      add :descripcion, :text
      add :precio, :decimal, precision: 10, scale: 2
      add :stock, :integer, default: 0
      add :activo, :boolean, default: true
      add :categoria_id, references(:categorias, on_delete: :restrict)

      timestamps()
    end

    create index(:productos, [:nombre])
    create unique_index(:productos, [:nombre])
    create index(:productos, [:categoria_id])
  end
end

# Ejecutar migraciones: mix ecto.migrate
# Revertir última migración: mix ecto.rollback
```

## Repo CRUD

El módulo Repo proporciona las operaciones básicas para interactuar con la base de datos:

```elixir
alias MiApp.Repo
alias MiApp.Catalogo.Producto

# Crear
{:ok, producto} = Repo.insert(%Producto{nombre: "Laptop", precio: 999.99})

# Leer
producto = Repo.get(Producto, 1)
producto = Repo.get!(Producto, 1)              # Lanza error si no existe
producto = Repo.get_by(Producto, nombre: "Laptop")
productos = Repo.all(Producto)

# Actualizar
changeset = Producto.changeset(producto, %{precio: 899.99})
{:ok, producto_actualizado} = Repo.update(changeset)

# Eliminar
{:ok, _producto} = Repo.delete(producto)
```

## Queries con from, where y select

Ecto ofrece un DSL de consultas composable y seguro contra inyección SQL:

```elixir
import Ecto.Query

# Consulta básica con from
query = from p in Producto,
  where: p.activo == true,
  where: p.precio > ^precio_minimo,
  order_by: [desc: p.precio],
  select: %{nombre: p.nombre, precio: p.precio}

productos = Repo.all(query)

# Consultas composables
def productos_activos do
  from p in Producto, where: p.activo == true
end

def por_categoria(query, categoria_id) do
  from p in query, where: p.categoria_id == ^categoria_id
end

def ordenar_por_precio(query, direccion \\ :asc) do
  from p in query, order_by: [{^direccion, p.precio}]
end

# Composición: combinar consultas
resultado =
  productos_activos()
  |> por_categoria(5)
  |> ordenar_por_precio(:desc)
  |> Repo.all()
```

## Resumen

Ecto es el pilar de la capa de datos en Phoenix. Los schemas definen la estructura y relaciones de los datos, los changesets validan y transforman la entrada con `cast` y funciones `validate_*`, las migraciones gestionan el esquema de la base de datos de forma versionada, el Repo ofrece operaciones CRUD directas, y el DSL de queries con `from`, `where` y `select` permite construir consultas composables, seguras y expresivas.
