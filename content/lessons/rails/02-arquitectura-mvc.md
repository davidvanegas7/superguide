# Arquitectura MVC en Rails

El patrón Model-View-Controller (MVC) es el corazón de Rails. Comprender cómo fluye una petición a través de estas tres capas es esencial para desarrollar aplicaciones bien organizadas.

---

## ¿Qué es MVC?

MVC separa tu aplicación en tres componentes con responsabilidades claras:

```
          Petición HTTP
               │
               ▼
         ┌──────────┐
         │  Router   │  ← config/routes.rb
         └────┬─────┘
              │
              ▼
      ┌───────────────┐
      │  Controller   │  ← Coordina la lógica
      └──┬─────────┬──┘
         │         │
         ▼         ▼
   ┌──────────┐  ┌──────────┐
   │  Model   │  │   View   │
   │ (datos)  │  │ (HTML)   │
   └──────────┘  └──────────┘
```

| Componente    | Responsabilidad                                         |
|---------------|---------------------------------------------------------|
| **Model**     | Representa datos, reglas de negocio, acceso a BD        |
| **View**      | Genera la respuesta HTML (o JSON) para el usuario       |
| **Controller**| Recibe la petición, interactúa con el modelo y renderiza la vista |

---

## Flujo de una petición HTTP en Rails

Cuando un usuario visita `http://localhost:3000/articles/5`, sucede lo siguiente:

### 1. El servidor recibe la petición

```
GET /articles/5 HTTP/1.1
```

### 2. El Router busca la ruta

```ruby
# config/routes.rb
Rails.application.routes.draw do
  resources :articles
end
```

Rails determina que `GET /articles/5` debe ir a `ArticlesController#show` con `params[:id] = 5`.

### 3. El Controller procesa la petición

```ruby
# app/controllers/articles_controller.rb
class ArticlesController < ApplicationController
  def show
    @article = Article.find(params[:id])
  end
end
```

### 4. El Model consulta la base de datos

```ruby
# app/models/article.rb
class Article < ApplicationRecord
  # Active Record genera automáticamente:
  # - Article.find(5) → SELECT * FROM articles WHERE id = 5
  # - Article.all     → SELECT * FROM articles
  # - article.title   → acceso a la columna title
end
```

### 5. La View renderiza el HTML

```erb
<!-- app/views/articles/show.html.erb -->
<h1><%= @article.title %></h1>
<p><%= @article.body %></p>
<small>Publicado: <%= @article.created_at.strftime("%d/%m/%Y") %></small>
```

### 6. La respuesta llega al navegador

```
HTTP/1.1 200 OK
Content-Type: text/html

<h1>Mi primer artículo</h1>
<p>Contenido del artículo...</p>
<small>Publicado: 23/02/2026</small>
```

---

## Estructura del directorio `app/`

El directorio `app/` es donde vive casi todo tu código:

```
app/
├── assets/           # Hojas de estilo, imágenes
├── channels/         # Action Cable (WebSockets)
├── controllers/      # Controladores
│   ├── application_controller.rb   # Controlador base
│   ├── articles_controller.rb
│   └── concerns/     # Módulos compartidos entre controladores
├── helpers/          # Métodos auxiliares para vistas
│   └── articles_helper.rb
├── javascript/       # JavaScript (Import Maps o bundler)
├── jobs/             # Active Job (tareas en segundo plano)
├── mailers/          # Action Mailer (correos electrónicos)
├── models/           # Modelos Active Record
│   ├── application_record.rb  # Modelo base
│   ├── article.rb
│   └── concerns/     # Módulos compartidos entre modelos
└── views/            # Vistas
    ├── layouts/       # Layouts (plantillas base)
    │   └── application.html.erb
    ├── articles/      # Vistas del controlador Articles
    │   ├── index.html.erb
    │   ├── show.html.erb
    │   ├── _form.html.erb   # Partial (empieza con _)
    │   └── new.html.erb
    └── shared/        # Partials compartidos
```

---

## El archivo `config/routes.rb`

El router es el punto de entrada de toda petición. Define qué controlador y acción manejan cada URL:

```ruby
# config/routes.rb
Rails.application.routes.draw do
  # Ruta raíz
  root "pages#home"

  # Rutas RESTful completas
  resources :articles

  # Rutas individuales
  get  "about",   to: "pages#about"
  post "contact", to: "pages#contact"
end
```

Para ver todas las rutas definidas:

