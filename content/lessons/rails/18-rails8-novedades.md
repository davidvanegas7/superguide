# Novedades de Rails 8

Rails 8 es una versión mayor que simplifica drásticamente la infraestructura necesaria para desplegar aplicaciones web. En esta lección exploraremos todas las novedades clave.

---

## Filosofía: One Person Framework

Rails 8 refuerza la visión de Rails como un framework donde **una sola persona** puede construir y desplegar una aplicación completa sin depender de servicios externos como Redis, Nginx o plataformas PaaS complicadas.

Las tres grandes áreas de cambio son:

1. **Solid Trifecta** — Reemplaza Redis y servicios externos
2. **Deployment simplificado** — Kamal 2 + Thruster
3. **Asset pipeline modernizado** — Propshaft + No Build

---

## Solid Trifecta

La Solid Trifecta es el corazón de Rails 8. Son tres librerías que usan la **base de datos** como backend para funcionalidades que tradicionalmente requerían Redis:

### Solid Cache

Reemplaza Redis como almacén de caché. Los discos SSD modernos hacen viable almacenar la caché en la base de datos:

```ruby
# config/environments/production.rb
config.cache_store = :solid_cache_store
```

```bash
bin/rails solid_cache:install
bin/rails db:migrate
```

```ruby
# Uso normal de caché — no cambia nada
Rails.cache.write("dato", "valor", expires_in: 1.hour)
Rails.cache.read("dato") # => "valor"

Rails.cache.fetch("estadisticas", expires_in: 15.minutes) do
  ExpensiveCalculation.run
end
```

Configuración avanzada:

```yaml
# config/solid_cache.yml
production:
  store:
    database: cache
    max_age: <%= 1.week.to_i %>
    max_size: <%= 256.megabytes %>
    namespace: miapp
```

### Solid Queue

Reemplaza Sidekiq/Resque como adaptador de colas. No necesita Redis:

```ruby
# config/environments/production.rb
config.active_job.queue_adapter = :solid_queue
```

```bash
bin/rails solid_queue:install
bin/rails db:migrate
```

Características clave:

- Múltiples colas con diferentes prioridades
- Workers concurrentes con threads
- Tareas recurrentes integradas
- Ejecución dentro del proceso de Puma (sin proceso separado)

```ruby
# Ejecutar Solid Queue dentro de Puma
# config/puma.rb
plugin :solid_queue if ENV["SOLID_QUEUE_IN_PUMA"]
```

```yaml
# config/recurring.yml
production:
  limpieza_diaria:
    class: CleanupJob
    schedule: every day at 2am

  reporte_semanal:
    class: WeeklyReportJob
    schedule: every Monday at 9am
```

### Solid Cable

Reemplaza Redis como backend para Action Cable (WebSockets):

```yaml
# config/cable.yml
production:
  adapter: solid_cable
  polling_interval: 0.1
  message_retention: 1.day
```

```bash
bin/rails solid_cable:install
bin/rails db:migrate
```

```ruby
# El código de Action Cable no cambia
class ChatChannel < ApplicationCable::Channel
  def subscribed
    stream_from "chat_#{params[:room_id]}"
  end

  def receive(data)
    ActionCable.server.broadcast("chat_#{params[:room_id]}", data)
  end
end
```

---

## Authentication Generator

Rails 8 incluye un **generador de autenticación** nativo. Ya no necesitas Devise para autenticación básica:

```bash
bin/rails generate authentication
```

Esto genera:

- Modelo `User` con `has_secure_password`
- Modelo `Session` para sesiones persistentes
- `SessionsController` para login/logout
- `PasswordsController` para reset de contraseña
- Mailer para recuperación de contraseña
- Migraciones correspondientes
- Concern `Authentication` para los controladores

```ruby
# app/models/user.rb (generado)
class User < ApplicationRecord
  has_secure_password
  has_many :sessions, dependent: :destroy

  normalizes :email_address, with: ->(e) { e.strip.downcase }
end
```

```ruby
# app/models/session.rb (generado)
class Session < ApplicationRecord
  belongs_to :user
end
```

