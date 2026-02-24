# Metaprogramación en Elixir

La metaprogramación en Elixir permite escribir código que genera código en tiempo de compilación. Elixir expone su propio AST (Abstract Syntax Tree) como estructuras de datos manipulables, lo que hace que las macros sean una extensión natural del lenguaje. Frameworks como Phoenix y Ecto aprovechan intensamente la metaprogramación.

## Quote: Representación del AST

`quote` convierte código Elixir en su representación interna como tuplas de tres elementos:

```elixir
# El AST es una tupla {nombre, metadata, argumentos}
quote do: 1 + 2
# => {:+, [context: Elixir, imports: [{1, Kernel}, {2, Kernel}]], [1, 2]}

quote do: sum(1, 2, 3)
# => {:sum, [], [1, 2, 3]}

# Expresiones más complejas
quote do
  if x > 0 do
    :positivo
  else
    :negativo
  end
end
# Genera una estructura AST anidada

# Literales se representan a sí mismos
quote do: "hola"     # => "hola"
quote do: :atomo     # => :atomo
quote do: 42         # => 42
quote do: [1, 2, 3]  # => [1, 2, 3]
```

## Unquote: Inyectar Valores en el AST

`unquote` inserta valores evaluados dentro de un bloque `quote`:

```elixir
nombre = :mundo

quote do
  "Hola, " <> unquote(Atom.to_string(nombre))
end
# => {:<>, [], ["Hola, ", "mundo"]}

# Construir funciones dinámicamente
operaciones = [{:doble, 2}, {:triple, 3}, {:cuadruple, 4}]

defmodule Multiplicador do
  for {nombre, factor} <- operaciones do
    def unquote(nombre)(n) do
      n * unquote(factor)
    end
  end
end

Multiplicador.doble(5)     # => 10
Multiplicador.triple(5)    # => 15
Multiplicador.cuadruple(5) # => 20
```

## unquote_splicing

`unquote_splicing` inyecta una lista expandiéndola en el contexto:

```elixir
valores = [1, 2, 3]

quote do
  [0, unquote_splicing(valores), 4]
end
# Equivale a: [0, 1, 2, 3, 4]

args = [{:a, [], nil}, {:b, [], nil}]
quote do
  def mi_funcion(unquote_splicing(args)) do
    unquote_splicing(args)
  end
end
```

## Macros

Las macros son funciones que reciben y retornan AST. Se ejecutan en tiempo de compilación:

```elixir
defmodule MisMacros do
  defmacro decir(mensaje) do
    quote do
      IO.puts("[INFO] " <> unquote(mensaje))
    end
  end

  defmacro medir_tiempo(nombre, do: bloque) do
    quote do
      inicio = System.monotonic_time(:microsecond)
      resultado = unquote(bloque)
      fin = System.monotonic_time(:microsecond)
      IO.puts("#{unquote(nombre)}: #{fin - inicio}μs")
      resultado
    end
  end

  defmacro a_menos_que(condicion, do: bloque) do
    quote do
      if !unquote(condicion) do
        unquote(bloque)
      end
    end
  end
end

defmodule Demo do
  require MisMacros

  def ejecutar do
    MisMacros.decir("Comenzando ejecución")

    MisMacros.medir_tiempo "cálculo" do
      Enum.sum(1..1_000_000)
    end

    MisMacros.a_menos_que false do
      IO.puts("Esto se ejecuta")
    end
  end
end
```

## Higiene en Macros

Las macros de Elixir son higiénicas por defecto: las variables definidas dentro de una macro no contaminan el contexto del llamador:

```elixir
defmacro mi_macro do
  quote do
    x = 42  # Esta x no afecta la x del contexto exterior
    x
  end
end

# Para escapar la higiene (usar con cuidado):
defmacro definir_variable(nombre, valor) do
  quote do
    var!(unquote(nombre)) = unquote(valor)
  end
end
```

## __using__ y Metaprogramación en Módulos

El macro `__using__/1` se invoca cuando otro módulo usa `use`:

```elixir
defmodule MiApp.Schema do
  defmacro __using__(opts) do
    tabla = Keyword.get(opts, :tabla, "registros")

    quote do
      import MiApp.Schema
      @tabla unquote(tabla)

      def tabla, do: @tabla

      def nuevo(attrs \\ %{}) do
        struct(__MODULE__, attrs)
      end
    end
  end

  defmacro campo(nombre, tipo, opts \\ []) do
    quote do
      @campos {unquote(nombre), unquote(tipo), unquote(opts)}
    end
  end
end

defmodule MiApp.Usuario do
  use MiApp.Schema, tabla: "usuarios"

  # Ahora tiene tabla/0 y nuevo/1 disponibles
end

MiApp.Usuario.tabla()  # => "usuarios"
```

## Código en Tiempo de Compilación

Elixir permite ejecutar código durante la compilación:

```elixir
defmodule Rutas do
  # Leer archivo en tiempo de compilación
  @external_resource "config/rutas.txt"
  @rutas File.read!("config/rutas.txt")
         |> String.split("\n", trim: true)
         |> Enum.map(&String.split(&1, ":"))

  for [metodo, path, handler] <- @rutas do
    def manejar(unquote(metodo), unquote(path)) do
      unquote(String.to_atom(handler))
    end
  end

  # Recompila si el archivo externo cambia
end

defmodule Constantes do
  @compile_time DateTime.utc_now()

  def compilado_en, do: @compile_time

  # Código ejecutado en compilación
  if Mix.env() == :dev do
    def debug(msg), do: IO.inspect(msg, label: "DEBUG")
  else
    def debug(_msg), do: :ok
  end
end
```

## Macro.to_string y Debugging

Herramientas para depurar macros:

```elixir
# Convertir AST a string legible
ast = quote do: Enum.map([1, 2, 3], &(&1 * 2))
Macro.to_string(ast)
# => "Enum.map([1, 2, 3], &(&1 * 2))"

# Expandir macros
Macro.expand(quote(do: unless(true, do: :x)), __ENV__)

# En IEx
# iex> require MisMacros
# iex> quote do: MisMacros.decir("hola") |> Macro.expand(__ENV__) |> Macro.to_string()
```

## Resumen

La metaprogramación en Elixir se basa en la capacidad de manipular el AST del lenguaje mediante `quote` y `unquote`. Las macros permiten generar código en tiempo de compilación, crear DSLs expresivos y eliminar boilerplate. Sin embargo, deben usarse con moderación: la regla de oro es "no uses una macro cuando una función sea suficiente". Frameworks como Phoenix y Ecto demuestran el poder de la metaprogramación bien aplicada, creando APIs intuitivas sin sacrificar la claridad del código generado.
