# Estructuras de Control en Ruby

Ruby ofrece estructuras de control expresivas con múltiples formas de escribirlas.

---

## Condicionales

### if / elsif / else

```ruby
edad = 20

if edad >= 18
  puts "Adulto"
elsif edad >= 13
  puts "Adolescente"
else
  puts "Niño"
end

# Forma inline (modifier)
puts "Mayor de edad" if edad >= 18
puts "Menor de edad" unless edad >= 18
```

### unless (if negado)

```ruby
usuario = nil

unless usuario
  puts "No hay usuario logueado"
end

# Equivale a:
if !usuario
  puts "No hay usuario logueado"
end
```

### case / when (pattern matching)

```ruby
dia = "lunes"

case dia
when "lunes", "martes", "miércoles", "jueves", "viernes"
  puts "Día laboral"
when "sábado", "domingo"
  puts "Fin de semana"
else
  puts "Día inválido"
end

# Con rangos
nota = 85
case nota
when 90..100 then "A"
when 80..89  then "B"
when 70..79  then "C"
when 60..69  then "D"
else "F"
end

# Con clases
valor = 42
case valor
when Integer then "Es un entero"
when String  then "Es un string"
when Array   then "Es un array"
end

# Con regex
email = "user@example.com"
case email
when /\A[\w.]+@gmail\.com\z/ then "Gmail"
when /\A[\w.]+@yahoo\.com\z/ then "Yahoo"
else "Otro proveedor"
end
```

### Pattern Matching (Ruby 3+)

```ruby
# in pattern
data = { name: "Ana", role: :admin, age: 28 }

case data
in { name: String => name, role: :admin }
  puts "Admin: #{name}"
in { name: String => name, role: :user }
  puts "User: #{name}"
end

# Deconstruct arrays
case [1, 2, 3]
in [Integer => a, Integer => b, *rest]
  puts "a=#{a}, b=#{b}, rest=#{rest}"
end

# Guard conditions
case { score: 95 }
in { score: (90..) => s }
  puts "Excelente: #{s}"
in { score: (70..89) => s }
  puts "Bueno: #{s}"
end
```

---

## Bucles

### while y until

```ruby
# while
i = 0
while i < 5
  puts i
  i += 1
end

# until (while negado)
i = 0
until i >= 5
  puts i
  i += 1
end

# Forma inline
i = 0
i += 1 while i < 5
```

### for (poco usado en Ruby)

```ruby
for i in 1..5
  puts i
end

# Se prefiere .each:
(1..5).each { |i| puts i }
```

### loop

```ruby
loop do
  puts "¿Continuar? (s/n)"
  respuesta = gets.chomp
  break if respuesta == "n"
end
```

### Iteradores numéricos

```ruby
# N veces
5.times { |i| puts "Iteración #{i}" }   # 0..4

# Desde hasta
1.upto(5) { |i| puts i }    # 1, 2, 3, 4, 5
5.downto(1) { |i| puts i }  # 5, 4, 3, 2, 1

# Con paso
(0..20).step(5) { |i| puts i }  # 0, 5, 10, 15, 20
```

---

## Control de flujo

```ruby
# break — sale del bucle
(1..10).each do |i|
  break if i > 5
  puts i
end
# 1, 2, 3, 4, 5

# next — salta a la siguiente iteración
(1..10).each do |i|
  next if i.even?
  puts i
end
# 1, 3, 5, 7, 9

# return — sale del método (solo dentro de métodos)
def buscar(items, target)
  items.each do |item|
    return item if item == target
  end
  nil
end
```

---

## Iteradores sobre colecciones

```ruby
numeros = [10, 25, 3, 47, 8, 36]

# each — itera sin retornar nuevo array
numeros.each { |n| puts n }

# map — transforma cada elemento
dobles = numeros.map { |n| n * 2 }

# select / filter — filtra
pares = numeros.select(&:even?)

# reject — filtra inverso
impares = numeros.reject(&:even?)

# find — primer match
primero_mayor_20 = numeros.find { |n| n > 20 }  # 25

# reduce / inject — acumula
suma = numeros.reduce(0) { |acc, n| acc + n }    # 129
suma = numeros.sum                                 # 129 (atajo)

# each_with_index
numeros.each_with_index do |n, i|
  puts "#{i}: #{n}"
end

# each_with_object
resultado = numeros.each_with_object({}) do |n, hash|
  hash[n] = n.even? ? "par" : "impar"
end

# Encadenamiento
numeros
  .select { |n| n > 10 }
  .map { |n| n * 2 }
  .sort
  .reverse
# [94, 72, 50]

# flat_map
[[1, 2], [3, 4], [5]].flat_map { |arr| arr }  # [1, 2, 3, 4, 5]

# group_by
numeros.group_by(&:even?)
# { false => [25, 3, 47], true => [10, 8, 36] }

# min, max, minmax
numeros.min      # 3
numeros.max      # 47
numeros.minmax   # [3, 47]
numeros.sort     # [3, 8, 10, 25, 36, 47]
```

---

## Operador &: (Symbol#to_proc)

```ruby
# Atajo para bloques simples de un método
["hola", "mundo"].map(&:upcase)     # ["HOLA", "MUNDO"]
[1, 2, 3, 4].select(&:even?)       # [2, 4]
["a", "b", "c"].map(&:to_sym)      # [:a, :b, :c]

# Equivale a:
["hola", "mundo"].map { |s| s.upcase }
```

---

## Resumen

| Estructura | Uso |
|---|---|
| `if/elsif/else` | Condicional estándar |
| `unless` | `if` negado (legibilidad) |
| `case/when` | Multi-branch con ranges, clases, regex |
| `case/in` | Pattern matching (Ruby 3+) |
| `.each` | Iterar sin transformar |
| `.map` | Transformar cada elemento |
| `.select` / `.reject` | Filtrar / filtrar inverso |
| `.reduce` | Acumular resultados |
| `.find` | Primer match |
| `&:method` | Atajo para bloques simples |
