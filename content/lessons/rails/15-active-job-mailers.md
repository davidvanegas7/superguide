# Active Job y Action Mailer

En esta lección aprenderás a ejecutar tareas en segundo plano con Active Job y a enviar correos electrónicos con Action Mailer, dos componentes esenciales de cualquier aplicación Rails en producción.

---

## ¿Qué es Active Job?

Active Job es el framework de Rails para declarar trabajos (jobs) y ejecutarlos en diferentes backends de colas. Proporciona una interfaz uniforme sin importar qué adaptador de cola uses por debajo.

Los casos de uso más comunes son:

- Envío de correos electrónicos
- Procesamiento de imágenes
- Generación de reportes PDF
- Llamadas a APIs externas
- Limpieza de datos periódica

---

## Crear un Job

```bash
rails generate job ProcessOrder
```

Esto genera:

```ruby
# app/jobs/process_order_job.rb
class ProcessOrderJob < ApplicationJob
  queue_as :default

  def perform(order)
    # Lógica del trabajo aquí
    order.update!(status: "processing")
    PaymentService.charge(order)
    order.update!(status: "completed")
    OrderMailer.confirmation(order).deliver_later
  end
end
```

---

## perform_later vs perform_now

Hay dos formas de ejecutar un job:

```ruby
# Asíncrono: se encola y ejecuta en segundo plano
ProcessOrderJob.perform_later(order)

# Síncrono: se ejecuta inmediatamente en el proceso actual
ProcessOrderJob.perform_now(order)
```

`perform_later` es lo que usarás en producción. Encola el trabajo para que un worker lo procese. `perform_now` es útil en tests o cuando necesitas el resultado inmediato.

```ruby
# Ejecutar con delay
ProcessOrderJob.set(wait: 5.minutes).perform_later(order)

# Ejecutar en un momento específico
ProcessOrderJob.set(wait_until: Date.tomorrow.noon).perform_later(order)

# Especificar cola
ProcessOrderJob.set(queue: :urgent).perform_later(order)
```

---

## Colas y prioridades

Puedes organizar jobs en diferentes colas según su prioridad:

```ruby
class ProcessOrderJob < ApplicationJob
  queue_as :critical

  def perform(order)
    # ...
  end
end

class GenerateReportJob < ApplicationJob
  queue_as :low_priority

  def perform(user)
    # ...
  end
end

class CleanupJob < ApplicationJob
  queue_as :maintenance

  def perform
    # ...
  end
end
```

---

## Callbacks y manejo de errores

Active Job proporciona callbacks y manejo de reintentos:

```ruby
class ImportDataJob < ApplicationJob
  queue_as :default

  # Reintentar hasta 5 veces con espera exponencial
  retry_on Net::OpenTimeout, wait: :polynomially_longer, attempts: 5

  # Descartar si el registro ya no existe
  discard_on ActiveRecord::RecordNotFound

  before_perform do |job|
    Rails.logger.info "Iniciando job: #{job.class.name} con args: #{job.arguments}"
  end

  after_perform do |job|
    Rails.logger.info "Job completado: #{job.class.name}"
  end

  around_perform do |job, block|
    start_time = Time.current
    block.call
    duration = Time.current - start_time
    Rails.logger.info "Job #{job.class.name} tardó #{duration}s"
  end

  def perform(file_path)
    data = CSV.read(file_path)
    data.each do |row|
      Product.create!(name: row[0], price: row[1])
    end
  end
end
```

---

## Solid Queue en Rails 8

Rails 8 introduce **Solid Queue** como el adaptador de colas por defecto. A diferencia de Sidekiq o Resque, Solid Queue usa la base de datos como backend, eliminando la necesidad de Redis.

```ruby
# config/application.rb
config.active_job.queue_adapter = :solid_queue
```

```bash
# Instalar Solid Queue
bin/rails solid_queue:install
```

Esto genera las migraciones necesarias:

```bash
bin/rails db:migrate
```

Configuración en `config/solid_queue.yml`:

```yaml
# config/solid_queue.yml
default: &default
  dispatchers:
    - polling_interval: 1
      batch_size: 500
  workers:
    - queues: "*"
      threads: 5
      polling_interval: 0.1

development:
  <<: *default

production:
  <<: *default
  workers:
    - queues: [critical, default]
      threads: 10
      polling_interval: 0.1
    - queues: [low_priority, maintenance]
      threads: 3
      polling_interval: 1
```

Iniciar el worker:

```bash
bin/jobs start
```

---

## Scheduling con Solid Queue

Solid Queue permite programar tareas recurrentes (similar a cron):

```yaml
# config/recurring.yml
production:
  cleanup_old_sessions:
    class: CleanupSessionsJob
    schedule: every day at 3am
    queue: maintenance

  generate_daily_report:
    class: DailyReportJob
    schedule: every day at 8am
    queue: default

  sync_external_data:
    class: SyncDataJob
    schedule: every 30 minutes
    queue: default
```

```ruby
# app/jobs/cleanup_sessions_job.rb
class CleanupSessionsJob < ApplicationJob
  queue_as :maintenance

  def perform
    Session.where("updated_at < ?", 30.days.ago).delete_all
    Rails.logger.info "Sesiones antiguas eliminadas"
  end
end
```

---

## Action Mailer: Introducción

Action Mailer te permite enviar correos electrónicos desde tu aplicación Rails. Los mailers funcionan de forma similar a los controladores.

```bash
rails generate mailer UserMailer welcome reset_password
```

Esto genera:

