# GenServer en Elixir

GenServer (Generic Server) es un behaviour de OTP que abstrae el patrón cliente-servidor en procesos Elixir. Proporciona una interfaz estandarizada para gestionar estado, manejar peticiones síncronas y asíncronas, y responder a mensajes del sistema. Es el componente más utilizado en aplicaciones Elixir de producción.

## ¿Qué es un GenServer?

Un GenServer es un proceso que mantiene estado y responde a mensajes de forma estructurada. En lugar de escribir loops de `receive` manualmente, GenServer proporciona callbacks estándar:

```elixir
defmodule MiServidor do
  use GenServer

  # --- API Cliente ---

  def iniciar do
    GenServer.start_link(__MODULE__, :ok, name: __MODULE__)
  end

  # --- Callbacks del Servidor ---

  @impl true
  def init(:ok) do
    estado_inicial = %{conteo: 0}
    {:ok, estado_inicial}
  end
end
```

## init/1: Inicialización

El callback `init/1` se ejecuta cuando el proceso se inicia y establece el estado inicial:

```elixir
defmodule Cache do
  use GenServer

  def start_link(opts \\ []) do
    nombre = Keyword.get(opts, :nombre, __MODULE__)
    ttl = Keyword.get(opts, :ttl, 60_000)
    GenServer.start_link(__MODULE__, %{ttl: ttl}, name: nombre)
  end

  @impl true
  def init(config) do
    estado = %{
      datos: %{},
      ttl: config.ttl,
      creado_en: System.monotonic_time(:millisecond)
    }
    # Programar limpieza periódica
    Process.send_after(self(), :limpiar, config.ttl)
    {:ok, estado}
  end
end
```

Los retornos posibles de `init/1` son:
- `{:ok, estado}` — inicia correctamente
- `{:ok, estado, timeout}` — inicia con timeout
- `:ignore` — no inicia el proceso
- `{:stop, razon}` — falla al iniciar

## handle_call/3: Peticiones Síncronas

`handle_call` maneja peticiones donde el cliente espera una respuesta:

```elixir
defmodule Inventario do
  use GenServer

  def start_link(_), do: GenServer.start_link(__MODULE__, %{}, name: __MODULE__)

  # API Cliente
  def agregar(producto, cantidad) do
    GenServer.call(__MODULE__, {:agregar, producto, cantidad})
  end

  def consultar(producto) do
    GenServer.call(__MODULE__, {:consultar, producto})
  end

  def listar do
    GenServer.call(__MODULE__, :listar)
  end

  # Callbacks
  @impl true
  def init(_), do: {:ok, %{}}

  @impl true
  def handle_call({:agregar, producto, cantidad}, _from, estado) do
    nuevo_estado = Map.update(estado, producto, cantidad, &(&1 + cantidad))
    {:reply, {:ok, nuevo_estado[producto]}, nuevo_estado}
  end

  @impl true
  def handle_call({:consultar, producto}, _from, estado) do
    resultado = Map.get(estado, producto, 0)
    {:reply, resultado, estado}
  end

  @impl true
  def handle_call(:listar, _from, estado) do
    {:reply, estado, estado}
  end
end
```

## handle_cast/2: Peticiones Asíncronas

`handle_cast` maneja mensajes fire-and-forget donde no se espera respuesta:

```elixir
defmodule Logger do
  use GenServer

  def start_link(_), do: GenServer.start_link(__MODULE__, [], name: __MODULE__)

  # API Cliente (asíncrona)
  def log(nivel, mensaje) do
    GenServer.cast(__MODULE__, {:log, nivel, mensaje, DateTime.utc_now()})
  end

  def limpiar do
    GenServer.cast(__MODULE__, :limpiar)
  end

  # Callbacks
  @impl true
  def init(_), do: {:ok, []}

  @impl true
  def handle_cast({:log, nivel, mensaje, timestamp}, logs) do
    entrada = %{nivel: nivel, mensaje: mensaje, timestamp: timestamp}
    IO.puts("[#{nivel}] #{mensaje}")
    {:noreply, [entrada | logs]}
  end

  @impl true
  def handle_cast(:limpiar, _logs) do
    {:noreply, []}
  end
end
```

## handle_info/2: Mensajes del Sistema

`handle_info` maneja mensajes que no provienen de `call` ni `cast`, como mensajes directos, timers o notificaciones de monitores:

```elixir
defmodule Monitor do
  use GenServer

  def start_link(pid_objetivo) do
    GenServer.start_link(__MODULE__, pid_objetivo)
  end

  @impl true
  def init(pid_objetivo) do
    Process.monitor(pid_objetivo)
    schedule_check()
    {:ok, %{objetivo: pid_objetivo, checks: 0}}
  end

  @impl true
  def handle_info(:check_periodico, estado) do
    IO.puts("Check ##{estado.checks + 1} - Proceso activo")
    schedule_check()
    {:noreply, %{estado | checks: estado.checks + 1}}
  end

  @impl true
  def handle_info({:DOWN, _ref, :process, pid, razon}, estado) do
    IO.puts("Proceso #{inspect(pid)} terminó: #{inspect(razon)}")
    {:stop, :normal, estado}
  end

  defp schedule_check do
    Process.send_after(self(), :check_periodico, 5_000)
  end
end
```

## Gestión del Estado

El estado del GenServer se pasa entre callbacks y se puede transformar en cada llamada:

```elixir
defmodule Carrito do
  use GenServer

  def start_link(usuario_id) do
    GenServer.start_link(__MODULE__, usuario_id, name: via(usuario_id))
  end

  defp via(usuario_id), do: {:via, Registry, {CarritoRegistry, usuario_id}}

  # API
  def agregar_item(usuario_id, item), do: GenServer.call(via(usuario_id), {:agregar, item})
  def obtener(usuario_id), do: GenServer.call(via(usuario_id), :obtener)
  def total(usuario_id), do: GenServer.call(via(usuario_id), :total)

  # Callbacks
  @impl true
  def init(usuario_id) do
    {:ok, %{usuario_id: usuario_id, items: [], actualizado: DateTime.utc_now()}}
  end

  @impl true
  def handle_call({:agregar, item}, _from, estado) do
    nuevo_estado = %{estado |
      items: [item | estado.items],
      actualizado: DateTime.utc_now()
    }
    {:reply, {:ok, length(nuevo_estado.items)}, nuevo_estado}
  end

  @impl true
  def handle_call(:obtener, _from, estado) do
    {:reply, estado.items, estado}
  end

  @impl true
  def handle_call(:total, _from, estado) do
    total = Enum.reduce(estado.items, 0, fn item, acc -> acc + item.precio end)
    {:reply, total, estado}
  end
end
```

## Resumen

GenServer es el pilar de la programación concurrente con estado en Elixir. Proporciona una estructura clara con `init` para inicialización, `handle_call` para operaciones síncronas, `handle_cast` para operaciones asíncronas y `handle_info` para mensajes del sistema. Al separar la API del cliente de los callbacks del servidor, el código resulta organizado y testeable. GenServer gestiona automáticamente el buzón de mensajes, el orden de procesamiento y la concurrencia, permitiendo centrarse en la lógica de negocio.
