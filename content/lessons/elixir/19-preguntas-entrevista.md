# Preguntas de Entrevista sobre Elixir

Esta lección recopila las preguntas más frecuentes en entrevistas técnicas sobre Elixir, cubriendo desde conceptos fundamentales hasta temas avanzados de OTP y concurrencia. Cada pregunta incluye una respuesta clara y concisa que demuestra comprensión profunda del lenguaje.

## Preguntas sobre la BEAM VM

**¿Qué es la BEAM y por qué es importante para Elixir?**

La BEAM (Bogdan/Björn's Erlang Abstract Machine) es la máquina virtual que ejecuta el código Elixir. Sus características clave son:

```elixir
# Procesos ligeros (no son threads del SO)
# Cada proceso usa ~2KB de memoria inicial
spawn(fn -> IO.puts("Proceso ligero") end)

# Garbage collection por proceso (sin pausas globales)
# Preemptive scheduling (equidad entre procesos)
# Soporte para millones de procesos concurrentes
# Hot code swapping (actualizar código sin detener el sistema)

# Verificar capacidades en IEx:
# :erlang.system_info(:process_count)     # Procesos activos
# :erlang.system_info(:process_limit)     # Límite configurado
# :erlang.system_info(:schedulers_online) # Schedulers (cores)
```

**¿Cuál es la diferencia entre Elixir y Erlang?**

Elixir se compila al mismo bytecode que Erlang y comparte la BEAM, pero ofrece macros (metaprogramación), herramientas modernas (Mix, Hex), sintaxis más amigable, protocolos para polimorfismo y un ecosistema de paquetes más accesible. Puedes llamar funciones Erlang directamente desde Elixir: `:erlang.system_info(:otp_release)`.

## Preguntas sobre Procesos y Concurrencia

**¿Cuál es la diferencia entre un proceso de la BEAM y un thread del sistema operativo?**

```elixir
# Procesos BEAM:
# - Gestionados por la VM, no por el SO
# - ~2KB de memoria inicial (vs ~1MB de un thread)
# - Sin memoria compartida (comunicación por mensajes)
# - GC individual (sin pausas globales)
# - Preemptive scheduling por la BEAM

# Se pueden crear millones fácilmente:
pids = for _ <- 1..100_000 do
  spawn(fn -> receive do :stop -> :ok end end)
end
length(pids)  # => 100_000
```

**¿Cómo se comunican los procesos en Elixir?**

```elixir
# Mediante paso de mensajes asíncrono
pid = spawn(fn ->
  receive do
    {:ping, caller} ->
      send(caller, :pong)
  end
end)

send(pid, {:ping, self()})
receive do
  :pong -> IO.puts("Recibido pong")
after
  1000 -> IO.puts("Timeout")
end
```

**¿Cuál es la diferencia entre `spawn`, `spawn_link` y `spawn_monitor`?**

- `spawn` crea un proceso independiente: si falla, el padre no se entera.
- `spawn_link` crea un proceso vinculado bidireccionalmente: si uno muere, el otro también (a menos que atrape exits).
- `spawn_monitor` crea un proceso con monitor unidireccional: el padre recibe un mensaje `{:DOWN, ...}` si el hijo muere.

## Preguntas sobre OTP

**¿Qué es un GenServer y cuándo lo usarías?**

```elixir
# GenServer es un behaviour para procesos que mantienen estado
# y procesan peticiones de forma secuencial.

# Usarlo cuando necesitas:
# 1. Mantener estado mutable (contador, cache, cola)
# 2. Serializar acceso a un recurso compartido
# 3. Procesos de larga vida con lógica compleja

defmodule RateLimiter do
  use GenServer

  def start_link(limite), do: GenServer.start_link(__MODULE__, limite, name: __MODULE__)
  def permitir?(usuario), do: GenServer.call(__MODULE__, {:check, usuario})

  @impl true
  def init(limite), do: {:ok, %{limite: limite, conteos: %{}}}

  @impl true
  def handle_call({:check, usuario}, _from, state) do
    conteo = Map.get(state.conteos, usuario, 0)
    if conteo < state.limite do
      {:reply, true, put_in(state, [:conteos, usuario], conteo + 1)}
    else
      {:reply, false, state}
    end
  end
end
```

**¿Qué estrategias de supervisión existen y cuándo usar cada una?**

- `:one_for_one` — Reinicia solo el proceso que falló. Úsalo cuando los procesos son independientes.
- `:one_for_all` — Reinicia todos los hijos. Úsalo cuando todos dependen entre sí.
- `:rest_for_one` — Reinicia el que falló y todos los que se iniciaron después. Úsalo con dependencias secuenciales.

**Explica la filosofía "let it crash"**

No significa dejar que todo falle sin control. Significa diseñar el sistema para que los procesos individuales puedan fallar de forma aislada y ser reiniciados automáticamente por supervisores, en lugar de intentar manejar cada posible error defensivamente.

## Preguntas sobre Pattern Matching

**¿Qué es el pattern matching y cómo se diferencia de la asignación?**

```elixir
# En Elixir, = es el operador de match, no de asignación
x = 1        # Match exitoso, x se vincula a 1
1 = x        # Match exitoso, 1 == 1
# 2 = x      # MatchError: 2 != 1

# Destructuring
{:ok, valor} = {:ok, 42}
valor  # => 42

# En funciones (dispatch por patrones)
def procesar({:ok, datos}), do: usar(datos)
def procesar({:error, _}), do: :error
```

**¿Para qué sirve el pin operator (^)?**

```elixir
x = 1
# Sin pin: x se reasigna
x = 2  # x ahora vale 2

# Con pin: se compara con el valor actual
x = 1
^x = 1  # OK, coincide
# ^x = 2  # MatchError, 1 != 2

# Útil en case, with y funciones
valor_esperado = 200
case respuesta do
  %{status: ^valor_esperado} -> :ok
  _ -> :error
end
```

## Preguntas sobre Concurrencia Avanzada

**¿Cuál es la diferencia entre `handle_call` y `handle_cast`?**

```elixir
# handle_call: SÍNCRONO — el cliente espera la respuesta
# El proceso que llama se bloquea hasta recibir {:reply, ...}
GenServer.call(pid, :obtener)  # Bloquea hasta recibir respuesta

# handle_cast: ASÍNCRONO — fire and forget
# El proceso que llama continúa inmediatamente
GenServer.cast(pid, {:actualizar, datos})  # No espera respuesta
```

**¿Cómo evitarías un cuello de botella en un GenServer?**

```elixir
# Problema: un solo GenServer procesa mensajes secuencialmente
# Soluciones:
# 1. Pool de procesos (usar :poolboy o similar)
# 2. ETS para lecturas sin pasar por el GenServer
# 3. Particionar el estado en múltiples GenServers
# 4. Usar Task para trabajo paralelo desde el GenServer

# Ejemplo con ETS para lecturas rápidas:
defmodule CacheRapido do
  use GenServer

  def get(clave) do
    case :ets.lookup(:mi_cache, clave) do
      [{^clave, valor}] -> {:ok, valor}
      [] -> :miss
    end
  end

  def put(clave, valor) do
    GenServer.call(__MODULE__, {:put, clave, valor})
  end

  @impl true
  def init(_) do
    tabla = :ets.new(:mi_cache, [:named_table, :public, read_concurrency: true])
    {:ok, tabla}
  end

  @impl true
  def handle_call({:put, clave, valor}, _from, tabla) do
    :ets.insert(tabla, {clave, valor})
    {:reply, :ok, tabla}
  end
end
```

## Preguntas sobre Supervisión

**¿Cómo diseñarías un árbol de supervisión para un chat en tiempo real?**

```elixir
# ChatApp.Supervisor (one_for_one)
# ├── ChatApp.Repo
# ├── ChatApp.PubSub
# ├── ChatApp.Presence
# ├── ChatApp.RoomSupervisor (DynamicSupervisor)
# │   ├── Room "sala_1" (GenServer, temporary)
# │   └── Room "sala_2" (GenServer, temporary)
# └── ChatApp.Endpoint (Phoenix)

# Las salas son temporary porque pueden recrearse bajo demanda
# PubSub es permanent porque todos lo necesitan
# El DynamicSupervisor permite crear/destruir salas en runtime
```

## Preguntas Rápidas

**¿Qué son los átomos y pueden causar problemas?** Los átomos no se recolectan por el GC. Crear átomos dinámicamente (por ejemplo con `String.to_atom/1` desde input de usuario) puede agotar la tabla de átomos. Usar `String.to_existing_atom/1` es más seguro.

**¿Cuál es la diferencia entre listas y tuplas?** Las listas son listas enlazadas (O(1) prepend, O(n) acceso). Las tuplas son arrays contiguos en memoria (O(1) acceso, O(n) modificación).

**¿Qué es un changeset en Ecto?** Es una estructura que encapsula los cambios a aplicar a un schema, junto con validaciones y transformaciones. Separa la validación de la persistencia.

**¿Cómo funciona el pipe operator?** `x |> f(y)` se transforma en `f(x, y)`. El resultado de la izquierda se pasa como primer argumento a la función de la derecha.

## Resumen

Las entrevistas de Elixir evalúan el conocimiento de la BEAM VM, el modelo de concurrencia basado en procesos y mensajes, los patrones OTP como GenServer y supervisores, y la capacidad de diseñar sistemas tolerantes a fallos. Dominar el pattern matching, entender la diferencia entre operaciones síncronas y asíncronas, y saber diseñar árboles de supervisión son las competencias más valoradas. La clave es demostrar no solo conocimiento técnico sino comprensión de las decisiones de diseño detrás de cada característica del lenguaje.
