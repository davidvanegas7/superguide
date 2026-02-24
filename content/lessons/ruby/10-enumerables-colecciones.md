# Enumerables y Colecciones Avanzadas

El módulo `Enumerable` es una de las joyas de Ruby. Proporciona métodos de iteración y transformación potentes.

---

## Enumerable en profundidad

Cualquier clase que implemente `each` e incluya `Enumerable` obtiene +50 métodos:

```ruby
class Rango
  include Enumerable

  def initialize(desde, hasta)
    @desde = desde
    @hasta = hasta
  end

  def each
    current = @desde
    while current <= @hasta
      yield current
      current += 1
    end
  end
end

r = Rango.new(1, 5)
puts r.map { |n| n * 2 }.inspect    # [2, 4, 6, 8, 10]
puts r.select(&:odd?).inspect        # [1, 3, 5]
puts r.reduce(:+)                    # 15
```

---

## Métodos de transformación

```ruby
numeros = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]

# map / collect — transformar cada elemento
cuadrados = numeros.map { |n| n ** 2 }
# [1, 4, 9, 16, 25, 36, 49, 64, 81, 100]

# flat_map — map + flatten
oraciones = ["hola mundo", "ruby es genial"]
palabras = oraciones.flat_map { |s| s.split }
# ["hola", "mundo", "ruby", "es", "genial"]

# filter_map (Ruby 2.7+) — map + compact
emails = ["ana@test.com", "", "bob@test.com", nil, ""]
validos = emails.filter_map { |e| e&.strip unless e.nil? || e.empty? }
# ["ana@test.com", "bob@test.com"]

# zip — combinar arrays
nombres = ["Ana", "Bob", "Carlos"]
edades = [28, 35, 42]
nombres.zip(edades)
# [["Ana", 28], ["Bob", 35], ["Carlos", 42]]

# each_slice — dividir en grupos
(1..10).each_slice(3).to_a
# [[1, 2, 3], [4, 5, 6], [7, 8, 9], [10]]

# each_cons — ventana deslizante
[1, 2, 3, 4, 5].each_cons(3).to_a
# [[1, 2, 3], [2, 3, 4], [3, 4, 5]]
```

---

## Métodos de filtrado

```ruby
numeros = [15, 3, 42, 7, 28, 91, 6, 53]

# select / filter
pares = numeros.select(&:even?)          # [42, 28, 6]

# reject
impares = numeros.reject(&:even?)        # [15, 3, 7, 91, 53]

# partition — divide en dos arrays
pares, impares = numeros.partition(&:even?)
# pares: [42, 28, 6], impares: [15, 3, 7, 91, 53]

# find / detect — primer match
primero = numeros.find { |n| n > 20 }    # 42

# find_index
indice = numeros.find_index { |n| n > 20 }  # 2

# take_while / drop_while
[1, 3, 5, 2, 4].take_while(&:odd?)      # [1, 3, 5]
[1, 3, 5, 2, 4].drop_while(&:odd?)      # [2, 4]

# grep — filtrar por patrón (usa ===)
["ruby", "rails", "python", "react"].grep(/r/)
# ["ruby", "rails", "react"]

[1, "dos", 3, "cuatro"].grep(Integer)    # [1, 3]
[1, "dos", 3, "cuatro"].grep(String)     # ["dos", "cuatro"]
```

---

## Métodos de reducción

```ruby
numeros = [1, 2, 3, 4, 5]

# reduce / inject
suma = numeros.reduce(0) { |acc, n| acc + n }   # 15
prod = numeros.reduce(1) { |acc, n| acc * n }   # 120

# Con symbol
suma = numeros.reduce(:+)    # 15
prod = numeros.reduce(:*)    # 120

# Construir un hash con reduce
palabras = ["ruby", "es", "genial", "ruby", "es"]
frecuencia = palabras.reduce(Hash.new(0)) do |hash, palabra|
  hash[palabra] += 1
  hash
end
# {"ruby"=>2, "es"=>2, "genial"=>1}

# tally (Ruby 2.7+) — atajo para frecuencia
frecuencia = palabras.tally
# {"ruby"=>2, "es"=>2, "genial"=>1}

# each_with_object — como reduce pero más claro
frecuencia = palabras.each_with_object(Hash.new(0)) do |palabra, hash|
  hash[palabra] += 1
end
```

---

## Métodos de verificación

