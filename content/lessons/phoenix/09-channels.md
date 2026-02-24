# Channels y Presencia en Phoenix

Los Channels de Phoenix permiten comunicación bidireccional en tiempo real entre el servidor y los clientes a través de WebSockets. Combinados con Phoenix.Presence, ofrecen una solución completa para rastrear usuarios conectados y sincronizar estado en tiempo real.

## UserSocket

El UserSocket es el punto de entrada para las conexiones WebSocket:

```elixir
defmodule MiAppWeb.UserSocket do
  use Phoenix.Socket

  # Definir los channels disponibles
  channel "sala:*", MiAppWeb.SalaChannel
  channel "notificaciones:*", MiAppWeb.NotificacionChannel

  @impl true
  def connect(%{"token" => token}, socket, _connect_info) do
    case Phoenix.Token.verify(socket, "user socket", token, max_age: 86400) do
      {:ok, user_id} ->
        {:ok, assign(socket, :user_id, user_id)}
      {:error, _reason} ->
        :error
    end
  end

  def connect(_params, _socket, _connect_info), do: :error

  @impl true
  def id(socket), do: "user_socket:#{socket.assigns.user_id}"
end

# Generar el token en el layout o controller
# token = Phoenix.Token.sign(conn, "user socket", user.id)
```

## Channel

Un channel maneja la lógica de un tema específico de comunicación en tiempo real:

```elixir
defmodule MiAppWeb.SalaChannel do
  use MiAppWeb, :channel

  alias MiApp.Chat

  @impl true
  def join("sala:" <> sala_id, _payload, socket) do
    if autorizado?(socket.assigns.user_id, sala_id) do
      mensajes = Chat.ultimos_mensajes(sala_id, 50)
      socket = assign(socket, :sala_id, sala_id)
      {:ok, %{mensajes: mensajes}, socket}
    else
      {:error, %{reason: "no autorizado"}}
    end
  end

  defp autorizado?(user_id, sala_id) do
    Chat.es_miembro?(user_id, sala_id)
  end
end
```

## join/3

El callback `join/3` se ejecuta cuando un cliente intenta unirse a un canal:

```elixir
@impl true
def join("sala:lobby", _payload, socket) do
  # Sala pública: todos pueden unirse
  send(self(), :after_join)
  {:ok, socket}
end

def join("sala:privada:" <> sala_id, %{"password" => password}, socket) do
  # Sala privada: requiere contraseña
  case Chat.verificar_sala(sala_id, password) do
    :ok ->
      send(self(), :after_join)
      {:ok, assign(socket, :sala_id, sala_id)}
    :error ->
      {:error, %{reason: "contraseña incorrecta"}}
  end
end

def join("sala:" <> _sala_id, _payload, _socket) do
  {:error, %{reason: "acceso denegado"}}
end

@impl true
def handle_info(:after_join, socket) do
  push(socket, "estado_actual", %{usuarios: Chat.usuarios_en_sala(socket.assigns.sala_id)})
  {:noreply, socket}
end
```

## handle_in/3

`handle_in/3` procesa los mensajes enviados por los clientes al canal:

```elixir
@impl true
def handle_in("nuevo_mensaje", %{"contenido" => contenido}, socket) do
  user_id = socket.assigns.user_id
  sala_id = socket.assigns.sala_id

  case Chat.crear_mensaje(%{contenido: contenido, user_id: user_id, sala_id: sala_id}) do
    {:ok, mensaje} ->
      broadcast!(socket, "mensaje_recibido", %{
        id: mensaje.id,
        contenido: mensaje.contenido,
        autor: mensaje.autor.nombre,
        timestamp: mensaje.inserted_at
      })
      {:reply, :ok, socket}

    {:error, changeset} ->
      {:reply, {:error, %{errors: errores_changeset(changeset)}}, socket}
  end
end

def handle_in("escribiendo", _payload, socket) do
  broadcast_from!(socket, "usuario_escribiendo", %{
    user_id: socket.assigns.user_id
  })
  {:noreply, socket}
end

def handle_in("ping", payload, socket) do
  {:reply, {:ok, payload}, socket}
end
```

## broadcast/3

`broadcast` envía un mensaje a todos los clientes suscritos al mismo tema:

```elixir
# broadcast!/3 envía a TODOS los clientes del canal (incluido el emisor)
broadcast!(socket, "mensaje_recibido", %{
  contenido: "Hola a todos",
  autor: "Juan"
})

# broadcast_from!/3 envía a todos EXCEPTO al emisor
broadcast_from!(socket, "usuario_escribiendo", %{
  usuario: "María"
})

# También se puede hacer broadcast desde fuera del canal
# usando el Endpoint
MiAppWeb.Endpoint.broadcast("sala:lobby", "anuncio", %{
  mensaje: "El servidor se reiniciará en 5 minutos"
})

# Broadcast desde un contexto o GenServer
defmodule MiApp.Chat do
  def notificar_nuevo_mensaje(sala_id, mensaje) do
    MiAppWeb.Endpoint.broadcast("sala:#{sala_id}", "mensaje_recibido", %{
      contenido: mensaje.contenido,
      autor: mensaje.autor.nombre
    })
  end
end
```

## intercept

`intercept` permite modificar o filtrar mensajes antes de enviarlos a clientes específicos:

```elixir
defmodule MiAppWeb.NotificacionChannel do
  use MiAppWeb, :channel

  intercept ["nueva_notificacion"]

  @impl true
  def join("notificaciones:" <> user_id, _params, socket) do
    if String.to_integer(user_id) == socket.assigns.user_id do
      {:ok, socket}
    else
      {:error, %{reason: "no autorizado"}}
    end
  end

  @impl true
  def handle_out("nueva_notificacion", payload, socket) do
    # Filtrar: solo enviar si el usuario tiene permisos
    if payload.nivel in socket.assigns.niveles_suscritos do
      push(socket, "nueva_notificacion", payload)
    end
    {:noreply, socket}
  end
end
```

## Phoenix.Presence

Phoenix.Presence permite rastrear usuarios conectados con sincronización automática entre nodos:

```elixir
defmodule MiAppWeb.Presence do
  use Phoenix.Presence,
    otp_app: :mi_app,
    pubsub_server: MiApp.PubSub
end

# En el Channel
defmodule MiAppWeb.SalaChannel do
  use MiAppWeb, :channel
  alias MiAppWeb.Presence

  def join("sala:" <> sala_id, _params, socket) do
    send(self(), :after_join)
    {:ok, assign(socket, :sala_id, sala_id)}
  end

  def handle_info(:after_join, socket) do
    # Rastrear la presencia del usuario
    {:ok, _} = Presence.track(socket, socket.assigns.user_id, %{
      nombre: socket.assigns.nombre,
      en_linea_desde: DateTime.utc_now(),
      estado: "activo"
    })

    # Enviar la lista actual de presencias al usuario que se une
    push(socket, "presence_state", Presence.list(socket))
    {:noreply, socket}
  end
end
```

## track y list

`track` registra un usuario en el sistema de presencia y `list` obtiene todos los usuarios rastreados:

```elixir
# Rastrear un usuario con metadatos
Presence.track(socket, user_id, %{
  nombre: "Carlos",
  avatar: "/img/carlos.png",
  estado: "activo"
})

# Actualizar metadatos de presencia
Presence.update(socket, user_id, %{
  nombre: "Carlos",
  avatar: "/img/carlos.png",
  estado: "ausente"
})

# Listar todas las presencias en el canal
presencias = Presence.list(socket)
# Resultado:
# %{
#   "1" => %{metas: [%{nombre: "Carlos", estado: "activo", phx_ref: "..."}]},
#   "2" => %{metas: [%{nombre: "Ana", estado: "activo", phx_ref: "..."}]}
# }

# En el cliente JavaScript se manejan los eventos de presencia
# import {Presence} from "phoenix"
# let presences = {}
# channel.on("presence_state", state => {
#   presences = Presence.syncState(presences, state)
# })
# channel.on("presence_diff", diff => {
#   presences = Presence.syncDiff(presences, diff)
# })
```

## Resumen

Los Channels de Phoenix proporcionan comunicación bidireccional en tiempo real a través de WebSockets. El `UserSocket` autentica conexiones, los channels manejan la lógica con `join/3` y `handle_in/3`, `broadcast` envía mensajes a todos los clientes e `intercept` permite filtrarlos. Phoenix.Presence añade rastreo de usuarios conectados con `track` y `list`, sincronizándose automáticamente entre nodos del clúster para ofrecer una solución completa de tiempo real.
