# Vistas, Layouts y Partials en Rails 8

Las vistas son la capa de presentación de tu aplicación Rails. En este capítulo aprenderás a dominar ERB, layouts, partials, helpers y las herramientas modernas que Rails 8 ofrece para construir interfaces dinámicas y mantenibles.

---

## ERB: Embedded Ruby

ERB (Embedded Ruby) es el motor de plantillas por defecto en Rails. Permite mezclar código Ruby con HTML usando etiquetas especiales.

### Etiquetas ERB

```ruby
# Etiqueta de salida: evalúa y muestra el resultado en el HTML
<%= expression %>

# Etiqueta silenciosa: evalúa pero NO muestra nada
<% code %>

# Comentario ERB: no se incluye en la salida HTML
<%# Esto es un comentario %>

# Etiqueta con guion: elimina el salto de línea al final
<%= expression -%>
<% code -%>
```

### Ejemplo práctico

```ruby
# app/views/courses/index.html.erb

<h1>Cursos Disponibles</h1>

<% if @courses.any? %>
  <ul>
    <% @courses.each do |course| %>
      <li>
        <%= link_to course.name, course_path(course) %>
        <span class="badge"><%= course.lessons_count %> lecciones</span>
      </li>
    <% end %>
  </ul>
<% else %>
  <p>No hay cursos disponibles aún.</p>
<% end %>
```

### Escapado de HTML

Rails escapa automáticamente las salidas con `<%= %>` para prevenir ataques XSS:

```ruby
# Esto escapa las etiquetas HTML automáticamente
<%= @user.bio %>

# Si necesitas renderizar HTML sin escapar (cuidado con XSS)
<%= raw @article.body %>
<%= @article.body.html_safe %>

# Forma preferida: usa sanitize para permitir solo etiquetas seguras
<%= sanitize @article.body, tags: %w[p br strong em a], attributes: %w[href] %>
```

---

## Layouts

Los layouts son plantillas que envuelven las vistas individuales, proporcionando una estructura HTML común (header, footer, navegación).

### El layout principal

```ruby
# app/views/layouts/application.html.erb

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><%= content_for?(:title) ? yield(:title) : "SuperGuide" %></title>
    <meta name="description" content="<%= content_for?(:description) ? yield(:description) : "Aprende programación" %>">

    <%= csrf_meta_tags %>
    <%= csp_meta_tag %>

    <%= stylesheet_link_tag "application", "data-turbo-track": "reload" %>
    <%= javascript_importmap_tags %>
  </head>

  <body>
    <%= render "shared/navbar" %>

    <% if notice.present? %>
      <div class="alert alert-success"><%= notice %></div>
    <% end %>

    <% if alert.present? %>
      <div class="alert alert-danger"><%= alert %></div>
    <% end %>

    <main class="container">
      <%= yield %>
    </main>

    <%= render "shared/footer" %>
  </body>
</html>
```

### `yield` y `content_for`

`yield` indica dónde se insertará el contenido de la vista. Puedes tener múltiples bloques con nombre:

```ruby
# En el layout
<head>
  <title><%= yield(:title) %></title>
  <%= yield(:head) %>
</head>
<body>
  <%= yield %>
  <%= yield(:sidebar) %>
</body>

# En la vista
<% content_for :title, "Mi Curso de Rails" %>

<% content_for :head do %>
  <%= stylesheet_link_tag "custom_page" %>
<% end %>

<% content_for :sidebar do %>
  <aside>
    <h3>Lecciones relacionadas</h3>
    <%= render @related_lessons %>
  </aside>
<% end %>

<h1>Contenido principal aquí</h1>
```

### Layouts específicos por controlador

```ruby
# Usar un layout diferente para un controlador
class AdminController < ApplicationController
  layout "admin"
end

# Layout condicional
class PagesController < ApplicationController
  layout :determine_layout

  private

  def determine_layout
    current_user&.admin? ? "admin" : "application"
  end
end

# Sin layout para una acción específica
class ApiController < ApplicationController
  layout false
end

# Layout por acción
class CoursesController < ApplicationController
  layout "special", only: [:show]
end
```

