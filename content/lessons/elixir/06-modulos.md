# Módulos y Structs en Elixir

Los módulos son la unidad de organización de código en Elixir. Agrupan funciones relacionadas y proporcionan namespacing. Los structs, por su parte, son maps con estructura definida que permiten crear tipos de datos personalizados con validación en tiempo de compilación.

## Definición de Módulos (defmodule)

Los módulos se definen con `defmodule` y pueden anidarse para crear jerarquías:

```elixir
defmodule MiApp.Cuentas.Usuario do
  def nombre_completo(nombre, apellido) do
    "#{nombre} #{apellido}"
  end

  def email_valido?(email) do
    String.contains?(email, "@")
  end
end

MiApp.Cuentas.Usuario.nombre_completo("Ana", "García")
# => "Ana García"
```

Para importar funciones de otros módulos se usan `import`, `alias` y `require`:

```elixir
defmodule MiModulo do
  # Alias para acortar nombres
  alias MiApp.Cuentas.Usuario

  # Import para usar funciones directamente
  import Enum, only: [map: 2, filter: 2]

  # Require para macros
  require Logger

  def procesar(datos) do
    Logger.info("Procesando datos")
    datos
    |> filter(&Usuario.email_valido?(&1.email))
    |> map(&Usuario.nombre_completo(&1.nombre, &1.apellido))
  end
end
```

## Atributos de Módulo

Los atributos de módulo sirven como constantes, anotaciones y almacenamiento temporal durante la compilación:

```elixir
defmodule Configuracion do
  @moduledoc """
  Módulo de configuración de la aplicación.
  """

  @version "1.0.0"
  @max_intentos 3
  @timeout_ms 5_000

  @doc """
  Retorna la versión actual de la aplicación.
  """
  def version, do: @version

  def max_intentos, do: @max_intentos

  def configurar_conexion(host) do
    %{host: host, timeout: @timeout_ms, intentos: @max_intentos}
  end
end
```

`@moduledoc` y `@doc` generan documentación accesible desde IEx con la función `h/1`.

## Structs

Los structs son maps con campos predefinidos y valores por defecto:

```elixir
defmodule Producto do
  @enforce_keys [:nombre, :precio]
  defstruct [:nombre, :precio, cantidad: 0, activo: true]

  def nuevo(nombre, precio) do
    %Producto{nombre: nombre, precio: precio}
  end

  def total(%Producto{precio: precio, cantidad: cantidad}) do
    precio * cantidad
  end

  def desactivar(%Producto{} = producto) do
    %Producto{producto | activo: false}
  end
end

laptop = Producto.nuevo("Laptop", 999.99)
# => %Producto{nombre: "Laptop", precio: 999.99, cantidad: 0, activo: true}

laptop = %Producto{laptop | cantidad: 5}
Producto.total(laptop)  # => 4999.95
```

`@enforce_keys` obliga a que ciertas claves estén presentes al crear el struct.

## Pattern Matching con Structs

Los structs se integran perfectamente con el pattern matching:

```elixir
defmodule Procesador do
  def procesar(%Producto{activo: true, precio: precio}) when precio > 0 do
    :producto_valido
  end

  def procesar(%Producto{activo: false}) do
    :producto_inactivo
  end

  def procesar(%Producto{precio: precio}) when precio <= 0 do
    :precio_invalido
  end
end
```

## Protocols

Los protocols permiten polimorfismo en Elixir. Definen una interfaz que diferentes tipos pueden implementar:

```elixir
defprotocol Imprimible do
  @doc "Convierte el dato a una representación legible"
  def imprimir(dato)
end

defimpl Imprimible, for: Producto do
  def imprimir(producto) do
    "📦 #{producto.nombre} - $#{producto.precio}"
  end
end

defmodule Categoria do
  defstruct [:nombre, :descripcion]
end

defimpl Imprimible, for: Categoria do
  def imprimir(categoria) do
    "📁 #{categoria.nombre}: #{categoria.descripcion}"
  end
end

# Uso polimórfico
laptop = %Producto{nombre: "Laptop", precio: 999}
tech = %Categoria{nombre: "Tecnología", descripcion: "Productos tech"}

Imprimible.imprimir(laptop)  # => "📦 Laptop - $999"
Imprimible.imprimir(tech)    # => "📁 Tecnología: Productos tech"
```

## Behaviours

Los behaviours definen un contrato que los módulos deben cumplir, similar a las interfaces en otros lenguajes:

```elixir
defmodule Repositorio do
  @callback obtener(id :: integer()) :: {:ok, map()} | {:error, String.t()}
  @callback guardar(entidad :: map()) :: {:ok, map()} | {:error, String.t()}
  @callback eliminar(id :: integer()) :: :ok | {:error, String.t()}
end

defmodule RepositorioMemoria do
  @behaviour Repositorio

  @impl Repositorio
  def obtener(id) do
    {:ok, %{id: id, datos: "desde memoria"}}
  end

  @impl Repositorio
  def guardar(entidad) do
    {:ok, Map.put(entidad, :guardado, true)}
  end

  @impl Repositorio
  def eliminar(_id) do
    :ok
  end
end
```

`@impl` es opcional pero recomendado: indica explícitamente que la función implementa un callback del behaviour.

## use y __using__

La macro `use` invoca el macro `__using__/1` del módulo referenciado, permitiendo inyectar código:

```elixir
defmodule MiApp.Schema do
  defmacro __using__(_opts) do
    quote do
      import MiApp.Schema
      @before_compile MiApp.Schema

      def tabla, do: __MODULE__ |> to_string() |> String.split(".") |> List.last() |> String.downcase()
    end
  end
end

defmodule MiApp.Usuario do
  use MiApp.Schema

  # Ahora tiene acceso a tabla/0 y las funciones importadas
end
```

## Resumen

Los módulos organizan el código en Elixir y proporcionan herramientas como atributos para constantes y documentación, structs para tipos de datos estructurados, protocols para polimorfismo basado en tipos y behaviours para contratos entre módulos. Estas abstracciones permiten construir sistemas bien organizados, extensibles y con contratos claros entre componentes.
