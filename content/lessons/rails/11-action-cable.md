# Action Cable: WebSockets en Rails 8

Action Cable integra WebSockets en Rails de forma nativa, permitiendo comunicación bidireccional en tiempo real entre el servidor y los clientes. En este capítulo aprenderás a construir funcionalidades en vivo como chats, notificaciones y dashboards actualizados al instante.

---

## ¿Qué son los WebSockets?

HTTP tradicional funciona con un modelo petición-respuesta: el cliente pide, el servidor responde. Los WebSockets abren una **conexión persistente** entre cliente y servidor, permitiendo que ambos envíen datos en cualquier momento.

### HTTP vs WebSockets

```
HTTP tradicional:
  Cliente → Petición → Servidor
  Cliente ← Respuesta ← Servidor
  (Conexión cerrada)

WebSockets:
  Cliente ←→ Conexión persistente ←→ Servidor
  (Ambos pueden enviar datos en cualquier momento)
```

Casos de uso ideales para WebSockets:
- Chat en tiempo real
- Notificaciones push
- Dashboards con datos en vivo
- Edición colaborativa
- Indicadores de presencia ("usuario escribiendo...")

---

## Arquitectura de Action Cable

Action Cable tiene tres componentes principales:

1. **Connection**: gestiona la conexión WebSocket y la autenticación.
2. **Channel**: similar a un controlador, maneja la lógica de un "tema" específico.
3. **Subscription**: la suscripción del cliente a un canal.

```
┌─────────────┐        ┌─────────────────┐
│   Cliente    │◄──────►│   Action Cable   │
│ (JavaScript) │   WS   │    Servidor      │
└─────────────┘        ├─────────────────┤
                       │   Connection     │
                       │   ├── Channel 1  │
                       │   ├── Channel 2  │
                       │   └── Channel N  │
                       └─────────────────┘
```

---

## Configuración

Action Cable viene preconfigurado en Rails 8. Veamos los archivos clave:

```ruby
# config/cable.yml
development:
  adapter: async

test:
  adapter: test

production:
  adapter: redis
  url: <%= ENV.fetch("REDIS_URL") { "redis://localhost:6379/1" } %>
  channel_prefix: superguide_production
```

```ruby
# Para producción necesitas Redis
# Gemfile
gem "redis", ">= 4.0.1"
```

```ruby
# config/routes.rb
Rails.application.routes.draw do
  # Action Cable se monta automáticamente en /cable
  # Puedes personalizarlo si es necesario:
  # mount ActionCable.server => "/ws"
end
```

---

## Connection: Autenticación

La conexión es donde autentificas al usuario que se conecta por WebSocket.

```ruby
# app/channels/application_cable/connection.rb
module ApplicationCable
  class Connection < ActionCable::Connection::Base
    identified_by :current_user

    def connect
      self.current_user = find_verified_user
    end

    private

    def find_verified_user
      # Usar la sesión del navegador (cookies)
      if verified_user = User.find_by(id: cookies.encrypted[:user_id])
        verified_user
      else
        reject_unauthorized_connection
      end
    end
  end
end
```

```ruby
# Alternativa: autenticar con token (útil para APIs)
module ApplicationCable
  class Connection < ActionCable::Connection::Base
    identified_by :current_user

    def connect
      self.current_user = find_verified_user
    end

    private

    def find_verified_user
      token = request.params[:token]
      if verified_user = User.find_by(auth_token: token)
        verified_user
      else
        reject_unauthorized_connection
      end
    end
  end
end
```

---

## Channels: Canales

Los canales son como controladores para WebSockets. Cada canal maneja un flujo de datos específico.

### Crear un canal

```bash
bin/rails generate channel Chat
# Crea:
#   app/channels/chat_channel.rb
#   app/javascript/channels/chat_channel.js
```

### Canal del servidor

```ruby
# app/channels/chat_channel.rb
class ChatChannel < ApplicationCable::Channel
  # Se ejecuta cuando un cliente se suscribe
  def subscribed
    course = Course.find(params[:course_id])
    stream_from "chat_course_#{course.id}"
  end

  # Se ejecuta cuando un cliente se desuscribe
  def unsubscribed
    # Limpiar recursos si es necesario
    stop_all_streams
  end

  # Método personalizado que el cliente puede invocar
  def speak(data)
    Message.create!(
      user: current_user,
      course_id: params[:course_id],
      body: data["message"]
    )
  end

  # Otro método: el usuario está escribiendo
  def typing
    ActionCable.server.broadcast(
      "chat_course_#{params[:course_id]}",
      { type: "typing", user: current_user.name }
    )
  end
end
```

### `stream_from` vs `stream_for`

