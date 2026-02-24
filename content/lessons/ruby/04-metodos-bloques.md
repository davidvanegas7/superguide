# Métodos y Bloques en Ruby

Los métodos y bloques son conceptos fundamentales en Ruby. Los bloques son una de las características más distintivas y poderosas del lenguaje.

---

## Métodos

### Definición básica

```ruby
def saludar(nombre)
  "Hola, #{nombre}!"
end

puts saludar("Ana")   # "Hola, Ana!"

# El return es implícito: la última expresión es el valor de retorno
def sumar(a, b)
  a + b   # retorno implícito
end

# Return explícito (para salir temprano)
def dividir(a, b)
  return "Error: división por cero" if b == 0
  a.to_f / b
end
```

### Parámetros

```ruby
# Valor por defecto
def saludar(nombre = "Mundo")
  "Hola, #{nombre}!"
end
puts saludar       # "Hola, Mundo!"
puts saludar("Ana") # "Hola, Ana!"

# Keyword arguments (nombrados)
def crear_usuario(nombre:, email:, rol: :user)
  { nombre: nombre, email: email, rol: rol }
end
crear_usuario(nombre: "Ana", email: "ana@test.com")
crear_usuario(email: "bob@test.com", nombre: "Bob", rol: :admin)

# Splat (varargs)
def suma(*numeros)
  numeros.reduce(0, :+)
end
puts suma(1, 2, 3, 4)   # 10

# Double splat (keyword varargs)
def config(**options)
  options.each { |k, v| puts "#{k}: #{v}" }
end
config(debug: true, port: 3000, host: "localhost")
```

### Convenciones de métodos

```ruby
# Métodos booleanos terminan en ?
def mayor_de_edad?(edad)
  edad >= 18
end

# Métodos destructivos terminan en !
numeros = [3, 1, 2]
numeros.sort    # retorna nuevo array [1, 2, 3], original intacto
numeros.sort!   # muta el array original

# Métodos de conversión empiezan con to_
42.to_s        # "42"
"42".to_i      # 42
[[:a, 1]].to_h # {a: 1}
```

---

## Bloques

Un bloque es un fragmento de código que se pasa a un método. Es la base de la iteración en Ruby.

### Sintaxis

```ruby
# Llaves — para una línea
[1, 2, 3].each { |n| puts n }

# do...end — para múltiples líneas
[1, 2, 3].each do |n|
  resultado = n * 2
  puts resultado
end
```

### yield — ejecutar el bloque recibido

```ruby
def ejecutar_dos_veces
  yield
  yield
end

ejecutar_dos_veces { puts "¡Hola!" }
# ¡Hola!
# ¡Hola!

# Con argumentos
def con_saludo
  yield("Ana")
  yield("Bob")
end

con_saludo { |nombre| puts "Hola, #{nombre}!" }
# Hola, Ana!
# Hola, Bob!

# block_given? — verificar si se pasó un bloque
def opcional
  if block_given?
    yield
  else
    puts "Sin bloque"
  end
end

opcional                    # "Sin bloque"
opcional { puts "¡Con bloque!" }  # "¡Con bloque!"
```

### Crear tus propios iteradores

```ruby
def repetir(n)
  i = 0
  while i < n
    yield(i)
    i += 1
  end
end

repetir(3) { |i| puts "Iteración #{i}" }
# Iteración 0
# Iteración 1
# Iteración 2

# Ejemplo práctico: benchmark
def medir
  inicio = Time.now
  resultado = yield
  fin = Time.now
  puts "Tiempo: #{fin - inicio}s"
  resultado
end

medir { (1..1_000_000).sum }
```

---

## Procs y Lambdas

Los bloques no son objetos. Para guardar un bloque como variable, usamos **Proc** o **Lambda**.

### Proc

```ruby
mi_proc = Proc.new { |n| puts n * 2 }
mi_proc.call(5)   # 10
mi_proc.(5)        # 10 (atajo)

# Desde un bloque
cuadrado = proc { |n| n ** 2 }
puts cuadrado.call(4)   # 16

# Pasar proc a un método
def aplicar(array, operacion)
  array.map { |item| operacion.call(item) }
end

doble = proc { |n| n * 2 }
puts aplicar([1, 2, 3], doble).inspect   # [2, 4, 6]
```

### Lambda

```ruby
mi_lambda = lambda { |n| n * 2 }
mi_lambda = ->(n) { n * 2 }   # sintaxis arrow (preferida)

puts mi_lambda.call(5)   # 10
puts mi_lambda.(5)        # 10
```

### Diferencias Proc vs Lambda

```ruby
# 1. Aridad: lambda verifica el número de argumentos
mi_proc = proc { |a, b| "#{a}, #{b}" }
mi_proc.call(1)          # "1, " (b = nil, sin error)

mi_lambda = ->(a, b) { "#{a}, #{b}" }
# mi_lambda.call(1)      # ArgumentError!

# 2. Return: en proc sale del método contenedor, en lambda solo del lambda
def test_proc
  mi_proc = proc { return "desde proc" }
  mi_proc.call
  "nunca llega aquí"
end
puts test_proc   # "desde proc"

def test_lambda
  mi_lambda = -> { return "desde lambda" }
  mi_lambda.call
  "sí llega aquí"
end
puts test_lambda   # "sí llega aquí"
```

### Convertir entre bloque y Proc

```ruby
# & convierte un bloque en Proc (y viceversa)
def capturar_bloque(&bloque)
  puts bloque.class    # Proc
  bloque.call("Ana")
end

capturar_bloque { |n| puts "Hola, #{n}!" }

# Pasar un Proc como bloque con &
doble = ->(n) { n * 2 }
[1, 2, 3].map(&doble)   # [2, 4, 6]

# Symbol#to_proc
[1, 2, 3].map(&:to_s)   # ["1", "2", "3"]
# Equivale a: [1, 2, 3].map { |n| n.to_s }
```

---

## Closures

Los bloques, procs y lambdas capturan el entorno donde se crean:

```ruby
def crear_contador
  count = 0
  incrementar = -> { count += 1; count }
  obtener = -> { count }
  [incrementar, obtener]
end

inc, get = crear_contador
puts inc.call   # 1
puts inc.call   # 2
puts inc.call   # 3
puts get.call   # 3
```

---

## Resumen

| Concepto | Sintaxis | Es objeto | Verifica aridad | Return |
|---|---|---|---|---|
| Bloque | `{ }` o `do...end` | ❌ | ❌ | Sale del método |
| Proc | `Proc.new { }` | ✅ | ❌ | Sale del método |
| Lambda | `-> { }` | ✅ | ✅ | Sale del lambda |
| Method | `method(:nombre)` | ✅ | ✅ | Sale del método |
