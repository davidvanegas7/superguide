# Autenticación en Rails 8

La autenticación es el proceso de verificar la identidad de un usuario. Rails 8 introduce un generador oficial de autenticación que elimina la necesidad de gemas externas como Devise para muchos casos. En este capítulo aprenderás desde los fundamentos hasta la implementación completa.

---

## Fundamentos: `has_secure_password`

Desde Rails 4, Active Model incluye `has_secure_password`, que proporciona una forma segura de almacenar contraseñas usando bcrypt.

### Configuración básica

```bash
# bcrypt es necesario para has_secure_password
# Ya viene en el Gemfile por defecto en Rails 8
gem "bcrypt", "~> 3.1.7"
```

```bash
# Crear la migración con el campo password_digest
bin/rails generate model User email:string password_digest:string name:string
bin/rails db:migrate
```

```ruby
# app/models/user.rb
class User < ApplicationRecord
  has_secure_password

  validates :email, presence: true,
                    uniqueness: { case_sensitive: false },
                    format: { with: URI::MailTo::EMAIL_REGEXP }
  validates :name, presence: true

  # Normalizar el email antes de guardar
  normalizes :email, with: ->(email) { email.strip.downcase }
end
```

### ¿Qué hace `has_secure_password`?

```ruby
# has_secure_password automáticamente:
# 1. Añade un atributo virtual `password` (nunca se guarda en texto plano)
# 2. Añade un atributo virtual `password_confirmation`
# 3. Valida que la contraseña esté presente al crear
# 4. Valida que password y password_confirmation coincidan (si se proporciona)
# 5. Hashea la contraseña con bcrypt y la guarda en `password_digest`
# 6. Añade el método `authenticate(password)` para verificar contraseñas

user = User.new(name: "Ana", email: "ana@example.com", password: "secreto123", password_confirmation: "secreto123")
user.save

# El password_digest contiene el hash bcrypt
user.password_digest
# => "$2a$12$K4t5Qx9B..."

# Verificar la contraseña
user.authenticate("secreto123")   # => user (el objeto User)
user.authenticate("incorrecta")    # => false
```

---

## El Generador de Autenticación de Rails 8

Rails 8 incluye un generador oficial que crea todo el sistema de autenticación por ti:

```bash
bin/rails generate authentication
```

Este comando genera:

```
create  app/models/user.rb
create  app/models/session.rb
create  app/models/current.rb
create  app/controllers/sessions_controller.rb
create  app/controllers/passwords_controller.rb
create  app/views/sessions/new.html.erb
create  app/views/passwords/new.html.erb
create  app/views/passwords/edit.html.erb
create  db/migrate/XXXX_create_users.rb
create  db/migrate/XXXX_create_sessions.rb
```

### Modelo User generado

```ruby
# app/models/user.rb
class User < ApplicationRecord
  has_secure_password
  has_many :sessions, dependent: :destroy

  normalizes :email_address, with: ->(e) { e.strip.downcase }
end
```

### Modelo Session

```ruby
# app/models/session.rb
class Session < ApplicationRecord
  belongs_to :user
end
```

La tabla `sessions` almacena las sesiones activas del usuario, permitiendo gestionar múltiples dispositivos y cerrar sesiones remotamente.

### Modelo Current

```ruby
# app/models/current.rb
class Current < ActiveSupport::CurrentAttributes
  attribute :session
  delegate :user, to: :session, allow_nil: true
end
```

`CurrentAttributes` es un singleton thread-safe que almacena atributos para la duración de la petición. Permite acceder al usuario actual desde cualquier parte de la aplicación.

### Controlador de Sesiones

