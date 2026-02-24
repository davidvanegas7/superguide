# Patrones de Diseño en Ruby

Los patrones de diseño se implementan de forma elegante en Ruby gracias a su naturaleza dinámica.

---

## Singleton

```ruby
require 'singleton'

class Configuration
  include Singleton

  attr_accessor :debug, :log_level, :database_url

  def initialize
    @debug = false
    @log_level = :info
    @database_url = "postgres://localhost/mydb"
  end
end

# Siempre la misma instancia
config1 = Configuration.instance
config2 = Configuration.instance
puts config1.equal?(config2)   # true

config1.debug = true
puts config2.debug             # true (misma instancia)

# Sin la gema — implementación manual
class Logger
  @instance = nil
  @mutex = Mutex.new

  def self.instance
    @mutex.synchronize do
      @instance ||= new
    end
  end

  private_class_method :new

  def log(message)
    puts "[#{Time.now}] #{message}"
  end
end
```

---

## Observer

```ruby
module Observable
  def self.included(base)
    base.instance_variable_set(:@observers, [])
    base.extend(ClassMethods)
  end

  module ClassMethods
    def add_observer(observer)
      @observers << observer
    end

    def observers
      @observers
    end
  end

  def notify_observers(event, data = {})
    self.class.observers.each do |observer|
      observer.update(event, data) if observer.respond_to?(:update)
    end
  end
end

class Store
  include Observable

  attr_reader :products

  def initialize
    @products = []
  end

  def add_product(product)
    @products << product
    notify_observers(:product_added, product: product)
  end

  def remove_product(product)
    @products.delete(product)
    notify_observers(:product_removed, product: product)
  end
end

class InventoryLogger
  def update(event, data)
    puts "[LOG] #{event}: #{data}"
  end
end

class EmailNotifier
  def update(event, data)
    puts "[EMAIL] Notificación de #{event}" if event == :product_added
  end
end

Store.add_observer(InventoryLogger.new)
Store.add_observer(EmailNotifier.new)

store = Store.new
store.add_product({ name: "Laptop", price: 999 })
```

---

## Strategy

```ruby
class ShippingCalculator
  def initialize(strategy)
    @strategy = strategy
  end

  def calculate(package)
    @strategy.call(package)
  end
end

# Estrategias como lambdas
standard = ->(pkg) { pkg[:weight] * 2.5 }
express = ->(pkg) { pkg[:weight] * 5.0 + 10 }
free = ->(_pkg) { 0 }

package = { weight: 5, destination: "Madrid" }

calc = ShippingCalculator.new(standard)
puts calc.calculate(package)    # 12.5

calc = ShippingCalculator.new(express)
puts calc.calculate(package)    # 35.0

# Con clases (más extensible)
class StandardShipping
  def calculate(package)
    package[:weight] * 2.5
  end
end

class ExpressShipping
  def calculate(package)
    package[:weight] * 5.0 + 10
  end
end

class ShippingService
  def initialize(strategy: StandardShipping.new)
    @strategy = strategy
  end

  def cost(package)
    @strategy.calculate(package)
  end
end
```

---

## Decorator

```ruby
# Con SimpleDelegator
require 'delegate'

class Coffee
  def cost = 2.0
  def description = "Café"
end

class MilkDecorator < SimpleDelegator
  def cost
    super + 0.5
  end

  def description
    "#{super} con leche"
  end
end

class SugarDecorator < SimpleDelegator
  def cost
    super + 0.25
  end

  def description
    "#{super} con azúcar"
  end
end

class WhipDecorator < SimpleDelegator
  def cost
    super + 0.75
  end

  def description
    "#{super} con crema"
  end
end

coffee = Coffee.new
coffee = MilkDecorator.new(coffee)
coffee = SugarDecorator.new(coffee)
coffee = WhipDecorator.new(coffee)

puts coffee.description   # "Café con leche con azúcar con crema"
puts coffee.cost          # 3.5

# Con módulos (más Ruby-like)
module Timestamped
  def save
    @updated_at = Time.now
    super
  end
end

module Validated  
  def save
    raise "Invalid!" unless valid?
    super
  end

  def valid?
    true  # implementar
  end
end

class Record
  prepend Timestamped
  prepend Validated

  def save
    puts "Guardando..."
  end
end
```

---

## Builder

```ruby
class QueryBuilder
  def initialize(table)
    @table = table
    @conditions = []
    @order = nil
    @limit_val = nil
    @select_cols = ["*"]
  end

  def select(*columns)
    @select_cols = columns
    self
  end

  def where(condition)
    @conditions << condition
    self
  end

  def order(column, direction = :asc)
    @order = "#{column} #{direction.upcase}"
    self
  end

  def limit(n)
    @limit_val = n
    self
  end

  def to_sql
    sql = "SELECT #{@select_cols.join(', ')} FROM #{@table}"
    sql += " WHERE #{@conditions.join(' AND ')}" unless @conditions.empty?
    sql += " ORDER BY #{@order}" if @order
    sql += " LIMIT #{@limit_val}" if @limit_val
    sql
  end
end

query = QueryBuilder.new(:users)
  .select(:name, :email)
  .where("age > 18")
  .where("active = true")
  .order(:name)
  .limit(10)
  .to_sql

puts query
# SELECT name, email FROM users WHERE age > 18 AND active = true ORDER BY name ASC LIMIT 10
```

---

## Repository

```ruby
class UserRepository
  def initialize(adapter)
    @adapter = adapter
  end

  def find(id)
    @adapter.find(:users, id)
  end

  def find_by_email(email)
    @adapter.where(:users, email: email).first
  end

  def create(attrs)
    @adapter.insert(:users, attrs)
  end

  def update(id, attrs)
    @adapter.update(:users, id, attrs)
  end

  def delete(id)
    @adapter.delete(:users, id)
  end

  def active_users
    @adapter.where(:users, active: true)
  end
end

# Se puede cambiar el adapter sin tocar la lógica
# repo = UserRepository.new(PostgresAdapter.new)
# repo = UserRepository.new(InMemoryAdapter.new)  # para tests
```

---

## Service Object

```ruby
class CreateOrder
  def initialize(user:, items:, payment_method:)
    @user = user
    @items = items
    @payment_method = payment_method
  end

  def call
    validate_items!
    order = build_order
    process_payment!(order)
    send_confirmation(order)
    order
  rescue PaymentError => e
    { error: e.message }
  end

  private

  def validate_items!
    raise "No hay items" if @items.empty?
  end

  def build_order
    {
      user: @user,
      items: @items,
      total: @items.sum { |i| i[:price] * i[:quantity] },
      status: :pending
    }
  end

  def process_payment!(order)
    # lógica de pago
    order[:status] = :paid
  end

  def send_confirmation(order)
    # enviar email
  end
end

# Uso
result = CreateOrder.new(
  user: current_user,
  items: cart_items,
  payment_method: :credit_card
).call
```

---

## Resumen

| Patrón | Uso en Ruby |
|---|---|
| Singleton | `include Singleton` |
| Observer | Módulo Observable con callbacks |
| Strategy | Lambdas o clases intercambiables |
| Decorator | `SimpleDelegator` o `prepend` |
| Builder | Method chaining con `self` |
| Repository | Abstrae acceso a datos |
| Service Object | Encapsula lógica de negocio |
