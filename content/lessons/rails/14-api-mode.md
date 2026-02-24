# Rails como API

En esta lección aprenderás a usar Ruby on Rails como un backend API puro, ideal para alimentar aplicaciones frontend en React, Angular, Vue o aplicaciones móviles.

---

## ¿Qué es el modo API?

Rails puede funcionar como una aplicación full-stack o como una API JSON pura. El modo API elimina middleware y funcionalidades innecesarias (vistas, helpers de formularios, cookies de sesión) para crear un backend más ligero y eficiente.

```bash
rails new mi_api --api
```

Al usar `--api`, Rails hace tres cosas principales:

1. Configura `ApplicationController` para heredar de `ActionController::API` en vez de `ActionController::Base`.
2. Elimina middleware innecesario (cookies, sesiones, protección CSRF, flash).
3. Los generadores no producen vistas ni assets.

```ruby
# app/controllers/application_controller.rb (modo API)
class ApplicationController < ActionController::API
end
```

Comparado con el modo normal:

```ruby
# app/controllers/application_controller.rb (modo full-stack)
class ApplicationController < ActionController::Base
end
```

---

## Estructura de un controlador API

Los controladores API responden exclusivamente con JSON:

```ruby
# app/controllers/api/v1/articles_controller.rb
module Api
  module V1
    class ArticlesController < ApplicationController
      before_action :set_article, only: [:show, :update, :destroy]

      def index
        @articles = Article.all
        render json: @articles, status: :ok
      end

      def show
        render json: @article, status: :ok
      end

      def create
        @article = Article.new(article_params)

        if @article.save
          render json: @article, status: :created
        else
          render json: { errors: @article.errors.full_messages }, status: :unprocessable_entity
        end
      end

      def update
        if @article.update(article_params)
          render json: @article, status: :ok
        else
          render json: { errors: @article.errors.full_messages }, status: :unprocessable_entity
        end
      end

      def destroy
        @article.destroy
        head :no_content
      end

      private

      def set_article
        @article = Article.find(params[:id])
      rescue ActiveRecord::RecordNotFound
        render json: { error: "Artículo no encontrado" }, status: :not_found
      end

      def article_params
        params.require(:article).permit(:title, :body, :published)
      end
    end
  end
end
```

---

## Versionamiento de API

Es una buena práctica versionar tu API desde el inicio. Usa namespaces en las rutas:

```ruby
# config/routes.rb
Rails.application.routes.draw do
  namespace :api do
    namespace :v1 do
      resources :articles
      resources :users, only: [:index, :show]
    end

    namespace :v2 do
      resources :articles
    end
  end
end
```

Esto genera URLs como `/api/v1/articles` y `/api/v2/articles`. La estructura de carpetas de controladores refleja el namespace:

```
app/controllers/
  api/
    v1/
      articles_controller.rb
      users_controller.rb
    v2/
      articles_controller.rb
```

---

## Serialización con Jbuilder

Jbuilder permite construir respuestas JSON de forma declarativa usando templates:

```ruby
# Gemfile
gem "jbuilder"
```

```ruby
# app/views/api/v1/articles/index.json.jbuilder
json.articles @articles do |article|
  json.id article.id
  json.title article.title
  json.excerpt article.body.truncate(100)
  json.author article.user.name
  json.created_at article.created_at.iso8601
end

json.meta do
  json.total @articles.count
end
```

```ruby
# app/views/api/v1/articles/show.json.jbuilder
json.article do
  json.extract! @article, :id, :title, :body, :published
  json.author do
    json.extract! @article.user, :id, :name, :email
  end
  json.comments @article.comments do |comment|
    json.extract! comment, :id, :body, :created_at
  end
end
```

> **Tip:** Jbuilder es útil pero puede ser lento en respuestas grandes. Considera alternativas como `ActiveModelSerializers` o `jsonapi-serializer` para proyectos grandes.

---

## Active Model Serializers

Una alternativa popular para serializar modelos a JSON:

```ruby
# Gemfile
gem "active_model_serializers", "~> 0.10"
```

```bash
rails g serializer Article
```

```ruby
# app/serializers/article_serializer.rb
class ArticleSerializer < ActiveModel::Serializer
  attributes :id, :title, :body, :published, :created_at

  belongs_to :user
  has_many :comments

  def created_at
    object.created_at.strftime("%d/%m/%Y")
  end
end
```

```ruby
# En el controlador, automáticamente usa el serializer
def index
  @articles = Article.includes(:user, :comments).all
  render json: @articles
end
```

---

## CORS con rack-cors

Cuando tu frontend está en un dominio diferente, necesitas configurar CORS (Cross-Origin Resource Sharing):

```ruby
# Gemfile
gem "rack-cors"
```

