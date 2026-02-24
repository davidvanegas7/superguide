# Preguntas de Entrevista Rails

Esta lección recopila las preguntas más comunes en entrevistas técnicas sobre Ruby on Rails, con respuestas detalladas y ejemplos de código. Prepárate para destacar en tu próxima entrevista.

---

## 1. ¿Qué es el patrón MVC y cómo lo implementa Rails?

**MVC** (Model-View-Controller) es un patrón arquitectónico que separa la aplicación en tres capas:

- **Model (Modelo):** Representa los datos y la lógica de negocio. En Rails, los modelos heredan de `ActiveRecord::Base` y se conectan a tablas de la base de datos.
- **View (Vista):** Presenta los datos al usuario. En Rails son archivos ERB, Haml o Jbuilder.
- **Controller (Controlador):** Recibe las peticiones HTTP, interactúa con el modelo y selecciona la vista. Hereda de `ActionController::Base`.

```ruby
# Modelo — lógica de negocio y datos
class Article < ApplicationRecord
  belongs_to :user
  validates :title, presence: true
  scope :published, -> { where(published: true) }
end

# Controlador — orquesta la petición
class ArticlesController < ApplicationController
  def show
    @article = Article.find(params[:id])
  end
end

# Vista — presenta los datos (app/views/articles/show.html.erb)
# <h1><%= @article.title %></h1>
```

El flujo es: **Router → Controller → Model → View → Respuesta HTTP**.

---

## 2. ¿Qué es el problema N+1 y cómo se soluciona?

El **problema N+1** ocurre cuando una consulta inicial (1) genera N consultas adicionales al acceder a asociaciones de cada registro.

```ruby
# ❌ Problema N+1: 1 consulta para artículos + N consultas para usuarios
articles = Article.all
articles.each do |article|
  puts article.user.name  # Cada iteración hace un SELECT a users
end
# SELECT * FROM articles
# SELECT * FROM users WHERE id = 1
# SELECT * FROM users WHERE id = 2
# SELECT * FROM users WHERE id = 3  ... N veces
```

**Solución: Eager Loading**

```ruby
# ✅ includes — precarga la asociación (2 consultas en total)
articles = Article.includes(:user).all
articles.each do |article|
  puts article.user.name  # No genera consultas adicionales
end
# SELECT * FROM articles
# SELECT * FROM users WHERE id IN (1, 2, 3, ...)

# ✅ eager_load — usa LEFT OUTER JOIN (1 consulta)
articles = Article.eager_load(:user).all

# ✅ preload — siempre consultas separadas (2 consultas)
articles = Article.preload(:user).all
```

Puedes detectar N+1 automáticamente con la gema `bullet`:

```ruby
# Gemfile (development)
gem "bullet"
```

---

## 3. ¿Qué son los Strong Parameters?

Los **Strong Parameters** protegen contra asignación masiva (mass assignment). Definen explícitamente qué parámetros del formulario están permitidos:

```ruby
class UsersController < ApplicationController
  def create
    @user = User.new(user_params)
    # ...
  end

  private

  def user_params
    params.require(:user).permit(:name, :email, :password, :password_confirmation)
    # Cualquier parámetro no listado (ej: :role, :admin) será DESCARTADO
  end
end
```

Sin strong params, un atacante podría enviar `user[admin]=true` y escalar privilegios. Rails lanza `ActionController::ForbiddenAttributesError` si intentas usar params sin filtrar.

---

## 4. ¿Qué son los Scopes y para qué sirven?

Los **scopes** son consultas reutilizables definidas en el modelo:

```ruby
class Article < ApplicationRecord
  scope :published, -> { where(published: true) }
  scope :recent, -> { order(created_at: :desc) }
  scope :by_author, ->(user_id) { where(user_id: user_id) }
  scope :popular, -> { where("views_count > ?", 100) }

  # Los scopes son encadenables
  # Article.published.recent.by_author(1).popular
end
```

