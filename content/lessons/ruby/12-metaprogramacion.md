# Metaprogramación en Ruby

La metaprogramación es una de las capacidades más poderosas de Ruby: código que escribe código.

---

## Introspección

```ruby
class Persona
  attr_accessor :nombre, :edad

  def initialize(nombre, edad)
    @nombre = nombre
    @edad = edad
  end

  def saludar
    "Hola, soy #{@nombre}"
  end

  private

  def secreto
    "info privada"
  end
end

persona = Persona.new("Ana", 28)

# Inspeccionar el objeto
puts persona.class                           # Persona
puts persona.is_a?(Persona)                  # true
puts persona.respond_to?(:saludar)           # true
puts persona.respond_to?(:secreto)           # false
puts persona.respond_to?(:secreto, true)     # true (incluye privados)

# Métodos
puts persona.methods.count                   # ~70+
puts persona.public_methods(false).inspect   # [:nombre, :nombre=, :edad, :edad=, :saludar]
puts persona.private_methods(false).inspect  # [:secreto]

# Variables de instancia
puts persona.instance_variables.inspect      # [:@nombre, :@edad]
puts persona.instance_variable_get(:@nombre) # "Ana"
persona.instance_variable_set(:@nombre, "Ana García")

# Clase
puts Persona.instance_methods(false).inspect    # [:nombre, :nombre=, ...]
puts Persona.superclass                          # Object
puts Persona.ancestors.inspect                   # [Persona, Object, Kernel, BasicObject]
```

---

## define_method — métodos dinámicos

```ruby
class Modelo
  CAMPOS = [:nombre, :email, :edad]

  CAMPOS.each do |campo|
    # Getter
    define_method(campo) do
      instance_variable_get("@#{campo}")
    end

    # Setter
    define_method("#{campo}=") do |valor|
      instance_variable_set("@#{campo}", valor)
    end

    # Validador
    define_method("#{campo}?") do
      !instance_variable_get("@#{campo}").nil?
    end
  end
end

m = Modelo.new
m.nombre = "Ana"
puts m.nombre       # "Ana"
puts m.nombre?      # true
puts m.email?       # false
```

---

## method_missing — interceptar llamadas

```ruby
class FlexibleHash
  def initialize(data = {})
    @data = data
  end

  def method_missing(name, *args)
    key = name.to_s

    if key.end_with?("=")
      @data[key.chomp("=").to_sym] = args.first
    elsif key.end_with?("?")
      @data.key?(key.chomp("?").to_sym)
    elsif @data.key?(key.to_sym)
      @data[key.to_sym]
    else
      super
    end
  end

  def respond_to_missing?(name, include_private = false)
    key = name.to_s.chomp("=").chomp("?").to_sym
    @data.key?(key) || super
  end

  def to_s
    @data.inspect
  end
end

config = FlexibleHash.new(host: "localhost", port: 3000)
puts config.host       # "localhost"
puts config.port       # 3000
puts config.host?      # true
puts config.debug?     # false
config.debug = true
puts config.debug      # true
```

---

## class_eval y instance_eval

```ruby
# class_eval — evalúa código en el contexto de la clase
class Persona; end

Persona.class_eval do
  attr_accessor :nombre

  def saludar
    "Hola, soy #{@nombre}"
  end
end

p = Persona.new
p.nombre = "Ana"
puts p.saludar   # "Hola, soy Ana"

# instance_eval — evalúa en el contexto de una instancia
obj = Object.new
obj.instance_eval do
  @nombre = "Ruby"
  def hablar
    "Soy #{@nombre}"
  end
end
puts obj.hablar   # "Soy Ruby"

# DSL con instance_eval
class Constructor
  attr_reader :config

  def initialize(&block)
    @config = {}
    instance_eval(&block) if block
  end

  def nombre(valor)
    @config[:nombre] = valor
  end

  def version(valor)
    @config[:version] = valor
  end

  def descripcion(valor)
    @config[:descripcion] = valor
  end
end

app = Constructor.new do
  nombre "MiApp"
  version "1.0"
  descripcion "Una app genial"
end

puts app.config.inspect
# {nombre: "MiApp", version: "1.0", descripcion: "Una app genial"}
```

---

## Hooks (callbacks)

```ruby
module Trackable
  def self.included(base)
    base.extend(ClassMethods)
    base.instance_variable_set(:@tracked_methods, [])
  end

  module ClassMethods
    def track(method_name)
      @tracked_methods << method_name

      original = instance_method(method_name)

      define_method(method_name) do |*args|
        puts "[TRACK] #{self.class}##{method_name} llamado con #{args}"
        inicio = Time.now
        resultado = original.bind(self).call(*args)
        puts "[TRACK] Completado en #{Time.now - inicio}s"
        resultado
      end
    end
  end
end

class Calculadora
  include Trackable

  def sumar(a, b)
    a + b
  end

  def factorial(n)
    return 1 if n <= 1
    n * factorial(n - 1)
  end

  track :sumar
  track :factorial
end

calc = Calculadora.new
calc.sumar(3, 4)
# [TRACK] Calculadora#sumar llamado con [3, 4]
# [TRACK] Completado en 0.00001s
```

---

## Patrón DSL (Domain Specific Language)

```ruby
class Router
  attr_reader :routes

  def initialize(&block)
    @routes = []
    instance_eval(&block)
  end

  def get(path, to:)
    @routes << { method: :get, path: path, handler: to }
  end

  def post(path, to:)
    @routes << { method: :post, path: path, handler: to }
  end

  def put(path, to:)
    @routes << { method: :put, path: path, handler: to }
  end

  def delete(path, to:)
    @routes << { method: :delete, path: path, handler: to }
  end

  def resources(name)
    get "/#{name}", to: "#{name}#index"
    get "/#{name}/:id", to: "#{name}#show"
    post "/#{name}", to: "#{name}#create"
    put "/#{name}/:id", to: "#{name}#update"
    delete "/#{name}/:id", to: "#{name}#destroy"
  end
end

router = Router.new do
  get "/", to: "home#index"
  get "/about", to: "pages#about"
  resources :users
  resources :posts
end

router.routes.each do |r|
  puts "#{r[:method].upcase.to_s.ljust(7)} #{r[:path].ljust(20)} => #{r[:handler]}"
end
```

---

## Resumen

| Técnica | Uso |
|---|---|
| `define_method` | Crear métodos dinámicamente |
| `method_missing` | Interceptar métodos no definidos |
| `class_eval` | Evaluar código en contexto de clase |
| `instance_eval` | Evaluar código en contexto de instancia |
| `send` | Llamar métodos (incluso privados) |
| `respond_to?` | ¿Responde al método? |
| Hooks | `included`, `inherited`, `method_added` |
| DSL | Lenguajes de dominio con instance_eval |
