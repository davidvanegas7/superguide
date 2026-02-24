# Pattern Matching Avanzado en Elixir

El pattern matching es una de las características más poderosas de Elixir. Va mucho más allá de la simple asignación de variables: permite destructurar datos complejos, definir múltiples cláusulas de funciones y controlar el flujo del programa de forma elegante y expresiva.

## Destructuring Profundo

El pattern matching permite extraer valores de estructuras anidadas de forma directa:

```elixir
# Destructuring de listas anidadas
[[a, b], [c, d]] = [[1, 2], [3, 4]]
a  # => 1
d  # => 4

# Destructuring de maps anidados
%{usuario: %{nombre: nombre, direccion: %{ciudad: ciudad}}} =
  %{usuario: %{nombre: "Laura", direccion: %{ciudad: "Madrid", cp: "28001"}}}
nombre  # => "Laura"
ciudad  # => "Madrid"

# Combinando tuplas y maps
{:ok, %{status: status, body: body}} = {:ok, %{status: 200, body: "datos"}}
status  # => 200
```

## Match en Cabeza y Cola de Listas

La descomposición `[head | tail]` es esencial para el procesamiento recursivo:

```elixir
[primero | resto] = [1, 2, 3, 4, 5]
primero  # => 1
resto    # => [2, 3, 4, 5]

# Capturar múltiples elementos
[a, b | cola] = [1, 2, 3, 4]
a     # => 1
b     # => 2
cola  # => [3, 4]

# Match con lista vacía
[unico | []] = [42]
unico  # => 42
```

## Pattern Matching en Funciones

Definir múltiples cláusulas de una función basadas en patrones es la forma idiomática de manejar diferentes casos:

```elixir
defmodule Calculadora do
  def operar(:suma, a, b), do: a + b
  def operar(:resta, a, b), do: a - b
  def operar(:multiplicacion, a, b), do: a * b
  def operar(:division, _a, 0), do: {:error, "División por cero"}
  def operar(:division, a, b), do: {:ok, a / b}
end

Calculadora.operar(:suma, 5, 3)        # => 8
Calculadora.operar(:division, 10, 0)   # => {:error, "División por cero"}

# Match en el parámetro de estructura
defmodule Procesador do
  def procesar(%{tipo: "texto", contenido: texto}) do
    String.upcase(texto)
  end

  def procesar(%{tipo: "numero", contenido: n}) when is_number(n) do
    n * 2
  end

  def procesar(_), do: :tipo_desconocido
end
```

## Guards (Guardas)

Las guards son condiciones adicionales que refinan el pattern matching:

```elixir
defmodule Clasificador do
  def clasificar(n) when is_integer(n) and n > 0, do: :positivo
  def clasificar(n) when is_integer(n) and n < 0, do: :negativo
  def clasificar(0), do: :cero
  def clasificar(n) when is_float(n), do: :decimal
  def clasificar(s) when is_binary(s), do: :texto
  def clasificar(_), do: :desconocido
end

Clasificador.clasificar(42)      # => :positivo
Clasificador.clasificar(-5)      # => :negativo
Clasificador.clasificar("hola")  # => :texto
```

Las funciones permitidas en guards incluyen: `is_atom/1`, `is_binary/1`, `is_integer/1`, `is_float/1`, `is_list/1`, `is_map/1`, `is_tuple/1`, `is_nil/1`, `is_number/1`, operadores aritméticos, de comparación y booleanos.

## Guards Personalizadas con defguard

Puedes crear tus propias guards reutilizables:

```elixir
defmodule MisGuards do
  defguard es_mayor_de_edad(edad) when is_integer(edad) and edad >= 18
  defguard es_porcentaje(n) when is_number(n) and n >= 0 and n <= 100
end

defmodule Validador do
  import MisGuards

  def verificar_acceso(edad) when es_mayor_de_edad(edad) do
    :acceso_permitido
  end

  def verificar_acceso(_edad), do: :acceso_denegado
end
```

## Pin Operator Avanzado

El operador pin `^` es esencial en contextos donde necesitas comparar con un valor existente:

```elixir
# En matchs de listas
buscado = "elixir"
lista = ["ruby", "elixir", "python"]

Enum.find(lista, fn
  ^buscado -> true
  _ -> false
end)

# En comprehensions
clave = :nombre
for {^clave, valor} <- [nombre: "Ana", edad: 30, nombre: "Luis"] do
  valor
end
# => ["Ana", "Luis"]

# En case
respuesta_esperada = 200
case hacer_peticion() do
  %{status: ^respuesta_esperada, body: body} ->
    {:ok, body}
  %{status: status} ->
    {:error, "Status inesperado: #{status}"}
end
```

## Pattern Matching en Binarios

Elixir permite hacer match sobre binarios y strings a nivel de bytes:

```elixir
# Extraer partes de un string
<<"Hola, ", nombre::binary>> = "Hola, Mundo"
nombre  # => "Mundo"

# Match de bytes específicos
<<cabecera::binary-size(4), _resto::binary>> = "ELIXIRdata"
cabecera  # => "ELIX"

# Parsear protocolo binario
<<tipo::8, longitud::16, payload::binary-size(longitud), _::binary>> =
  <<1, 0, 5, "Hola!", 0, 0>>
tipo      # => 1
longitud  # => 5
payload   # => "Hola!"
```

## Resumen

El pattern matching avanzado en Elixir transforma la forma de escribir código: elimina la necesidad de múltiples condicionales, permite destructurar datos complejos de forma declarativa y, combinado con guards, ofrece un sistema de dispatch por patrones extremadamente expresivo. Dominar el pattern matching es la clave para escribir código Elixir idiomático y mantenible.
