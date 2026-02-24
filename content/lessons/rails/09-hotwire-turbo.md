# Hotwire y Turbo en Rails 8

Hotwire es el enfoque moderno de Rails para construir aplicaciones web rápidas e interactivas sin escribir mucho JavaScript. En Rails 8, Hotwire viene integrado por defecto y es la forma recomendada de añadir interactividad a tus aplicaciones.

---

## ¿Qué es Hotwire?

Hotwire (HTML Over The Wire) es un conjunto de herramientas que envían HTML desde el servidor en lugar de JSON. Está compuesto por:

- **Turbo**: acelera la navegación y permite actualizaciones parciales de página.
- **Stimulus**: framework JavaScript ligero para añadir comportamiento a tu HTML.
- **Strada** (opcional): conecta aplicaciones web con aplicaciones nativas móviles.

La filosofía central es: **el servidor genera HTML y lo envía al navegador**, evitando la necesidad de frameworks JavaScript pesados como React o Vue en la mayoría de casos.

```ruby
# Hotwire ya viene incluido en Rails 8
# Verificar en tu Gemfile
gem "turbo-rails"
gem "stimulus-rails"
```

---

## Turbo Drive

Turbo Drive (el sucesor de Turbolinks) intercepta automáticamente los clics en enlaces y envíos de formularios, convirtiendo las navegaciones completas en peticiones AJAX que reemplazan solo el `<body>`.

### Funcionamiento automático

No necesitas configurar nada. Al incluir Turbo en tu aplicación, todos los enlaces y formularios se aceleran automáticamente:

```ruby
# Este enlace ya funciona con Turbo Drive (sin configuración adicional)
<%= link_to "Ver cursos", courses_path %>

# Este formulario también se envía vía Turbo Drive
<%= form_with model: @course do |f| %>
  <%= f.text_field :name %>
  <%= f.submit "Guardar" %>
<% end %>
```

### Deshabilitar Turbo Drive

```ruby
# Deshabilitar para un enlace específico
<%= link_to "Descargar PDF", course_pdf_path(@course), data: { turbo: false } %>

# Deshabilitar para una sección completa
<div data-turbo="false">
  <a href="/external">Este enlace no usa Turbo</a>
  <a href="/another">Este tampoco</a>
</div>
```

### Indicador de progreso

Turbo Drive muestra una barra de progreso en la parte superior durante la navegación:

```css
/* Personalizar la barra de progreso */
.turbo-progress-bar {
  height: 4px;
  background-color: #4f46e5;
}
```

### Caché y vistas previas

```ruby
# Deshabilitar caché para una página que cambia frecuentemente
<% turbo_page_requires_reload %>

# Deshabilitar la vista previa de caché (evita mostrar datos stale)
<head>
  <meta name="turbo-cache-control" content="no-preview">
  <!-- o para deshabilitar completamente -->
  <meta name="turbo-cache-control" content="no-cache">
</head>
```

---

## Turbo Frames

Los Turbo Frames dividen la página en secciones independientes que se pueden actualizar de forma individual sin recargar toda la página.

### Definir un frame

```ruby
# app/views/courses/show.html.erb
<h1><%= @course.name %></h1>

<%= turbo_frame_tag "course_description" do %>
  <p><%= @course.description %></p>
  <%= link_to "Editar descripción", edit_course_path(@course) %>
<% end %>

<%= turbo_frame_tag "course_lessons" do %>
  <h2>Lecciones</h2>
  <ul>
    <% @course.lessons.each do |lesson| %>
      <li><%= lesson.title %></li>
    <% end %>
  </ul>
<% end %>
```

### Edición in-place con Frames

```ruby
# app/views/courses/edit.html.erb
<%= turbo_frame_tag "course_description" do %>
  <%= form_with model: @course do |f| %>
    <%= f.text_area :description, rows: 5 %>
    <%= f.submit "Actualizar" %>
    <%= link_to "Cancelar", course_path(@course) %>
  <% end %>
<% end %>
```

Cuando el usuario hace clic en "Editar descripción", Turbo reemplaza solo el contenido del frame `course_description` con el formulario del `edit`, sin recargar la página completa.

### Carga diferida (Lazy Loading)

```ruby
# El contenido se carga cuando el frame entra en el viewport
<%= turbo_frame_tag "reviews", src: course_reviews_path(@course), loading: :lazy do %>
  <div class="skeleton-loader">
    <p>Cargando reseñas...</p>
  </div>
<% end %>
```

### Romper el frame (target)

A veces necesitas que un enlace dentro de un frame navegue fuera de él:

