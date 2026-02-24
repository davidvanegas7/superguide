# Introducción a Ruby

**Ruby** es un lenguaje de programación interpretado, orientado a objetos y diseñado para la **productividad y felicidad del programador**. Creado por Yukihiro "Matz" Matsumoto en 1995.

---

## Filosofía de Ruby

> "Ruby está diseñado para hacer a los programadores felices." — Matz

- **Principio de menor sorpresa**: el lenguaje se comporta como esperas
- **Todo es un objeto**: incluso los números y `nil`
- **Sintaxis expresiva**: código legible que parece pseudocódigo
- **Múltiples formas de hacer las cosas**: flexibilidad por diseño

---

## Instalación

### Con rbenv (recomendado)

```bash
# Instalar rbenv
curl -fsSL https://github.com/rbenv/rbenv-installer/raw/HEAD/bin/rbenv-installer | bash

# Añadir al shell
echo 'eval "$(rbenv init -)"' >> ~/.bashrc
source ~/.bashrc

# Instalar Ruby
rbenv install 3.3.0
rbenv global 3.3.0

ruby -v   # ruby 3.3.0
```

### Verificar la instalación

```bash
ruby -e 'puts "¡Hola desde Ruby!"'
irb   # Interactive Ruby (REPL)
```

---

## Primeros pasos

### Variables y tipos

```ruby
# Variables locales (snake_case)
nombre = "Ana"
edad = 28
activo = true
precio = 19.99

# Todo es un objeto
puts nombre.class   # String
puts edad.class     # Integer
puts activo.class   # TrueClass
puts precio.class   # Float

# nil = ausencia de valor
valor = nil
puts valor.nil?     # true
```

### Strings

```ruby
nombre = "Ruby"

# Interpolación (solo con comillas dobles)
puts "Hola, #{nombre}!"        # "Hola, Ruby!"
puts "2 + 2 = #{2 + 2}"        # "2 + 2 = 4"

# Métodos de string
puts nombre.length              # 4
puts nombre.upcase              # "RUBY"
puts nombre.downcase            # "ruby"
puts nombre.reverse             # "ybuR"
puts nombre.include?("ub")      # true
puts "  hola  ".strip           # "hola"

# Métodos con ! (mutan el original)
texto = "hola"
texto.upcase!
puts texto   # "HOLA"
```

### Números

```ruby
# Enteros
puts 10 + 3     # 13
puts 10 - 3     # 7
puts 10 * 3     # 30
puts 10 / 3     # 3 (división entera)
puts 10.0 / 3   # 3.333... (división float)
puts 10 % 3     # 1 (módulo)
puts 2 ** 10    # 1024 (potencia)

# Métodos numéricos
puts 42.even?    # true
puts 42.odd?     # false
puts -5.abs      # 5
puts 3.14.round  # 3
puts 3.14.ceil   # 4
puts 3.14.floor  # 3
```

---

## Entrada/Salida

```ruby
# Salida
puts "Con salto de línea"
print "Sin salto de línea"
p [1, 2, 3]   # inspección (muestra el tipo)

# Entrada
print "Tu nombre: "
nombre = gets.chomp   # chomp elimina el \n
puts "Hola, #{nombre}"
```

---

## Convenciones de nombres

| Tipo | Convención | Ejemplo |
|---|---|---|
| Variable local | snake_case | `mi_variable` |
| Constante | SCREAMING_SNAKE | `MAX_SIZE` |
| Clase/Módulo | PascalCase | `MiClase` |
| Método | snake_case | `calcular_total` |
| Método booleano | termina en ? | `empty?`, `valid?` |
| Método destructivo | termina en ! | `sort!`, `map!` |
| Variable de instancia | @prefijo | `@nombre` |
| Variable de clase | @@prefijo | `@@contador` |
| Variable global | $prefijo | `$debug` |

---

## Ruby vs otros lenguajes

| Aspecto | Ruby | Python | JavaScript |
|---|---|---|---|
| Filosofía | Felicidad del programador | Legibilidad | Versatilidad |
| Tipado | Dinámico, fuerte | Dinámico, fuerte | Dinámico, débil |
| Todo es objeto | ✅ | ✅ (casi) | ❌ |
| Bloques/Closures | ✅ First-class | ✅ Lambda | ✅ Functions |
| Metaprogramación | ✅ Muy potente | 🟡 Limitada | 🟡 Proxy |
| Framework web | Rails | Django/Flask | Express/Next |

---

## Resumen

| Concepto | Descripción |
|---|---|
| Todo es objeto | `42.even?`, `"hola".length` |
| Interpolación | `"Hola, #{nombre}"` |
| Convención ? | Métodos que retornan boolean |
| Convención ! | Métodos que mutan el receptor |
| `nil` | Ausencia de valor, también es un objeto |
| `puts` / `print` / `p` | Salida con/sin newline/inspección |
