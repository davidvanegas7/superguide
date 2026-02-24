# Preguntas de Entrevista: Ruby

Preguntas técnicas frecuentes en entrevistas de trabajo para desarrolladores Ruby.

---

## 1. ¿Qué diferencia hay entre Symbol y String?

```ruby
# Symbol: inmutable, singleton (un solo objeto en memoria)
:hello.object_id == :hello.object_id   # true

# String: mutable, cada instancia es un objeto nuevo
"hello".object_id == "hello".object_id  # false

# Symbols son ideales para:
# - Keys de hashes
# - Nombres de métodos
# - Identificadores internos
# - Comparación rápida (compara object_id)
```

---

## 2. ¿Qué es el GVL y cómo afecta a la concurrencia?

```ruby
# GVL (Global VM Lock) / GIL — previene que múltiples threads
# ejecuten código Ruby al mismo tiempo en CRuby/MRI

# Threads SÍ son útiles para I/O (HTTP, files, DB)
# porque Ruby libera el GVL durante operaciones I/O

# Para paralelismo real:
# - Ractors (Ruby 3.0+)
# - fork/Process
# - JRuby o TruffleRuby (sin GVL)
```

---

## 3. Explica la diferencia entre Proc y Lambda

```ruby
# 1. Aridad — Lambda verifica argumentos
mi_proc = proc { |a, b| [a, b] }
mi_proc.call(1)        # [1, nil] — sin error

mi_lambda = ->(a, b) { [a, b] }
# mi_lambda.call(1)    # ArgumentError!

# 2. Return — Proc sale del método contenedor
def test_proc
  p = proc { return 42 }
  p.call
  "nunca llega"   # no se ejecuta
end

def test_lambda
  l = -> { return 42 }
  l.call
  "sí llega"   # sí se ejecuta
end
```

---

## 4. ¿Qué es duck typing?

```ruby
# Ruby no verifica tipos — solo que el objeto responda al método
# "If it walks like a duck and quacks like a duck, it's a duck"

def area(shape)
  shape.area   # no importa la clase, solo que tenga #area
end

class Circle
  def initialize(r) = @r = r
  def area = Math::PI * @r ** 2
end

class Square
  def initialize(s) = @s = s
  def area = @s ** 2
end

# Ambos funcionan con el mismo método
area(Circle.new(5))    # 78.54
area(Square.new(4))    # 16
```

---

## 5. ¿Qué es method_missing y cuándo usarlo?

```ruby
# method_missing intercepta llamadas a métodos no definidos
# Siempre debe implementarse con respond_to_missing?

class DynamicProxy
  def method_missing(name, *args, &block)
    if name.to_s.start_with?("find_by_")
      field = name.to_s.sub("find_by_", "")
      puts "Buscando por #{field}: #{args.first}"
    else
      super   # IMPORTANTE: siempre llamar super para métodos no manejados
    end
  end

  def respond_to_missing?(name, include_private = false)
    name.to_s.start_with?("find_by_") || super
  end
end

# Cuándo usarlo: proxies, DSLs, ActiveRecord-style finders
# Cuándo NO: cuando define_method es suficiente (mejor rendimiento)
```

---

## 6. Explica include, extend y prepend

```ruby
module Greetable
  def greet = "Hello from #{self.class}"
end

class A
  include Greetable    # métodos de INSTANCIA (después de A en ancestors)
end
A.new.greet   # funciona

class B
  extend Greetable     # métodos de CLASE
end
B.greet       # funciona

class C
  def greet = "Original"
  prepend Greetable    # ANTES de C en ancestors (puede hacer super)
end
C.new.greet   # "Hello from C" (módulo tiene prioridad)

# Ancestor chain:
# include: [A, Greetable, Object]
# prepend: [Greetable, C, Object]
```

---

## 7. ¿Qué son los bloques y yield?

```ruby
# Un bloque es código que se pasa a un método
# yield ejecuta el bloque

def with_logging
  puts "Inicio"
  result = yield          # ejecuta el bloque
  puts "Fin"
  result
end

with_logging { 2 + 2 }   # Inicio, 4, Fin

# yield con argumentos
def transform(value)
  yield(value)
end

transform(5) { |n| n * 2 }   # 10

# block_given? para hacer el bloque opcional
def maybe_transform(value)
  block_given? ? yield(value) : value
end
```