```bash
rails routes

# Salida:
#       Prefix  Verb   URI Pattern               Controller#Action
#     articles  GET    /articles(.:format)        articles#index
#               POST   /articles(.:format)        articles#create
#  new_article  GET    /articles/new(.:format)    articles#new
# edit_article  GET    /articles/:id/edit(.:format) articles#edit
#      article  GET    /articles/:id(.:format)    articles#show
#               PATCH  /articles/:id(.:format)    articles#update
#               DELETE /articles/:id(.:format)    articles#destroy
```

---

## Convenciones de nombres

Rails sigue convenciones estrictas de nomenclatura. Si las respetas, todo funciona automáticamente:

| Concepto       | Convención                | Ejemplo                        |
|----------------|---------------------------|---------------------------------|
| Modelo         | Singular, CamelCase       | `Article`                       |
| Tabla BD       | Plural, snake_case        | `articles`                      |
| Controlador    | Plural, CamelCase + Controller | `ArticlesController`      |
| Archivo modelo | Singular, snake_case      | `app/models/article.rb`        |
| Archivo controlador | Plural, snake_case   | `app/controllers/articles_controller.rb` |
| Vistas         | Plural, directorio        | `app/views/articles/`           |
| Migración      | Descriptiva               | `create_articles`               |
| Helper         | Plural                    | `ArticlesHelper`                |

```ruby
# Rails infiere automáticamente:
# Modelo Article → tabla "articles"
# ArticlesController → vistas en app/views/articles/
# resources :articles → rutas hacia ArticlesController
```

> 💡 Si necesitas romper una convención (por ejemplo, un nombre de tabla diferente), puedes configurarlo manualmente en el modelo con `self.table_name = "mi_tabla"`.

---

## Auto-carga de clases con Zeitwerk

Rails 8 usa **Zeitwerk** como cargador de código. Esto significa que nunca necesitas escribir `require` manualmente para archivos dentro de `app/`:

```ruby
# NO necesitas hacer esto:
# require "app/models/article"
# require "app/services/payment_processor"

# Zeitwerk carga automáticamente basándose en la ruta del archivo:
# app/models/article.rb         → Article
# app/services/payment_processor.rb → PaymentProcessor
# app/models/admin/user.rb      → Admin::User
```

### Reglas de Zeitwerk

```ruby
# El nombre del archivo debe coincidir con el nombre de la clase:
# app/models/blog_post.rb → BlogPost       ✅
# app/models/blogpost.rb  → BlogPost       ❌ (no coincide)

# Los directorios se convierten en módulos (namespaces):
# app/controllers/admin/users_controller.rb → Admin::UsersController

# Puedes agregar directorios personalizados a la auto-carga:
# config/application.rb
config.autoload_paths << Rails.root.join("app/services")
config.autoload_paths << Rails.root.join("app/validators")
```

### Recargar código en desarrollo

En modo desarrollo, Zeitwerk **recarga** automáticamente los archivos cuando los modificas. No necesitas reiniciar el servidor para ver cambios en modelos, controladores o vistas.

```ruby
# Si necesitas forzar la recarga en consola:
reload!
```

---

## El Application Controller

Todos los controladores heredan de `ApplicationController`, que a su vez hereda de `ActionController::Base`:

```ruby
# app/controllers/application_controller.rb
class ApplicationController < ActionController::Base
  # Métodos aquí son compartidos por TODOS los controladores

  before_action :set_locale

  private

  def set_locale
    I18n.locale = params[:locale] || I18n.default_locale
  end
end
```

```ruby
# app/controllers/articles_controller.rb
class ArticlesController < ApplicationController
  # Hereda todo lo definido en ApplicationController
  def index
    @articles = Article.all
  end
end
```

---

## El Application Record

De forma similar, todos los modelos heredan de `ApplicationRecord`:

```ruby
# app/models/application_record.rb
class ApplicationRecord < ActiveRecord::Base
  self.abstract_class = true

  # Métodos compartidos por todos los modelos
end
```

```ruby
# app/models/article.rb
class Article < ApplicationRecord
  # Hereda de ApplicationRecord → ActiveRecord::Base
  validates :title, presence: true
end
```

---

## Resumen

En esta lección aprendiste:

- Cómo funciona el patrón MVC y el rol de cada componente
- El flujo completo de una petición HTTP en Rails (Router → Controller → Model → View)
- La estructura del directorio `app/` y sus subdirectorios
- Cómo funciona el archivo `config/routes.rb`
- Las convenciones de nombres de Rails y por qué importan
- Cómo Zeitwerk auto-carga clases sin necesidad de `require`

En la siguiente lección profundizaremos en el **sistema de rutas** de Rails y cómo definir rutas RESTful, anidadas y con namespaces.
