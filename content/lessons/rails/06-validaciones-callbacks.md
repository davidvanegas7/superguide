# Validaciones y Callbacks en Active Record

Las validaciones garantizan que solo datos válidos se guarden en la base de datos. Los callbacks permiten ejecutar lógica automáticamente en momentos clave del ciclo de vida de un modelo.

---

## ¿Por qué validar?

Sin validaciones, podrías guardar datos inconsistentes:

```ruby
# ❌ Sin validaciones — se guarda un artículo sin título
Article.create(title: "", body: nil)

# ✅ Con validaciones — Rails rechaza datos inválidos
class Article < ApplicationRecord
  validates :title, presence: true
  validates :body,  presence: true
end

article = Article.new(title: "", body: nil)
article.valid?  # => false
article.save    # => false (no se guarda)
article.errors.full_messages
# => ["Title can't be blank", "Body can't be blank"]
```

---

## Validaciones integradas

### presence — campo no vacío

```ruby
class Article < ApplicationRecord
  validates :title, presence: true
  validates :body,  presence: true
end
```

### uniqueness — valor único

```ruby
class User < ApplicationRecord
  validates :email, uniqueness: true

  # Case-insensitive
  validates :email, uniqueness: { case_sensitive: false }

  # Único dentro de un scope
  validates :slug, uniqueness: { scope: :category }
end
```

> 💡 Siempre complementa `uniqueness` con un índice único en la base de datos para evitar condiciones de carrera.

### length — longitud del texto

```ruby
class Article < ApplicationRecord
  validates :title, length: { minimum: 5 }
  validates :title, length: { maximum: 200 }
  validates :title, length: { in: 5..200 }

  validates :summary, length: {
    maximum: 500,
    too_long: "no puede exceder los %{count} caracteres"
  }

  validates :password, length: { is: 8 }  # exactamente 8 caracteres
end
```

### format — expresión regular

```ruby
class User < ApplicationRecord
  validates :email, format: {
    with: /\A[\w+\-.]+@[a-z\d\-]+(\.[a-z\d\-]+)*\.[a-z]+\z/i,
    message: "no tiene un formato válido"
  }

  validates :username, format: {
    with: /\A[a-zA-Z0-9_]+\z/,
    message: "solo permite letras, números y guiones bajos"
  }
end
```

### numericality — valores numéricos

```ruby
class Product < ApplicationRecord
  validates :price, numericality: true                        # es un número
  validates :price, numericality: { greater_than: 0 }         # mayor que 0
  validates :stock, numericality: { only_integer: true }      # solo enteros
  validates :discount, numericality: {
    greater_than_or_equal_to: 0,
    less_than_or_equal_to: 100
  }
end
```

### inclusion y exclusion

```ruby
class Article < ApplicationRecord
  validates :status, inclusion: {
    in: %w[draft published archived],
    message: "%{value} no es un estado válido"
  }

  validates :category, exclusion: {
    in: %w[spam test],
    message: "la categoría %{value} no está permitida"
  }
end
```

### acceptance — aceptar términos

```ruby
class User < ApplicationRecord
  validates :terms_of_service, acceptance: true
end
```

### confirmation — confirmar campo

```ruby
class User < ApplicationRecord
  validates :email, confirmation: true
  # El usuario debe llenar email y email_confirmation
end
```

---

## Opciones comunes de validación

```ruby
class Article < ApplicationRecord
  # Solo validar al crear
  validates :slug, uniqueness: true, on: :create

  # Solo validar al actualizar
  validates :reason, presence: true, on: :update

  # Validar condicionalmente
  validates :body, presence: true, if: :published?
  validates :draft_note, presence: true, unless: :published?

  # Con un proc
  validates :special_field, presence: true, if: -> { category == "premium" }

  # Permitir nil o blank
  validates :website, format: { with: /\Ahttps?:\/\/.*\z/ }, allow_blank: true
  validates :age, numericality: true, allow_nil: true

  private

  def published?
    status == "published"
  end
end
```

---

## Validaciones personalizadas

### Método validate

```ruby
class Article < ApplicationRecord
  validate :title_cannot_contain_spam
  validate :publish_date_in_future, on: :create

  private

  def title_cannot_contain_spam
    if title.present? && title.downcase.include?("spam")
      errors.add(:title, "no puede contener palabras prohibidas")
    end
  end

  def publish_date_in_future
    if publish_date.present? && publish_date < Date.today
      errors.add(:publish_date, "debe ser una fecha futura")
    end
  end
end
```

### Validador personalizado como clase

```ruby
# app/validators/email_domain_validator.rb
class EmailDomainValidator < ActiveModel::EachValidator
  def validate_each(record, attribute, value)
    return if value.blank?

    domain = value.split("@").last
    blocked_domains = %w[tempmail.com throwaway.com]

    if blocked_domains.include?(domain)
      record.errors.add(attribute, options[:message] || "no permite dominios temporales")
    end
  end
end

# Uso en el modelo
class User < ApplicationRecord
  validates :email, email_domain: true
  # O con mensaje personalizado:
  validates :email, email_domain: { message: "usa un correo válido" }
end
```

---

## El objeto errors