```ruby
class ChatChannel < ApplicationCable::Channel
  # stream_from: usa un string como identificador del stream
  def subscribed
    stream_from "chat_course_#{params[:course_id]}"
  end
end

class NotificationChannel < ApplicationCable::Channel
  # stream_for: usa un modelo. Rails genera el nombre automáticamente
  def subscribed
    stream_for current_user
  end
end

# Para transmitir a stream_for:
NotificationChannel.broadcast_to(user, {
  title: "Nueva lección disponible",
  body: "Se ha publicado la lección 5 del curso de Rails"
})
```

---

## Cliente JavaScript

### Suscribirse a un canal

```javascript
// app/javascript/channels/chat_channel.js
import consumer from "channels/consumer"

const chatChannel = consumer.subscriptions.create(
  { channel: "ChatChannel", course_id: 42 },
  {
    // Cuando la suscripción se conecta
    connected() {
      console.log("Conectado al chat del curso")
    },

    // Cuando se pierde la conexión
    disconnected() {
      console.log("Desconectado del chat")
    },

    // Cuando llega un mensaje del servidor
    received(data) {
      if (data.type === "typing") {
        this.showTypingIndicator(data.user)
        return
      }

      const messagesContainer = document.getElementById("messages")
      messagesContainer.insertAdjacentHTML("beforeend", data.html)
      messagesContainer.scrollTop = messagesContainer.scrollHeight
    },

    // Métodos personalizados
    speak(message) {
      this.perform("speak", { message: message })
    },

    notifyTyping() {
      this.perform("typing")
    },

    showTypingIndicator(userName) {
      const indicator = document.getElementById("typing-indicator")
      indicator.textContent = `${userName} está escribiendo...`
      setTimeout(() => { indicator.textContent = "" }, 2000)
    }
  }
)

// Usar desde el DOM
document.getElementById("chat-form").addEventListener("submit", (event) => {
  event.preventDefault()
  const input = document.getElementById("chat-input")
  chatChannel.speak(input.value)
  input.value = ""
})
```

### El consumer

```javascript
// app/javascript/channels/consumer.js
import { createConsumer } from "@rails/actioncable"

export default createConsumer()

// Con URL personalizada
// export default createConsumer("wss://miapp.com/cable")

// Con token de autenticación
// export default createConsumer(`/cable?token=${getAuthToken()}`)
```

---

## Broadcasting: Transmisiones

Broadcasting es el mecanismo para enviar datos desde el servidor a todos los clientes suscritos.

### Desde un modelo (callback)

```ruby
# app/models/message.rb
class Message < ApplicationRecord
  belongs_to :user
  belongs_to :course

  after_create_commit :broadcast_message

  private

  def broadcast_message
    ActionCable.server.broadcast(
      "chat_course_#{course_id}",
      {
        html: ApplicationController.renderer.render(
          partial: "messages/message",
          locals: { message: self }
        )
      }
    )
  end
end
```

### Desde un controlador

```ruby
# app/controllers/messages_controller.rb
class MessagesController < ApplicationController
  def create
    @message = current_user.messages.build(message_params)

    if @message.save
      ActionCable.server.broadcast(
        "chat_course_#{@message.course_id}",
        {
          html: render_to_string(
            partial: "messages/message",
            locals: { message: @message }
          )
        }
      )
      head :ok
    else
      render json: { errors: @message.errors }, status: :unprocessable_entity
    end
  end
end
```

### Desde un Job (recomendado para tareas pesadas)

```ruby
# app/jobs/broadcast_message_job.rb
class BroadcastMessageJob < ApplicationJob
  queue_as :default

  def perform(message)
    ActionCable.server.broadcast(
      "chat_course_#{message.course_id}",
      {
        html: ApplicationController.renderer.render(
          partial: "messages/message",
          locals: { message: message }
        ),
        user_id: message.user_id,
        created_at: message.created_at.iso8601
      }
    )
  end
end

# En el modelo
class Message < ApplicationRecord
  after_create_commit -> { BroadcastMessageJob.perform_later(self) }
end
```

---

## Ejemplo Completo: Chat en Tiempo Real

### Modelo y migración

```bash
bin/rails generate model Message user:references course:references body:text
bin/rails db:migrate
```

```ruby
# app/models/message.rb
class Message < ApplicationRecord
  belongs_to :user
  belongs_to :course

  validates :body, presence: true

  after_create_commit :broadcast_to_course

  private

  def broadcast_to_course
    broadcast_append_to(
      "chat_course_#{course_id}",
      target: "messages",
      partial: "messages/message",
      locals: { message: self }
    )
  end
end
```

### Canal

```ruby
# app/channels/chat_channel.rb
class ChatChannel < ApplicationCable::Channel
  def subscribed
    @course = Course.find(params[:course_id])
    stream_from "chat_course_#{@course.id}"
  end

  def unsubscribed
    stop_all_streams
  end
end
```

### Vistas