Ventajas:
- **Reutilizables** en cualquier parte de la aplicación
- **Encadenables** con otros scopes y métodos de ActiveRecord
- **Semánticos** — hacen el código más legible
- Siempre retornan un `ActiveRecord::Relation` (nunca nil)

---

## 5. ¿Qué son los Concerns?

Los **Concerns** son módulos que encapsulan funcionalidad compartida entre modelos o controladores:

```ruby
# app/models/concerns/searchable.rb
module Searchable
  extend ActiveSupport::Concern

  included do
    scope :search, ->(query) {
      where("title ILIKE :q OR body ILIKE :q", q: "%#{query}%")
    }
  end

  class_methods do
    def most_searched
      order(search_count: :desc).limit(10)
    end
  end
end

# Uso en modelos
class Article < ApplicationRecord
  include Searchable
end

class Product < ApplicationRecord
  include Searchable
end

# Article.search("rails")
# Product.search("laptop")
```

---

## 6. ¿Qué es STI (Single Table Inheritance)?

**STI** permite que múltiples modelos compartan una sola tabla en la base de datos, usando una columna `type` para diferenciarlos:

```ruby
# Migración: la tabla tiene una columna 'type'
create_table :vehicles do |t|
  t.string :type
  t.string :name
  t.integer :capacity
  t.boolean :electric, default: false
  t.timestamps
end

# Modelos
class Vehicle < ApplicationRecord
  validates :name, presence: true
end

class Car < Vehicle
  def description
    "Automóvil: #{name}"
  end
end

class Truck < Vehicle
  def description
    "Camión: #{name} (capacidad: #{capacity} ton)"
  end
end

class Motorcycle < Vehicle
  default_scope { where(electric: false) }
end

# Uso
Car.create!(name: "Sedan")       # type = "Car"
Truck.create!(name: "F-150")     # type = "Truck"
Vehicle.all                       # Retorna todos
Car.all                           # Solo coches
```

STI es útil cuando los modelos comparten la mayoría de atributos. Si difieren mucho, usa **polimorfismo** o tablas separadas.

---

## 7. ¿Cuáles son los pros y contras de los Callbacks?

```ruby
class Order < ApplicationRecord
  before_validation :normalize_data
  after_create :send_confirmation
  before_destroy :check_cancelable

  private

  def normalize_data
    self.email = email.strip.downcase
  end

  def send_confirmation
    OrderMailer.confirmation(self).deliver_later
  end

  def check_cancelable
    throw(:abort) unless cancelable?
  end
end
```

**Pros:**
- Automatizan lógica repetitiva
- Mantienen consistencia de datos
- Centralizan efectos secundarios

**Contras:**
- Crean acoplamiento implícito (difícil de rastrear)
- Dificultan el testing
- Pueden causar efectos secundarios inesperados
- Problemas de rendimiento en cadenas largas de callbacks
- `after_save` puede fallar silenciosamente

**Alternativa recomendada:** Usar **Service Objects** para lógica compleja:

```ruby
class CreateOrder
  def initialize(params, user)
    @params = params
    @user = user
  end

  def call
    order = Order.new(@params.merge(user: @user))
    if order.save
      OrderMailer.confirmation(order).deliver_later
      InventoryService.reserve(order)
      { success: true, order: order }
    else
      { success: false, errors: order.errors }
    end
  end
end
```

---

## 8. ¿Qué son los principios REST y cómo los aplica Rails?

**REST** (Representational State Transfer) mapea operaciones CRUD a verbos HTTP:

| Verbo HTTP | Ruta | Acción | Propósito |
|---|---|---|---|
| GET | /articles | index | Listar recursos |
| GET | /articles/:id | show | Mostrar un recurso |
| GET | /articles/new | new | Formulario de creación |
| POST | /articles | create | Crear recurso |
| GET | /articles/:id/edit | edit | Formulario de edición |
| PATCH/PUT | /articles/:id | update | Actualizar recurso |
| DELETE | /articles/:id | destroy | Eliminar recurso |