```ruby
<%= turbo_frame_tag "course_card" do %>
  <h3><%= @course.name %></h3>
  <!-- Este enlace reemplaza la página completa -->
  <%= link_to "Ver detalle completo", course_path(@course), data: { turbo_frame: "_top" } %>
<% end %>
```

---

## Turbo Streams

Turbo Streams es la herramienta más poderosa de Turbo. Permite realizar múltiples actualizaciones quirúrgicas en el DOM con una sola respuesta del servidor.

### Las 7 acciones de Turbo Streams

```ruby
# 1. append - Añadir al final de un contenedor
turbo_stream.append "lessons" do
  render partial: "lessons/lesson", locals: { lesson: @lesson }
end

# 2. prepend - Añadir al inicio de un contenedor
turbo_stream.prepend "notifications" do
  render partial: "notifications/notification", locals: { notification: @notification }
end

# 3. replace - Reemplazar un elemento completo (incluido el contenedor)
turbo_stream.replace dom_id(@course) do
  render partial: "courses/course", locals: { course: @course }
end

# 4. update - Reemplazar solo el contenido interno de un elemento
turbo_stream.update "flash_messages" do
  "<p class='alert alert-success'>¡Operación exitosa!</p>"
end

# 5. remove - Eliminar un elemento del DOM
turbo_stream.remove dom_id(@lesson)

# 6. before - Insertar antes de un elemento
turbo_stream.before dom_id(@lesson) do
  render partial: "lessons/divider"
end

# 7. after - Insertar después de un elemento
turbo_stream.after dom_id(@lesson) do
  render partial: "lessons/related"
end
```

### Respuestas Turbo Stream desde el controlador

```ruby
# app/controllers/lessons_controller.rb
class LessonsController < ApplicationController
  def create
    @lesson = @course.lessons.build(lesson_params)

    if @lesson.save
      respond_to do |format|
        format.turbo_stream  # Busca create.turbo_stream.erb
        format.html { redirect_to @course, notice: "Lección creada" }
      end
    else
      render :new, status: :unprocessable_entity
    end
  end

  def destroy
    @lesson = Lesson.find(params[:id])
    @lesson.destroy

    respond_to do |format|
      format.turbo_stream { render turbo_stream: turbo_stream.remove(dom_id(@lesson)) }
      format.html { redirect_to course_path(@lesson.course), notice: "Lección eliminada" }
    end
  end
end
```

```ruby
# app/views/lessons/create.turbo_stream.erb
<%= turbo_stream.append "lessons" do %>
  <%= render partial: "lessons/lesson", locals: { lesson: @lesson } %>
<% end %>

<%= turbo_stream.update "lesson_count" do %>
  <%= @course.lessons.count %> lecciones
<% end %>

<%= turbo_stream.update "flash_messages" do %>
  <div class="alert alert-success">Lección "<%= @lesson.title %>" creada exitosamente.</div>
<% end %>
```

### HTML necesario en la vista

```ruby
# app/views/courses/show.html.erb
<div id="flash_messages"></div>

<h1><%= @course.name %></h1>
<p id="lesson_count"><%= @course.lessons.count %> lecciones</p>

<div id="lessons">
  <% @course.lessons.each do |lesson| %>
    <%= render partial: "lessons/lesson", locals: { lesson: lesson } %>
  <% end %>
</div>

# app/views/lessons/_lesson.html.erb
<div id="<%= dom_id(lesson) %>" class="lesson-item">
  <h3><%= lesson.title %></h3>
  <p><%= lesson.description %></p>
  <%= button_to "Eliminar", lesson_path(lesson), method: :delete,
      class: "btn btn-danger btn-sm",
      data: { turbo_confirm: "¿Estás seguro?" } %>
</div>
```

---

## Broadcasting con Turbo Streams

Puedes transmitir actualizaciones en tiempo real a todos los usuarios conectados usando ActionCable:

```ruby
# app/models/lesson.rb
class Lesson < ApplicationRecord
  belongs_to :course

  # Transmitir automáticamente cuando se crea/actualiza/elimina
  broadcasts_to :course
end
```

```ruby
# En la vista, suscribirse al canal
# app/views/courses/show.html.erb
<%= turbo_stream_from @course %>

<div id="lessons">
  <%= render @course.lessons %>
</div>
```

