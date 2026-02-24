# Oban y Tareas en Background

## Introducción

Oban es la librería estándar en el ecosistema Elixir/Phoenix para ejecutar tareas en background con persistencia en base de datos. Garantiza que los jobs se completen incluso si la aplicación se reinicia.

## Instalación y Configuración

Agregamos Oban al proyecto y lo configuramos:

```elixir
# mix.exs
defp deps do
  [
    {:oban, "~> 2.17"}
  ]
end

# config/config.exs
config :my_app, Oban,
  repo: MyApp.Repo,
  queues: [default: 10, emails: 20, reportes: 5, importaciones: 3]

# config/test.exs
config :my_app, Oban, testing: :inline
```

Ejecutamos la migración:

```elixir
mix ecto.gen.migration add_oban_jobs_table

# En la migración generada
defmodule MyApp.Repo.Migrations.AddObanJobsTable do
  use Ecto.Migration

  def up, do: Oban.Migration.up(version: 12)
  def down, do: Oban.Migration.down(version: 1)
end
```

Agregamos Oban al supervision tree:

```elixir
# application.ex
children = [
  MyApp.Repo,
  {Oban, Application.fetch_env!(:my_app, Oban)},
  MyAppWeb.Endpoint
]
```

## Workers

Un worker define la lógica del job:

```elixir
defmodule MyApp.Workers.EnviarEmail do
  use Oban.Worker, queue: :emails, max_attempts: 5

  @impl Oban.Worker
  def perform(%Oban.Job{args: %{"tipo" => tipo, "usuario_id" => usuario_id}}) do
    usuario = MyApp.Cuentas.get_usuario!(usuario_id)

    case tipo do
      "bienvenida" -> MyApp.Emails.bienvenida(usuario) |> MyApp.Mailer.deliver()
      "reporte" -> MyApp.Emails.reporte_semanal(usuario) |> MyApp.Mailer.deliver()
      _ -> {:error, "Tipo de email desconocido: #{tipo}"}
    end
  end
end
```

## perform/1 y Enqueue

Encolar jobs para ejecución:

```elixir
# Encolar inmediatamente
%{tipo: "bienvenida", usuario_id: 42}
|> MyApp.Workers.EnviarEmail.new()
|> Oban.insert()

# Con prioridad
%{tipo: "urgente", usuario_id: 42}
|> MyApp.Workers.EnviarEmail.new(priority: 0)
|> Oban.insert()

# Dentro de una transacción Ecto
Multi.new()
|> Multi.insert(:usuario, Usuario.changeset(%Usuario{}, attrs))
|> Oban.insert(:email_job, fn %{usuario: u} ->
  MyApp.Workers.EnviarEmail.new(%{tipo: "bienvenida", usuario_id: u.id})
end)
|> Repo.transaction()
```

## Queues

Las colas controlan la concurrencia por tipo de trabajo:

```elixir
config :my_app, Oban,
  queues: [
    default: 10,         # 10 jobs concurrentes
    emails: 20,          # 20 emails simultáneos
    reportes: 5,         # 5 reportes a la vez
    importaciones: 3,    # 3 importaciones pesadas
    critico: [limit: 1, dispatch_cooldown: 500]
  ]
```

## Scheduling: Jobs Diferidos

Programar jobs para el futuro:

```elixir
# Ejecutar en 1 hora
%{reporte_id: 1}
|> MyApp.Workers.GenerarReporte.new(scheduled_at: DateTime.add(DateTime.utc_now(), 3600))
|> Oban.insert()

# Ejecutar en una fecha específica
%{evento_id: 5}
|> MyApp.Workers.Recordatorio.new(schedule_in: {7, :days})
|> Oban.insert()
```

## Cron Jobs

Tareas recurrentes con el plugin Cron:

```elixir
config :my_app, Oban,
  repo: MyApp.Repo,
  queues: [default: 10, emails: 20],
  plugins: [
    {Oban.Plugins.Cron, crontab: [
      {"0 8 * * *", MyApp.Workers.ReporteDiario},
      {"0 0 * * 0", MyApp.Workers.LimpiezaSemanal},
      {"*/15 * * * *", MyApp.Workers.SyncInventario, args: %{tipo: "parcial"}},
      {"0 2 1 * *", MyApp.Workers.BackupMensual}
    ]}
  ]
```

## Unique Jobs

Evitar duplicados con restricciones de unicidad:

```elixir
defmodule MyApp.Workers.SyncUsuario do
  use Oban.Worker,
    queue: :default,
    unique: [period: 300, fields: [:args, :queue], states: [:available, :scheduled, :executing]]

  @impl Oban.Worker
  def perform(%Oban.Job{args: %{"usuario_id" => id}}) do
    MyApp.Sync.sincronizar_usuario(id)
  end
end
```

## Plugins

Oban incluye plugins útiles para mantenimiento:

```elixir
config :my_app, Oban,
  plugins: [
    {Oban.Plugins.Pruner, max_age: 60 * 60 * 24 * 7},
    {Oban.Plugins.Stager, interval: :timer.seconds(1)},
    {Oban.Plugins.Lifeline, rescue_after: :timer.minutes(30)},
    {Oban.Plugins.Reindexer, schedule: "@weekly"}
  ]
```

## Error Handling

Manejo de errores y reintentos:

```elixir
defmodule MyApp.Workers.ProcesoFragil do
  use Oban.Worker, queue: :default, max_attempts: 10

  @impl Oban.Worker
  def perform(%Oban.Job{args: args, attempt: attempt}) do
    case MyApp.ServicioExterno.llamar(args) do
      {:ok, resultado} ->
        :ok

      {:error, :timeout} when attempt < 5 ->
        {:snooze, attempt * 60}

      {:error, :no_reintentar} ->
        {:discard, "Error permanente, no reintentar"}

      {:error, reason} ->
        {:error, reason}
    end
  end

  @impl Oban.Worker
  def backoff(%Oban.Job{attempt: attempt}) do
    trunc(:math.pow(2, attempt) + :rand.uniform(30))
  end
end
```

## Resumen

Oban es la solución robusta para tareas en background en Phoenix. Ofrece workers persistidos en base de datos, múltiples colas con concurrencia configurable, scheduling y cron jobs, unicidad para evitar duplicados, plugins de mantenimiento y manejo avanzado de errores con reintentos exponenciales.
