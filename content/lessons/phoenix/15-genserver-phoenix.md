# GenServer en Phoenix

## Introducción

GenServer es el proceso genérico de OTP para manejar estado y ejecutar lógica en background. En Phoenix, los GenServers se integran al supervision tree para crear workers resilientes y servicios con estado persistente.

## Background Workers

Un GenServer básico como worker en background:

```elixir
defmodule MyApp.Workers.CacheWarmer do
  use GenServer

  def start_link(opts) do
    GenServer.start_link(__MODULE__, opts, name: __MODULE__)
  end

  def init(_opts) do
    schedule_warm()
    {:ok, %{last_run: nil}}
  end

  def handle_info(:warm_cache, state) do
    datos = MyApp.Catalogo.list_productos_populares()
    MyApp.Cache.put("productos_populares", datos)
    schedule_warm()
    {:noreply, %{state | last_run: DateTime.utc_now()}}
  end

  defp schedule_warm do
    Process.send_after(self(), :warm_cache, :timer.minutes(5))
  end
end
```

## State Management

Gestión de estado con llamadas síncronas y asíncronas:

```elixir
defmodule MyApp.Workers.Contador do
  use GenServer

  # API pública
  def start_link(inicial), do: GenServer.start_link(__MODULE__, inicial, name: __MODULE__)
  def valor, do: GenServer.call(__MODULE__, :valor)
  def incrementar, do: GenServer.cast(__MODULE__, :incrementar)
  def reset, do: GenServer.call(__MODULE__, :reset)

  # Callbacks
  def init(valor_inicial), do: {:ok, valor_inicial}

  def handle_call(:valor, _from, state), do: {:reply, state, state}
  def handle_call(:reset, _from, _state), do: {:reply, :ok, 0}

  def handle_cast(:incrementar, state), do: {:noreply, state + 1}
end
```

## Registry

El Registry permite localizar procesos por nombre dinámico:

```elixir
defmodule MyApp.SalaRegistry do
  def child_spec(_opts) do
    Registry.child_spec(keys: :unique, name: __MODULE__)
  end

  def via(sala_id) do
    {:via, Registry, {__MODULE__, sala_id}}
  end

  def buscar(sala_id) do
    case Registry.lookup(__MODULE__, sala_id) do
      [{pid, _}] -> {:ok, pid}
      [] -> {:error, :not_found}
    end
  end
end

defmodule MyApp.Sala do
  use GenServer

  def start_link(sala_id) do
    GenServer.start_link(__MODULE__, sala_id, name: MyApp.SalaRegistry.via(sala_id))
  end

  def init(sala_id) do
    {:ok, %{id: sala_id, usuarios: [], mensajes: []}}
  end
end
```

## DynamicSupervisor

Para crear procesos bajo demanda:

```elixir
defmodule MyApp.SalaSupervisor do
  use DynamicSupervisor

  def start_link(init_arg) do
    DynamicSupervisor.start_link(__MODULE__, init_arg, name: __MODULE__)
  end

  def init(_init_arg) do
    DynamicSupervisor.init(strategy: :one_for_one)
  end

  def crear_sala(sala_id) do
    spec = {MyApp.Sala, sala_id}
    DynamicSupervisor.start_child(__MODULE__, spec)
  end

  def cerrar_sala(sala_id) do
    case MyApp.SalaRegistry.buscar(sala_id) do
      {:ok, pid} -> DynamicSupervisor.terminate_child(__MODULE__, pid)
      error -> error
    end
  end
end
```

## Agent

Agent simplifica el manejo de estado cuando no necesitamos lógica compleja:

```elixir
defmodule MyApp.ConfigStore do
  use Agent

  def start_link(_opts) do
    Agent.start_link(fn -> cargar_config() end, name: __MODULE__)
  end

  def get(clave) do
    Agent.get(__MODULE__, &Map.get(&1, clave))
  end

  def put(clave, valor) do
    Agent.update(__MODULE__, &Map.put(&1, clave, valor))
  end

  def all do
    Agent.get(__MODULE__, & &1)
  end

  defp cargar_config do
    %{
      max_upload_size: 10_000_000,
      maintenance_mode: false,
      feature_flags: %{nuevo_dashboard: true}
    }
  end
end
```

## Task.Supervisor

Para ejecutar tareas asíncronas supervisadas:

```elixir
defmodule MyApp.TaskRunner do
  def enviar_notificaciones(usuarios, mensaje) do
    Task.Supervisor.async_stream_nolink(
      MyApp.TaskSupervisor,
      usuarios,
      fn usuario ->
        MyApp.Notificaciones.enviar(usuario, mensaje)
      end,
      max_concurrency: 10,
      timeout: 30_000
    )
    |> Enum.reduce({0, 0}, fn
      {:ok, {:ok, _}}, {ok, err} -> {ok + 1, err}
      _, {ok, err} -> {ok, err + 1}
    end)
  end

  def procesar_en_background(fun) do
    Task.Supervisor.start_child(MyApp.TaskSupervisor, fun)
  end
end
```

## Integración con el Supervision Tree

Todos los procesos se registran en el árbol de supervisión de la aplicación:

```elixir
defmodule MyApp.Application do
  use Application

  def start(_type, _args) do
    children = [
      MyAppWeb.Telemetry,
      MyApp.Repo,
      {Registry, keys: :unique, name: MyApp.SalaRegistry},
      {Task.Supervisor, name: MyApp.TaskSupervisor},
      MyApp.SalaSupervisor,
      MyApp.Workers.CacheWarmer,
      MyApp.ConfigStore,
      {Phoenix.PubSub, name: MyApp.PubSub},
      MyAppWeb.Endpoint
    ]

    opts = [strategy: :one_for_one, name: MyApp.Supervisor]
    Supervisor.start_link(children, opts)
  end
end
```

El orden importa: los procesos se inician secuencialmente de arriba a abajo.

## Resumen

GenServer en Phoenix permite crear workers con estado, procesos en background y servicios resilientes. Registry y DynamicSupervisor gestionan procesos dinámicos, Agent simplifica el estado simple, y Task.Supervisor ejecuta tareas asíncronas. Todo se integra en el supervision tree de OTP para máxima fiabilidad.