---

## Partials

Los partials son fragmentos reutilizables de vistas. Sus nombres de archivo empiezan con guion bajo (`_`).

### Uso básico

```ruby
# app/views/shared/_navbar.html.erb
<nav class="navbar">
  <div class="navbar-brand">
    <%= link_to "SuperGuide", root_path %>
  </div>
  <div class="navbar-menu">
    <% if current_user %>
      <%= link_to "Mi perfil", profile_path %>
      <%= button_to "Cerrar sesión", session_path, method: :delete %>
    <% else %>
      <%= link_to "Iniciar sesión", new_session_path %>
    <% end %>
  </div>
</nav>

# Renderizar el partial desde cualquier vista
<%= render "shared/navbar" %>
```

### Variables locales en partials

```ruby
# app/views/courses/_card.html.erb
<div class="course-card">
  <h3><%= course.name %></h3>
  <p><%= truncate(course.description, length: 120) %></p>
  <% if show_price %>
    <span class="price"><%= number_to_currency(course.price) %></span>
  <% end %>
  <%= link_to "Ver curso", course_path(course), class: "btn" %>
</div>

# Pasar variables locales al renderizar
<%= render "courses/card", course: @featured_course, show_price: true %>

# Forma explícita con partial:
<%= render partial: "courses/card", locals: { course: @featured_course, show_price: false } %>
```

### Colecciones

Rails puede iterar automáticamente sobre una colección renderizando un partial para cada elemento:

```ruby
# Renderizar una colección (Rails infiere el partial por el modelo)
<%= render @courses %>
# Equivale a:
<%= render partial: "courses/course", collection: @courses %>

# Con variable local personalizada
<%= render partial: "courses/card", collection: @courses, as: :course %>

# Con separador entre elementos
<%= render partial: "courses/card", collection: @courses, spacer_template: "courses/separator" %>

# Con contenedor vacío
<%= render(@courses) || render("courses/empty_state") %>
```

### Partial con layout

```ruby
# Un partial puede tener su propio layout
<%= render partial: "courses/card", layout: "courses/card_wrapper", locals: { course: @course } %>

# app/views/courses/_card_wrapper.html.erb
<div class="card-wrapper shadow-lg">
  <%= yield %>
</div>
```

---

## Helpers

Los helpers son módulos con métodos disponibles en las vistas. Rails incluye muchos helpers incorporados y puedes crear los tuyos.

### Helpers incorporados útiles

```ruby
# Enlaces
<%= link_to "Inicio", root_path %>
<%= link_to "Curso", course_path(@course), class: "btn", data: { turbo_method: :delete } %>
<%= button_to "Eliminar", course_path(@course), method: :delete %>

# Formateo de números
<%= number_to_currency(29.99) %>            # $29.99
<%= number_with_delimiter(10000) %>          # 10,000
<%= number_to_percentage(85.5, precision: 1) %> # 85.5%

# Fechas y tiempo
<%= time_ago_in_words(@course.created_at) %> # "hace 3 días"
<%= l(@course.created_at, format: :long) %>   # formato localizado

# Texto
<%= truncate(@course.description, length: 100, omission: "...") %>
<%= simple_format(@course.description) %>     # convierte \n en <br>/<p>
<%= pluralize(@courses.count, "curso") %>      # "3 cursos" o "1 curso"

# Imágenes
<%= image_tag "logo.png", alt: "Logo", class: "logo" %>
```

### Helpers personalizados

