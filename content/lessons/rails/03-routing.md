# Sistema de Rutas en Rails

El router de Rails es el componente que recibe cada petición HTTP y decide qué controlador y acción deben manejarla. Dominar el sistema de rutas es fundamental para construir aplicaciones bien estructuradas.

---

## La ruta raíz

Toda aplicación necesita una página de inicio. Se define con `root`:

```ruby
# config/routes.rb
Rails.application.routes.draw do
  root "pages#home"
  # GET / → PagesController#home
end
```

---

## Rutas RESTful con `resources`

La forma más común de definir rutas en Rails es con `resources`, que genera las 7 rutas RESTful estándar:

```ruby
resources :articles
```

Esto genera automáticamente:

| Verbo HTTP | Ruta                    | Acción     | Helper                  |
|------------|-------------------------|------------|--------------------------|
| GET        | `/articles`             | `index`    | `articles_path`          |
| GET        | `/articles/new`         | `new`      | `new_article_path`       |
| POST       | `/articles`             | `create`   | `articles_path`          |
| GET        | `/articles/:id`         | `show`     | `article_path(article)`  |
| GET        | `/articles/:id/edit`    | `edit`     | `edit_article_path(article)` |
| PATCH/PUT  | `/articles/:id`         | `update`   | `article_path(article)`  |
| DELETE     | `/articles/:id`         | `destroy`  | `article_path(article)`  |

### Limitar las acciones generadas

```ruby
# Solo generar algunas acciones
resources :articles, only: [:index, :show]

# Generar todas excepto algunas
resources :articles, except: [:destroy]
```

---

## Recurso singular con `resource`

Cuando un recurso no tiene un ID (por ejemplo, el perfil del usuario actual), usa `resource` en singular:

```ruby
resource :profile
# GET    /profile      → profiles#show
# GET    /profile/new  → profiles#new
# POST   /profile      → profiles#create
# GET    /profile/edit → profiles#edit
# PATCH  /profile      → profiles#update
# DELETE /profile      → profiles#destroy
```

> 💡 Nota que no genera la acción `index` porque solo hay un recurso, y la URL no tiene `:id`.

---

## Rutas member y collection

Puedes agregar rutas personalizadas a un recurso:

### Member: actúa sobre un registro específico (incluye `:id`)

```ruby
resources :articles do
  member do
    patch :publish
    patch :archive
    get   :preview
  end
end

# PATCH /articles/:id/publish  → articles#publish
# PATCH /articles/:id/archive  → articles#archive
# GET   /articles/:id/preview  → articles#preview
```

Forma abreviada para una sola ruta:

```ruby
resources :articles do
  patch :publish, on: :member
end
```

### Collection: actúa sobre la colección completa (sin `:id`)

```ruby
resources :articles do
  collection do
    get :search
    get :drafts
    get :popular
  end
end

# GET /articles/search   → articles#search
# GET /articles/drafts   → articles#drafts
# GET /articles/popular  → articles#popular
```

---

## Rutas anidadas (nested routes)

Cuando un recurso pertenece a otro, puedes anidar las rutas:

```ruby
resources :articles do
  resources :comments
end

# Genera rutas como:
# GET    /articles/:article_id/comments          → comments#index
# POST   /articles/:article_id/comments          → comments#create
# GET    /articles/:article_id/comments/:id      → comments#show
# PATCH  /articles/:article_id/comments/:id      → comments#update
# DELETE /articles/:article_id/comments/:id      → comments#destroy
```

En el controlador accedes al artículo padre:

```ruby
class CommentsController < ApplicationController
  def index
    @article = Article.find(params[:article_id])
    @comments = @article.comments
  end

  def create
    @article = Article.find(params[:article_id])
    @comment = @article.comments.build(comment_params)

    if @comment.save
      redirect_to article_comments_path(@article)
    else
      render :new, status: :unprocessable_entity
    end
  end
end
```

### Limitar la profundidad con `shallow`

Evita URLs demasiado largas con `shallow`:

```ruby
resources :articles do
  resources :comments, shallow: true
end

# Genera:
# GET    /articles/:article_id/comments     → comments#index
# POST   /articles/:article_id/comments     → comments#create
# GET    /comments/:id                      → comments#show     (sin article_id)
# PATCH  /comments/:id                      → comments#update   (sin article_id)
# DELETE /comments/:id                      → comments#destroy  (sin article_id)
```

> 💡 La regla general es: no anides más de un nivel. Si necesitas `/a/:a_id/b/:b_id/c/:c_id`, probablemente hay un mejor diseño.