```ruby
# app/controllers/concerns/authentication.rb (generado)
module Authentication
  extend ActiveSupport::Concern

  included do
    before_action :require_authentication
    helper_method :authenticated?
  end

  private

  def authenticated?
    Current.session.present?
  end

  def require_authentication
    resume_session || request_authentication
  end

  def resume_session
    Current.session = find_session_by_cookie
  end

  def request_authentication
    session[:return_to_after_authenticating] = request.url
    redirect_to new_session_url
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

> **Tip:** Este generador cubre el 80% de los casos de uso de autenticación. Para funcionalidades avanzadas (OAuth, 2FA), puedes extenderlo manualmente o usar gemas complementarias.

---

## Kamal 2

Kamal 2 es la herramienta oficial de deployment. Principales mejoras sobre Kamal 1:

- **Proxy integrado** que reemplaza Traefik con un proxy propio más simple
- **Zero-downtime deploys** mejorados
- **Configuración simplificada**

```bash
# Instalar Kamal
gem install kamal

# Inicializar
kamal init
```

```yaml
# config/deploy.yml (Kamal 2)
service: miapp
image: usuario/miapp

servers:
  web:
    hosts:
      - 192.168.1.100
  job:
    hosts:
      - 192.168.1.100
    cmd: bin/jobs

proxy:
  ssl: true
  host: miapp.com
  app_port: 3000

registry:
  username: usuario
  password:
    - KAMAL_REGISTRY_PASSWORD

env:
  secret:
    - RAILS_MASTER_KEY
    - DATABASE_URL

builder:
  arch: amd64
```

```bash
kamal setup    # Primer despliegue
kamal deploy   # Despliegues subsecuentes
kamal app logs # Ver logs
kamal rollback # Revertir
```

---

## Thruster

Thruster es un proxy HTTP escrito en Go que se pone delante de Puma. Funcionalidades:

- **Compresión gzip/brotli** automática
- **Caché de assets** con headers de larga duración
- **Terminación SSL** con Let's Encrypt automático
- **X-Sendfile** para servir archivos estáticos eficientemente

```bash
# Se incluye automáticamente en nuevas apps Rails 8
# En el Dockerfile:
CMD ["./bin/thrust", "./bin/rails", "server"]
```

Thruster elimina la necesidad de configurar Nginx o Apache como proxy reverso. Es transparente y no requiere configuración en la mayoría de los casos.

Variables de entorno opcionales:

```bash
THRUSTER_HTTP_PORT=80
THRUSTER_HTTPS_PORT=443
THRUSTER_TLS_DOMAIN=miapp.com
THRUSTER_CACHE_SIZE=128mb
THRUSTER_MAX_BODY_SIZE=20mb
```

---

## Propshaft: Reemplazo de Sprockets

**Propshaft** es el nuevo asset pipeline por defecto, reemplazando a Sprockets:

```ruby
# Gemfile (apps Rails 8 nuevas)
gem "propshaft"
```

Diferencias clave con Sprockets:

| Sprockets | Propshaft |
|---|---|
| Compilación compleja | Solo fingerprinting y resolución |
| Soporta CoffeeScript, Sass | No compila nada |
| Pipeline de transformaciones | Sirve archivos tal cual |
| Lento | Muy rápido |
| Configuración compleja | Mínima configuración |

```ruby
# Propshaft no necesita manifests ni requires
# Solo coloca archivos en app/assets/ y los referencia

