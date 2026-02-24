# Colecciones en Elixir

Las colecciones son estructuras de datos fundamentales en Elixir. A diferencia de otros lenguajes, todas las colecciones en Elixir son inmutables: cada operación que "modifica" una colección en realidad crea una nueva. Elixir ofrece varias estructuras optimizadas para diferentes casos de uso.

## Listas

Las listas en Elixir son listas enlazadas. Son eficientes para prepend (agregar al inicio) pero costosas para acceso por índice:

```elixir
lista = [1, 2, 3, 4, 5]

# Prepend (O(1))
nueva = [0 | lista]   # => [0, 1, 2, 3, 4, 5]

# Head y Tail
[cabeza | cola] = [1, 2, 3]
cabeza  # => 1
cola    # => [2, 3]

# Concatenación
[1, 2] ++ [3, 4]      # => [1, 2, 3, 4]

# Diferencia
[1, 2, 3] -- [2]      # => [1, 3]

# Funciones comunes
length([1, 2, 3])      # => 3
Enum.at([10, 20, 30], 1) # => 20
List.first([1, 2, 3])   # => 1
List.last([1, 2, 3])    # => 3
```

## Tuplas

Las tuplas almacenan elementos contiguos en memoria. Son eficientes para acceso por índice pero costosas para modificación:

```elixir
tupla = {:ok, "resultado", 42}

# Acceso por índice
elem(tupla, 0)         # => :ok
elem(tupla, 1)         # => "resultado"

# Tamaño
tuple_size(tupla)      # => 3

# Modificar (crea nueva tupla)
put_elem(tupla, 1, "nuevo")  # => {:ok, "nuevo", 42}

# Uso típico como retorno de funciones
case File.read("archivo.txt") do
  {:ok, contenido} -> IO.puts(contenido)
  {:error, razon}  -> IO.puts("Error: #{razon}")
end
```

## Keyword Lists

Las keyword lists son listas de tuplas de dos elementos donde el primer elemento es un átomo. Permiten claves duplicadas y mantienen el orden:

```elixir
# Sintaxis completa y abreviada
opciones = [{:puerto, 4000}, {:host, "localhost"}]
opciones = [puerto: 4000, host: "localhost"]  # Equivalente

# Acceso
opciones[:puerto]      # => 4000

# Claves duplicadas permitidas
config = [param: 1, param: 2]
Keyword.get_values(config, :param)  # => [1, 2]

# Se usan mucho como último argumento de funciones
String.split("a b c", " ", trim: true)

# Funciones del módulo Keyword
Keyword.put(opciones, :ssl, true)
Keyword.has_key?(opciones, :puerto)  # => true
```

## Maps

Los maps son la colección clave-valor principal en Elixir. No permiten claves duplicadas y no garantizan orden:

```elixir
# Crear un map
usuario = %{nombre: "Carlos", edad: 28, activo: true}

# Acceso con átomos
usuario.nombre          # => "Carlos"
usuario[:edad]          # => 28

# Acceso con cualquier tipo de clave
datos = %{"clave" => "valor", 42 => "número"}
datos["clave"]          # => "valor"
datos[42]               # => "número"

# Actualizar (crea nuevo map)
actualizado = %{usuario | edad: 29}
# => %{nombre: "Carlos", edad: 29, activo: true}

# Agregar clave nueva
Map.put(usuario, :email, "carlos@mail.com")

# Funciones útiles
Map.keys(usuario)       # => [:activo, :edad, :nombre]
Map.values(usuario)     # => [true, 28, "Carlos"]
Map.merge(%{a: 1}, %{b: 2})  # => %{a: 1, b: 2}
Map.drop(usuario, [:activo])  # => %{edad: 28, nombre: "Carlos"}
```

## Pattern Matching con Maps

Una característica poderosa es que puedes hacer match parcial con maps:

```elixir
%{nombre: nombre} = %{nombre: "Ana", edad: 25}
nombre  # => "Ana"

# En funciones
defmodule Saludador do
  def saludar(%{nombre: nombre, idioma: "es"}), do: "¡Hola, #{nombre}!"
  def saludar(%{nombre: nombre, idioma: "en"}), do: "Hello, #{nombre}!"
  def saludar(%{nombre: nombre}), do: "Hi, #{nombre}!"
end
```

## MapSet

MapSet implementa un conjunto (set) donde cada elemento es único:

```elixir
set = MapSet.new([1, 2, 3, 2, 1])
# => MapSet.new([1, 2, 3])

MapSet.put(set, 4)
# => MapSet.new([1, 2, 3, 4])

MapSet.member?(set, 2)   # => true

# Operaciones de conjuntos
a = MapSet.new([1, 2, 3])
b = MapSet.new([2, 3, 4])

MapSet.union(a, b)        # => MapSet.new([1, 2, 3, 4])
MapSet.intersection(a, b) # => MapSet.new([2, 3])
MapSet.difference(a, b)   # => MapSet.new([1])
```

## Ranges

Los ranges representan secuencias de números:

```elixir
rango = 1..10

Enum.to_list(1..5)        # => [1, 2, 3, 4, 5]
Enum.to_list(1..10//3)    # => [1, 4, 7, 10] (con paso)

3 in 1..10                # => true
15 in 1..10               # => false

# Uso con Enum
Enum.map(1..5, &(&1 * 2))  # => [2, 4, 6, 8, 10]
Enum.sum(1..100)            # => 5050
```

## Resumen

Elixir proporciona colecciones especializadas para cada necesidad: listas enlazadas para procesamiento secuencial, tuplas para grupos pequeños de tamaño fijo, keyword lists para opciones con orden, maps para datos estructurados clave-valor, MapSet para conjuntos sin duplicados y ranges para secuencias numéricas. La inmutabilidad de todas estas estructuras garantiza la seguridad en entornos concurrentes y facilita el razonamiento sobre el código.
