# Deployment y DevOps

En esta lección aprenderás a preparar tu aplicación Rails para producción y desplegarla usando las herramientas modernas del ecosistema Rails 8, incluyendo Kamal, Docker y la Solid Trifecta.

---

## Preparar para producción

Antes de desplegar, revisa la configuración de producción:

```ruby
# config/environments/production.rb
Rails.application.configure do
  config.cache_classes = true
  config.eager_load = true
  config.consider_all_requests_local = false
  config.action_controller.perform_caching = true

  # Servir archivos estáticos (Thruster se encarga en Rails 8)
  config.public_file_server.enabled = true

  # Nivel de log
  config.log_level = :info
  config.log_tags = [:request_id]

  # Forzar SSL
  config.force_ssl = true

  # Mailer
  config.action_mailer.default_url_options = { host: "miapp.com" }
  config.action_mailer.delivery_method = :smtp

  # Active Job
  config.active_job.queue_adapter = :solid_queue

  # Cache
  config.cache_store = :solid_cache_store

  # Action Cable
  config.action_cable.adapter = :solid_cable
end
```

---

## Credenciales y variables de entorno

Rails usa un sistema de credenciales encriptadas para gestionar secretos:

```bash
# Editar credenciales (abre el editor)
EDITOR="code --wait" bin/rails credentials:edit

# Editar credenciales para un entorno específico
EDITOR="code --wait" bin/rails credentials:edit --environment production
```

El archivo desencriptado tiene esta estructura:

```yaml
# config/credentials.yml.enc (desencriptado)
secret_key_base: abc123...

smtp:
  user: noreply@miapp.com
  password: smtp_password_seguro

database:
  host: db.miapp.com
  username: rails_user
  password: db_password_seguro

aws:
  access_key_id: AKIA...
  secret_access_key: wJalrXU...
  bucket: miapp-storage

stripe:
  secret_key: sk_live_...
  publishable_key: pk_live_...
```

Acceder a las credenciales en el código:

```ruby
Rails.application.credentials.smtp[:user]
Rails.application.credentials.dig(:aws, :access_key_id)
Rails.application.credentials.secret_key_base
```

> **Importante:** El archivo `config/master.key` (o `config/credentials/production.key`) **nunca** se sube al repositorio. Se comparte de forma segura con el equipo.

---

## Assets Precompile

En producción, los assets se precompilan para optimizar el rendimiento:

```bash
bin/rails assets:precompile
```

Con **Propshaft** (nuevo en Rails 8), el proceso es más simple que con Sprockets:

```ruby
# config/application.rb
# Propshaft es el asset pipeline por defecto en Rails 8
```

Si usas importmaps (por defecto en Rails 8), no necesitas compilar JavaScript:

```bash
# Los importmaps se resuelven directamente en el navegador
bin/rails importmap:pin lodash
```

---

## Dockerfile

Rails 8 genera un Dockerfile optimizado automáticamente:

```dockerfile
# Dockerfile
ARG RUBY_VERSION=3.3.0
FROM docker.io/library/ruby:$RUBY_VERSION-slim AS base

WORKDIR /rails

# Instalar dependencias del sistema
RUN apt-get update -qq && \
    apt-get install --no-install-recommends -y \
    curl \
    libjemalloc2 \
    libvips \
    postgresql-client \
    && rm -rf /var/lib/apt/lists /var/cache/apt/archives

# Variables de entorno para producción
ENV RAILS_ENV="production" \
    BUNDLE_DEPLOYMENT="1" \
    BUNDLE_PATH="/usr/local/bundle" \
    BUNDLE_WITHOUT="development:test"

# Etapa de build
FROM base AS build

RUN apt-get update -qq && \
    apt-get install --no-install-recommends -y \
    build-essential \
    git \
    libpq-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists /var/cache/apt/archives

COPY Gemfile Gemfile.lock ./
RUN bundle install && \
    rm -rf ~/.bundle/ "${BUNDLE_PATH}"/ruby/*/cache

COPY . .

RUN bundle exec bootsnap precompile app/ lib/
RUN SECRET_KEY_BASE_DUMMY=1 ./bin/rails assets:precompile

# Etapa final
FROM base

COPY --from=build "${BUNDLE_PATH}" "${BUNDLE_PATH}"
COPY --from=build /rails /rails

RUN groupadd --system --gid 1000 rails && \
    useradd rails --uid 1000 --gid 1000 --create-home --shell /bin/bash && \
    chown -R rails:rails db log storage tmp

USER 1000:1000

ENTRYPOINT ["/rails/bin/docker-entrypoint"]
EXPOSE 3000
CMD ["./bin/thrust", "./bin/rails", "server"]
```

```bash
# Construir imagen
docker build -t miapp:latest .

# Ejecutar contenedor
docker run -p 3000:3000 \
  -e RAILS_MASTER_KEY=$(cat config/master.key) \
  -e DATABASE_URL=postgres://user:pass@host/miapp_production \
  miapp:latest
```

---

## Docker Compose para desarrollo

```yaml
# docker-compose.yml
services:
  web:
    build: .
    ports:
      - "3000:3000"
    environment:
      - DATABASE_URL=postgres://postgres:password@db/miapp_development
      - REDIS_URL=redis://redis:6379
      - RAILS_ENV=development
    volumes:
      - .:/rails
      - bundle:/usr/local/bundle
    depends_on:
      db:
        condition: service_healthy

  db:
    image: postgres:16
    environment:
      POSTGRES_PASSWORD: password
      POSTGRES_DB: miapp_development
    volumes:
      - postgres_data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postgres"]
      interval: 5s
      timeout: 5s
      retries: 5
    ports:
      - "5432:5432"

volumes:
  postgres_data:
  bundle:
```