```ruby
# app/helpers/courses_helper.rb
module CoursesHelper
  def difficulty_badge(level)
    colors = { beginner: "green", intermediate: "yellow", advanced: "red" }
    label = { beginner: "Principiante", intermediate: "Intermedio", advanced: "Avanzado" }

    content_tag(:span, label[level.to_sym], class: "badge bg-#{colors[level.to_sym]}")
  end

  def progress_bar(percentage)
    content_tag(:div, class: "progress") do
      content_tag(:div, "#{percentage}%",
        class: "progress-bar",
        style: "width: #{percentage}%",
        role: "progressbar",
        aria: { valuenow: percentage, valuemin: 0, valuemax: 100 }
      )
    end
  end
end
```

```ruby
# Usar en la vista
<%= difficulty_badge(@course.level) %>
<%= progress_bar(@user_progress.percentage) %>
```

---

## View Components (Componentes de Vista)

Aunque no viene incluido por defecto, el patrón de View Components se ha vuelto muy popular en Rails. Puedes usar la gema `view_component` para crear componentes encapsulados y testables.

```bash
# Instalar la gema
bundle add view_component
```

```ruby
# app/components/alert_component.rb
class AlertComponent < ViewComponent::Base
  def initialize(type:, dismissible: true)
    @type = type
    @dismissible = dismissible
  end

  def css_class
    case @type
    when :success then "alert-success"
    when :error   then "alert-danger"
    when :warning then "alert-warning"
    else "alert-info"
    end
  end
end
```

```ruby
# app/components/alert_component.html.erb
<div class="alert <%= css_class %>" role="alert"
     <% if @dismissible %>data-controller="dismissible"<% end %>>
  <%= content %>
  <% if @dismissible %>
    <button type="button" class="close" data-action="click->dismissible#dismiss">×</button>
  <% end %>
</div>
```

```ruby
# Usar en cualquier vista
<%= render(AlertComponent.new(type: :success)) do %>
  ¡Curso creado exitosamente!
<% end %>
```

---

## Turbo Frames como Placeholders

Rails 8 integra Hotwire por defecto. Los Turbo Frames permiten cargar secciones de la página de manera diferida:

```ruby
# Carga diferida: el contenido se solicita al servidor cuando el frame aparece
<%= turbo_frame_tag "course_reviews", src: course_reviews_path(@course), loading: :lazy do %>
  <p>Cargando reseñas...</p>
<% end %>

# Frame que envuelve un formulario para edición in-place
<%= turbo_frame_tag dom_id(@course) do %>
  <h2><%= @course.name %></h2>
  <%= link_to "Editar", edit_course_path(@course) %>
<% end %>
```

---

## Consejos Prácticos

1. **Mantén las vistas simples**: mueve la lógica compleja a helpers, decorators o presenters.
2. **Nombra los partials con claridad**: usa `_form.html.erb`, `_card.html.erb`, `_list_item.html.erb`.
3. **Usa `content_for` para inyectar JS/CSS específico** de una página sin cargarlo globalmente.
4. **Prefiere `render @collection`** sobre iterar manualmente: es más rápido porque Rails optimiza la instanciación del partial.
5. **Usa `turbo_frame_tag` con `loading: :lazy`** para diferir contenido pesado y mejorar el tiempo de carga inicial.
6. **Evita consultas N+1 en vistas**: asegúrate de usar `includes` o `preload` en el controlador.

```ruby
# En el controlador, no en la vista
def index
  @courses = Course.includes(:lessons, :tags).published.page(params[:page])
end
```

---

## Resumen

Las vistas en Rails 8 ofrecen un sistema completo y flexible para construir interfaces:

- **ERB** te permite mezclar Ruby con HTML de forma segura y expresiva.
- **Layouts** proporcionan estructura compartida con `yield` y `content_for`.
- **Partials** promueven la reutilización y el principio DRY.
- **Helpers** encapsulan lógica de presentación repetitiva.
- **View Components** ofrecen componentes testables y encapsulados.
- **Turbo Frames** permiten cargar secciones de forma diferida e interactiva.

Dominar estas herramientas te permitirá construir interfaces limpias, mantenibles y con excelente rendimiento.