```ruby
# app/controllers/sessions_controller.rb
class SessionsController < ApplicationController
  allow_unauthenticated_access only: %i[new create]
  rate_limit to: 10, within: 3.minutes, only: :create, with: -> {
    redirect_to new_session_url, alert: "Intenta de nuevo más tarde."
  }

  def new
  end

  def create
    if user = User.authenticate_by(email_address: params[:email_address], password: params[:password])
      start_new_session_for user
      redirect_to after_authentication_url
    else
      redirect_to new_session_path, alert: "Correo o contraseña incorrectos."
    end
  end

  def destroy
    terminate_session
    redirect_to new_session_path
  end
end
```

### Vista de login

```ruby
# app/views/sessions/new.html.erb
<h1>Iniciar Sesión</h1>

<%= form_with url: session_path do |f| %>
  <div class="field">
    <%= f.label :email_address, "Correo electrónico" %>
    <%= f.email_field :email_address, required: true, autofocus: true,
        autocomplete: "email", placeholder: "tu@email.com" %>
  </div>

  <div class="field">
    <%= f.label :password, "Contraseña" %>
    <%= f.password_field :password, required: true,
        autocomplete: "current-password" %>
  </div>

  <%= f.submit "Iniciar sesión", class: "btn btn-primary" %>
<% end %>

<p><%= link_to "¿Olvidaste tu contraseña?", new_password_path %></p>
```

---

## El Concern `Authentication`

El generador crea un concern que se incluye en `ApplicationController`:

```ruby
# app/controllers/concerns/authentication.rb
module Authentication
  extend ActiveSupport::Concern

  included do
    before_action :require_authentication
    helper_method :authenticated?
  end

  class_methods do
    def allow_unauthenticated_access(**options)
      skip_before_action :require_authentication, **options
    end
  end

  private

  def authenticated?
    resume_session
  end

  def require_authentication
    resume_session || request_authentication
  end

  def resume_session
    Current.session ||= find_session_by_cookie
  end

  def find_session_by_cookie
    Session.find_by(id: cookies.signed[:session_id])
  end

  def request_authentication
    session[:return_to_after_authenticating] = request.url
    redirect_to new_session_path
  end

  def after_authentication_url
    session.delete(:return_to_after_authenticating) || root_url
  end

  def start_new_session_for(user)
    user.sessions.create!(user_agent: request.user_agent, ip_address: request.remote_ip).tap do |session|
      Current.session = session
      cookies.signed.permanent[:session_id] = { value: session.id, httponly: true, same_site: :lax }
    end
  end

  def terminate_session
    Current.session.destroy
    cookies.delete(:session_id)
  end
end
```

```ruby
# app/controllers/application_controller.rb
class ApplicationController < ActionController::Base
  include Authentication
end
```

---

## Acceder al Usuario Actual

```ruby
# Desde cualquier controlador o vista
Current.user          # El usuario autenticado (o nil)

# En vistas
<% if Current.user %>
  <p>Bienvenido, <%= Current.user.name %></p>
  <%= button_to "Cerrar sesión", session_path, method: :delete %>
<% else %>
  <%= link_to "Iniciar sesión", new_session_path %>
<% end %>

# En controladores
class CoursesController < ApplicationController
  def create
    @course = Current.user.courses.build(course_params)
    # ...
  end
end
```

### Permitir acceso sin autenticación

```ruby
class PagesController < ApplicationController
  # Permite acceso a estas acciones sin estar autenticado
  allow_unauthenticated_access only: [:home, :about]

  def home
  end

  def about
  end
end

class CoursesController < ApplicationController
  allow_unauthenticated_access only: [:index, :show]

  def index
    @courses = Course.published
  end
end
```

---

## Registro de Usuarios

El generador no crea el flujo de registro, pero es sencillo añadirlo:

```ruby
# app/controllers/registrations_controller.rb
class RegistrationsController < ApplicationController
  allow_unauthenticated_access

  def new
    @user = User.new
  end

  def create
    @user = User.new(user_params)

    if @user.save
      start_new_session_for @user
      redirect_to root_path, notice: "¡Registro exitoso! Bienvenido/a."
    else
      render :new, status: :unprocessable_entity
    end
  end

  private

  def user_params
    params.require(:user).permit(:name, :email_address, :password, :password_confirmation)
  end
end
```

