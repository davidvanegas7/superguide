# Controllers y Acciones en Rails

Los controladores son el punto de conexión entre las peticiones HTTP del usuario, los modelos y las vistas. Cada acción del controlador corresponde a una funcionalidad específica de tu aplicación.

---

## Estructura de un controlador

Un controlador es una clase Ruby que hereda de `ApplicationController`:

```ruby
# app/controllers/articles_controller.rb
class ArticlesController < ApplicationController
  def index
    @articles = Article.all
  end

  def show
    @article = Article.find(params[:id])
  end
end
```

Cada método público representa una **acción** que responde a una ruta definida en `config/routes.rb`.

---

## Las 7 acciones RESTful

Rails sigue el patrón REST con 7 acciones estándar para operaciones CRUD:

```ruby
class ArticlesController < ApplicationController
  # GET /articles
  def index
    @articles = Article.order(created_at: :desc)
  end

  # GET /articles/:id
  def show
    @article = Article.find(params[:id])
  end

  # GET /articles/new
  def new
    @article = Article.new
  end

  # POST /articles
  def create
    @article = Article.new(article_params)

    if @article.save
      redirect_to @article, notice: "Artículo creado exitosamente."
    else
      render :new, status: :unprocessable_entity
    end
  end

  # GET /articles/:id/edit
  def edit
    @article = Article.find(params[:id])
  end

  # PATCH/PUT /articles/:id
  def update
    @article = Article.find(params[:id])

    if @article.update(article_params)
      redirect_to @article, notice: "Artículo actualizado."
    else
      render :edit, status: :unprocessable_entity
    end
  end

  # DELETE /articles/:id
  def destroy
    @article = Article.find(params[:id])
    @article.destroy
    redirect_to articles_path, notice: "Artículo eliminado.", status: :see_other
  end

  private

  def article_params
    params.require(:article).permit(:title, :body, :category, :published)
  end
end
```

---

## Strong Parameters

Rails requiere que declares explícitamente qué parámetros acepta tu controlador. Esto previene ataques de asignación masiva:

```ruby
# ❌ PELIGROSO — nunca hagas esto
@article = Article.new(params[:article])
# Un usuario malicioso podría enviar { article: { admin: true } }

# ✅ SEGURO — usa strong parameters
@article = Article.new(article_params)

private

def article_params
  params.require(:article).permit(:title, :body, :category)
end
```

### Parámetros anidados

```ruby
# Para formularios con modelos anidados
def article_params
  params.require(:article).permit(
    :title,
    :body,
    :category,
    tag_ids: [],                              # Array de IDs
    comments_attributes: [:id, :body, :_destroy],  # Nested attributes
    metadata: {}                              # Hash libre (usar con precaución)
  )
end
```

### Parámetros condicionales

```ruby
def article_params
  permitted = [:title, :body, :category]
  permitted << :published if current_user.admin?
  params.require(:article).permit(permitted)
end
```

---

## before_action (filtros)

Los filtros ejecutan código antes, después o alrededor de las acciones:

### before_action

```ruby
class ArticlesController < ApplicationController
  before_action :authenticate_user!
  before_action :set_article, only: [:show, :edit, :update, :destroy]
  before_action :authorize_owner, only: [:edit, :update, :destroy]

  def show
    # @article ya está cargado por set_article
  end

  def edit
    # @article ya está cargado y autorizado
  end

  def update
    if @article.update(article_params)
      redirect_to @article
    else
      render :edit, status: :unprocessable_entity
    end
  end

  def destroy
    @article.destroy
    redirect_to articles_path, status: :see_other
  end

  private

  def set_article
    @article = Article.find(params[:id])
  end

  def authorize_owner
    unless @article.user == current_user
      redirect_to articles_path, alert: "No tienes permiso para esta acción."
    end
  end
end
```

### after_action y around_action

```ruby
class ArticlesController < ApplicationController
  after_action :track_page_view, only: [:show]
  around_action :measure_time

  private

  def track_page_view
    @article.increment!(:views_count)
  end

  def measure_time
    start = Time.current
    yield
    duration = Time.current - start
    Rails.logger.info "Acción completada en #{duration}s"
  end
end
```

