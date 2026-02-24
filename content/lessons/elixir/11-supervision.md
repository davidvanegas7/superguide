# Supervisión y OTP en Elixir

OTP (Open Telecom Platform) es un conjunto de bibliotecas y patrones de diseño que forman el núcleo de las aplicaciones Elixir robustas. El concepto central de OTP es la supervisión: en lugar de intentar prevenir todos los errores posibles, el sistema se diseña para detectarlos y recuperarse automáticamente. Esta filosofía se conoce como "let it crash".

## Árboles de Supervisión

Un árbol de supervisión es una jerarquía donde los supervisores vigilan a sus procesos hijos y los reinician si fallan:

```elixir
# Un supervisor básico
defmodule MiApp.Application do
  use Application

  @impl true
  def start(_type, _args) do
    children = [
      {MiApp.Cache, []},
      {MiApp.Inventario, []},
      {MiApp.Logger, nombre: :logger_principal}
    ]

    opts = [strategy: :one_for_one, name: MiApp.Supervisor]
    Supervisor.start_link(children, opts)
  end
end
```

## Estrategias de Supervisión

Las estrategias determinan qué sucede cuando un proceso hijo falla:

```elixir
# :one_for_one — Solo reinicia el proceso que falló
children = [
  {ServidorA, []},
  {ServidorB, []},
  {ServidorC, []}
]
Supervisor.start_link(children, strategy: :one_for_one)

# :one_for_all — Reinicia TODOS los hijos si uno falla
# Útil cuando los procesos dependen unos de otros
Supervisor.start_link(children, strategy: :one_for_all)

# :rest_for_one — Reinicia el que falló y todos los que le siguen
# Útil cuando hay dependencias secuenciales
Supervisor.start_link(children, strategy: :rest_for_one)
```

## Definir un Supervisor

Puedes crear supervisores personalizados con el behaviour `Supervisor`:

```elixir
defmodule MiApp.WorkerSupervisor do
  use Supervisor

  def start_link(opts) do
    Supervisor.start_link(__MODULE__, opts, name: __MODULE__)
  end

  @impl true
  def init(_opts) do
    children = [
      {MiApp.BaseDatos, pool_size: 10},
      {MiApp.Cache, ttl: 60_000},
      {MiApp.Notificador, []}
    ]

    Supervisor.init(children, strategy: :rest_for_one)
  end
end
```

## child_spec: Especificación de Hijos

Cada proceso hijo necesita una especificación que indica al supervisor cómo iniciarlo:

```elixir
defmodule MiApp.Worker do
  use GenServer

  # child_spec personalizado
  def child_spec(opts) do
    %{
      id: __MODULE__,
      start: {__MODULE__, :start_link, [opts]},
      restart: :permanent,    # :permanent | :temporary | :transient
      shutdown: 5000,         # Tiempo para terminar gracefully
      type: :worker           # :worker | :supervisor
    }
  end

  def start_link(opts) do
    GenServer.start_link(__MODULE__, opts, name: __MODULE__)
  end

  @impl true
  def init(opts), do: {:ok, opts}
end
```

Opciones de `restart`:
- `:permanent` — siempre se reinicia (por defecto)
- `:temporary` — nunca se reinicia
- `:transient` — solo se reinicia si termina anormalmente

## DynamicSupervisor

`DynamicSupervisor` permite agregar y eliminar hijos en tiempo de ejecución:

```elixir
defmodule MiApp.SessionSupervisor do
  use DynamicSupervisor

  def start_link(_opts) do
    DynamicSupervisor.start_link(__MODULE__, :ok, name: __MODULE__)
  end

  @impl true
  def init(:ok) do
    DynamicSupervisor.init(strategy: :one_for_one)
  end

  def crear_sesion(usuario_id) do
    spec = {MiApp.Session, usuario_id}
    DynamicSupervisor.start_child(__MODULE__, spec)
  end

  def terminar_sesion(pid) do
    DynamicSupervisor.terminate_child(__MODULE__, pid)
  end

  def sesiones_activas do
    DynamicSupervisor.count_children(__MODULE__)
  end
end

# Uso
{:ok, pid} = MiApp.SessionSupervisor.crear_sesion("user_123")
MiApp.SessionSupervisor.sesiones_activas()
# => %{active: 1, specs: 1, supervisors: 0, workers: 1}
```

## Application

Una Application es el punto de entrada de una aplicación OTP. Define qué procesos se inician automáticamente:

```elixir
defmodule MiApp.Application do
  use Application

  @impl true
  def start(_type, _args) do
    children = [
      # Servicios base
      MiApp.Repo,
      {Registry, keys: :unique, name: MiApp.Registry},

      # Supervisores de dominio
      MiApp.WorkerSupervisor,
      MiApp.SessionSupervisor,

      # Servidor web
      {Plug.Cowboy, scheme: :http, plug: MiApp.Router, options: [port: 4000]}
    ]

    opts = [strategy: :one_for_one, name: MiApp.Supervisor]
    Supervisor.start_link(children, opts)
  end

  @impl true
  def stop(_state) do
    IO.puts("Aplicación detenida")
  end
end
```

En `mix.exs` se registra la aplicación:

```elixir
def application do
  [
    mod: {MiApp.Application, []},
    extra_applications: [:logger]
  ]
end
```

## Ejemplo Completo: Árbol de Supervisión

```elixir
# Estructura del árbol:
# MiApp.Supervisor (one_for_one)
# ├── MiApp.Repo (worker)
# ├── MiApp.Cache (worker)
# ├── MiApp.WorkerSupervisor (supervisor, rest_for_one)
# │   ├── MiApp.EventBus (worker)
# │   └── MiApp.Notificador (worker)
# └── MiApp.SessionSupervisor (dynamic_supervisor)
#     ├── Session "user_1" (worker, temporary)
#     └── Session "user_2" (worker, temporary)

# Inspeccionar el árbol en IEx:
# :observer.start()  # Abre interfaz gráfica
# Supervisor.which_children(MiApp.Supervisor)
# Supervisor.count_children(MiApp.Supervisor)
```

## Resumen

OTP y la supervisión son lo que hace de Elixir un lenguaje excepcional para sistemas de producción. Los supervisores organizan los procesos en árboles jerárquicos, las estrategias de reinicio permiten recuperarse de fallos automáticamente, y DynamicSupervisor permite gestionar procesos dinámicos. La filosofía "let it crash" libera al desarrollador de manejar cada caso de error posible, delegando la recuperación al sistema de supervisión. Diseñar buenos árboles de supervisión es la clave para construir aplicaciones Elixir resilientes.