```ruby
# config/routes.rb
resources :articles  # Genera las 7 rutas RESTful

resources :articles, only: [:index, :show]  # Solo lectura
resources :articles, except: [:destroy]      # Sin eliminar

# Rutas anidadas
resources :articles do
  resources :comments, only: [:create, :destroy]
end
# → POST /articles/:article_id/comments
```

---

## 9. ¿Cuál es la diferencia entre Eager Loading, Lazy Loading y Preloading?

```ruby
# Lazy Loading (por defecto) — carga cuando se accede
article = Article.first
article.comments  # La consulta SQL ocurre AQUÍ

# Eager Loading con includes — precarga inteligente
articles = Article.includes(:comments, :user)
# Rails decide: 2 consultas separadas o 1 JOIN

# Eager Loading con eager_load — fuerza JOIN
articles = Article.eager_load(:comments)
# SELECT articles.*, comments.* FROM articles LEFT OUTER JOIN comments ...

# Preload — fuerza consultas separadas
articles = Article.preload(:comments)
# SELECT * FROM articles
# SELECT * FROM comments WHERE article_id IN (...)

# Strict Loading — lanza error si se hace lazy loading
article = Article.strict_loading.first
article.comments  # => ActiveRecord::StrictLoadingViolationError
```

**Regla general:** Usa `includes` cuando sabes que vas a acceder a la asociación. Usa `strict_loading` en desarrollo para detectar N+1.

---

## 10. ¿Turbo/Hotwire vs SPA? ¿Cuándo usar cada uno?

**Hotwire (Turbo + Stimulus):**
- Renderizado en el servidor, actualizaciones parciales vía HTML
- Menos JavaScript, más simple
- SEO amigable de forma nativa
- Ideal para: CRUD, dashboards, CMS, e-commerce

**SPA (React, Vue, Angular):**
- Renderizado en el cliente, comunicación vía JSON API
- Rica interactividad del lado del cliente
- Requiere API separada
- Ideal para: editores colaborativos, apps tipo Gmail, visualización de datos en tiempo real

```ruby
# Turbo Frame — actualiza solo una sección
<%= turbo_frame_tag "article_#{article.id}" do %>
  <h2><%= article.title %></h2>
  <%= link_to "Editar", edit_article_path(article) %>
<% end %>

# Turbo Stream — actualizar múltiples partes
# app/views/articles/create.turbo_stream.erb
<%= turbo_stream.prepend "articles", @article %>
<%= turbo_stream.update "article_count", Article.count %>
```

---

## 11. ¿Qué estrategias de caching existen en Rails?

```ruby
# Fragment Caching — cachear partes de la vista
<% cache @article do %>
  <div class="article">
    <h2><%= @article.title %></h2>
    <p><%= @article.body %></p>
  </div>
<% end %>

# Russian Doll Caching — caché anidada con touch
class Comment < ApplicationRecord
  belongs_to :article, touch: true
end

<% cache @article do %>
  <h2><%= @article.title %></h2>
  <% @article.comments.each do |comment| %>
    <% cache comment do %>
      <p><%= comment.body %></p>
    <% end %>
  <% end %>
<% end %>

# Low-level Caching
Rails.cache.fetch("top_articles", expires_in: 1.hour) do
  Article.published.order(views: :desc).limit(10).to_a
end

# HTTP Caching
def show
  @article = Article.find(params[:id])
  fresh_when(@article)  # Usa ETag y Last-Modified
end

# Counter Cache — evitar COUNT queries
class Comment < ApplicationRecord
  belongs_to :article, counter_cache: true
end
# Requiere columna comments_count en articles
```

---

## 12. ¿Por qué son importantes los índices en la base de datos?

Los **índices** aceleran las consultas al crear una estructura de búsqueda optimizada:

```ruby
# Migración con índices
class AddIndexesToArticles < ActiveRecord::Migration[8.0]
  def change
    # Índice simple — búsquedas por columna
    add_index :articles, :user_id

    # Índice único — garantiza unicidad
    add_index :users, :email, unique: true

    # Índice compuesto — búsquedas por múltiples columnas
    add_index :articles, [:user_id, :published]

    # Índice parcial — solo para un subconjunto
    add_index :articles, :created_at, where: "published = true"
  end
end
```

**Cuándo agregar índices:**
- Columnas usadas en `WHERE`, `ORDER BY`, `JOIN`
- Foreign keys (`user_id`, `category_id`)
- Columnas de búsqueda frecuente
- Columnas con restricción de unicidad

**Cuándo NO agregar:**
- Tablas con muy pocos registros
- Columnas que cambian constantemente (los índices ralentizan escrituras)

---

## 13. ¿Qué es Connection Pooling?

El **connection pool** limita el número de conexiones simultáneas a la base de datos:

```yaml
# config/database.yml
production:
  adapter: postgresql
  pool: <%= ENV.fetch("RAILS_MAX_THREADS") { 5 } %>
  timeout: 5000
  host: <%= ENV["DATABASE_HOST"] %>
  database: miapp_production
```

Cada thread de Puma necesita una conexión. La fórmula es:

```
pool = RAILS_MAX_THREADS (workers de Puma) × número de threads
```

Si el pool se agota, los threads esperan hasta el `timeout` y luego lanzan `ActiveRecord::ConnectionTimeoutError`.

```ruby
# Verificar conexiones activas
ActiveRecord::Base.connection_pool.stat
# => { size: 5, connections: 3, busy: 1, dead: 0, idle: 2, waiting: 0 }
```

---

## 14. ¿Cómo maneja Rails la seguridad (CSRF, XSS, SQL Injection)?

### CSRF (Cross-Site Request Forgery)

Rails incluye protección CSRF por defecto:

```ruby
class ApplicationController < ActionController::Base
  protect_from_forgery with: :exception
  # Cada formulario incluye un token CSRF automáticamente
end
```

```erb
<%# El token se incluye automáticamente en form_with %>
<%= form_with model: @article do |f| %>
  <%# Rails genera: <input type="hidden" name="authenticity_token" value="..."> %>
<% end %>
```

### XSS (Cross-Site Scripting)

Rails escapa HTML automáticamente en las vistas:

```erb
<%# ✅ Seguro — Rails escapa automáticamente %>
<%= @user.name %>
<%# Si name = "<script>alert('xss')</script>", muestra el texto literal %>

<%# ⚠️ Peligroso — no escapa HTML %>
<%= raw @article.body %>
<%= @article.body.html_safe %>
<%# Solo usa html_safe con contenido que TÚ controlas %>

<%# ✅ Sanitizar HTML del usuario %>
<%= sanitize @article.body, tags: %w[p br strong em] %>
```

### SQL Injection

ActiveRecord protege contra SQL injection usando parámetros preparados:

```ruby
# ✅ Seguro — parámetros escapados
User.where("email = ?", params[:email])
User.where(email: params[:email])

# ❌ VULNERABLE — interpolación directa
User.where("email = '#{params[:email]}'")
# Un atacante podría enviar: ' OR 1=1 --
```

---

## 15. ¿Qué es el patrón Service Object y cuándo usarlo?

Los **Service Objects** encapsulan lógica de negocio compleja que no pertenece al modelo ni al controlador:

```ruby
# app/services/register_user.rb
class RegisterUser
  Result = Struct.new(:success?, :user, :errors, keyword_init: true)

  def initialize(params)
    @params = params
  end

  def call
    user = User.new(@params)

    ActiveRecord::Base.transaction do
      user.save!
      ProfileCreator.new(user).call
      WelcomeMailer.send_welcome(user).deliver_later
      AnalyticsService.track("user_registered", user_id: user.id)
    end

    Result.new(success?: true, user: user)
  rescue ActiveRecord::RecordInvalid => e
    Result.new(success?: false, errors: e.record.errors.full_messages)
  end
end

# En el controlador
class UsersController < ApplicationController
  def create
    result = RegisterUser.new(user_params).call

    if result.success?
      redirect_to root_path, notice: "¡Bienvenido!"
    else
      flash.now[:alert] = result.errors.join(", ")
      render :new
    end
  end
end
```