### skip_before_action

```ruby
class ArticlesController < ApplicationController
  skip_before_action :authenticate_user!, only: [:index, :show]
  # Las acciones index y show son públicas
end
```

---

## respond_to: múltiples formatos

Un controlador puede responder a diferentes formatos de petición:

```ruby
class ArticlesController < ApplicationController
  def index
    @articles = Article.all

    respond_to do |format|
      format.html # Renderiza index.html.erb
      format.json { render json: @articles }
      format.csv  { send_data @articles.to_csv, filename: "articulos.csv" }
    end
  end

  def show
    @article = Article.find(params[:id])

    respond_to do |format|
      format.html
      format.json { render json: @article }
      format.turbo_stream # Rails 8 con Turbo
    end
  end
end
```

---

## render: controlar la respuesta

```ruby
class ArticlesController < ApplicationController
  def create
    @article = Article.new(article_params)

    if @article.save
      redirect_to @article
    else
      # Renderizar otra plantilla
      render :new, status: :unprocessable_entity
    end
  end

  def custom_action
    # Renderizar una plantilla específica
    render "articles/special_view"

    # Renderizar texto plano
    render plain: "Hola mundo"

    # Renderizar JSON
    render json: { message: "OK", data: @articles }

    # Renderizar con un layout diferente
    render :index, layout: "admin"

    # Renderizar sin layout
    render :index, layout: false

    # Renderizar con código de estado
    render :show, status: :ok              # 200
    render :new,  status: :unprocessable_entity  # 422
    render json: { error: "No encontrado" }, status: :not_found  # 404
  end
end
```

---

## redirect_to: redireccionar

```ruby
class ArticlesController < ApplicationController
  def create
    @article = Article.new(article_params)

    if @article.save
      # Redireccionar al artículo creado
      redirect_to @article
      # Equivalente a: redirect_to article_path(@article)

      # A una ruta específica
      redirect_to articles_path

      # A una URL externa
      redirect_to "https://example.com"

      # Con un mensaje flash
      redirect_to @article, notice: "Artículo creado exitosamente."
      redirect_to articles_path, alert: "Hubo un problema."

      # Con código de estado (importante para DELETE en Rails 8)
      redirect_to articles_path, status: :see_other  # 303

      # Volver a la página anterior
      redirect_back fallback_location: articles_path
    end
  end
end
```

---

## Flash messages

Los mensajes flash persisten durante una sola petición y son ideales para notificaciones al usuario:

```ruby
class ArticlesController < ApplicationController
  def create
    @article = Article.new(article_params)

    if @article.save
      flash[:notice] = "Artículo creado exitosamente."
      redirect_to @article
    else
      flash.now[:alert] = "No se pudo crear el artículo."
      render :new, status: :unprocessable_entity
    end
  end

  def destroy
    @article = Article.find(params[:id])
    @article.destroy
    flash[:notice] = "Artículo eliminado."
    redirect_to articles_path, status: :see_other
  end
end
```

> 💡 Usa `flash.now` cuando hagas `render` (no redireccionas). Usa `flash` regular cuando hagas `redirect_to`.

En el layout puedes mostrar los mensajes:

```erb
<!-- app/views/layouts/application.html.erb -->
<body>
  <% if flash[:notice] %>
    <div class="alert alert-success"><%= flash[:notice] %></div>
  <% end %>
  <% if flash[:alert] %>
    <div class="alert alert-danger"><%= flash[:alert] %></div>
  <% end %>

  <%= yield %>
</body>
```

---

## rescue_from: manejo de excepciones

Captura excepciones de forma centralizada en el controlador:

```ruby
class ApplicationController < ActionController::Base
  rescue_from ActiveRecord::RecordNotFound, with: :not_found
  rescue_from ActionController::ParameterMissing, with: :bad_request
  rescue_from Pundit::NotAuthorizedError, with: :forbidden

  private

  def not_found
    respond_to do |format|
      format.html { render "errors/not_found", status: :not_found }
      format.json { render json: { error: "Recurso no encontrado" }, status: :not_found }
    end
  end

  def bad_request(exception)
    respond_to do |format|
      format.html { redirect_to root_path, alert: exception.message }
      format.json { render json: { error: exception.message }, status: :bad_request }
    end
  end

  def forbidden
    respond_to do |format|
      format.html { redirect_to root_path, alert: "No tienes permiso para esta acción." }
      format.json { render json: { error: "Acceso denegado" }, status: :forbidden }
    end
  end
end
```

---

## Concerns: compartir lógica entre controladores

Los concerns permiten extraer lógica reutilizable:

```ruby
# app/controllers/concerns/paginable.rb
module Paginable
  extend ActiveSupport::Concern

  private

  def page
    params[:page]&.to_i || 1
  end

  def per_page
    params[:per_page]&.to_i || 25
  end

  def paginate(collection)
    collection.limit(per_page).offset((page - 1) * per_page)
  end
end
```

```ruby
# app/controllers/concerns/searchable.rb
module Searchable
  extend ActiveSupport::Concern

  private

  def apply_search(scope, search_fields)
    return scope if params[:q].blank?

    conditions = search_fields.map { |field| "#{field} ILIKE :query" }.join(" OR ")
    scope.where(conditions, query: "%#{params[:q]}%")
  end
end
```

```ruby
# Usar los concerns en un controlador
class ArticlesController < ApplicationController
  include Paginable
  include Searchable

  def index
    @articles = Article.order(created_at: :desc)
    @articles = apply_search(@articles, [:title, :body])
    @articles = paginate(@articles)
  end
end
```

---

## Ejemplo completo: controlador robusto

```ruby
class ArticlesController < ApplicationController
  include Paginable

  before_action :authenticate_user!, except: [:index, :show]
  before_action :set_article, only: [:show, :edit, :update, :destroy]
  before_action :authorize_user!, only: [:edit, :update, :destroy]

  # GET /articles
  def index
    @articles = Article.where(published: true)
                       .includes(:tags, :user)
                       .order(created_at: :desc)
    @articles = paginate(@articles)
  end

  # GET /articles/:id
  def show
  end

  # GET /articles/new
  def new
    @article = current_user.articles.build
  end

  # POST /articles
  def create
    @article = current_user.articles.build(article_params)

    if @article.save
      redirect_to @article, notice: "Artículo publicado."
    else
      flash.now[:alert] = "Corrige los errores para continuar."
      render :new, status: :unprocessable_entity
    end
  end

  # GET /articles/:id/edit
  def edit
  end

  # PATCH /articles/:id
  def update
    if @article.update(article_params)
      redirect_to @article, notice: "Artículo actualizado."
    else
      render :edit, status: :unprocessable_entity
    end
  end

  # DELETE /articles/:id
  def destroy
    @article.destroy
    redirect_to articles_path, notice: "Artículo eliminado.", status: :see_other
  end

  private

  def set_article
    @article = Article.find(params[:id])
  end

  def authorize_user!
    unless @article.user == current_user || current_user.admin?
      redirect_to articles_path, alert: "No autorizado."
    end
  end

  def article_params
    params.require(:article).permit(:title, :body, :category, :published, tag_ids: [])
  end
end
```

---

## Resumen

En esta lección aprendiste:

- Las 7 acciones RESTful estándar: `index`, `show`, `new`, `create`, `edit`, `update`, `destroy`
- Cómo proteger tu aplicación con strong parameters (`params.require.permit`)
- Cómo usar `before_action` para compartir lógica entre acciones
- Responder a múltiples formatos con `respond_to`
- Las diferencias entre `render` y `redirect_to`
- Cómo usar flash messages para notificar al usuario
- Manejo centralizado de excepciones con `rescue_from`
- Cómo extraer lógica reutilizable con concerns

Con estos conocimientos tienes las bases para construir controladores robustos y bien organizados en Rails 8.