```ruby
article = Article.new(title: "")
article.valid?  # => false

# Todos los errores
article.errors.full_messages
# => ["Title can't be blank", "Body can't be blank"]

# Errores de un campo específico
article.errors[:title]
# => ["can't be blank"]

# ¿Tiene errores en un campo?
article.errors.include?(:title)  # => true

# Agregar errores manualmente
article.errors.add(:base, "Ocurrió un error general")

# Cantidad de errores
article.errors.count  # => 3

# Limpiar errores
article.errors.clear
```

En las vistas puedes mostrar los errores:

```erb
<% if @article.errors.any? %>
  <div class="alert alert-danger">
    <h4><%= pluralize(@article.errors.count, "error") %> impiden guardar:</h4>
    <ul>
      <% @article.errors.full_messages.each do |message| %>
        <li><%= message %></li>
      <% end %>
    </ul>
  </div>
<% end %>
```

---

## Callbacks del ciclo de vida

Los callbacks son métodos que se ejecutan automáticamente en momentos específicos:

```
Crear: before_validation → after_validation → before_save → around_save →
       before_create → around_create → after_create → after_save → after_commit

Actualizar: before_validation → after_validation → before_save → around_save →
            before_update → around_update → after_update → after_save → after_commit

Eliminar: before_destroy → around_destroy → after_destroy → after_commit
```

### before_save

Se ejecuta antes de guardar (crear o actualizar):

```ruby
class Article < ApplicationRecord
  before_save :generate_slug
  before_save :normalize_title

  private

  def generate_slug
    self.slug = title.parameterize if slug.blank?
  end

  def normalize_title
    self.title = title.strip.titleize
  end
end
```

### before_create

Solo se ejecuta antes de crear un registro nuevo:

```ruby
class User < ApplicationRecord
  before_create :assign_default_role
  before_create :generate_auth_token

  private

  def assign_default_role
    self.role ||= "member"
  end

  def generate_auth_token
    self.auth_token = SecureRandom.hex(20)
  end
end
```

### after_create

Se ejecuta después de crear un registro:

```ruby
class User < ApplicationRecord
  after_create :send_welcome_email
  after_create :create_default_settings

  private

  def send_welcome_email
    UserMailer.welcome(self).deliver_later
  end

  def create_default_settings
    create_setting(theme: "light", notifications: true)
  end
end
```

### before_destroy

Se ejecuta antes de eliminar un registro:

```ruby
class Article < ApplicationRecord
  before_destroy :check_if_deletable

  private

  def check_if_deletable
    if published? && comments.count > 0
      errors.add(:base, "No se puede eliminar un artículo publicado con comentarios")
      throw(:abort)  # Cancela la eliminación
    end
  end
end
```

### around_* callbacks

Envuelven la operación, permitiendo ejecutar código antes y después:

```ruby
class Article < ApplicationRecord
  around_save :log_changes

  private

  def log_changes
    Rails.logger.info "Antes de guardar: #{changes}"
    yield  # Ejecuta el save
    Rails.logger.info "Después de guardar: #{title} guardado correctamente"
  end
end
```

### after_commit

Se ejecuta después de que la transacción de base de datos se confirme:

```ruby
class Article < ApplicationRecord
  after_commit :notify_subscribers, on: :create
  after_commit :update_search_index, on: [:create, :update]
  after_commit :remove_from_search_index, on: :destroy

  private

  def notify_subscribers
    NotifySubscribersJob.perform_later(self)
  end

  def update_search_index
    SearchIndexJob.perform_later("update", self.class.name, id)
  end

  def remove_from_search_index
    SearchIndexJob.perform_later("delete", self.class.name, id)
  end
end
```

> 💡 Usa `after_commit` en lugar de `after_save` cuando necesites que la transacción esté completada (por ejemplo, para enviar trabajos a una cola).

---

## Callbacks condicionales

```ruby
class Article < ApplicationRecord
  before_save :notify_editor, if: :published?
  before_save :clear_cache,   unless: :draft?
  after_save  :log_update,    if: -> { saved_change_to_title? }

  private

  def notify_editor
    EditorMailer.article_published(self).deliver_later
  end

  def clear_cache
    Rails.cache.delete("articles/#{id}")
  end

  def log_update
    Rails.logger.info "Título cambiado a: #{title}"
  end
end
```

---

## Saltar callbacks

A veces necesitas guardar sin ejecutar callbacks:

```ruby
# Actualizar sin callbacks
article.update_column(:views_count, 100)
article.update_columns(views_count: 100, last_viewed_at: Time.current)

# Estos métodos NO ejecutan callbacks:
# update_column, update_columns, update_all
# delete, delete_all
# increment!, decrement!
# touch (depende de la versión)
```

---

## Orden de los callbacks

Los callbacks se ejecutan en el orden en que se definen:

```ruby
class Article < ApplicationRecord
  before_save :primero
  before_save :segundo
  before_save :tercero

  # Se ejecutan en orden: primero → segundo → tercero
end
```

---

## Resumen

En esta lección aprendiste:

- Las validaciones integradas: `presence`, `uniqueness`, `length`, `format`, `numericality`, `inclusion`
- Opciones como `on:`, `if:`, `unless:`, `allow_blank:`
- Cómo crear validaciones personalizadas con `validate` y clases validadoras
- Cómo trabajar con el objeto `errors` para mostrar mensajes al usuario
- Los callbacks del ciclo de vida: `before_save`, `after_create`, `before_destroy`, `around_*`, `after_commit`
- Callbacks condicionales y cómo saltarlos cuando es necesario

En la siguiente lección exploraremos los **controladores y acciones** en detalle.
