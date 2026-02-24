# Preguntas de Entrevista sobre Phoenix

## Introducción

Esta lección recopila las preguntas más frecuentes en entrevistas técnicas sobre Phoenix Framework, cubriendo desde arquitectura hasta decisiones de diseño y patrones avanzados.

## LiveView vs SPA: Pros y Contras

**¿Cuándo elegir LiveView sobre una SPA con React/Vue?**

```elixir
# LiveView - ideal para:
# - Aplicaciones internas, dashboards, admin panels
# - Equipos pequeños sin especialistas frontend
# - Tiempo real sin complejidad de APIs
# - SEO nativo (server-rendered)

# SPA - ideal para:
# - Apps offline-first o PWAs
# - Interacciones complejas de UI (drag & drop avanzado)
# - Equipos frontend/backend separados
# - Apps móviles que comparten API

# LiveView mantiene el estado en el servidor
defmodule DashboardLive do
  use MyAppWeb, :live_view

  def mount(_params, _session, socket) do
    if connected?(socket), do: :timer.send_interval(5000, :tick)
    {:ok, assign(socket, datos: cargar_metricas())}
  end

  def handle_info(:tick, socket) do
    {:noreply, assign(socket, datos: cargar_metricas())}
  end
end
```

## Channels vs LiveView

**¿Cuál es la diferencia entre Channels y LiveView?**

```elixir
# Channels: comunicación bidireccional pura
# - Chat en tiempo real, juegos multijugador
# - Clientes no-web (apps móviles, IoT)
# - Control total del protocolo de mensajes

# LiveView: UI renderizada desde el servidor
# - El servidor controla el HTML
# - Actualizaciones via diffs mínimos del DOM
# - No necesita escribir JavaScript

# Channel: el cliente maneja la UI
channel "sala:lobby", MyAppWeb.SalaChannel

# LiveView: el servidor maneja la UI
live "/dashboard", DashboardLive
```

## OTP en Phoenix

**¿Cómo aprovecha Phoenix la plataforma OTP?**

```elixir
# Phoenix usa OTP extensivamente:
# 1. Supervision trees para tolerancia a fallos
# 2. GenServer para estado y workers
# 3. ETS para caché de alta velocidad
# 4. PubSub distribuido entre nodos
# 5. Process per connection (channels/LiveView)

# Ejemplo: cada LiveView es un proceso independiente
# Si uno falla, los demás no se ven afectados
defmodule MyApp.Application do
  def start(_type, _args) do
    children = [
      MyApp.Repo,                          # Pool de conexiones DB
      {Phoenix.PubSub, name: MyApp.PubSub}, # PubSub distribuido
      MyAppWeb.Endpoint,                   # Servidor HTTP
      MyApp.Cache,                         # GenServer de caché
      {Task.Supervisor, name: MyApp.Tasks} # Tareas asíncronas
    ]

    Supervisor.start_link(children, strategy: :one_for_one)
  end
end
```

## Contextos: ¿Por Qué y Cuándo?

**¿Cómo decides la frontera de un contexto?**

```elixir
# Pregúntate: ¿estos conceptos cambian juntos?
# Si sí -> mismo contexto. Si no -> contextos separados.

# Ejemplo: Cuentas vs Facturación
defmodule MyApp.Cuentas do
  # Registrar, autenticar, perfil
  def registrar_usuario(attrs), do: # ...
  def autenticar(email, password), do: # ...
end

defmodule MyApp.Facturacion do
  # Planes, suscripciones, pagos
  # Puede referenciar Cuentas pero no acceder a su DB directamente
  def crear_suscripcion(usuario, plan) do
    # usuario viene de Cuentas.get_usuario/1
    %Suscripcion{usuario_id: usuario.id, plan_id: plan.id}
    |> Repo.insert()
  end
end

# Las reglas clave:
# - Un contexto NO debe hacer queries directos a tablas de otro
# - Siempre usar la API pública del otro contexto
# - Mantener los changesets dentro de su contexto
```

## Performance en la BEAM

**¿Por qué Phoenix es tan performante?**

```elixir
# La BEAM VM optimiza para:
# 1. Concurrencia masiva: millones de procesos ligeros
# 2. Baja latencia: GC per-process, no stop-the-world
# 3. I/O no bloqueante nativo
# 4. Preemptive scheduling: ningún proceso monopoliza CPU

# Benchmark típico de Phoenix:
# - 2M+ conexiones WebSocket simultáneas (un solo servidor)
# - Latencia p99 < 10ms para requests HTTP
# - Hot code upgrades sin downtime

# Optimizaciones comunes en entrevistas:
# - ETS para caché en memoria (microsegundos de acceso)
:ets.new(:mi_cache, [:set, :public, :named_table])
:ets.insert(:mi_cache, {"key", "valor"})

# - Precargar asociaciones para evitar N+1
Repo.all(from p in Producto, preload: [:categoria, :reviews])

# - Streams para procesar colecciones grandes
Repo.stream(from u in Usuario)
|> Stream.each(&procesar/1)
|> Stream.run()
```

## Estrategias de Deployment

**¿Cómo despliegas una app Phoenix en producción?**

```elixir
# Opciones principales:
# 1. Mix Release + Docker -> máxima flexibilidad
# 2. Fly.io -> ideal para clustering BEAM
# 3. Gigalixir -> PaaS especializado en Elixir
# 4. Kubernetes -> cuando ya tienes la infra

# Release con migraciones automáticas:
defmodule MyApp.Release do
  def migrate do
    for repo <- repos() do
      {:ok, _, _} = Ecto.Migrator.with_repo(repo, &Ecto.Migrator.run(&1, :up, all: true))
    end
  end

  def rollback(repo, version) do
    {:ok, _, _} = Ecto.Migrator.with_repo(repo, &Ecto.Migrator.run(&1, :down, to: version))
  end

  defp repos, do: Application.fetch_env!(:my_app, :ecto_repos)
end
```

## Patrones Ecto en Entrevistas

**¿Qué patrones de Ecto demuestran experiencia?**

```elixir
# Multi para transacciones complejas
Multi.new()
|> Multi.insert(:pedido, Pedido.changeset(%Pedido{}, attrs))
|> Multi.update(:stock, fn %{pedido: pedido} ->
  Producto.reducir_stock_changeset(pedido.producto, pedido.cantidad)
end)
|> Multi.insert(:pago, fn %{pedido: pedido} ->
  Pago.changeset(%Pago{}, %{pedido_id: pedido.id, monto: pedido.total})
end)
|> Repo.transaction()

# Composición de queries
defmodule MyApp.Queries.Producto do
  import Ecto.Query

  def base, do: from(p in Producto)

  def publicados(query), do: where(query, [p], p.publicado == true)
  def por_categoria(query, cat), do: where(query, [p], p.categoria == ^cat)
  def ordenar_precio(query, dir), do: order_by(query, [p], [{^dir, :precio}])
  def con_reviews(query), do: preload(query, [:reviews])
end

# Uso composable:
Producto
|> Queries.Producto.publicados()
|> Queries.Producto.por_categoria("electrónica")
|> Queries.Producto.ordenar_precio(:asc)
|> Queries.Producto.con_reviews()
|> Repo.all()
```

## Resumen

En entrevistas sobre Phoenix, los temas clave son: LiveView vs SPA según el caso de uso, Channels vs LiveView para tiempo real, OTP como base de concurrencia y tolerancia a fallos, contextos para arquitectura limpia, rendimiento de la BEAM, estrategias de deploy y patrones avanzados de Ecto. Demostrar comprensión de estos conceptos muestra dominio real del ecosistema.
