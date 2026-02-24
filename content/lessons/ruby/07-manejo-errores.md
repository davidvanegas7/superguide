# Manejo de Errores y Excepciones

Ruby tiene un sistema robusto de excepciones que permite manejar errores de forma elegante.

---

## Jerarquía de excepciones

```
Exception
├── NoMemoryError
├── ScriptError
│   ├── LoadError
│   └── SyntaxError
├── SignalException
│   └── Interrupt
└── StandardError          ← rescue captura estas por defecto
    ├── ArgumentError
    ├── IOError
    ├── NameError
    │   └── NoMethodError
    ├── RangeError
    ├── RuntimeError       ← raise sin clase usa esta
    ├── TypeError
    ├── ZeroDivisionError
    └── StopIteration
```

---

## begin / rescue / ensure

```ruby
begin
  resultado = 10 / 0
rescue ZeroDivisionError => e
  puts "Error: #{e.message}"       # "Error: divided by 0"
  puts "Clase: #{e.class}"         # "Clase: ZeroDivisionError"
  puts "Backtrace: #{e.backtrace.first}"
ensure
  puts "Esto siempre se ejecuta"   # como finally
end

# Múltiples tipos de error
begin
  # código peligroso
rescue ArgumentError => e
  puts "Argumento inválido: #{e.message}"
rescue TypeError => e
  puts "Tipo incorrecto: #{e.message}"
rescue StandardError => e
  puts "Error general: #{e.message}"
end

# rescue en una línea
valor = Integer("abc") rescue nil   # nil (en vez de error)
```

### retry

```ruby
intentos = 0

begin
  intentos += 1
  puts "Intento #{intentos}..."
  raise "Error temporal" if intentos < 3
  puts "¡Éxito!"
rescue RuntimeError => e
  retry if intentos < 3
  puts "Falló después de #{intentos} intentos"
end
```

---

## raise — lanzar excepciones

```ruby
# Formas de raise
raise "Algo salió mal"                          # RuntimeError
raise ArgumentError, "Parámetro inválido"       # tipo específico
raise ArgumentError.new("Parámetro inválido")   # equivalente

# En métodos
def dividir(a, b)
  raise ArgumentError, "b no puede ser cero" if b == 0
  a.to_f / b
end

begin
  dividir(10, 0)
rescue ArgumentError => e
  puts e.message   # "b no puede ser cero"
end
```

---

## Excepciones personalizadas

```ruby
# Convención: heredar de StandardError
class AppError < StandardError; end
class NotFoundError < AppError; end
class ValidationError < AppError
  attr_reader :field, :code

  def initialize(message, field: nil, code: nil)
    super(message)
    @field = field
    @code = code
  end
end

class AuthenticationError < AppError; end
class AuthorizationError < AppError; end

# Uso
def buscar_usuario(id)
  raise NotFoundError, "Usuario #{id} no encontrado" unless id > 0
  { id: id, nombre: "Ana" }
end

def validar_email(email)
  unless email.match?(/\A[\w.]+@[\w.]+\z/)
    raise ValidationError.new(
      "Email inválido",
      field: :email,
      code: :format
    )
  end
end

begin
  validar_email("invalido")
rescue ValidationError => e
  puts "#{e.message} (campo: #{e.field}, código: #{e.code})"
  # "Email inválido (campo: email, código: format)"
end
```

---

## rescue en métodos (sin begin)

```ruby
# Ruby permite rescue directo en métodos
def leer_archivo(path)
  File.read(path)
rescue Errno::ENOENT
  "Archivo no encontrado: #{path}"
rescue Errno::EACCES
  "Sin permisos para: #{path}"
end

puts leer_archivo("/no/existe")   # "Archivo no encontrado: ..."
```

---

## throw / catch (control de flujo)

```ruby
# throw/catch NO es para excepciones, es para saltar niveles
resultado = catch(:encontrado) do
  [1, 2, 3].each do |x|
    [4, 5, 6].each do |y|
      if x + y == 7
        throw :encontrado, [x, y]   # sale de ambos loops
      end
    end
  end
  nil   # si no se encuentra
end

puts resultado.inspect   # [1, 6] o [2, 5] o [3, 4]
```

---

## Patrones de manejo de errores

### Result Object

```ruby
class Result
  attr_reader :value, :error

  def initialize(value: nil, error: nil)
    @value = value
    @error = error
  end

  def success? = @error.nil?
  def failure? = !success?

  def self.success(value) = new(value: value)
  def self.failure(error) = new(error: error)
end

def dividir_seguro(a, b)
  return Result.failure("División por cero") if b == 0
  Result.success(a.to_f / b)
end

resultado = dividir_seguro(10, 3)
if resultado.success?
  puts "Resultado: #{resultado.value}"
else
  puts "Error: #{resultado.error}"
end
```

### Logging de errores

```ruby
def with_error_logging
  yield
rescue StandardError => e
  $stderr.puts "[ERROR] #{e.class}: #{e.message}"
  $stderr.puts e.backtrace.first(5).join("\n")
  raise   # re-lanza la excepción
end

with_error_logging do
  # código que puede fallar
end
```

---

## Resumen

| Concepto | Uso |
|---|---|
| `begin/rescue/ensure` | Manejar excepciones |
| `raise` | Lanzar excepciones |
| `retry` | Reintentar el bloque begin |
| `rescue =>` | Capturar y nombrar la excepción |
| `ensure` | Código que siempre ejecuta |
| `StandardError` | Base para excepciones custom |
| `throw/catch` | Control de flujo (no excepciones) |
| `rescue` inline | `valor rescue default` |