**Usa Service Objects cuando:**
- La lógica involucra múltiples modelos
- Necesitas coordinar efectos secundarios (emails, APIs externas)
- El callback del modelo se vuelve demasiado complejo
- Quieres mantener los modelos y controladores delgados

---

## 16. ¿Cómo funciona el Asset Pipeline?

El **Asset Pipeline** procesa y optimiza archivos estáticos (CSS, JS, imágenes):

```ruby
# Rails 8 usa Propshaft (reemplazo de Sprockets)
# Solo hace fingerprinting y resolución de rutas

# Ubicación de assets
# app/assets/        — assets de la aplicación
# lib/assets/        — assets de librerías propias
# vendor/assets/     — assets de terceros

# Referencia en vistas
<%= stylesheet_link_tag "application" %>
<%= javascript_importmap_tags %>
<%= image_tag "logo.png" %>

# En producción, los assets obtienen un hash (fingerprint):
# application-a1b2c3d4e5.css
# Esto permite caché agresiva (1 año) porque cada cambio genera un nuevo hash
```

---

## 17. ¿Qué es Polymorphic Association?

Las **asociaciones polimórficas** permiten que un modelo pertenezca a más de un tipo de modelo:

```ruby
# Migración
create_table :comments do |t|
  t.text :body
  t.references :commentable, polymorphic: true
  # Crea: commentable_id (integer) y commentable_type (string)
  t.timestamps
end

# Modelos
class Comment < ApplicationRecord
  belongs_to :commentable, polymorphic: true
end

class Article < ApplicationRecord
  has_many :comments, as: :commentable
end

class Photo < ApplicationRecord
  has_many :comments, as: :commentable
end

# Uso
article.comments.create!(body: "Gran artículo")
photo.comments.create!(body: "Hermosa foto")
comment.commentable  # Retorna el Article o Photo asociado
```

---

## 18. ¿Cómo manejar migraciones en producción?

```ruby
# Reglas de oro:
# 1. NUNCA edites una migración ya ejecutada en producción
# 2. Crea nuevas migraciones para cambios
# 3. Las migraciones deben ser reversibles

class AddStatusToOrders < ActiveRecord::Migration[8.0]
  def change
    add_column :orders, :status, :string, default: "pending", null: false
    add_index :orders, :status
  end
end

# Para migraciones de datos grandes, usa batches:
class BackfillOrderStatus < ActiveRecord::Migration[8.0]
  disable_ddl_transaction!

  def up
    Order.in_batches(of: 1000) do |batch|
      batch.update_all(status: "pending")
    end
  end
end
```

---

## Consejos para la entrevista

1. **Explica tu razonamiento**, no solo la respuesta.
2. **Menciona trade-offs** — cada decisión técnica tiene pros y contras.
3. **Usa ejemplos de código** para respaldar tus respuestas.
4. **Conoce las novedades** de la versión actual (Rails 8).
5. **Habla de tu experiencia** real con cada concepto.
6. **No tengas miedo de decir "no sé"**, pero ofrece cómo lo investigarías.

---

## Resumen

Las entrevistas de Rails evalúan tu comprensión de:

- **Fundamentos:** MVC, REST, ActiveRecord, migraciones
- **Rendimiento:** N+1, caching, índices, connection pooling
- **Seguridad:** CSRF, XSS, SQL Injection, Strong Parameters
- **Diseño:** Concerns, Service Objects, STI, polimorfismo
- **Prácticas:** Testing, callbacks vs services, eager loading
- **Modernidad:** Hotwire vs SPA, Rails 8 features, Solid Trifecta

Practica explicando cada concepto en voz alta y escribiendo código de ejemplo. La combinación de teoría sólida y experiencia práctica es lo que buscan los entrevistadores.
