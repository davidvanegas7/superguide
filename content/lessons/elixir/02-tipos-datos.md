# Tipos de Datos y Variables en Elixir

Elixir es un lenguaje dinámicamente tipado con un conjunto rico de tipos de datos primitivos. Comprender estos tipos y cómo funciona la asignación de variables mediante pattern matching es fundamental para escribir código idiomático en Elixir.

## Átomos

Los átomos son constantes cuyo nombre es su propio valor. Se utilizan ampliamente en Elixir para representar estados, claves y etiquetas:

```elixir
:ok
:error
:hola_mundo
true   # es el átomo :true
false  # es el átomo :false
nil    # es el átomo :nil

# Los módulos también son átomos
String == :"Elixir.String"  # => true
```

Los átomos son extremadamente eficientes en comparaciones porque internamente se almacenan como enteros en una tabla global.

## Strings (Cadenas de Texto)

Las cadenas en Elixir son binarios codificados en UTF-8:

```elixir
nombre = "Elixir"
saludo = "¡Hola, #{nombre}!"  # Interpolación

# Cadenas multilínea con heredoc
texto = """
Esta es una cadena
que ocupa múltiples líneas
sin necesidad de concatenar.
"""

# Operaciones comunes
String.length("café")       # => 4
String.upcase("hola")       # => "HOLA"
String.split("a,b,c", ",")  # => ["a", "b", "c"]
byte_size("café")           # => 5 (bytes UTF-8)
```

Es importante distinguir entre `String.length/1` (cuenta grafemas) y `byte_size/1` (cuenta bytes).

## Números: Integers y Floats

Elixir soporta enteros de precisión arbitraria y números de punto flotante IEEE 754:

```elixir
# Integers
edad = 25
grande = 1_000_000          # Separador visual con guion bajo
binario = 0b1010             # => 10
hexadecimal = 0xFF           # => 255
octal = 0o777                # => 511

# Floats
pi = 3.14159
cientifico = 1.0e-3          # => 0.001

# Operaciones
div(10, 3)    # => 3 (división entera)
rem(10, 3)    # => 1 (resto)
10 / 3        # => 3.3333... (siempre retorna float)
```

## Booleanos

Los booleanos en Elixir son los átomos `true` y `false`. Existen operadores estrictos y relajados:

```elixir
# Operadores estrictos (esperan booleanos)
true and false   # => false
true or false    # => true
not true         # => false

# Operadores relajados (cualquier valor)
nil || "valor"   # => "valor"
0 && "hola"      # => "hola"
!nil             # => true
!"hola"          # => false

# Solo nil y false son "falsy"
!0               # => false (0 es truthy)
```

## Variables y Pattern Matching

En Elixir, el operador `=` no es una asignación tradicional, sino un operador de coincidencia de patrones (pattern matching):

```elixir
# Asignación básica
x = 1
1 = x     # => 1 (coincide, no da error)
# 2 = x   # => MatchError (no coincide)

# Destructuring de tuplas
{nombre, edad} = {"Ana", 30}
nombre  # => "Ana"
edad    # => 30

# Ignorar valores con _
{_, segundo, _} = {1, 2, 3}
segundo  # => 2
```

## El Pin Operator (^)

El pin operator `^` se usa para referenciar el valor actual de una variable en lugar de reasignarla:

```elixir
x = 1
x = 2       # Reasignación: x ahora vale 2

x = 1
^x = 1      # Coincide: x sigue valiendo 1
# ^x = 2    # => MatchError (1 != 2)

# Uso práctico en case
valor = "elixir"
case {"elixir", 42} do
  {^valor, n} -> "Encontrado con número #{n}"
  _ -> "No coincide"
end
# => "Encontrado con número 42"
```

## Charlists y Binarios

Además de los strings, Elixir tiene charlists (listas de caracteres) heredados de Erlang:

```elixir
# Charlist (lista de code points)
charlist = ~c"hola"
charlist == [104, 111, 108, 97]  # => true

# Binarios
<<1, 2, 3>>           # Binario de 3 bytes
<<104, 111, 108, 97>> # Equivale a "hola"

# Conversión
to_string(~c"hola")            # => "hola"
to_charlist("hola")            # => ~c"hola"
```

## Resumen

Elixir ofrece un sistema de tipos expresivo que incluye átomos para etiquetas eficientes, strings UTF-8, números de precisión arbitraria y booleanos. El pattern matching como operador fundamental transforma la forma en que pensamos sobre la asignación de variables, y el pin operator nos da control explícito sobre cuándo queremos comparar versus reasignar. Estos conceptos son los cimientos sobre los que se construye todo programa Elixir.
