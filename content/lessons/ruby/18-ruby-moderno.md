# Ruby Moderno (3.x): Novedades y Best Practices

Las últimas versiones de Ruby han traído mejoras significativas en sintaxis, rendimiento y funcionalidad.

---

## Ruby 3.0 — Ractors y tipos

### Pattern Matching (estable)

```ruby
# case/in con deconstruct
case { name: "Ana", age: 28, role: :admin }
in { name: String => name, role: :admin }
  puts "Admin: #{name}"
in { name: String => name, role: :user }
  puts "User: #{name}"
end

# Find pattern
case [1, 2, 3, "error", 4, 5]
in [*, String => s, *]
  puts "Encontrado string: #{s}"   # "error"
end

# Pin operator (comparar con variable existente)
expected = 42
case { value: 42 }
in { value: ^expected }
  puts "Match!"
end

# Guard
case { score: 95 }
in { score: (90..) => s } if s < 100
  puts "Excelente: #{s}"
end
```

### Ractors

```ruby
# Paralelismo real sin GVL
ractor = Ractor.new do
  "Resultado desde Ractor"
end
puts ractor.take   # "Resultado desde Ractor"

# Pipeline
r1 = Ractor.new { Ractor.yield 1; Ractor.yield 2; Ractor.yield 3 }
r2 = Ractor.new(r1) do |source|
  3.times { Ractor.yield source.take * 10 }
end

3.times { puts r2.take }   # 10, 20, 30
```

### RBS (tipos estáticos)

```ruby
# sig/calculadora.rbs
class Calculadora
  def sumar: (Integer, Integer) -> Integer
  def dividir: (Numeric, Numeric) -> Float
  def nombre: () -> String
end
```

```bash
# Verificar tipos
gem install steep
steep check
```

---

## Ruby 3.1

### Anonymous block forwarding

```ruby
# Antes
def wrap(&block)
  puts "antes"
  block.call
  puts "después"
end

# Ruby 3.1 — &anónimo
def wrap(&)
  puts "antes"
  yield
  puts "después"
end
```

### Hash#values_in (propuesta) y mejoras de Hash

```ruby
# Shorthand hash syntax (como JavaScript)
x = 1
y = 2
punto = { x:, y: }   # equivale a { x: x, y: y }
puts punto            # {x: 1, y: 2}

# Útil en métodos
def crear_usuario(nombre, email)
  { nombre:, email: }   # { nombre: nombre, email: email }
end
```

### irb mejorado

```bash
irb   # Ahora con autocompletado y colores por defecto
```

---

## Ruby 3.2

### Data class (value objects inmutables)

```ruby
# Reemplazo moderno de Struct para objetos inmutables
Point = Data.define(:x, :y)
Color = Data.define(:r, :g, :b)

p = Point.new(x: 3, y: 4)
puts p.x      # 3
puts p.y      # 4
# p.x = 5    # NoMethodError — inmutable!

# Con métodos custom
Money = Data.define(:amount, :currency) do
  def to_s
    "#{amount} #{currency}"
  end

  def +(other)
    raise "Currency mismatch" unless currency == other.currency
    Money.new(amount: amount + other.amount, currency: currency)
  end
end

total = Money.new(amount: 100, currency: "EUR") + Money.new(amount: 50, currency: "EUR")
puts total   # "150 EUR"

# Deconstruct para pattern matching
case Point.new(x: 0, y: 0)
in Point[x: 0, y: 0]
  puts "Origen"
in Point[x:, y:] if x == y
  puts "Diagonal"
end
```

### Regexp timeout

```ruby
# Prevenir ReDoS (regex denial of service)
Regexp.timeout = 1.0   # timeout global de 1 segundo

# O por regex
/complex_pattern/.timeout   
```

---

## Ruby 3.3

### YJIT mejorado (producción-ready)

```bash
# YJIT — JIT compiler para mejor rendimiento
ruby --yjit app.rb

# O con variable de entorno
RUBY_YJIT_ENABLE=1 ruby app.rb
```

### Prism parser

```ruby
# Nuevo parser por defecto (más rápido y mantenible)
require 'prism'

result = Prism.parse("1 + 2")
puts result.value.inspect
```

### Range#overlap?

```ruby
(1..5).overlap?(3..7)    # true
(1..5).overlap?(6..10)   # false
```

---

## Best Practices modernas

### Frozen string literals

```ruby
# frozen_string_literal: true

# Todos los strings son inmutables por defecto
nombre = "Ruby"
# nombre << " 3.3"   # FrozenError!
nombre = nombre + " 3.3"   # Crea nuevo string — OK
```

### Endless method (Ruby 3.0+)

```ruby
# Para métodos de una línea
def cuadrado(n) = n ** 2
def saludar(nombre) = "Hola, #{nombre}!"
def admin?(user) = user.role == :admin

# Con multiple expressions (usar begin)
def full_name = "#{first_name} #{last_name}"
```

### Pattern matching en assignments

```ruby
# Deconstruct en asignación
{ name: "Ana", age: 28 } => { name:, age: }
puts name   # "Ana"
puts age    # 28

# Con arrays
[1, 2, 3] => [first, *rest]
puts first   # 1
puts rest    # [2, 3]

# In expressions
{ name: "Ana", age: 28 } in { name: /^A/ }   # true
```

### Numbered block parameters

```ruby
# _1, _2, _3... en vez de |a, b, c|
[1, 2, 3].map { _1 * 2 }           # [2, 4, 6]
[[1, 2], [3, 4]].map { _1 + _2 }   # [3, 7]

# Útil para bloques simples
users.sort_by { _1[:name] }
hash.select { _2 > 10 }
```

### Error highlighting

```ruby
# Ruby 3.1+ muestra exactamente dónde está el error
# undefined method `upcase' for nil:NilClass
#
#     name.upcase
#          ^^^^^^^ 
```

---

## Configuración de proyecto moderno

```ruby
# .ruby-version
3.3.0

# Gemfile
source 'https://rubygems.org'

ruby '~> 3.3'

gem 'zeitwerk'      # autoloading
gem 'dry-types'     # type system
gem 'dry-struct'    # typed structs
gem 'concurrent-ruby'  # concurrencia

group :development, :test do
  gem 'rspec', '~> 3.13'
  gem 'rubocop', '~> 1.60'
  gem 'rubocop-rspec'
  gem 'debug'
end

# .rubocop.yml  
AllCops:
  TargetRubyVersion: 3.3
  NewCops: enable

Style/FrozenStringLiteralComment:
  EnforcedStyle: always
```

---

## Resumen

| Ruby 3.x | Característica |
|---|---|
| 3.0 | Ractors, Pattern matching, RBS |
| 3.1 | Hash shorthand, `&` anónimo |
| 3.2 | `Data.define`, Regexp timeout |
| 3.3 | YJIT producción, Prism parser |
| Best | `frozen_string_literal`, endless methods |