```ruby
# app/views/courses/show.html.erb
<h1><%= @course.name %></h1>

<div id="chat-section">
  <h2>Chat del Curso</h2>

  <!-- Suscripción a Turbo Streams via Action Cable -->
  <%= turbo_stream_from "chat_course_#{@course.id}" %>

  <div id="messages" class="chat-messages" style="height: 400px; overflow-y: auto;">
    <% @course.messages.includes(:user).order(:created_at).last(50).each do |message| %>
      <%= render partial: "messages/message", locals: { message: message } %>
    <% end %>
  </div>

  <div id="typing-indicator" class="text-muted small"></div>

  <%= form_with model: Message.new, url: course_messages_path(@course), class: "mt-3" do |f| %>
    <div class="input-group">
      <%= f.text_field :body, placeholder: "Escribe un mensaje...",
          class: "form-control", autocomplete: "off" %>
      <%= f.submit "Enviar", class: "btn btn-primary" %>
    </div>
  <% end %>
</div>

# app/views/messages/_message.html.erb
<div id="<%= dom_id(message) %>" class="chat-message mb-2">
  <strong><%= message.user.name %>:</strong>
  <span><%= message.body %></span>
  <small class="text-muted"><%= l(message.created_at, format: :short) %></small>
</div>
```

### Controlador de mensajes

```ruby
# app/controllers/messages_controller.rb
class MessagesController < ApplicationController
  before_action :set_course

  def create
    @message = @course.messages.build(message_params)
    @message.user = current_user

    if @message.save
      respond_to do |format|
        format.turbo_stream { head :ok }
        format.html { redirect_to @course }
      end
    else
      redirect_to @course, alert: "No se pudo enviar el mensaje"
    end
  end

  private

  def set_course
    @course = Course.find(params[:course_id])
  end

  def message_params
    params.require(:message).permit(:body)
  end
end
```

---

## Turbo Streams sobre WebSocket

Rails 8 integra Turbo Streams con Action Cable de forma elegante. Es la forma más sencilla de añadir tiempo real:

```ruby
# app/models/lesson.rb
class Lesson < ApplicationRecord
  belongs_to :course

  # Transmite automáticamente crear/actualizar/eliminar a los suscriptores
  broadcasts_to :course
end
```

```ruby
# En la vista del curso, suscribirse
<%= turbo_stream_from @course %>

<div id="lessons">
  <%= render @course.lessons %>
</div>
```

Con estas dos líneas, cualquier cambio en las lecciones del curso (crear, editar, eliminar) se refleja automáticamente en todos los navegadores que estén viendo ese curso. No necesitas escribir JavaScript ni canales personalizados.

```ruby
# Personalizar las transmisiones
class Lesson < ApplicationRecord
  belongs_to :course

  broadcasts_to :course,
    inserts_by: :prepend,
    target: "lessons"

  # O transmisiones más específicas
  after_create_commit -> {
    broadcast_prepend_to(course, target: "lessons")
  }

  after_update_commit -> {
    broadcast_replace_to(course)
  }

  after_destroy_commit -> {
    broadcast_remove_to(course)
  }
end
```

---

## Consejos Prácticos

1. **Usa Redis en producción**: el adaptador `async` solo funciona para un solo proceso.
2. **Autentica siempre**: nunca dejes la conexión sin autenticar en producción.
3. **Usa jobs para broadcasts pesados**: no bloquees el request principal.
4. **Prefiere Turbo Streams sobre Action Cable manual**: es más sencillo e idiomático en Rails 8.
5. **Maneja desconexiones**: implementa la lógica de reconexión y estado offline.
6. **Limita los datos transmitidos**: envía solo lo necesario para reducir ancho de banda.

```ruby
# En producción, configurar la URL de cable
# config/environments/production.rb
config.action_cable.url = "wss://miapp.com/cable"
config.action_cable.allowed_request_origins = ["https://miapp.com"]
```

```ruby
# Limitar conexiones por usuario
# app/channels/application_cable/connection.rb
def connect
  self.current_user = find_verified_user
  logger.add_tags "ActionCable", current_user.email
end
```

---

## Resumen

Action Cable trae WebSockets al mundo de Rails de forma integrada y productiva:

- **WebSockets** abren una conexión persistente bidireccional entre cliente y servidor.
- **Connection** autentica al usuario que se conecta.
- **Channels** son como controladores: gestionan la lógica de cada flujo de datos en tiempo real.
- **`stream_from`/`stream_for`** definen a qué transmisiones se suscribe un canal.
- **Broadcasting** permite enviar datos a todos los clientes suscritos desde modelos, controladores o jobs.
- **Turbo Streams sobre WebSocket** es la integración moderna que simplifica enormemente las funcionalidades en tiempo real.

Con Action Cable y Turbo Streams, construir chat, notificaciones y dashboards en vivo es tan natural como escribir cualquier otra funcionalidad en Rails.