---

## Namespace y Scope

### Namespace

Agrupa rutas bajo un prefijo de URL y módulo de controlador:

```ruby
namespace :admin do
  resources :articles
  resources :users
end

# GET /admin/articles → Admin::ArticlesController#index
# El controlador debe estar en:
# app/controllers/admin/articles_controller.rb
```

```ruby
# app/controllers/admin/articles_controller.rb
module Admin
  class ArticlesController < ApplicationController
    def index
      @articles = Article.all
    end
  end
end
```

### Scope

Agrupa rutas bajo un prefijo de URL pero **sin** cambiar el módulo del controlador:

```ruby
scope "/admin" do
  resources :articles
end

# GET /admin/articles → ArticlesController#index
# (no Admin::ArticlesController)
```

### Scope con módulo pero sin prefijo

```ruby
scope module: :admin do
  resources :articles
end

# GET /articles → Admin::ArticlesController#index
# (URL sin prefijo, pero controlador con módulo)
```

---

## Constraints (restricciones)

Puedes restringir rutas con expresiones regulares u objetos personalizados:

```ruby
# Restringir el formato del parámetro
resources :articles, constraints: { id: /[0-9]+/ }

# Restringir por subdominio
constraints subdomain: "api" do
  resources :articles
end

# Constraint personalizado con una clase
class AdminConstraint
  def matches?(request)
    request.session[:user_role] == "admin"
  end
end

constraints AdminConstraint.new do
  namespace :admin do
    resources :dashboard, only: [:index]
  end
end
```

---

## Route helpers: `_path` y `_url`

Rails genera métodos auxiliares para cada ruta:

```ruby
# _path genera rutas relativas
articles_path          # => "/articles"
article_path(@article) # => "/articles/42"
new_article_path       # => "/articles/new"
edit_article_path(@article) # => "/articles/42/edit"

# _url genera URLs absolutas (útil para correos)
articles_url           # => "http://localhost:3000/articles"
article_url(@article)  # => "http://localhost:3000/articles/42"
```

Uso en controladores y vistas:

```ruby
# En un controlador
redirect_to articles_path
redirect_to article_path(@article)

# En una vista ERB
<%= link_to "Ver artículo", article_path(@article) %>
<%= link_to "Todos los artículos", articles_path %>
```

> 💡 Siempre usa helpers en lugar de escribir URLs a mano. Si cambias las rutas, los helpers se actualizan automáticamente.

---

## Rutas manuales

Para rutas que no siguen el patrón RESTful:

```ruby
# GET
get "about", to: "pages#about"
get "contact", to: "pages#contact", as: :contact_page
# contact_page_path => "/contact"

# POST
post "search", to: "search#create"

# Rutas con parámetros
get "articles/:slug", to: "articles#show_by_slug", as: :article_by_slug
# article_by_slug_path(slug: "mi-articulo")
```

---

## Draw files en Rails 8

En aplicaciones grandes, el archivo `routes.rb` puede crecer demasiado. Rails 8 soporta **draw files** para dividir las rutas en archivos separados:

```ruby
# config/routes.rb
Rails.application.routes.draw do
  root "pages#home"

  draw :admin    # Carga config/routes/admin.rb
  draw :api      # Carga config/routes/api.rb
end
```

```ruby
# config/routes/admin.rb
namespace :admin do
  resources :articles
  resources :users
  resources :settings, only: [:index, :update]
end
```

```ruby
# config/routes/api.rb
namespace :api do
  namespace :v1 do
    resources :articles, only: [:index, :show, :create]
    resources :users, only: [:show]
  end
end
```

---

## Depurar rutas

```bash
# Ver todas las rutas
rails routes

# Filtrar rutas por controlador
rails routes -c articles

# Filtrar por verbo HTTP
rails routes -g GET

# Buscar por patrón
rails routes -g search
```

También puedes visitar `/rails/info/routes` en desarrollo para ver las rutas en el navegador.

---

## Resumen

En esta lección aprendiste:

- Cómo definir la ruta raíz con `root`
- Cómo generar rutas RESTful con `resources` y `resource`
- Agregar rutas personalizadas con `member` y `collection`
- Anidar recursos y limitar profundidad con `shallow`
- Organizar rutas con `namespace` y `scope`
- Aplicar restricciones con `constraints`
- Usar helpers `_path` y `_url` en lugar de URLs manuales
- Dividir rutas en archivos con draw files (Rails 8)

En la siguiente lección exploraremos **Active Record**, el ORM de Rails para trabajar con modelos y migraciones.
