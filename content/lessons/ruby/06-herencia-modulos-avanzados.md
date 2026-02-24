# Herencia, Módulos y Mixins Avanzados

Profundizamos en la herencia y el sistema de módulos de Ruby, incluyendo patrones avanzados.

---

## Cadena de ancestros (Ancestor chain)

Ruby resuelve los métodos siguiendo la **ancestor chain**:

```ruby
module Saludable
  def estado
    "Excelente"
  end
end

module Deportista
  def actividad
    "Correr"
  end
end

class Persona
  include Saludable
  include Deportista
end

puts Persona.ancestors.inspect
# [Persona, Deportista, Saludable, Object, Kernel, BasicObject]

# El método se busca en este orden:
# 1. Persona
# 2. Deportista (último include primero)
# 3. Saludable
# 4. Object
# 5. Kernel (módulo incluido en Object)
# 6. BasicObject
```

---

## Hooks de módulos

```ruby
module Plugin
  def self.included(base)
    puts "#{self} incluido en #{base}"
    base.extend(ClassMethods)
  end

  module ClassMethods
    def plugin_name
      "Plugin v1"
    end
  end

  def plugin_info
    "Info del plugin"
  end
end

class App
  include Plugin    # "Plugin incluido en App"
end

puts App.plugin_name        # "Plugin v1"
puts App.new.plugin_info    # "Info del plugin"
```

### prepend vs include

```ruby
module Logging
  def saludar
    puts "LOG: llamando a saludar"
    resultado = super    # llama al método original
    puts "LOG: saludar completado"
    resultado
  end
end

class Servicio
  prepend Logging    # se inserta ANTES en la cadena

  def saludar
    "Hola desde Servicio"
  end
end

Servicio.new.saludar
# LOG: llamando a saludar
# LOG: saludar completado

puts Servicio.ancestors.inspect
# [Logging, Servicio, Object, Kernel, BasicObject]
```

---

## Patrón Concern (estilo Rails)

```ruby
module Authenticatable
  def self.included(base)
    base.extend(ClassMethods)
    base.instance_variable_set(:@auth_field, :email)
  end

  module ClassMethods
    def authenticate_by(field)
      @auth_field = field
    end

    def auth_field
      @auth_field
    end
  end

  def authenticate(password)
    puts "Autenticando #{send(self.class.auth_field)} con password"
    true
  end
end

class User
  include Authenticatable
  authenticate_by :username

  attr_accessor :username, :email

  def initialize(username, email)
    @username = username
    @email = email
  end
end

user = User.new("ana123", "ana@test.com")
puts User.auth_field           # :username
user.authenticate("secret")   # "Autenticando ana123 con password"
```

---

## Herencia vs Composición

```ruby
# ❌ Herencia profunda — frágil
class Animal; end
class Mamifero < Animal; end
class Perro < Mamifero; end
class PerroGuia < Perro; end

# ✅ Composición con módulos — flexible
module Nadador
  def nadar
    "#{nombre} está nadando"
  end
end

module Volador
  def volar
    "#{nombre} está volando"
  end
end

module Corredor
  def correr
    "#{nombre} está corriendo"
  end
end

class Pato
  include Nadador, Volador, Corredor
  attr_reader :nombre

  def initialize(nombre)
    @nombre = nombre
  end
end

class Pinguino
  include Nadador, Corredor
  attr_reader :nombre

  def initialize(nombre)
    @nombre = nombre
  end
end

pato = Pato.new("Donald")
puts pato.nadar    # "Donald está nadando"
puts pato.volar    # "Donald está volando"

pinguino = Pinguino.new("Tux")
puts pinguino.nadar   # "Tux está nadando"
# pinguino.volar       # NoMethodError
```

---

## Refinements (monkey patching seguro)

```ruby
# Refinement — extiende una clase solo en un scope
module StringExtensions
  refine String do
    def palindromo?
      self == self.reverse
    end

    def word_count
      split.length
    end
  end
end

# Sin using:
# "ana".palindromo?   # NoMethodError

# Con using:
using StringExtensions
puts "ana".palindromo?            # true
puts "hola mundo".word_count      # 2
```

---

## Method Missing

```ruby
class DynamicConfig
  def initialize(data = {})
    @data = data
  end

  def method_missing(name, *args)
    key = name.to_s

    if key.end_with?("=")
      @data[key.chomp("=").to_sym] = args.first
    elsif @data.key?(key.to_sym)
      @data[key.to_sym]
    else
      super   # lanza NoMethodError si no se maneja
    end
  end

  def respond_to_missing?(name, include_private = false)
    key = name.to_s.chomp("=").to_sym
    @data.key?(key) || super
  end
end

config = DynamicConfig.new(host: "localhost", port: 3000)
puts config.host    # "localhost"
puts config.port    # 3000
config.debug = true
puts config.debug   # true
```

---

## Frozen Objects

```ruby
# Congelar un objeto para hacerlo inmutable
config = { host: "localhost", port: 3000 }.freeze
# config[:host] = "other"   # FrozenError!

# Congelar string
nombre = "Ruby".freeze
# nombre << " 3.3"   # FrozenError!

# Strings congelados por defecto (magic comment)
# frozen_string_literal: true
# (al inicio del archivo)
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| `ancestors` | Cadena de búsqueda de métodos |
| `include` | Mixin después de la clase |
| `prepend` | Mixin antes de la clase |
| `extend` | Añade métodos de clase |
| `self.included` | Hook al ser incluido |
| `method_missing` | Intercepta métodos no definidos |
| `respond_to_missing?` | Complemento de method_missing |
| `refine` | Monkey patching con scope |
| `freeze` | Hace un objeto inmutable |