# En las vistas:
<%= stylesheet_link_tag "application" %>
<%= image_tag "logo.png" %>
```

Propshaft asume que usarás herramientas modernas del navegador (como importmaps o bundlers externos) para procesar JavaScript y CSS.

---

## No Build por defecto: Importmaps

Rails 8 usa **importmaps** por defecto, eliminando la necesidad de Node.js, Webpack, o cualquier bundler JavaScript:

```ruby
# Gemfile
gem "importmap-rails"
```

```bash
# Agregar una librería JavaScript
bin/rails importmap:pin stimulus
bin/rails importmap:pin lodash
```

```ruby
# config/importmap.rb
pin "application"
pin "@hotwired/turbo-rails", to: "turbo.min.js"
pin "@hotwired/stimulus", to: "stimulus.min.js"
pin "@hotwired/stimulus-loading", to: "stimulus-loading.js"
pin_all_from "app/javascript/controllers", under: "controllers"
```

```html
<!-- app/views/layouts/application.html.erb -->
<%= javascript_importmap_tags %>
```

> **Nota:** Si tu proyecto requiere un bundler (React, Vue, etc.), puedes usar `rails new miapp --javascript=esbuild` como alternativa.

---

## Script Folder

Rails 8 introduce la carpeta `script/` para scripts de mantenimiento y operaciones:

```
script/
├── setup       # Configurar la aplicación por primera vez
├── dev         # Iniciar entorno de desarrollo
└── ci          # Ejecutar suite de CI
```

```bash
# script/setup
#!/bin/bash
set -e

echo "=== Instalando dependencias ==="
bundle install

echo "=== Preparando base de datos ==="
bin/rails db:prepare

echo "=== Limpiando logs y temp ==="
bin/rails log:clear tmp:clear

echo "=== ¡Listo! ==="
```

```bash
# script/dev
#!/bin/bash
bin/rails server -p 3000
```

---

## Brakeman incluido

**Brakeman**, el escáner de seguridad para Rails, viene incluido por defecto:

```ruby
# Gemfile (grupo development)
gem "brakeman", require: false
```

```bash
# Ejecutar análisis de seguridad
bin/brakeman

# Con formato JSON
bin/brakeman -f json -o brakeman_report.json

# Ignorar falsos positivos
bin/brakeman -I
```

Brakeman detecta vulnerabilidades como:

- SQL Injection
- Cross-Site Scripting (XSS)
- Mass Assignment
- Redirect vulnerabilities
- File access issues
- Command injection

---

## CI Workflow generado

Rails 8 genera un workflow de CI para GitHub Actions:

```yaml
# .github/workflows/ci.yml (generado)
name: CI

on:
  pull_request:
  push:
    branches: [main]

jobs:
  scan_ruby:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: ruby/setup-ruby@v1
        with:
          bundler-cache: true
      - name: Scan for security vulnerabilities
        run: bin/brakeman --no-pager

  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: ruby/setup-ruby@v1
        with:
          bundler-cache: true
      - name: Lint code
        run: bin/rubocop -f github

  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: ruby/setup-ruby@v1
        with:
          bundler-cache: true
      - name: Run tests
        env:
          RAILS_ENV: test
        run: |
          bin/rails db:test:prepare
          bin/rails test
```

---

## Otras mejoras menores

```ruby
# Strict locals en parciales (permite validar qué variables se pasan)
<%# app/views/articles/_card.html.erb %>
<%# locals: (article:, show_author: true) %>
<div class="card">
  <h2><%= article.title %></h2>
  <% if show_author %>
    <p>Por: <%= article.user.name %></p>
  <% end %>
</div>

# Normalización de atributos
class User < ApplicationRecord
  normalizes :email, with: ->(email) { email.strip.downcase }
  normalizes :phone, with: ->(phone) { phone.gsub(/\D/, "") }
end

# Generación de tokens
class User < ApplicationRecord
  generates_token_for :password_reset, expires_in: 15.minutes do
    password_salt&.last(10)
  end

  generates_token_for :email_confirmation, expires_in: 24.hours do
    email
  end
end
```

---

## Resumen

- **Solid Trifecta** (Cache, Queue, Cable) elimina la dependencia de Redis usando la base de datos.
- El **generador de autenticación** proporciona login/logout/reset sin gemas externas.
- **Kamal 2** simplifica el deployment con Docker vía SSH, sin necesidad de Kubernetes.
- **Thruster** reemplaza Nginx como proxy reverso con SSL automático.
- **Propshaft** reemplaza Sprockets con un asset pipeline más rápido y simple.
- **Importmaps** eliminan la necesidad de Node.js y bundlers JavaScript.
- **Brakeman** viene incluido para seguridad, junto con un workflow de CI generado.
- Rails 8 reduce drásticamente la complejidad operacional, permitiendo que una persona gestione toda la infraestructura.