---

## 8. ¿Cómo funciona la herencia en Ruby?

```ruby
# Ruby tiene herencia simple (una sola superclase)
# Composición mediante módulos (mixins)

class Animal
  def breathe = "Respirando..."
end

class Dog < Animal          # herencia simple
  def speak = "Woof!"
end

# Para "herencia múltiple" → módulos
module Swimmable
  def swim = "Nadando..."
end

module Fetchable
  def fetch(item) = "Buscando #{item}..."
end

class Labrador < Dog
  include Swimmable
  include Fetchable
end

lab = Labrador.new
lab.breathe   # de Animal
lab.speak     # de Dog
lab.swim      # de Swimmable
lab.fetch("pelota")  # de Fetchable
```

---

## 9. ¿Qué es freeze y para qué sirve?

```ruby
# freeze hace un objeto inmutable
str = "hello".freeze
# str << " world"   # FrozenError!
str.frozen?          # true

# frozen_string_literal: true (magic comment)
# Congela TODOS los strings del archivo

# Cuándo usar:
# - Constantes
# - Strings que se pasan como keys
# - Prevenir mutaciones accidentales
# - Performance (Ruby reutiliza frozen strings)

# IMPORTANTE: freeze es shallow
arr = [1, [2, 3]].freeze
# arr << 4          # FrozenError
arr[1] << 4         # ¡Funciona! [2, 3, 4] — el inner array no está frozen
```

---

## 10. ¿Qué es el pattern matching en Ruby 3?

```ruby
# case/in — structural pattern matching
response = { status: 200, body: { users: [{ name: "Ana" }] } }

case response
in { status: 200, body: { users: [{ name: String => name }, *] } }
  puts "Primer usuario: #{name}"
in { status: 404 }
  puts "No encontrado"
in { status: (500..) }
  puts "Error del servidor"
end

# Find pattern
case [1, 2, "error", 3]
in [*, String => msg, *]
  puts "Error encontrado: #{msg}"
end

# In expression (boolean)
if response in { status: 200 }
  puts "OK"
end
```

---

## 11. ¿Cómo manejas errores en Ruby?

```ruby
# begin/rescue/ensure
begin
  risky_operation
rescue SpecificError => e
  handle_specific(e)
rescue StandardError => e
  handle_general(e)
  raise   # re-raise si es necesario
ensure
  cleanup   # siempre ejecuta
end

# Custom exceptions
class AppError < StandardError; end
class NotFoundError < AppError; end

# rescue inline
value = dangerous_call rescue default_value

# NUNCA rescatar Exception (incluye Interrupt, NoMemoryError, etc.)
# SIEMPRE rescatar StandardError o subclases
```

---

## 12. ¿Qué es Enumerable y por qué es importante?

```ruby
# Enumerable es un módulo que proporciona 50+ métodos de iteración
# Solo necesitas implementar #each

class WordCollection
  include Enumerable

  def initialize(text)
    @words = text.split
  end

  def each(&block)
    @words.each(&block)
  end
end

wc = WordCollection.new("Ruby es un lenguaje genial")
wc.map(&:upcase)           # ["RUBY", "ES", "UN", "LENGUAJE", "GENIAL"]
wc.select { |w| w.length > 3 }  # ["Ruby", "lenguaje", "genial"]
wc.sort                     # ordenados
wc.count                    # 5
wc.min_by(&:length)         # "es"
```

---

## Resumen de temas clave

| Tema | Concepto clave |
|---|---|
| Symbol vs String | Inmutable/singleton vs mutable/nuevo objeto |
| GVL | Previene paralelismo de threads, usar Ractors |
| Proc vs Lambda | Aridad + comportamiento de return |
| Duck Typing | respond_to? en vez de is_a? |
| method_missing | + respond_to_missing?, preferir define_method |
| include/extend/prepend | Instance/class methods, ancestor chain |
| Bloques/yield | Código pasado a métodos |
| freeze | Inmutabilidad (shallow) |
| Pattern matching | case/in, deconstruct, find pattern |
| Enumerable | Implementar each, obtener 50+ métodos |