```ruby
# app/views/registrations/new.html.erb
<h1>Crear Cuenta</h1>

<%= form_with model: @user, url: registration_path do |f| %>
  <% if @user.errors.any? %>
    <div class="alert alert-danger">
      <h4><%= pluralize(@user.errors.count, "error") %> impidieron el registro:</h4>
      <ul>
        <% @user.errors.full_messages.each do |msg| %>
          <li><%= msg %></li>
        <% end %>
      </ul>
    </div>
  <% end %>

  <div class="field">
    <%= f.label :name, "Nombre" %>
    <%= f.text_field :name, required: true %>
  </div>

  <div class="field">
    <%= f.label :email_address, "Correo electrónico" %>
    <%= f.email_field :email_address, required: true %>
  </div>

  <div class="field">
    <%= f.label :password, "Contraseña" %>
    <%= f.password_field :password, required: true, minlength: 8 %>
  </div>

  <div class="field">
    <%= f.label :password_confirmation, "Confirmar contraseña" %>
    <%= f.password_field :password_confirmation, required: true %>
  </div>

  <%= f.submit "Crear cuenta", class: "btn btn-primary" %>
<% end %>

<p>¿Ya tienes cuenta? <%= link_to "Iniciar sesión", new_session_path %></p>
```

```ruby
# config/routes.rb
Rails.application.routes.draw do
  resource :session
  resource :registration, only: [:new, :create]
  resources :passwords, param: :token
end
```

---

## Recuperación de Contraseña

El generador incluye el flujo de reset de contraseña:

```ruby
# app/controllers/passwords_controller.rb
class PasswordsController < ApplicationController
  allow_unauthenticated_access
  before_action :set_user_by_token, only: %i[edit update]

  def new
  end

  def create
    if user = User.find_by(email_address: params[:email_address])
      PasswordsMailer.reset(user).deliver_later
    end

    # Siempre mostrar el mismo mensaje (evitar enumeración de emails)
    redirect_to new_session_path,
      notice: "Si el correo existe, recibirás instrucciones para restablecer tu contraseña."
  end

  def edit
  end

  def update
    if @user.update(params.permit(:password, :password_confirmation))
      redirect_to new_session_path, notice: "Contraseña actualizada. Inicia sesión."
    else
      redirect_to edit_password_path(params[:token]), alert: "No se pudo actualizar."
    end
  end

  private

  def set_user_by_token
    @user = User.find_by_token_for!(:password_reset, params[:token])
  rescue ActiveSupport::MessageVerifier::InvalidSignature
    redirect_to new_password_path, alert: "El enlace no es válido o ha expirado."
  end
end
```

### Tokens seguros con `generates_token_for`

Rails 8 usa `generates_token_for` para crear tokens seguros con expiración:

```ruby
# app/models/user.rb
class User < ApplicationRecord
  has_secure_password
  has_many :sessions, dependent: :destroy

  normalizes :email_address, with: ->(e) { e.strip.downcase }

  # Token de reseteo de contraseña (expira en 15 minutos)
  # Se invalida automáticamente si el password_digest cambia
  generates_token_for :password_reset, expires_in: 15.minutes do
    password_salt&.last(10)
  end

  # Token de confirmación de email (expira en 24 horas)
  generates_token_for :email_confirmation, expires_in: 24.hours do
    email_address
  end
end
```

```ruby
# Generar un token
token = user.generate_token_for(:password_reset)

# Encontrar un usuario por token
user = User.find_by_token_for(:password_reset, token)
# Retorna nil si el token no es válido o ha expirado
```

### Mailer de contraseña

```ruby
# app/mailers/passwords_mailer.rb
class PasswordsMailer < ApplicationMailer
  def reset(user)
    @user = user
    @token = user.generate_token_for(:password_reset)

    mail to: user.email_address, subject: "Restablecer tu contraseña"
  end
end
```