```ruby
numeros = [2, 4, 6, 8]

numeros.all?(&:even?)      # true — ¿todos cumplen?
numeros.any?(&:odd?)       # false — ¿alguno cumple?
numeros.none?(&:odd?)      # true — ¿ninguno cumple?
numeros.one? { |n| n > 7 } # true — ¿exactamente uno?
numeros.count(&:even?)     # 4 — ¿cuántos cumplen?
numeros.include?(4)        # true — ¿contiene?
```

---

## Métodos de ordenación

```ruby
# sort
[3, 1, 4, 1, 5].sort                    # [1, 1, 3, 4, 5]
[3, 1, 4, 1, 5].sort { |a, b| b <=> a } # [5, 4, 3, 1, 1]

# sort_by (más eficiente para transformaciones)
personas = [
  { nombre: "Carlos", edad: 42 },
  { nombre: "Ana", edad: 28 },
  { nombre: "Bob", edad: 35 }
]

personas.sort_by { |p| p[:edad] }
# Ana(28), Bob(35), Carlos(42)

personas.sort_by { |p| [-p[:edad], p[:nombre]] }
# Carlos(42), Bob(35), Ana(28) — descendente por edad

# min, max, minmax
[3, 1, 4, 1, 5].min           # 1
[3, 1, 4, 1, 5].max           # 5
[3, 1, 4, 1, 5].minmax        # [1, 5]

# min_by, max_by
personas.min_by { |p| p[:edad] }   # Ana
personas.max_by { |p| p[:edad] }   # Carlos
```

---

## Lazy Enumerators

Para colecciones infinitas o muy grandes:

```ruby
# Sin lazy: evalúa TODO antes de tomar
# (1..Float::INFINITY).select(&:odd?).first(5)  # ¡nunca termina!

# Con lazy: evalúa bajo demanda
(1..Float::INFINITY)
  .lazy
  .select(&:odd?)
  .map { |n| n ** 2 }
  .first(5)
# [1, 9, 25, 49, 81]

# Generador Fibonacci
fib = Enumerator.new do |yielder|
  a, b = 0, 1
  loop do
    yielder.yield a
    a, b = b, a + b
  end
end

puts fib.lazy.select(&:odd?).first(10).inspect
# [1, 1, 3, 5, 13, 21, 55, 89, 233, 377]
```

---

## Hash avanzado

```ruby
datos = { a: 1, b: 2, c: 3, d: 4, e: 5 }

# transform_keys / transform_values
datos.transform_keys(&:to_s)         # {"a"=>1, "b"=>2, ...}
datos.transform_values { |v| v * 10 } # {a: 10, b: 20, ...}

# slice — subconjunto
datos.slice(:a, :c)                  # {a: 1, c: 3}

# except (Ruby 3.0+) — excluir keys
datos.except(:a, :b)                 # {c: 3, d: 4, e: 5}

# select / reject en hashes
datos.select { |k, v| v > 2 }       # {c: 3, d: 4, e: 5}

# group_by
personas = [
  { nombre: "Ana", dept: "IT" },
  { nombre: "Bob", dept: "HR" },
  { nombre: "Carlos", dept: "IT" }
]

por_depto = personas.group_by { |p| p[:dept] }
# {"IT" => [{nombre: "Ana"...}, {nombre: "Carlos"...}], "HR" => [...]}

# chunk — agrupa consecutivos
[1, 1, 2, 2, 2, 3, 1, 1].chunk { |n| n }.to_a
# [[1, [1, 1]], [2, [2, 2, 2]], [3, [3]], [1, [1, 1]]]
```

---

## Set

```ruby
require 'set'

s1 = Set.new([1, 2, 3, 4])
s2 = Set.new([3, 4, 5, 6])

s1 & s2         # Set[3, 4] (intersección)
s1 | s2         # Set[1, 2, 3, 4, 5, 6] (unión)
s1 - s2         # Set[1, 2] (diferencia)
s1 ^ s2         # Set[1, 2, 5, 6] (diferencia simétrica)

s1.add(5)        # Set[1, 2, 3, 4, 5]
s1.include?(3)   # true
s1.subset?(s2)   # false
```

---

## Resumen

| Método | Descripción |
|---|---|
| `map` / `flat_map` | Transformar elementos |
| `select` / `reject` | Filtrar |
| `find` | Primer match |
| `reduce` | Acumular |
| `tally` | Contar frecuencias |
| `partition` | Dividir en dos |
| `group_by` | Agrupar por criterio |
| `sort_by` | Ordenar por transformación |
| `lazy` | Evaluación diferida |
| `grep` | Filtrar por patrón |
