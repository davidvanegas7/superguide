# LiveView Fundamentos

Phoenix LiveView permite construir experiencias interactivas en tiempo real sin escribir JavaScript. Utiliza WebSockets para mantener una conexión persistente entre el servidor y el navegador, enviando solo las diferencias del HTML cuando el estado cambia.

## ¿Qué es LiveView vs SPA?

LiveView ofrece una alternativa al enfoque tradicional de Single Page Applications (SPA):

```elixir
# SPA tradicional:
# - Frontend en React/Vue/Angular envía peticiones a una API
# - Estado duplicado en cliente y servidor
# - Complejidad de sincronización y bundling JS

# LiveView:
# - El estado vive solo en el servidor
# - El HTML se renderiza en el servidor y se envía por WebSocket
# - Solo se envían los cambios (diffs) al navegador
# - Mínimo JavaScript necesario
# - Latencia baja gracias a la conexión persistente

defmodule MiAppWeb.ContadorLive do
  use MiAppWeb, :live_view

  def mount(_params, _session, socket) do
    {:ok, assign(socket, cuenta: 0)}
  end

  def render(assigns) do
    ~H"""
    <h1>Contador: <%= @cuenta %></h1>
    <button phx-click="incrementar">+1</button>
    <button phx-click="decrementar">-1</button>
    """
  end

  def handle_event("incrementar", _params, socket) do
    {:noreply, update(socket, :cuenta, &(&1 + 1))}
  end

  def handle_event("decrementar", _params, socket) do
    {:noreply, update(socket, :cuenta, &(&1 - 1))}
  end
end
```

## mount/3

`mount/3` es el callback que inicializa el LiveView. Se llama dos veces: primero en la solicitud HTTP estática y luego cuando se establece el WebSocket:

```elixir
defmodule MiAppWeb.DashboardLive do
  use MiAppWeb, :live_view

  def mount(params, session, socket) do
    # params: parámetros de la URL
    # session: datos de la sesión del usuario
    # socket: el estado del LiveView

    usuario_id = session["user_id"]

    # Suscribirse a actualizaciones solo en la conexión WebSocket
    if connected?(socket) do
      Phoenix.PubSub.subscribe(MiApp.PubSub, "dashboard:#{usuario_id}")
      Process.send_after(self(), :actualizar_stats, 5000)
    end

    {:ok,
     socket
     |> assign(:usuario_id, usuario_id)
     |> assign(:estadisticas, cargar_stats(usuario_id))
     |> assign(:page_title, "Dashboard")}
  end
end
```

## render/1

`render/1` genera el HTML del LiveView. Se llama automáticamente cada vez que los assigns cambian:

```elixir
defmodule MiAppWeb.TareasLive do
  use MiAppWeb, :live_view

  def render(assigns) do
    ~H"""
    <div class="tareas-app">
      <h1><%= @page_title %></h1>

      <form phx-submit="agregar">
        <input type="text" name="titulo" placeholder="Nueva tarea..."
          value="" autocomplete="off" />
        <button type="submit">Agregar</button>
      </form>

      <ul class="lista-tareas">
        <li :for={tarea <- @tareas} class={tarea.completada && "completada"}>
          <input type="checkbox" phx-click="toggle"
            phx-value-id={tarea.id} checked={tarea.completada} />
          <span><%= tarea.titulo %></span>
          <button phx-click="eliminar" phx-value-id={tarea.id}>×</button>
        </li>
      </ul>

      <p>Total: <%= length(@tareas) %> | Pendientes: <%= @pendientes %></p>
    </div>
    """
  end
end
```

## handle_event/3

`handle_event/3` procesa los eventos del usuario enviados desde el navegador al servidor:

```elixir
def handle_event("agregar", %{"titulo" => titulo}, socket) do
  nueva_tarea = %{id: System.unique_integer(), titulo: titulo, completada: false}
  tareas = [nueva_tarea | socket.assigns.tareas]
  pendientes = Enum.count(tareas, &(!&1.completada))

  {:noreply,
   socket
   |> assign(:tareas, tareas)
   |> assign(:pendientes, pendientes)}
end

def handle_event("toggle", %{"id" => id}, socket) do
  id = String.to_integer(id)
  tareas = Enum.map(socket.assigns.tareas, fn
    %{id: ^id} = t -> %{t | completada: !t.completada}
    t -> t
  end)

  {:noreply,
   socket
   |> assign(:tareas, tareas)
   |> assign(:pendientes, Enum.count(tareas, &(!&1.completada)))}
end

def handle_event("eliminar", %{"id" => id}, socket) do
  id = String.to_integer(id)
  tareas = Enum.reject(socket.assigns.tareas, &(&1.id == id))
  {:noreply, assign(socket, tareas: tareas, pendientes: Enum.count(tareas, &(!&1.completada)))}
end
```

## handle_info/2

`handle_info/2` procesa mensajes internos del servidor, como mensajes de PubSub o timers:

```elixir
def handle_info(:actualizar_stats, socket) do
  stats = cargar_stats(socket.assigns.usuario_id)
  # Programar la próxima actualización
  Process.send_after(self(), :actualizar_stats, 5000)
  {:noreply, assign(socket, estadisticas: stats)}
end

def handle_info({:nueva_notificacion, notificacion}, socket) do
  notificaciones = [notificacion | socket.assigns.notificaciones]
  {:noreply,
   socket
   |> assign(:notificaciones, notificaciones)
   |> put_flash(:info, "Nueva notificación: #{notificacion.mensaje}")}
end

def handle_info({:pedido_actualizado, pedido}, socket) do
  pedidos = Enum.map(socket.assigns.pedidos, fn
    p when p.id == pedido.id -> pedido
    p -> p
  end)
  {:noreply, assign(socket, pedidos: pedidos)}
end
```

## Lifecycle Hooks

LiveView ofrece varios hooks del ciclo de vida para controlar el comportamiento en diferentes momentos:

```elixir
defmodule MiAppWeb.ArticuloLive do
  use MiAppWeb, :live_view

  # Se llama cuando se actualizan los parámetros de la URL
  def handle_params(%{"id" => id}, _uri, socket) do
    articulo = Blog.obtener_articulo!(id)
    {:noreply,
     socket
     |> assign(:articulo, articulo)
     |> assign(:page_title, articulo.titulo)}
  end

  # on_mount se usa para ejecutar lógica antes del mount
  on_mount {MiAppWeb.UserAuth, :ensure_authenticated}

  # Se llama cuando el LiveView se termina
  def terminate(_reason, _socket) do
    # Limpieza de recursos si es necesario
    :ok
  end
end
```

## Assigns y Socket

El socket mantiene todo el estado del LiveView a través de los assigns:

```elixir
def mount(_params, _session, socket) do
  {:ok,
   socket
   |> assign(:contador, 0)                    # Asignar un valor
   |> assign(titulo: "Mi App", tema: "claro") # Asignar múltiples valores
   |> assign_new(:usuario, fn -> cargar_usuario() end)} # Solo asigna si no existe
end

def handle_event("cambiar_tema", _params, socket) do
  # Leer un assign
  tema_actual = socket.assigns.tema
  nuevo_tema = if tema_actual == "claro", do: "oscuro", else: "claro"

  # update/3 transforma un assign existente
  {:noreply, update(socket, :tema, fn _ -> nuevo_tema end)}
end
```

## Resumen

Phoenix LiveView permite construir interfaces interactivas en tiempo real con estado en el servidor. `mount/3` inicializa el estado, `render/1` genera el HTML reactivo, `handle_event/3` procesa eventos del usuario y `handle_info/2` maneja mensajes internos del servidor. Los assigns en el socket mantienen el estado y desencadenan re-renderizados automáticos cuando cambian, enviando solo las diferencias al navegador por WebSocket.