```ruby
# app/views/passwords_mailer/reset.html.erb
<h1>Restablecer contraseña</h1>

<p>Hola <%= @user.name %>,</p>

<p>Hemos recibido una solicitud para restablecer tu contraseña.
   Haz clic en el siguiente enlace (válido por 15 minutos):</p>

<p><%= link_to "Restablecer contraseña", edit_password_url(token: @token) %></p>

<p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
```

---

## Remember Me (Recordar sesión)

La implementación por defecto ya usa cookies permanentes. Si quieres darle al usuario la opción:

```ruby
# En el método start_new_session_for del concern Authentication
def start_new_session_for(user, remember: false)
  user.sessions.create!(user_agent: request.user_agent, ip_address: request.remote_ip).tap do |session|
    Current.session = session

    if remember
      cookies.signed.permanent[:session_id] = {
        value: session.id, httponly: true, same_site: :lax
      }
    else
      # Cookie de sesión (se borra al cerrar el navegador)
      cookies.signed[:session_id] = {
        value: session.id, httponly: true, same_site: :lax
      }
    end
  end
end
```

```ruby
# En SessionsController#create
def create
  if user = User.authenticate_by(email_address: params[:email_address], password: params[:password])
    start_new_session_for user, remember: params[:remember_me] == "1"
    redirect_to after_authentication_url
  else
    redirect_to new_session_path, alert: "Correo o contraseña incorrectos."
  end
end
```

```ruby
# En la vista del login
<div class="field">
  <%= f.check_box :remember_me %>
  <%= f.label :remember_me, "Recordarme en este dispositivo" %>
</div>
```

---

## Rate Limiting

Rails 8 incluye rate limiting integrado para prevenir ataques de fuerza bruta:

```ruby
class SessionsController < ApplicationController
  # Máximo 10 intentos de login cada 3 minutos
  rate_limit to: 10, within: 3.minutes, only: :create, with: -> {
    redirect_to new_session_url, alert: "Demasiados intentos. Espera unos minutos."
  }
end

class PasswordsController < ApplicationController
  # Máximo 5 solicitudes de reseteo cada hora
  rate_limit to: 5, within: 1.hour, only: :create, with: -> {
    redirect_to new_password_url, alert: "Intenta de nuevo más tarde."
  }
end
```

---

## Consejos Prácticos

1. **Usa el generador**: `bin/rails generate authentication` te da una base sólida y segura.
2. **Nunca guardes contraseñas en texto plano**: `has_secure_password` se encarga de esto.
3. **Previene enumeración de emails**: muestra el mismo mensaje tanto si el email existe como si no.
4. **Usa rate limiting**: protege login y reset de contraseña contra fuerza bruta.
5. **Tokens con expiración**: usa `generates_token_for` con tiempos cortos para reset de contraseña.
6. **HTTPS en producción**: las cookies de sesión deben viajar siempre cifradas.
7. **Gestiona sesiones activas**: la tabla `sessions` permite cerrar sesiones remotamente.

```ruby
# Cerrar todas las sesiones excepto la actual
Current.user.sessions.where.not(id: Current.session.id).destroy_all
```

---

## Resumen

Rails 8 simplifica enormemente la autenticación con herramientas integradas:

- **`has_secure_password`** maneja el hashing seguro de contraseñas con bcrypt.
- **El generador de autenticación** (`bin/rails generate authentication`) crea todo el sistema: modelos, controladores, vistas y mailers.
- **`CurrentAttributes`** proporciona acceso thread-safe al usuario actual con `Current.user`.
- **`generates_token_for`** crea tokens seguros con expiración automática para reseteo de contraseña.
- **Rate limiting** integrado protege contra ataques de fuerza bruta.
- **La tabla de sesiones** permite gestionar múltiples dispositivos y cerrar sesiones remotamente.

Con estas herramientas nativas, la mayoría de aplicaciones Rails 8 no necesitan gemas externas para autenticación.
