# Programación Orientada a Objetos en Ruby

En Ruby **todo es un objeto**. La POO no es una feature adicional — es el fundamento del lenguaje.

---

## Clases y objetos

```ruby
class Persona
  # Constructor
  def initialize(nombre, edad)
    @nombre = nombre   # variable de instancia
    @edad = edad
  end

  # Getter
  def nombre
    @nombre
  end

  # Setter
  def nombre=(nuevo_nombre)
    @nombre = nuevo_nombre
  end

  # Método de instancia
  def presentarse
    "Soy #{@nombre}, tengo #{@edad} años"
  end

  # Método privado
  private

  def secreto
    "información privada"
  end
end

persona = Persona.new("Ana", 28)
puts persona.nombre            # "Ana"
persona.nombre = "Ana García"
puts persona.presentarse       # "Soy Ana García, tengo 28 años"
```

### Accessors (atajos para getters/setters)

```ruby
class Usuario
  attr_reader :id                    # solo getter
  attr_writer :password              # solo setter
  attr_accessor :nombre, :email      # getter + setter

  def initialize(id, nombre, email)
    @id = id
    @nombre = nombre
    @email = email
  end
end

u = Usuario.new(1, "Ana", "ana@test.com")
puts u.id          # 1
puts u.nombre      # "Ana"
u.nombre = "Ana G" # setter
# u.id = 2         # NoMethodError (solo tiene reader)
```

---

## Herencia

```ruby
class Animal
  attr_reader :nombre

  def initialize(nombre)
    @nombre = nombre
  end

  def hablar
    raise NotImplementedError, "Subclase debe implementar #hablar"
  end

  def to_s
    "#{self.class}: #{@nombre}"
  end
end

class Perro < Animal
  def hablar
    "¡Guau!"
  end

  def buscar(objeto)
    "#{@nombre} busca #{objeto}"
  end
end

class Gato < Animal
  def hablar
    "¡Miau!"
  end
end

perro = Perro.new("Rex")
gato = Gato.new("Mishi")

puts perro.hablar     # "¡Guau!"
puts gato.hablar      # "¡Miau!"
puts perro.to_s       # "Perro: Rex"

# super — llamar al método del padre
class Cachorro < Perro
  def initialize(nombre, juguete)
    super(nombre)          # llama a Perro#initialize
    @juguete = juguete
  end

  def hablar
    "#{super} (chiquito)"  # "¡Guau! (chiquito)"
  end
end
```

---

## Módulos (Mixins)

Ruby no tiene herencia múltiple. En su lugar usa **módulos como mixins**:

```ruby
module Loggable
  def log(mensaje)
    puts "[#{self.class}] #{mensaje}"
  end
end

module Serializable
  def to_json
    vars = instance_variables.map do |var|
      "\"#{var.to_s.delete('@')}\": \"#{instance_variable_get(var)}\""
    end
    "{#{vars.join(', ')}}"
  end
end

class Producto
  include Loggable        # métodos de instancia
  include Serializable

  attr_accessor :nombre, :precio

  def initialize(nombre, precio)
    @nombre = nombre
    @precio = precio
    log("Producto creado: #{nombre}")
  end
end

p = Producto.new("Laptop", 999)    # [Producto] Producto creado: Laptop
puts p.to_json                      # {"nombre": "Laptop", "precio": "999"}

# include vs extend
module ClassMethods
  def crear_default
    new("Default", 0)
  end
end

class Producto
  extend ClassMethods    # métodos de CLASE
end

p2 = Producto.crear_default
```

### Comparable y Enumerable

```ruby
class Temperatura
  include Comparable

  attr_reader :grados

  def initialize(grados)
    @grados = grados
  end

  def <=>(other)
    @grados <=> other.grados
  end
end

temps = [Temperatura.new(30), Temperatura.new(15), Temperatura.new(25)]
puts temps.sort.map(&:grados).inspect   # [15, 25, 30]
puts temps.min.grados                    # 15
puts temps.max.grados                    # 30
```

---

## Visibilidad

```ruby
class CuentaBancaria
  def initialize(titular, saldo)
    @titular = titular
    @saldo = saldo
  end

  # Público (por defecto)
  def consultar_saldo
    "Saldo: $#{@saldo}"
  end

  def transferir(destino, monto)
    return "Fondos insuficientes" unless puede_transferir?(monto)
    restar(monto)
    destino.depositar(monto)
    "Transferencia exitosa"
  end

  # Protected: accesible desde la misma clase y subclases
  protected

  def depositar(monto)
    @saldo += monto
  end

  # Private: solo accesible desde la misma instancia
  private

  def puede_transferir?(monto)
    @saldo >= monto
  end

  def restar(monto)
    @saldo -= monto
  end
end
```

---

## Métodos de clase

```ruby
class Contador
  @@total = 0    # variable de clase

  def initialize
    @@total += 1
  end

  # Método de clase (self.nombre)
  def self.total
    @@total
  end

  # Alternativa con class << self
  class << self
    def reset
      @@total = 0
    end
  end
end

Contador.new
Contador.new
puts Contador.total    # 2
Contador.reset
puts Contador.total    # 0
```

---

## Duck Typing

Ruby no requiere que un objeto sea de un tipo específico — solo que **responda al método**:

```ruby
class Pato
  def hablar = "¡Cuac!"
end

class Persona
  def hablar = "¡Hola!"
end

class Robot
  def hablar = "Beep boop"
end

def hacer_hablar(cosa)
  if cosa.respond_to?(:hablar)
    puts cosa.hablar
  else
    puts "No sabe hablar"
  end
end

[Pato.new, Persona.new, Robot.new, "string"].each do |obj|
  hacer_hablar(obj)
end
```

---

## Struct y Data (value objects)

```ruby
# Struct — clase simple con atributos
Punto = Struct.new(:x, :y) do
  def distancia(otro)
    Math.sqrt((x - otro.x)**2 + (y - otro.y)**2)
  end
end

p1 = Punto.new(0, 0)
p2 = Punto.new(3, 4)
puts p1.distancia(p2)   # 5.0
puts p1 == Punto.new(0, 0)  # true (compara por valor)

# Data (Ruby 3.2+) — struct inmutable
Config = Data.define(:host, :port, :ssl) do
  def url
    "#{ssl ? 'https' : 'http'}://#{host}:#{port}"
  end
end

config = Config.new(host: "localhost", port: 3000, ssl: false)
puts config.url    # "http://localhost:3000"
# config.port = 8080  # Error: inmutable
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| `attr_accessor` | Genera getter + setter |
| `<` | Herencia simple |
| `include` | Mixin (métodos de instancia) |
| `extend` | Mixin (métodos de clase) |
| `private/protected` | Control de acceso |
| `self.method` | Método de clase |
| `respond_to?` | Duck typing |
| `Struct` | Value object mutable |
| `Data` | Value object inmutable (Ruby 3.2+) |