```ruby
# Broadcasting manual desde cualquier lugar
Turbo::StreamsChannel.broadcast_append_to(
  @course,
  target: "lessons",
  partial: "lessons/lesson",
  locals: { lesson: @lesson }
)

# Broadcast con replace
Turbo::StreamsChannel.broadcast_replace_to(
  "global_notifications",
  target: "system_alert",
  html: "<div id='system_alert' class='alert'>Mantenimiento programado a las 23:00</div>"
)
```

---

## Atributos data-turbo-*

Rails 8 ofrece varios atributos `data` para controlar el comportamiento de Turbo:

```ruby
# Confirmación antes de enviar
<%= button_to "Eliminar", course_path(@course),
    method: :delete,
    data: { turbo_confirm: "¿Seguro que deseas eliminar este curso?" } %>

# Abrir enlace en un frame específico
<%= link_to "Editar", edit_course_path(@course),
    data: { turbo_frame: "course_form" } %>

# Deshabilitar Turbo para un formulario
<%= form_with model: @upload, data: { turbo: false } do |f| %>
  <%= f.file_field :document %>
  <%= f.submit "Subir" %>
<% end %>

# Mantener el scroll después de una actualización Turbo Stream
<div id="chat_messages" data-turbo-permanent>
  <!-- Contenido que persiste entre navegaciones -->
</div>

# Enviar formulario en cambio (sin botón submit)
<%= form_with url: filter_path, method: :get do |f| %>
  <%= f.select :category, @categories,
      { prompt: "Filtrar por categoría" },
      data: { action: "change->form#submit" } %>
<% end %>
```

---

## Morphing en Rails 8

Rails 8 introduce **page morphing** como alternativa al reemplazo completo del `<body>`. En lugar de reemplazar todo el HTML, el morphing compara el DOM actual con el nuevo y aplica solo los cambios necesarios, preservando el estado del DOM (scroll, focus, animaciones).

### Habilitar morphing

```ruby
# Aplicar morphing a nivel de página con Turbo Drive
<head>
  <meta name="turbo-refresh-method" content="morph">
  <meta name="turbo-refresh-scroll" content="preserve">
</head>
```

### Morphing con Turbo Streams

```ruby
# Acción refresh que usa morphing
turbo_stream.action(:morph, "course_stats") do
  render partial: "courses/stats", locals: { course: @course }
end

# En el controlador
respond_to do |format|
  format.turbo_stream do
    render turbo_stream: turbo_stream.replace("course_stats",
      partial: "courses/stats",
      locals: { course: @course },
      method: :morph
    )
  end
end
```

### Broadcast con refresh (morphing)

```ruby
# app/models/course.rb
class Course < ApplicationRecord
  # Usar broadcasts_refreshes en lugar de broadcasts
  # para que los cambios se apliquen via morphing
  broadcasts_refreshes
end
```

```ruby
# En la vista
<%= turbo_stream_from @course %>

# Cuando el curso se actualiza, la página se "refresca"
# usando morphing en lugar de reemplazar fragmentos de HTML
```

---

## Consejos Prácticos

1. **Empieza con Turbo Drive**: no necesitas configurar nada y ya acelera tu aplicación.
2. **Usa Turbo Frames para edición in-place**: formularios de edición, filtros, paginación.
3. **Usa Turbo Streams para respuestas de formularios**: crear, actualizar y eliminar sin recargar.
4. **Broadcasting para tiempo real**: chat, notificaciones, dashboards en vivo.
5. **Morphing para páginas complejas**: preserva el estado del DOM en actualizaciones frecuentes.
6. **Siempre provee un fallback HTML**: usa `respond_to` para soportar navegación sin JavaScript.
7. **Usa `dom_id`**: genera IDs consistentes basados en el modelo (`course_42`).

```ruby
# dom_id genera identificadores únicos y consistentes
dom_id(@course)           # "course_42"
dom_id(@course, :edit)    # "edit_course_42"
dom_id(Course.new)        # "new_course"
```

---

## Resumen

Hotwire y Turbo representan la filosofía moderna de Rails: enviar HTML desde el servidor y dejar que el framework maneje la interactividad.

- **Turbo Drive** acelera la navegación automáticamente interceptando clics y envíos de formularios.
- **Turbo Frames** permiten actualizar secciones independientes de la página.
- **Turbo Streams** ofrecen 7 acciones para manipular el DOM quirúrgicamente.
- **Broadcasting** permite enviar actualizaciones en tiempo real a todos los usuarios.
- **Morphing** (Rails 8) compara y aplica cambios mínimos al DOM, preservando el estado.

Con Hotwire, puedes construir aplicaciones altamente interactivas escribiendo mínimo JavaScript, manteniendo la productividad y simplicidad que caracterizan a Rails.
