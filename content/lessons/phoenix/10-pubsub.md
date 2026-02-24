# PubSub y Tiempo Real en Phoenix

Phoenix.PubSub es el sistema de mensajería publish/subscribe que permite la comunicación entre procesos en tiempo real. Es la base sobre la que funcionan los Channels y LiveView, y puede usarse directamente para crear funcionalidades reactivas y distribuidas entre nodos de un clúster.

## Phoenix.PubSub

PubSub viene configurado automáticamente en cada proyecto Phoenix y permite suscribirse a temas y enviar mensajes:

```elixir
# Configuración en application.ex (ya incluida por defecto)
defmodule MiApp.Application do
  use Application

  def start(_type, _args) do
    children = [
      {Phoenix.PubSub, name: MiApp.PubSub},
      MiAppWeb.Endpoint
    ]

    Supervisor.start_link(children, strategy: :one_for_one)
  end
end

# PubSub usa un adaptador configurable
# Por defecto: Phoenix.PubSub.PG2 (distribuido con Erlang PG)
# config/config.exs
config :mi_app, MiApp.PubSub,
  adapter: Phoenix.PubSub.PG2
```

## subscribe y broadcast

Las dos operaciones fundamentales de PubSub son suscribirse a un tema y publicar mensajes:

```elixir
# Suscribirse a un tema
Phoenix.PubSub.subscribe(MiApp.PubSub, "pedidos")

# Publicar un mensaje en un tema
Phoenix.PubSub.broadcast(MiApp.PubSub, "pedidos", {:nuevo_pedido, pedido})

# broadcast! lanza error si falla
Phoenix.PubSub.broadcast!(MiApp.PubSub, "pedidos", {:pedido_actualizado, pedido})

# broadcast_from excluye al proceso emisor
Phoenix.PubSub.broadcast_from(MiApp.PubSub, self(), "pedidos", {:nuevo_pedido, pedido})

# Ejemplo práctico en un contexto
defmodule MiApp.Pedidos do
  alias MiApp.Repo
  alias MiApp.Pedidos.Pedido

  def crear_pedido(attrs) do
    case %Pedido{} |> Pedido.changeset(attrs) |> Repo.insert() do
      {:ok, pedido} ->
        Phoenix.PubSub.broadcast(MiApp.PubSub, "pedidos", {:nuevo_pedido, pedido})
        Phoenix.PubSub.broadcast(MiApp.PubSub, "pedidos:#{pedido.usuario_id}",
          {:mi_pedido, pedido})
        {:ok, pedido}

      error ->
        error
    end
  end

  def actualizar_estado(pedido, nuevo_estado) do
    case pedido |> Pedido.changeset(%{estado: nuevo_estado}) |> Repo.update() do
      {:ok, pedido} ->
        Phoenix.PubSub.broadcast(MiApp.PubSub, "pedidos:#{pedido.id}",
          {:estado_cambiado, pedido})
        {:ok, pedido}

      error ->
        error
    end
  end
end
```

## Topics (Temas)

Los topics son cadenas de texto que organizan los mensajes por categoría o recurso:

```elixir
# Temas genéricos para eventos globales
Phoenix.PubSub.subscribe(MiApp.PubSub, "sistema:alertas")
Phoenix.PubSub.subscribe(MiApp.PubSub, "metricas:ventas")

# Temas específicos por recurso
Phoenix.PubSub.subscribe(MiApp.PubSub, "producto:#{producto_id}")
Phoenix.PubSub.subscribe(MiApp.PubSub, "usuario:#{usuario_id}:notificaciones")

# Patrón común: módulo helper para topics
defmodule MiApp.Topics do
  def pedido(pedido_id), do: "pedido:#{pedido_id}"
  def usuario_pedidos(usuario_id), do: "usuario:#{usuario_id}:pedidos"
  def sala_chat(sala_id), do: "chat:sala:#{sala_id}"
  def sistema_alertas, do: "sistema:alertas"
end

# Uso
alias MiApp.Topics
Phoenix.PubSub.subscribe(MiApp.PubSub, Topics.pedido(42))
Phoenix.PubSub.broadcast(MiApp.PubSub, Topics.pedido(42), {:actualizado, pedido})
```

## Integración con LiveView