```bash
docker compose up -d
docker compose exec web bin/rails db:setup
```

---

## Kamal: Deploy tool de Rails 8

**Kamal** es la herramienta oficial de deployment de Rails 8. Despliega contenedores Docker directamente en servidores usando SSH, sin necesidad de Kubernetes.

```bash
# Instalar Kamal
gem install kamal

# Inicializar en el proyecto
kamal init
```

Configuración principal:

```yaml
# config/deploy.yml
service: miapp

image: usuario/miapp

servers:
  web:
    hosts:
      - 192.168.1.100
      - 192.168.1.101
    labels:
      traefik.http.routers.miapp.rule: Host(`miapp.com`)
      traefik.http.routers.miapp.tls.certresolver: letsencrypt
  job:
    hosts:
      - 192.168.1.102
    cmd: bin/jobs start

proxy:
  ssl: true
  host: miapp.com

registry:
  username: usuario
  password:
    - KAMAL_REGISTRY_PASSWORD

env:
  clear:
    RAILS_LOG_LEVEL: info
    SOLID_QUEUE_IN_PUMA: true
  secret:
    - RAILS_MASTER_KEY
    - DATABASE_URL
    - SMTP_PASSWORD

volumes:
  - "miapp_storage:/rails/storage"

asset_path: /rails/public/assets

builder:
  multiarch: false

accessories:
  db:
    image: postgres:16
    host: 192.168.1.100
    port: 5432
    env:
      clear:
        POSTGRES_DB: miapp_production
      secret:
        - POSTGRES_PASSWORD
    volumes:
      - postgres_data:/var/lib/postgresql/data
```

Comandos de Kamal:

```bash
# Primer despliegue
kamal setup

# Despliegues posteriores
kamal deploy

# Ver logs
kamal app logs

# Consola Rails remota
kamal app exec -i "bin/rails console"

# Rollback
kamal rollback

# Ver estado
kamal details
```

---

## Thruster

**Thruster** es un proxy HTTP incluido en Rails 8 que se pone delante de Puma. Proporciona:

- Compresión gzip/brotli automática
- Caché de assets con headers apropiados
- Terminación SSL con certificados Let's Encrypt
- Protección X-Sendfile para archivos estáticos

No requiere configuración adicional. Se activa automáticamente en producción:

```bash
# bin/thrust se usa como wrapper de Puma
./bin/thrust ./bin/rails server
```

Esto elimina la necesidad de Nginx o Apache como proxy reverso en muchos casos.

---

## Solid Cache

Solid Cache almacena la caché en la base de datos en lugar de Redis:

```ruby
# config/environments/production.rb
config.cache_store = :solid_cache_store
```

```bash
bin/rails solid_cache:install
bin/rails db:migrate
```

```yaml
# config/solid_cache.yml
production:
  store:
    database: cache
    max_age: 604800 # 7 días en segundos
    max_size: 256000000 # 256 MB
```

Ventajas de Solid Cache:

- Sin dependencia externa (no necesita Redis)
- Los discos SSD modernos son suficientemente rápidos
- Integración nativa con Rails
- Menor complejidad operacional

---

## Solid Queue y Solid Cable

**Solid Queue** como adaptador de colas (visto en la lección anterior):

```ruby
config.active_job.queue_adapter = :solid_queue
```

**Solid Cable** para Action Cable (WebSockets):

```ruby
# config/cable.yml
production:
  adapter: solid_cable
  polling_interval: 0.1
  message_retention: 1.day
```

Ambos usan la base de datos como backend, simplificando la infraestructura:

```bash
bin/rails solid_queue:install
bin/rails solid_cable:install
bin/rails db:migrate
```

---

## Checklist de deployment

Antes de cada deploy, verifica:

```bash
# 1. Tests pasan
bin/rails test
bin/rails test:system

# 2. Seguridad
bin/brakeman

# 3. Assets compilan
bin/rails assets:precompile

# 4. Migraciones pendientes
bin/rails db:migrate:status

# 5. Credenciales configuradas
bin/rails credentials:show --environment production

# 6. Variables de entorno establecidas
kamal env push
```

```ruby
# Verificar configuración de producción
Rails.application.config.force_ssl          # => true
Rails.application.config.log_level          # => :info
Rails.application.config.eager_load         # => true
Rails.application.config.cache_classes      # => true
```

---

## Monitoreo en producción

Configura logging estructurado y monitoreo:

```ruby
# config/environments/production.rb
config.logger = ActiveSupport::Logger.new(STDOUT)
  .tap { |logger| logger.formatter = ::Logger::Formatter.new }
  .then { |logger| ActiveSupport::TaggedLogging.new(logger) }

config.log_tags = [:request_id]
config.log_level = ENV.fetch("RAILS_LOG_LEVEL", "info")
```

Health check endpoint:

```ruby
# config/routes.rb
Rails.application.routes.draw do
  get "up" => "rails/health#show", as: :rails_health_check
end
```

```bash
# Verificar salud de la aplicación
curl https://miapp.com/up
# => 200 OK
```

---

## Resumen

- Configura `config/environments/production.rb` correctamente antes de desplegar.
- Usa **credenciales encriptadas** para gestionar secretos de forma segura.
- **Kamal** es la herramienta oficial de deploy en Rails 8 — despliega Docker vía SSH.
- **Thruster** reemplaza la necesidad de Nginx como proxy reverso.
- La **Solid Trifecta** (Solid Cache, Solid Queue, Solid Cable) elimina la dependencia de Redis.
- Usa `docker-compose` para desarrollo local y el `Dockerfile` generado por Rails para producción.
- Automatiza el proceso de deploy y configura monitoreo desde el primer día.
