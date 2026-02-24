# Funciones en Elixir

Las funciones son el bloque de construcción fundamental en Elixir. Como lenguaje funcional, todo en Elixir gira alrededor de transformar datos mediante funciones. Elixir distingue entre funciones anónimas y funciones con nombre, y ofrece herramientas poderosas como el pipe operator para componer transformaciones de manera legible.

## Funciones Anónimas

Las funciones anónimas se crean con la palabra clave `fn` y se invocan con un punto antes de los paréntesis:

```elixir
# Definición y llamada
suma = fn a, b -> a + b end
suma.(3, 4)  # => 7

# Con múltiples cláusulas
saludo = fn
  "es" -> "¡Hola!"
  "en" -> "Hello!"
  "fr" -> "Bonjour!"
  _    -> "Hi!"
end

saludo.("es")  # => "¡Hola!"
saludo.("fr")  # => "Bonjour!"

# Función sin argumentos
decir_hola = fn -> "¡Hola Mundo!" end
decir_hola.()  # => "¡Hola Mundo!"
```

## Funciones con Nombre (Named Functions)

Las funciones con nombre se definen dentro de módulos usando `def` (públicas) y `defp` (privadas):

```elixir
defmodule Matematica do
  # Función pública
  def cuadrado(n), do: n * n

  # Función con múltiples cláusulas
  def factorial(0), do: 1
  def factorial(n) when n > 0 do
    n * factorial(n - 1)
  end

  # Función privada
  defp validar_positivo(n) when n >= 0, do: :ok
  defp validar_positivo(_), do: {:error, "Número negativo"}

  def raiz_cuadrada(n) do
    case validar_positivo(n) do
      :ok -> {:ok, :math.sqrt(n)}
      error -> error
    end
  end
end
```

## Aridad

La aridad es el número de argumentos de una función. Funciones con el mismo nombre pero diferente aridad son funciones distintas:

```elixir
defmodule Saludador do
  # saludar/0
  def saludar, do: "¡Hola!"

  # saludar/1
  def saludar(nombre), do: "¡Hola, #{nombre}!"

  # saludar/2
  def saludar(nombre, idioma) do
    case idioma do
      :es -> "¡Hola, #{nombre}!"
      :en -> "Hello, #{nombre}!"
    end
  end
end

Saludador.saludar()              # => "¡Hola!"
Saludador.saludar("Ana")         # => "¡Hola, Ana!"
Saludador.saludar("Ana", :en)    # => "Hello, Ana!"
```

## Valores por Defecto

Se pueden definir valores por defecto usando `\\`:

```elixir
defmodule Config do
  def conectar(host, puerto \\ 5432, ssl \\ false) do
    "Conectando a #{host}:#{puerto}, SSL: #{ssl}"
  end
end

Config.conectar("localhost")          # => "Conectando a localhost:5432, SSL: false"
Config.conectar("db.com", 3306)       # => "Conectando a db.com:3306, SSL: false"
Config.conectar("db.com", 3306, true) # => "Conectando a db.com:3306, SSL: true"
```

## Closures

Las funciones anónimas capturan las variables de su entorno (closure):

```elixir
multiplicador = fn factor ->
  fn numero -> numero * factor end
end

doble = multiplicador.(2)
triple = multiplicador.(3)

doble.(5)   # => 10
triple.(5)  # => 15
```

## El Pipe Operator (|>)

El pipe operator pasa el resultado de una expresión como primer argumento de la siguiente función:

```elixir
# Sin pipe (difícil de leer)
String.trim(String.downcase(String.replace("  ¡HOLA MUNDO!  ", "!", "")))

# Con pipe (legible y expresivo)
"  ¡HOLA MUNDO!  "
|> String.replace("!", "")
|> String.downcase()
|> String.trim()
# => "¡hola mundo"

# Ejemplo práctico: procesar lista de datos
[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
|> Enum.filter(&(rem(&1, 2) == 0))
|> Enum.map(&(&1 * &1))
|> Enum.sum()
# => 220 (4 + 16 + 36 + 64 + 100)
```

## Operador de Captura (&)

El operador `&` permite crear funciones anónimas de forma concisa y referenciar funciones existentes:

```elixir
# Captura de función con nombre
lista = [3, 1, 4, 1, 5]
Enum.sort(lista, &>=/2)  # => [5, 4, 3, 1, 1]

# Referencia a función de módulo
Enum.map(["hola", "mundo"], &String.upcase/1)
# => ["HOLA", "MUNDO"]

# Función anónima corta
Enum.map([1, 2, 3], &(&1 * 2))     # => [2, 4, 6]
Enum.map([1, 2, 3], &(&1 + 10))    # => [11, 12, 13]

# Con múltiples argumentos
Enum.reduce([1, 2, 3], 0, &(&1 + &2))  # => 6

# Equivalencias
fn x -> x * 2 end   # Forma larga
&(&1 * 2)            # Forma corta con captura
```

## Funciones como Ciudadanos de Primera Clase

Las funciones pueden pasarse como argumentos, retornarse y almacenarse en estructuras de datos:

```elixir
defmodule Transformador do
  def aplicar(lista, transformacion) do
    Enum.map(lista, transformacion)
  end

  def componer(f, g) do
    fn x -> f.(g.(x)) end
  end
end

duplicar = &(&1 * 2)
sumar_uno = &(&1 + 1)

Transformador.aplicar([1, 2, 3], duplicar)
# => [2, 4, 6]

duplicar_y_sumar = Transformador.componer(sumar_uno, duplicar)
duplicar_y_sumar.(5)  # => 11
```

## Resumen

Las funciones en Elixir son herramientas versátiles que permiten definir comportamiento mediante pattern matching en cláusulas, controlar la visibilidad con funciones públicas y privadas, y componer transformaciones elegantes con el pipe operator. El operador de captura `&` simplifica la sintaxis en casos comunes, y los closures permiten crear funciones especializadas dinámicamente. Dominar estas herramientas es esencial para escribir código funcional expresivo.
