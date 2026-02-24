# Procs, Lambdas y Programación Funcional

Ruby soporta programación funcional con first-class functions a través de Procs y Lambdas.

---

## Funciones de orden superior

```ruby
# Pasar funciones como argumentos
def aplicar(array, funcion)
  array.map { |item| funcion.call(item) }
end

doble = ->(n) { n * 2 }
cuadrado = ->(n) { n ** 2 }

puts aplicar([1, 2, 3], doble).inspect      # [2, 4, 6]
puts aplicar([1, 2, 3], cuadrado).inspect    # [1, 4, 9]

# Retornar funciones
def multiplicador(factor)
  ->(n) { n * factor }
end

triple = multiplicador(3)
puts triple.call(5)    # 15
puts triple.(10)       # 30

# Method objects
def gritar(texto)
  texto.upcase + "!"
end

m = method(:gritar)
puts m.call("hola")                          # "HOLA!"
puts ["hola", "mundo"].map(&m).inspect       # ["HOLA!", "MUNDO!"]
```

---

## Composición de funciones

```ruby
# Operador >> (Ruby 2.6+)
doble = ->(n) { n * 2 }
incrementar = ->(n) { n + 1 }
cuadrado = ->(n) { n ** 2 }

# Componer: primero doble, luego incrementar
transformar = doble >> incrementar
puts transformar.call(5)    # 11 (5*2=10, 10+1=11)

# Operador << (orden inverso)
transformar2 = incrementar << doble
puts transformar2.call(5)   # 11 (mismo resultado)

# Cadena más larga
pipeline = doble >> incrementar >> cuadrado
puts pipeline.call(3)       # 49 (3*2=6, 6+1=7, 7²=49)
```

---

## Currying

```ruby
# curry — convierte una función de N args en N funciones de 1 arg
suma = ->(a, b) { a + b }
suma_curried = suma.curry

suma_5 = suma_curried.(5)
puts suma_5.(3)       # 8
puts suma_5.(10)      # 15

# Currying práctico
filtrar = ->(condicion, array) { array.select(&condicion) }
filtrar_pares = filtrar.curry.(method(:even?).to_proc)

# Multiplicador con curry
multiplicar = ->(a, b) { a * b }.curry
doble = multiplicar.(2)
triple = multiplicar.(3)

puts [1, 2, 3].map(&doble).inspect    # [2, 4, 6]
puts [1, 2, 3].map(&triple).inspect   # [3, 6, 9]
```

---

## Memoization

```ruby
# Básico con hash
def fibonacci(n, memo = {})
  return n if n <= 1
  memo[n] ||= fibonacci(n - 1, memo) + fibonacci(n - 2, memo)
end

puts fibonacci(100)   # instantáneo

# Patrón memoize genérico
module Memoizable
  def memoize(method_name)
    original = instance_method(method_name)
    define_method(method_name) do |*args|
      @_memo ||= {}
      key = [method_name, args]
      @_memo[key] ||= original.bind(self).call(*args)
    end
  end
end

class Calculadora
  extend Memoizable

  def factorial(n)
    return 1 if n <= 1
    n * factorial(n - 1)
  end

  memoize :factorial
end
```

---

## Pipelines con then/yield_self

```ruby
# then (alias de yield_self, Ruby 2.6+)
resultado = 5
  .then { |n| n * 2 }
  .then { |n| n + 1 }
  .then { |n| n.to_s }
  .then { |s| "Resultado: #{s}" }
# "Resultado: 11"

# Práctico: transformar datos
usuario = { nombre: "  Ana García  ", email: "ANA@TEST.COM" }

procesado = usuario
  .then { |u| u.merge(nombre: u[:nombre].strip) }
  .then { |u| u.merge(email: u[:email].downcase) }
  .then { |u| u.merge(slug: u[:nombre].downcase.gsub(" ", "-")) }
# {nombre: "Ana García", email: "ana@test.com", slug: "ana-garcía"}

# tap — ejecutar side effects sin romper la cadena
[3, 1, 4, 1, 5]
  .tap { |a| puts "Original: #{a}" }
  .sort
  .tap { |a| puts "Ordenado: #{a}" }
  .reverse
  .tap { |a| puts "Invertido: #{a}" }
```

---

## Patrones funcionales

### Map / Filter / Reduce

```ruby
pedidos = [
  { producto: "Laptop", precio: 999, cantidad: 1 },
  { producto: "Mouse", precio: 29, cantidad: 3 },
  { producto: "Monitor", precio: 499, cantidad: 2 },
  { producto: "Teclado", precio: 79, cantidad: 1 },
  { producto: "Cable", precio: 9, cantidad: 5 }
]

# Total de pedidos mayores a $50
total_premium = pedidos
  .map { |p| { **p, total: p[:precio] * p[:cantidad] } }
  .select { |p| p[:total] > 50 }
  .reduce(0) { |sum, p| sum + p[:total] }
# 2076
```

### Either pattern

```ruby
class Either
  attr_reader :value

  def self.right(value) = Right.new(value)
  def self.left(value) = Left.new(value)
end

class Right < Either
  def initialize(value) = @value = value
  def right? = true
  def left? = false
  def map(&block) = Right.new(block.call(@value))
  def flat_map(&block) = block.call(@value)
  def or_else(_) = self
end

class Left < Either
  def initialize(value) = @value = value
  def right? = false
  def left? = true
  def map(&_block) = self
  def flat_map(&_block) = self
  def or_else(default) = Right.new(default)
end

def parse_int(str)
  Integer(str)
    .then { |n| Either.right(n) }
rescue ArgumentError
  Either.left("'#{str}' no es un número")
end

resultado = parse_int("42")
  .map { |n| n * 2 }
  .map { |n| "Resultado: #{n}" }

puts resultado.value   # "Resultado: 84"

error = parse_int("abc")
  .map { |n| n * 2 }
  .or_else(0)

puts error.value   # 0
```

---

## Enumerator como generador

```ruby
# Crear secuencias infinitas
naturales = Enumerator.new do |y|
  n = 1
  loop do
    y.yield n
    n += 1
  end
end

puts naturales.lazy.select(&:odd?).first(5).inspect
# [1, 3, 5, 7, 9]

# Collatz sequence
def collatz(n)
  Enumerator.new do |y|
    current = n
    loop do
      y.yield current
      break if current == 1
      current = current.even? ? current / 2 : current * 3 + 1
    end
  end
end

puts collatz(27).to_a.length   # 112 pasos
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| `->() { }` | Lambda (función anónima) |
| `curry` | Aplicación parcial |
| `>>` / `<<` | Composición de funciones |
| `then` | Pipeline de transformaciones |
| `tap` | Side effects en cadena |
| `method(:name)` | Referencia a método |
| `lazy` | Evaluación diferida |
| Memoize | Cache de resultados |
