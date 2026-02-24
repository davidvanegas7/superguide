# Procesos y Concurrencia en Elixir

La concurrencia es el corazón de Elixir. Los procesos de la BEAM no son procesos del sistema operativo, sino entidades extremadamente ligeras gestionadas por la máquina virtual. Un sistema Elixir puede ejecutar millones de procesos simultáneos, cada uno con su propia memoria y recolección de basura.

## Spawn: Crear Procesos

La forma más básica de crear un proceso es con `spawn/1`:

```elixir
# Spawn crea un proceso y retorna su PID
pid = spawn(fn ->
  IO.puts("¡Hola desde el proceso #{inspect(self())}!")
end)

# El proceso padre continúa inmediatamente
IO.puts("Proceso creado: #{inspect(pid)}")
Process.alive?(pid)  # => false (ya terminó)

# spawn_link: similar pero enlaza el proceso al padre
pid = spawn_link(fn ->
  Process.sleep(1000)
  IO.puts("Proceso terminado")
end)
```

## Send y Receive: Comunicación entre Procesos

Los procesos se comunican mediante paso de mensajes asíncrono:

```elixir
# Enviar y recibir mensajes
defmodule Mensajero do
  def escuchar do
    receive do
      {:saludo, nombre} ->
        IO.puts("¡Hola, #{nombre}!")
        escuchar()  # Seguir escuchando

      {:despedida, nombre} ->
        IO.puts("¡Adiós, #{nombre}!")
        escuchar()

      :detener ->
        IO.puts("Deteniendo el proceso...")
        # No llamamos escuchar(), el proceso termina
    after
      5000 ->
        IO.puts("Timeout: sin mensajes por 5 segundos")
    end
  end
end

pid = spawn(&Mensajero.escuchar/0)

send(pid, {:saludo, "Ana"})      # => ¡Hola, Ana!
send(pid, {:despedida, "Pedro"}) # => ¡Adiós, Pedro!
send(pid, :detener)              # => Deteniendo el proceso...
```

## Patrón Request-Reply

Un patrón común es enviar un mensaje y esperar una respuesta:

```elixir
defmodule Contador do
  def iniciar(valor_inicial \\ 0) do
    spawn(fn -> bucle(valor_inicial) end)
  end

  defp bucle(conteo) do
    receive do
      {:incrementar, caller} ->
        nuevo = conteo + 1
        send(caller, {:ok, nuevo})
        bucle(nuevo)

      {:obtener, caller} ->
        send(caller, {:ok, conteo})
        bucle(conteo)

      {:decrementar, caller} ->
        nuevo = conteo - 1
        send(caller, {:ok, nuevo})
        bucle(nuevo)
    end
  end

  # Funciones cliente
  def incrementar(pid) do
    send(pid, {:incrementar, self()})
    receive do
      {:ok, valor} -> valor
    end
  end

  def obtener(pid) do
    send(pid, {:obtener, self()})
    receive do
      {:ok, valor} -> valor
    end
  end
end

pid = Contador.iniciar(0)
Contador.incrementar(pid)  # => 1
Contador.incrementar(pid)  # => 2
Contador.obtener(pid)      # => 2
```

## Links: Procesos Enlazados

Los links crean una relación bidireccional entre procesos. Si uno falla, el otro también:

```elixir
# spawn_link enlaza automáticamente
pid = spawn_link(fn ->
  Process.sleep(1000)
  raise "¡Error en proceso hijo!"
end)
# El proceso padre también morirá después de 1 segundo

# Atrapar exits
Process.flag(:trap_exit, true)
pid = spawn_link(fn -> exit(:fallo) end)

receive do
  {:EXIT, ^pid, razon} ->
    IO.puts("Proceso #{inspect(pid)} terminó: #{inspect(razon)}")
end
```

## Monitors: Supervisión Unidireccional

A diferencia de los links, los monitors son unidireccionales — el observador recibe una notificación sin ser afectado:

```elixir
pid = spawn(fn ->
  Process.sleep(500)
  exit(:terminado)
end)

ref = Process.monitor(pid)

receive do
  {:DOWN, ^ref, :process, ^pid, razon} ->
    IO.puts("Proceso monitoreado terminó: #{inspect(razon)}")
end
# => Proceso monitoreado terminó: :terminado
```

## El Módulo Process

El módulo `Process` proporciona funciones útiles para gestionar procesos:

```elixir
# Información del proceso actual
self()                    # PID del proceso actual
Process.alive?(pid)       # ¿El proceso sigue vivo?

# Registrar un proceso con nombre
Process.register(pid, :mi_proceso)
send(:mi_proceso, :mensaje)

# Dormir el proceso
Process.sleep(1000)  # Pausa de 1 segundo

# Listar procesos
Process.list()           # Todos los procesos
Process.info(self())     # Info del proceso actual
Process.info(self(), :message_queue_len)  # Mensajes en cola

# Terminar un proceso
Process.exit(pid, :kill)  # Forzar terminación
```

## Task: Concurrencia Simplificada

El módulo `Task` simplifica los patrones comunes de concurrencia:

```elixir
# Ejecutar una tarea asíncrona y esperar resultado
tarea = Task.async(fn ->
  Process.sleep(1000)
  42
end)

resultado = Task.await(tarea)  # => 42

# Ejecutar múltiples tareas en paralelo
resultados =
  ["url1", "url2", "url3"]
  |> Enum.map(fn url ->
    Task.async(fn -> simular_peticion(url) end)
  end)
  |> Enum.map(&Task.await/1)

# Task.async_stream para procesamiento concurrente
1..10
|> Task.async_stream(fn n ->
  Process.sleep(100)
  n * n
end, max_concurrency: 4)
|> Enum.to_list()
# => [ok: 1, ok: 4, ok: 9, ...]
```

## Resumen

Los procesos ligeros de la BEAM son la base del modelo de concurrencia en Elixir. Con `spawn` para crear procesos, `send`/`receive` para comunicación por mensajes, links para propagación de errores y monitors para supervisión unidireccional, Elixir proporciona todas las primitivas necesarias para construir sistemas concurrentes robustos. El módulo `Task` simplifica los patrones más comunes, permitiendo ejecutar y coordinar trabajo en paralelo de forma segura.