PubSub se integra de forma natural con LiveView para crear interfaces reactivas en tiempo real:

```elixir
defmodule MiAppWeb.PedidosLive do
  use MiAppWeb, :live_view

  def mount(_params, _session, socket) do
    if connected?(socket) do
      # Suscribirse cuando el WebSocket está conectado
      Phoenix.PubSub.subscribe(MiApp.PubSub, "pedidos")
    end

    pedidos = MiApp.Pedidos.listar_recientes()
    {:ok, stream(socket, :pedidos, pedidos)}
  end

  def render(assigns) do
    ~H"""
    <h1>Pedidos en Tiempo Real</h1>
    <div id="pedidos" phx-update="stream">
      <div :for={{dom_id, pedido} <- @streams.pedidos} id={dom_id}
        class={"pedido estado-#{pedido.estado}"}>
        <span>Pedido #<%= pedido.id %></span>
        <span><%= pedido.cliente %></span>
        <span class="estado"><%= pedido.estado %></span>
        <span>$<%= pedido.total %></span>
      </div>
    </div>
    """
  end

  # handle_info recibe los mensajes de PubSub
  def handle_info({:nuevo_pedido, pedido}, socket) do
    {:noreply,
     socket
     |> stream_insert(:pedidos, pedido, at: 0)
     |> put_flash(:info, "Nuevo pedido ##{pedido.id}")}
  end

  def handle_info({:estado_cambiado, pedido}, socket) do
    {:noreply, stream_insert(socket, :pedidos, pedido)}
  end
end

# LiveView para seguimiento individual de un pedido
defmodule MiAppWeb.PedidoDetalleLive do
  use MiAppWeb, :live_view

  def mount(%{"id" => id}, _session, socket) do
    pedido = MiApp.Pedidos.obtener!(id)

    if connected?(socket) do
      Phoenix.PubSub.subscribe(MiApp.PubSub, "pedidos:#{id}")
    end

    {:ok, assign(socket, pedido: pedido)}
  end

  def handle_info({:estado_cambiado, pedido}, socket) do
    {:noreply, assign(socket, pedido: pedido)}
  end
end
```

## PubSub Distribuido con Clusters

Una de las características más poderosas de Phoenix.PubSub es su capacidad de funcionar de forma distribuida entre nodos de un clúster Erlang:

```elixir
# PubSub usa PG2 por defecto, que funciona automáticamente en clusters
# Un mensaje publicado en un nodo llega a todos los suscriptores en todos los nodos

# config/runtime.exs - Configuración para cluster
config :mi_app, MiApp.PubSub,
  adapter: Phoenix.PubSub.PG2

# Conectar nodos manualmente
# En nodo 1: iex --sname nodo1 -S mix phx.server
# En nodo 2: iex --sname nodo2 -S mix phx.server
# En iex: Node.connect(:"nodo1@hostname")

# Configurar con libcluster para descubrimiento automático
# mix.exs
defp deps do
  [{:libcluster, "~> 3.3"}]
end

# config/config.exs
config :libcluster,
  topologies: [
    mi_app: [
      strategy: Cluster.Strategy.Gossip
    ]
  ]

# application.ex
children = [
  {Cluster.Supervisor, [Application.get_env(:libcluster, :topologies)]},
  {Phoenix.PubSub, name: MiApp.PubSub},
  MiAppWeb.Endpoint
]

# Con esta configuración, PubSub funciona entre nodos automáticamente
# Un broadcast en nodo1 llega a suscriptores en nodo1, nodo2, nodo3...
Phoenix.PubSub.broadcast(MiApp.PubSub, "eventos_globales", {:alerta, mensaje})
# Todos los LiveViews suscritos en cualquier nodo recibirán este mensaje
```

## Resumen

Phoenix.PubSub es el sistema de mensajería que habilita la comunicación en tiempo real en Phoenix. Con `subscribe` y `broadcast`, los procesos pueden intercambiar mensajes organizados por topics. Su integración con LiveView permite crear interfaces reactivas donde los cambios en los datos se reflejan automáticamente en todos los clientes conectados. Gracias al adaptador PG2 y herramientas como libcluster, PubSub funciona de forma distribuida entre nodos de un clúster sin cambios en el código.
