# Recursión y Enumerables en Elixir

La recursión es la forma natural de iterar en lenguajes funcionales. Elixir, al no tener bucles imperativos como `for` o `while` tradicionales, depende de la recursión y de módulos como `Enum` y `Stream` para procesar colecciones. La optimización de tail recursion en la BEAM garantiza que las funciones recursivas bien escritas no agoten la pila.

## Recursión Básica

La recursión en Elixir se expresa mediante múltiples cláusulas de función, con un caso base y un caso recursivo:

```elixir
defmodule Recursion do
  # Sumar elementos de una lista
  def sumar([]), do: 0
  def sumar([cabeza | cola]) do
    cabeza + sumar(cola)
  end

  # Longitud de una lista
  def longitud([]), do: 0
  def longitud([_ | cola]), do: 1 + longitud(cola)

  # Invertir una lista
  def invertir(lista), do: invertir(lista, [])
  defp invertir([], acumulador), do: acumulador
  defp invertir([cabeza | cola], acumulador) do
    invertir(cola, [cabeza | acumulador])
  end
end

Recursion.sumar([1, 2, 3, 4, 5])  # => 15
Recursion.longitud([1, 2, 3])      # => 3
Recursion.invertir([1, 2, 3])      # => [3, 2, 1]
```

## Tail Recursion

La tail recursion ocurre cuando la llamada recursiva es la última operación de la función. La BEAM optimiza estas llamadas para no consumir espacio adicional en la pila:

```elixir
defmodule TailRecursion do
  # NO es tail recursive (la suma ocurre después de la recursión)
  def factorial_no_tail(0), do: 1
  def factorial_no_tail(n), do: n * factorial_no_tail(n - 1)

  # SÍ es tail recursive (usa acumulador)
  def factorial(n), do: factorial(n, 1)
  defp factorial(0, acc), do: acc
  defp factorial(n, acc), do: factorial(n - 1, n * acc)

  # Fibonacci tail recursive
  def fibonacci(n), do: fibonacci(n, 0, 1)
  defp fibonacci(0, a, _b), do: a
  defp fibonacci(n, a, b), do: fibonacci(n - 1, b, a + b)
end

TailRecursion.factorial(20)   # => 2432902008176640000
TailRecursion.fibonacci(10)   # => 55
```

## El Módulo Enum

`Enum` proporciona funciones para trabajar con colecciones de forma eagerly (evalúa todo inmediatamente):

```elixir
numeros = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]

# map: transformar cada elemento
Enum.map(numeros, &(&1 * 2))
# => [2, 4, 6, 8, 10, 12, 14, 16, 18, 20]

# filter: seleccionar elementos
Enum.filter(numeros, &(rem(&1, 2) == 0))
# => [2, 4, 6, 8, 10]

# reduce: acumular un resultado
Enum.reduce(numeros, 0, &+/2)
# => 55

# Otras funciones útiles
Enum.any?(numeros, &(&1 > 5))       # => true
Enum.all?(numeros, &(&1 > 0))       # => true
Enum.find(numeros, &(&1 > 7))       # => 8
Enum.chunk_every(numeros, 3)         # => [[1,2,3],[4,5,6],[7,8,9],[10]]
Enum.zip([1, 2, 3], [:a, :b, :c])   # => [{1, :a}, {2, :b}, {3, :c}]
Enum.flat_map([[1,2],[3,4]], &(&1))  # => [1, 2, 3, 4]
Enum.group_by(numeros, &(rem(&1, 2) == 0))
# => %{false: [1,3,5,7,9], true: [2,4,6,8,10]}
```

## El Módulo Stream

`Stream` crea enumerables lazy que solo se evalúan cuando se consumen. Ideal para datos grandes o infinitos:

```elixir
# Stream lazy: no se evalúa hasta que se consume
resultado =
  1..1_000_000
  |> Stream.filter(&(rem(&1, 3) == 0))
  |> Stream.map(&(&1 * 2))
  |> Enum.take(5)
# => [6, 12, 18, 24, 30]

# Streams infinitos
Stream.iterate(1, &(&1 + 1))
|> Stream.filter(&(rem(&1, 7) == 0))
|> Enum.take(5)
# => [7, 14, 21, 28, 35]

# Stream.cycle: repite infinitamente
Stream.cycle([:rojo, :verde, :azul])
|> Enum.take(7)
# => [:rojo, :verde, :azul, :rojo, :verde, :azul, :rojo]

# Stream.unfold: generar secuencias
Stream.unfold(1, fn n -> {n, n * 2} end)
|> Enum.take(8)
# => [1, 2, 4, 8, 16, 32, 64, 128]
```

## Lazy Evaluation vs Eager Evaluation

La diferencia clave entre `Enum` y `Stream` se observa en cadenas de transformaciones:

```elixir
# Eager: crea listas intermedias en cada paso
1..100
|> Enum.map(&(&1 * 3))       # Lista intermedia 1
|> Enum.filter(&(&1 > 50))   # Lista intermedia 2
|> Enum.take(5)
# Procesa los 100 elementos en cada paso

# Lazy: procesa elemento por elemento
1..100
|> Stream.map(&(&1 * 3))     # No evalúa aún
|> Stream.filter(&(&1 > 50)) # No evalúa aún
|> Enum.take(5)               # Ahora evalúa solo lo necesario
# Solo procesa ~18 elementos hasta obtener 5 resultados
```

## Reduce: La Base de Todo

`reduce` es la función más fundamental — muchas otras funciones se pueden implementar con ella:

```elixir
defmodule MiEnum do
  # Implementar map con reduce
  def map(lista, funcion) do
    lista
    |> Enum.reduce([], fn elem, acc -> [funcion.(elem) | acc] end)
    |> Enum.reverse()
  end

  # Implementar filter con reduce
  def filter(lista, predicado) do
    lista
    |> Enum.reduce([], fn elem, acc ->
      if predicado.(elem), do: [elem | acc], else: acc
    end)
    |> Enum.reverse()
  end

  # Frecuencia de elementos
  def frecuencias(lista) do
    Enum.reduce(lista, %{}, fn elem, acc ->
      Map.update(acc, elem, 1, &(&1 + 1))
    end)
  end
end

MiEnum.frecuencias(["a", "b", "a", "c", "b", "a"])
# => %{"a" => 3, "b" => 2, "c" => 1}
```

## Resumen

La recursión con tail call optimization permite iterar de forma segura sin agotar la pila. El módulo `Enum` proporciona más de 70 funciones para transformar colecciones de forma eager, mientras que `Stream` ofrece evaluación lazy para datos grandes o infinitos. `reduce` es la función más poderosa del arsenal funcional, capaz de implementar prácticamente cualquier transformación. Elegir entre Enum y Stream depende del tamaño de los datos y las necesidades de rendimiento de cada situación.
