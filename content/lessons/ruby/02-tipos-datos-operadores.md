# Tipos de Datos y Operadores en Ruby

Ruby tiene un sistema de tipos dinámico pero fuerte. Cada valor es un objeto con métodos.

---

## Tipos principales

### Integers y Floats

```ruby
entero = 42
flotante = 3.14
grande = 1_000_000   # separador visual

puts entero.is_a?(Integer)   # true
puts entero.is_a?(Numeric)   # true (herencia)

# Conversiones
puts "42".to_i       # 42
puts "3.14".to_f     # 3.14
puts 42.to_f         # 42.0
puts 3.14.to_i       # 3

# Métodos útiles
puts 255.to_s(16)    # "ff" (hexadecimal)
puts 10.to_s(2)      # "1010" (binario)
puts 5.between?(1, 10)  # true
```

### Strings

```ruby
# Creación
simple = 'Sin interpolación: #{1+1}'   # literal
doble = "Con interpolación: #{1+1}"   # "Con interpolación: 2"
heredoc = <<~TEXT
  Texto multilínea
  con indentación limpia
TEXT

# Métodos de búsqueda
"Hola Mundo".start_with?("Hola")    # true
"Hola Mundo".end_with?("Mundo")     # true
"Hola Mundo".index("Mundo")         # 5

# Manipulación
"hola mundo".capitalize     # "Hola mundo"
"hola mundo".split(" ")     # ["hola", "mundo"]
"a-b-c".split("-")          # ["a", "b", "c"]
["a", "b", "c"].join(", ")  # "a, b, c"
"hola".chars                # ["h", "o", "l", "a"]

# Reemplazo
"Hola Mundo".sub("Mundo", "Ruby")    # "Hola Ruby" (primera)
"aaa".gsub("a", "b")                # "bbb" (todas)

# Slicing
str = "Ruby es genial"
puts str[0]      # "R"
puts str[0..3]   # "Ruby"
puts str[-6..]   # "genial"
```

### Symbols

```ruby
# Los Symbols son strings inmutables y únicos en memoria
status = :active
role = :admin

# Ideal para keys de hashes y nombres internos
puts :active.object_id == :active.object_id   # true (mismo objeto)
puts "active".object_id == "active".object_id # false (objetos distintos)

# Conversión
puts :hello.to_s   # "hello"
puts "hello".to_sym # :hello
```

### Booleans y nil

```ruby
# Valores falsy: solo nil y false
puts !!nil     # false
puts !!false   # false

# TODO lo demás es truthy
puts !!0       # true (¡diferente de JavaScript!)
puts !!""      # true (¡diferente de Python!)
puts !![]      # true
```

---

## Arrays

```ruby
# Creación
numeros = [1, 2, 3, 4, 5]
mixto = [1, "dos", :tres, [4, 5]]
vacio = []
palabras = %w[hola mundo ruby]   # ["hola", "mundo", "ruby"]

# Acceso
puts numeros[0]     # 1
puts numeros[-1]    # 5
puts numeros[1..3]  # [2, 3, 4]
puts numeros.first  # 1
puts numeros.last   # 5

# Métodos esenciales
numeros.push(6)          # [1, 2, 3, 4, 5, 6]
numeros << 7             # [1, 2, 3, 4, 5, 6, 7] (shovel operator)
numeros.pop              # 7 (retorna y elimina último)
numeros.unshift(0)       # [0, 1, 2, 3, 4, 5, 6]
numeros.shift            # 0 (retorna y elimina primero)

# Búsqueda
[1, 2, 3].include?(2)   # true
[1, 2, 3].index(2)      # 1
[3, 1, 2].sort           # [1, 2, 3]
[1, 2, 2, 3].uniq        # [1, 2, 3]
[1, 2, 3].reverse        # [3, 2, 1]

# Transformación
[1, 2, 3].map { |n| n * 2 }        # [2, 4, 6]
[1, 2, 3, 4].select { |n| n.even? } # [2, 4]
[1, 2, 3, 4].reject { |n| n.even? } # [1, 3]
[1, 2, 3].reduce(0) { |sum, n| sum + n }  # 6

# Información
[1, 2, 3].length   # 3
[1, 2, 3].count    # 3
[].empty?          # true
[1, 2, 3].any? { |n| n > 2 }  # true
[1, 2, 3].all? { |n| n > 0 }  # true
```

---

## Hashes

```ruby
# Creación (symbol keys — forma moderna)
persona = {
  nombre: "Ana",
  edad: 28,
  activo: true
}

# Acceso
puts persona[:nombre]    # "Ana"
puts persona[:email]     # nil (no existe)
puts persona.fetch(:nombre)              # "Ana"
puts persona.fetch(:email, "N/A")        # "N/A" (con default)

# Modificación
persona[:email] = "ana@test.com"
persona.delete(:activo)

# Iteración
persona.each do |key, value|
  puts "#{key}: #{value}"
end

# Métodos útiles
persona.keys     # [:nombre, :edad, :email]
persona.values   # ["Ana", 28, "ana@test.com"]
persona.key?(:nombre)    # true (alias: has_key?)
persona.value?("Ana")   # true (alias: has_value?)
persona.merge({ role: :admin })  # nuevo hash combinado

# Dig (acceso seguro anidado)
data = { user: { address: { city: "Madrid" } } }
puts data.dig(:user, :address, :city)    # "Madrid"
puts data.dig(:user, :phone, :number)    # nil (sin error)
```

---

## Ranges

```ruby
# Inclusivo
(1..5).to_a        # [1, 2, 3, 4, 5]

# Exclusivo
(1...5).to_a       # [1, 2, 3, 4]

# Métodos
(1..10).include?(5)    # true
(1..10).min            # 1
(1..10).max            # 10
(1..10).sum            # 55

# Con letras
('a'..'f').to_a    # ["a", "b", "c", "d", "e", "f"]
```

---

## Operadores

### Comparación

```ruby
1 == 1       # true
1 != 2       # true  
1 <=> 2      # -1 (spaceship operator: -1, 0, 1)
2 <=> 2      # 0
3 <=> 2      # 1

# Igualdad de tipo
1 == 1.0     # true (valor)
1.eql?(1.0)  # false (valor + tipo)
1.equal?(1)  # true (mismo objeto)
```

### Lógicos

```ruby
true && false    # false
true || false    # true
!true            # false

# Versiones legibles
true and false   # false
true or false    # true
not true         # false

# Operador ternario
edad = 20
estado = edad >= 18 ? "adulto" : "menor"
```

### Asignación

```ruby
x = 10
x += 5    # 15
x -= 3    # 12
x *= 2    # 24
x /= 4   # 6

# Asignación condicional
nombre = nil
nombre ||= "Default"   # "Default" (asigna si es nil/false)
nombre ||= "Otro"      # "Default" (no reasigna)
```

---

## Resumen

| Tipo | Ejemplo | Mutable | Notas |
|---|---|---|---|
| Integer | `42` | ❌ | Todo es objeto |
| Float | `3.14` | ❌ | |
| String | `"hola"` | ✅ | ¡Los strings son mutables! |
| Symbol | `:active` | ❌ | Inmutable, singleton |
| Array | `[1, 2, 3]` | ✅ | Ordenado, indexado |
| Hash | `{a: 1}` | ✅ | Key-value |
| Range | `1..10` | ❌ | Lazily evaluated |
| Boolean | `true/false` | ❌ | Solo nil y false son falsy |
| NilClass | `nil` | ❌ | Ausencia de valor |
