# LiveView Avanzado

Una vez dominados los fundamentos de LiveView, es momento de explorar las características avanzadas: componentes con estado propio, streams para listas eficientes, hooks de JavaScript para interoperabilidad con el DOM, y assigns asíncronos para operaciones que toman tiempo.

## live_component

Los `live_component` son módulos que encapsulan estado y lógica propios dentro de un LiveView padre:

```elixir
defmodule MiAppWeb.FormularioComentarioComponent do
  use MiAppWeb, :live_component

  def mount(socket) do
    {:ok, assign(socket, form: to_form(%{"contenido" => ""}))}
  end

  def render(assigns) do
    ~H"""
    <div class="comentario-form">
      <.form for={@form} phx-submit="guardar" phx-target={@myself}>
        <.input field={@form[:contenido]} type="textarea" label="Tu comentario" />
        <.button>Publicar</.button>
      </.form>
    </div>
    """
  end

  def handle_event("guardar", %{"contenido" => contenido}, socket) do
    # Notificar al padre
    send(self(), {:comentario_creado, contenido, socket.assigns.articulo_id})
    {:noreply, assign(socket, form: to_form(%{"contenido" => ""}))}
  end
end

# Uso en el LiveView padre
<.live_component module={MiAppWeb.FormularioComentarioComponent}
  id="form-comentario" articulo_id={@articulo.id} />
```

## Stateful vs Stateless Components

Es importante distinguir entre componentes con estado (stateful) y sin estado (stateless):

```elixir
# STATELESS: Function component - no tiene estado propio
# Se re-renderiza cuando cambian los assigns del padre
defmodule MiAppWeb.CoreComponents do
  use Phoenix.Component

  def tarjeta_producto(assigns) do
    ~H"""
    <div class="tarjeta">
      <h3><%= @producto.nombre %></h3>
      <p>$<%= @producto.precio %></p>
    </div>
    """
  end
end

# STATEFUL: Live component - tiene su propio estado y ciclo de vida
# Maneja sus propios eventos con phx-target={@myself}
defmodule MiAppWeb.CarritoComponent do
  use MiAppWeb, :live_component

  def update(assigns, socket) do
    {:ok,
     socket
     |> assign(assigns)
     |> assign(:total, calcular_total(assigns.items))}
  end

  def render(assigns) do
    ~H"""
    <div class="carrito">
      <h2>Carrito (<%= length(@items) %>)</h2>
      <p>Total: $<%= @total %></p>
      <button phx-click="vaciar" phx-target={@myself}>Vaciar</button>
    </div>
    """
  end

  def handle_event("vaciar", _params, socket) do
    send(self(), :carrito_vaciado)
    {:noreply, assign(socket, items: [], total: 0)}
  end
end
```

## Streams

Los streams permiten manejar listas grandes de forma eficiente sin mantener todos los elementos en memoria:

```elixir
defmodule MiAppWeb.MensajesLive do
  use MiAppWeb, :live_view

  def mount(_params, _session, socket) do
    mensajes = Chat.listar_mensajes()
    {:ok, stream(socket, :mensajes, mensajes)}
  end

  def render(assigns) do
    ~H"""
    <div id="mensajes" phx-update="stream">
      <div :for={{dom_id, mensaje} <- @streams.mensajes} id={dom_id}
        class="mensaje">
        <strong><%= mensaje.autor %></strong>
        <p><%= mensaje.contenido %></p>
      </div>
    </div>
    """
  end

  def handle_info({:nuevo_mensaje, mensaje}, socket) do
    # Insertar al inicio del stream
    {:noreply, stream_insert(socket, :mensajes, mensaje, at: 0)}
  end

  def handle_event("eliminar", %{"id" => id}, socket) do
    mensaje = Chat.obtener_mensaje!(id)
    Chat.eliminar_mensaje(mensaje)
    # Eliminar del stream sin recargar todo
    {:noreply, stream_delete(socket, :mensajes, mensaje)}
  end
end
```

## JS Hooks

Los hooks de JavaScript permiten ejecutar código JS personalizado cuando elementos del DOM se montan o actualizan:

```elixir
# En app.js
let Hooks = {}

Hooks.InfiniteScroll = {
  mounted() {
    this.observer = new IntersectionObserver(entries => {
      const entry = entries[0]
      if (entry.isIntersecting) {
        this.pushEvent("cargar-mas", {})
      }
    })
    this.observer.observe(this.el)
  },
  destroyed() {
    this.observer.disconnect()
  }
}

Hooks.CopiarPortapapeles = {
  mounted() {
    this.el.addEventListener("click", () => {
      const texto = this.el.dataset.texto
      navigator.clipboard.writeText(texto)
      this.pushEvent("copiado", {texto: texto})
    })
  }
}

// En la configuración del LiveSocket
let liveSocket = new LiveSocket("/live", Socket, {
  hooks: Hooks,
  params: {_csrf_token: csrfToken}
})
```

```elixir
# En la plantilla HEEx
<div id="scroll-trigger" phx-hook="InfiniteScroll"></div>

<button phx-hook="CopiarPortapapeles" id="btn-copiar"
  data-texto={@codigo}>
  Copiar código
</button>
```

## JS.push

`JS.push` permite enviar eventos al servidor con transformaciones JavaScript del lado del cliente:

```elixir
alias Phoenix.LiveView.JS

def render(assigns) do
  ~H"""
  <div>
    <button phx-click={JS.push("toggle", value: %{id: @item.id})
      |> JS.toggle(to: "#detalle-#{@item.id}")}>
      Ver detalles
    </button>

    <div id={"detalle-#{@item.id}"} style="display:none">
      <%= @item.descripcion %>
    </div>

    <button phx-click={JS.push("eliminar", value: %{id: @item.id})
      |> JS.hide(to: "#item-#{@item.id}", transition: "fade-out")}>
      Eliminar
    </button>

    <!-- Transiciones CSS con JS -->
    <button phx-click={JS.toggle(to: "#menu",
      in: {"ease-out duration-200", "opacity-0", "opacity-100"},
      out: {"ease-in duration-150", "opacity-100", "opacity-0"})}>
      Menú
    </button>
  </div>
  """
end
```

## Uploads en Tiempo Real

LiveView permite mostrar progreso de subida de archivos y previsualizaciones en tiempo real:

```elixir
def mount(_params, _session, socket) do
  {:ok,
   socket
   |> assign(:imagenes_subidas, [])
   |> allow_upload(:fotos,
     accept: ~w(.jpg .png .webp),
     max_entries: 5,
     max_file_size: 10_000_000,
     progress: &handle_progress/3,
     auto_upload: true
   )}
end

defp handle_progress(:fotos, entry, socket) do
  if entry.done? do
    {:noreply,
     socket
     |> put_flash(:info, "#{entry.client_name} subido correctamente")}
  else
    {:noreply, socket}
  end
end

def render(assigns) do
  ~H"""
  <form phx-change="validate" phx-submit="save">
    <.live_file_input upload={@uploads.fotos} />
    <%= for entry <- @uploads.fotos.entries do %>
      <div>
        <.live_img_preview entry={entry} width="150" />
        <progress value={entry.progress} max="100"><%= entry.progress %>%</progress>
      </div>
    <% end %>
  </form>
  """
end
```

## Async Assigns

Los assigns asíncronos permiten cargar datos sin bloquear el renderizado inicial:

```elixir
defmodule MiAppWeb.DashboardLive do
  use MiAppWeb, :live_view

  def mount(_params, _session, socket) do
    {:ok,
     socket
     |> assign(:page_title, "Dashboard")
     |> assign_async(:estadisticas, fn ->
       {:ok, %{estadisticas: Reportes.calcular_estadisticas()}}
     end)
     |> assign_async(:ultimos_pedidos, fn ->
       {:ok, %{ultimos_pedidos: Pedidos.listar_recientes()}}
     end)}
  end

  def render(assigns) do
    ~H"""
    <h1>Dashboard</h1>
    <.async_result :let={stats} assign={@estadisticas}>
      <:loading>Cargando estadísticas...</:loading>
      <:failed :let={_reason}>Error al cargar estadísticas</:failed>
      <div class="stats">
        <p>Ventas: <%= stats.ventas_totales %></p>
        <p>Usuarios: <%= stats.usuarios_activos %></p>
      </div>
    </.async_result>
    """
  end
end
```

## Resumen

LiveView avanzado ofrece herramientas poderosas para aplicaciones complejas. Los `live_component` encapsulan estado y lógica, los streams manejan listas grandes eficientemente, los JS hooks permiten interoperabilidad con JavaScript nativo, `JS.push` combina acciones del cliente y servidor, los uploads en tiempo real muestran progreso y previsualizaciones, y los async assigns cargan datos en segundo plano sin bloquear la interfaz del usuario.
