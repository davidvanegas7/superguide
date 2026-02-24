# Vistas y Templates en Phoenix

Phoenix utiliza HEEx (HTML + Embedded Elixir) como su motor de plantillas. Este sistema permite crear interfaces de usuario dinámicas con componentes reutilizables, layouts y una sintaxis expresiva que combina HTML con Elixir de forma segura.

## Templates HEEx

HEEx es el formato de plantillas por defecto en Phoenix. Ofrece validación de HTML en tiempo de compilación y prevención automática de inyección XSS:

```elixir
# lib/mi_app_web/controllers/producto_html/index.html.heex
<h1>Lista de Productos</h1>

<ul>
  <%= for producto <- @productos do %>
    <li>
      <strong><%= producto.nombre %></strong>
      <span>Precio: $<%= producto.precio %></span>
      <a href={~p"/productos/#{producto}"}>Ver detalle</a>
    </li>
  <% end %>
</ul>

<%= if @productos == [] do %>
  <p>No hay productos disponibles.</p>
<% end %>
```

## Assigns

Los assigns son variables que se pasan desde el controller a las plantillas mediante el símbolo `@`:

```elixir
# En el controller
def index(conn, _params) do
  render(conn, :index,
    productos: Catalogo.listar_productos(),
    titulo: "Catálogo",
    total: Catalogo.contar_productos()
  )
end

# En la plantilla se acceden con @
<h1><%= @titulo %></h1>
<p>Total de productos: <%= @total %></p>

<%= for producto <- @productos do %>
  <div class="producto">
    <h2><%= producto.nombre %></h2>
  </div>
<% end %>
```

## Layouts

Los layouts envuelven las plantillas individuales proporcionando una estructura HTML común:

```elixir
# lib/mi_app_web/components/layouts/root.html.heex
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><%= assigns[:page_title] || "MiApp" %></title>
    <link rel="stylesheet" href={~p"/assets/app.css"} />
    <script defer src={~p"/assets/app.js"}></script>
  </head>
  <body>
    <%= @inner_content %>
  </body>
</html>

# lib/mi_app_web/components/layouts/app.html.heex
<header>
  <nav>
    <a href={~p"/"}>Inicio</a>
    <a href={~p"/productos"}>Productos</a>
  </nav>
</header>

<main>
  <.flash_group flash={@flash} />
  <%= @inner_content %>
</main>

<footer>
  <p>&copy; 2026 MiApp</p>
</footer>
```

## Function Components

Los function components son la forma principal de crear componentes reutilizables en Phoenix:

```elixir
defmodule MiAppWeb.CoreComponents do
  use Phoenix.Component

  # Componente de botón reutilizable
  def boton(assigns) do
    ~H"""
    <button class={"btn btn-#{@tipo}"} phx-click={@accion}>
      <%= render_slot(@inner_block) %>
    </button>
    """
  end

  # Componente de tarjeta
  def tarjeta(assigns) do
    ~H"""
    <div class="tarjeta">
      <h3 class="tarjeta-titulo"><%= @titulo %></h3>
      <div class="tarjeta-contenido">
        <%= render_slot(@inner_block) %>
      </div>
    </div>
    """
  end
end
```

## Slots y Atributos

Los slots permiten pasar contenido personalizado a los componentes, y `attr` define los atributos tipados:

```elixir
defmodule MiAppWeb.CoreComponents do
  use Phoenix.Component

  attr :titulo, :string, required: true
  attr :class, :string, default: ""
  slot :acciones
  slot :inner_block, required: true

  def panel(assigns) do
    ~H"""
    <div class={"panel #{@class}"}>
      <div class="panel-header">
        <h2><%= @titulo %></h2>
        <div class="panel-acciones">
          <%= render_slot(@acciones) %>
        </div>
      </div>
      <div class="panel-body">
        <%= render_slot(@inner_block) %>
      </div>
    </div>
    """
  end
end

# Uso del componente con slots
<.panel titulo="Usuarios">
  <:acciones>
    <button>Agregar</button>
  </:acciones>
  <p>Contenido del panel aquí.</p>
</.panel>
```

## embed_templates

La macro `embed_templates` permite cargar plantillas HEEx desde archivos externos:

```elixir
defmodule MiAppWeb.ProductoHTML do
  use MiAppWeb, :html

  # Carga todas las plantillas .heex del directorio producto_html/
  embed_templates "producto_html/*"

  # Esto hace disponibles las funciones:
  # index(assigns) -> desde producto_html/index.html.heex
  # show(assigns)  -> desde producto_html/show.html.heex
  # new(assigns)   -> desde producto_html/new.html.heex
end
```

## El Sigil ~H

El sigil `~H` permite escribir plantillas HEEx directamente en código Elixir con todas las validaciones en tiempo de compilación:

```elixir
defmodule MiAppWeb.Components do
  use Phoenix.Component

  def badge(assigns) do
    ~H"""
    <span class={"badge badge-#{@color}"}>
      <%= @texto %>
    </span>
    """
  end

  def lista_items(assigns) do
    ~H"""
    <ul class="lista">
      <li :for={item <- @items} class="lista-item">
        <.badge color="blue" texto={item.estado} />
        <span><%= item.nombre %></span>
      </li>
    </ul>
    """
  end
end
```

## Resumen

Phoenix utiliza HEEx como motor de plantillas con validación en compilación y protección XSS automática. Los assigns (`@variable`) conectan los datos del controller con las vistas. Los layouts proporcionan estructura HTML común, mientras que los function components con `attr` y `slot` permiten construir interfaces modulares y reutilizables. La macro `embed_templates` carga plantillas externas y el sigil `~H` permite escribir HEEx inline en el código Elixir.