```ruby
# config/initializers/cors.rb
Rails.application.config.middleware.insert_before 0, Rack::Cors do
  allow do
    origins "http://localhost:3000", "https://mifrontend.com"

    resource "*",
      headers: :any,
      methods: [:get, :post, :put, :patch, :delete, :options, :head],
      credentials: true,
      max_age: 3600
  end
end
```

> **Seguridad:** Nunca uses `origins "*"` en producción. Siempre especifica los dominios permitidos.

---

## Autenticación con Tokens

Para APIs, la autenticación basada en tokens es el estándar. Una implementación sencilla:

```ruby
# app/controllers/application_controller.rb
class ApplicationController < ActionController::API
  before_action :authenticate_request

  private

  def authenticate_request
    header = request.headers["Authorization"]
    token = header&.split(" ")&.last

    begin
      decoded = JWT.decode(token, Rails.application.credentials.secret_key_base)
      @current_user = User.find(decoded[0]["user_id"])
    rescue ActiveRecord::RecordNotFound, JWT::DecodeError
      render json: { error: "No autorizado" }, status: :unauthorized
    end
  end
end
```

```ruby
# app/controllers/api/v1/auth_controller.rb
module Api
  module V1
    class AuthController < ApplicationController
      skip_before_action :authenticate_request, only: [:login]

      def login
        user = User.find_by(email: params[:email])

        if user&.authenticate(params[:password])
          token = JWT.encode(
            { user_id: user.id, exp: 24.hours.from_now.to_i },
            Rails.application.credentials.secret_key_base
          )
          render json: { token: token, user: { id: user.id, email: user.email } }
        else
          render json: { error: "Credenciales inválidas" }, status: :unauthorized
        end
      end
    end
  end
end
```

Agrega la gema JWT al Gemfile:

```ruby
gem "jwt"
```

---

## Rate Limiting

Rails 8 incluye soporte nativo para rate limiting en controladores:

```ruby
class Api::V1::ArticlesController < ApplicationController
  rate_limit to: 100, within: 1.minute, only: [:index, :show]
  rate_limit to: 10, within: 1.minute, only: [:create, :update, :destroy]

  # ...acciones del controlador
end
```

También puedes usar `rack-attack` para un control más granular:

```ruby
# Gemfile
gem "rack-attack"
```

```ruby
# config/initializers/rack_attack.rb
Rack::Attack.throttle("api/requests", limit: 300, period: 5.minutes) do |req|
  req.ip if req.path.start_with?("/api/")
end

Rack::Attack.throttled_responder = lambda do |_env|
  [429, { "Content-Type" => "application/json" }, [{ error: "Demasiadas solicitudes" }.to_json]]
end
```

---

## Manejo de errores global

Centraliza el manejo de errores para respuestas consistentes:

```ruby
# app/controllers/application_controller.rb
class ApplicationController < ActionController::API
  rescue_from ActiveRecord::RecordNotFound, with: :not_found
  rescue_from ActiveRecord::RecordInvalid, with: :unprocessable_entity
  rescue_from ActionController::ParameterMissing, with: :bad_request

  private

  def not_found(exception)
    render json: { error: exception.message }, status: :not_found
  end

  def unprocessable_entity(exception)
    render json: { error: exception.record.errors.full_messages }, status: :unprocessable_entity
  end

  def bad_request(exception)
    render json: { error: exception.message }, status: :bad_request
  end
end
```

---

## Mención: Grape como alternativa

**Grape** es un framework ligero para construir APIs en Ruby, que puede usarse dentro de Rails o de forma independiente:

```ruby
# Gemfile
gem "grape"
```

```ruby
# app/api/v1/articles_api.rb
class V1::ArticlesApi < Grape::API
  resource :articles do
    desc "Listar artículos"
    get do
      Article.all
    end

    desc "Crear artículo"
    params do
      requires :title, type: String
      requires :body, type: String
    end
    post do
      Article.create!(declared(params))
    end
  end
end
```

Grape es útil si necesitas una API muy estructurada con validaciones automáticas de parámetros, pero para la mayoría de casos Rails API mode es suficiente.

---

## Resumen

- Usa `rails new --api` para crear aplicaciones API ligeras sin vistas ni assets.
- Versiona tu API desde el inicio con namespaces (`/api/v1/`).
- Serializa respuestas con `render json:`, Jbuilder o Active Model Serializers.
- Configura CORS con `rack-cors` para permitir peticiones desde frontends externos.
- Implementa autenticación con JWT u otra estrategia basada en tokens.
- Aprovecha el rate limiting nativo de Rails 8 o usa `rack-attack`.
- Centraliza el manejo de errores con `rescue_from` en el `ApplicationController`.
- Considera Grape solo si necesitas funcionalidades específicas que Rails API mode no ofrece.