```ruby
# app/mailers/user_mailer.rb
class UserMailer < ApplicationMailer
  def welcome(user)
    @user = user
    @login_url = login_url

    mail(
      to: @user.email,
      subject: "¡Bienvenido a nuestra plataforma!"
    )
  end

  def reset_password(user)
    @user = user
    @token = user.generate_reset_token

    mail(
      to: @user.email,
      subject: "Restablecer contraseña"
    )
  end
end
```

---

## Vistas de correo

Los correos usan vistas como cualquier otro componente de Rails. Crea versiones HTML y texto plano:

```erb
<%# app/views/user_mailer/welcome.html.erb %>
<h1>¡Hola <%= @user.name %>!</h1>

<p>Gracias por registrarte en nuestra plataforma.</p>

<p>Para comenzar, inicia sesión aquí:</p>
<p><%= link_to "Iniciar sesión", @login_url %></p>

<p>¡Que disfrutes la experiencia!</p>
```

```erb
<%# app/views/user_mailer/welcome.text.erb %>
¡Hola <%= @user.name %>!

Gracias por registrarte en nuestra plataforma.

Para comenzar, inicia sesión aquí: <%= @login_url %>

¡Que disfrutes la experiencia!
```

---

## Configuración del Mailer

```ruby
# app/mailers/application_mailer.rb
class ApplicationMailer < ActionMailer::Base
  default from: "noreply@miapp.com"
  layout "mailer"
end
```

Configura el servidor SMTP en el entorno correspondiente:

```ruby
# config/environments/production.rb
config.action_mailer.delivery_method = :smtp
config.action_mailer.smtp_settings = {
  address: "smtp.gmail.com",
  port: 587,
  domain: "miapp.com",
  user_name: Rails.application.credentials.dig(:smtp, :user),
  password: Rails.application.credentials.dig(:smtp, :password),
  authentication: "plain",
  enable_starttls_auto: true
}

config.action_mailer.default_url_options = { host: "miapp.com" }
```

Para desarrollo, usa `letter_opener` para ver correos en el navegador:

```ruby
# Gemfile (grupo development)
gem "letter_opener"

# config/environments/development.rb
config.action_mailer.delivery_method = :letter_opener
config.action_mailer.perform_deliveries = true
```

---

## Enviar correos

```ruby
# Enviar inmediatamente (bloquea el proceso)
UserMailer.welcome(@user).deliver_now

# Enviar en segundo plano (recomendado)
UserMailer.welcome(@user).deliver_later

# Enviar con delay
UserMailer.welcome(@user).deliver_later(wait: 1.hour)
```

> **Tip:** Siempre usa `deliver_later` en producción. Enviar correos de forma síncrona bloquea la petición HTTP y degrada la experiencia del usuario.

---

## Adjuntos (Attachments)

```ruby
class InvoiceMailer < ApplicationMailer
  def send_invoice(user, invoice)
    @user = user
    @invoice = invoice

    # Adjuntar archivo
    attachments["factura_#{invoice.number}.pdf"] = invoice.generate_pdf

    # Adjuntar archivo desde disco
    attachments["terminos.pdf"] = File.read("public/docs/terminos.pdf")

    # Adjuntar imagen inline
    attachments.inline["logo.png"] = File.read("app/assets/images/logo.png")

    mail(to: @user.email, subject: "Tu factura ##{invoice.number}")
  end
end
```

En la vista HTML, referencia imágenes inline:

```erb
<%= image_tag attachments["logo.png"].url, alt: "Logo" %>
```

---

## Mailer Previews

Rails permite previsualizar correos en el navegador sin enviarlos:

```ruby
# test/mailers/previews/user_mailer_preview.rb
class UserMailerPreview < ActionMailer::Preview
  def welcome
    user = User.first || User.new(name: "Juan", email: "juan@test.com")
    UserMailer.welcome(user)
  end

  def reset_password
    user = User.first || User.new(name: "Juan", email: "juan@test.com")
    UserMailer.reset_password(user)
  end
end
```

Visita `http://localhost:3000/rails/mailers` para ver todas las previsualizaciones disponibles.

---

## Ejemplo completo: Sistema de notificaciones

```ruby
# app/jobs/notification_job.rb
class NotificationJob < ApplicationJob
  queue_as :default

  retry_on Net::SMTPError, wait: 5.minutes, attempts: 3

  def perform(user, event_type)
    case event_type
    when "order_confirmed"
      OrderMailer.confirmation(user).deliver_now
    when "shipping_update"
      ShippingMailer.update(user).deliver_now
    when "weekly_digest"
      DigestMailer.weekly(user).deliver_now
    end
  end
end

# Uso en un controlador
class OrdersController < ApplicationController
  def confirm
    @order = Order.find(params[:id])
    @order.update!(status: :confirmed)
    NotificationJob.perform_later(@order.user, "order_confirmed")
    redirect_to @order, notice: "Pedido confirmado"
  end
end
```

---

## Resumen

- **Active Job** proporciona una interfaz unificada para ejecutar tareas en segundo plano.
- Usa `perform_later` para encolar trabajos y `perform_now` para ejecución inmediata.
- **Solid Queue** (Rails 8) usa la base de datos como cola, sin necesidad de Redis.
- Configura tareas recurrentes con `config/recurring.yml` en Solid Queue.
- **Action Mailer** permite enviar correos desde Rails con vistas HTML y texto plano.
- Siempre usa `deliver_later` en producción para no bloquear peticiones HTTP.
- Usa **mailer previews** para verificar el diseño de correos sin enviarlos.
- Maneja errores en los jobs con `retry_on` y `discard_on`.
