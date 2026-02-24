<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhoenixExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'phoenix-framework')->first();

        if (! $course) {
            $this->command->warn('Phoenix course not found. Run CourseSeeder + PhoenixLessonSeeder first.');
            return;
        }

        $lessons = Lesson::where('course_id', $course->id)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('sort_order');

        $exercises = $this->exercises($lessons);
        $now = now();

        foreach ($exercises as $ex) {
            DB::table('lesson_exercises')->updateOrInsert(
                ['lesson_id' => $ex['lesson_id']],
                array_merge($ex, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $this->command->info('Phoenix exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── 1 · Introducción a Phoenix ──────────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Estructura de un proyecto Phoenix',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula la estructura y configuración de un proyecto Phoenix.

```elixir
# project_structure/0 → retorna un mapa con la estructura principal
# Claves: "lib/", "config/", "priv/", "test/", "assets/", "mix.exs"
# Valores: descripción breve de cada directorio/archivo

# endpoint_config/1 → recibe un keyword list de config
# Ejemplo: [port: 4000, host: "localhost", scheme: "http"]
# Retorna string: "http://localhost:4000"

# mix_task/1 → simula comandos mix
# Comandos: "phx.server", "phx.routes", "ecto.migrate",
#            "ecto.create", "phx.gen.html", "phx.gen.json"
# Retorna descripción. Si desconocido: "Tarea desconocida: {task}"
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PhoenixIntro do
  def project_structure do
    # Retorna mapa con directorios/archivos principales
  end

  def endpoint_config(opts) do
    # Construye URL desde keyword list de config
  end

  def mix_task(task) do
    # Simula comandos mix de Phoenix
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PhoenixIntro do
  def project_structure do
    %{
      "lib/" => "Código de la aplicación: contexts, schemas, controllers, views",
      "config/" => "Archivos de configuración por ambiente (dev, test, prod)",
      "priv/" => "Migraciones, seeds, archivos estáticos compilados",
      "test/" => "Tests de la aplicación organizados por tipo",
      "assets/" => "JavaScript, CSS y assets del frontend",
      "mix.exs" => "Definición del proyecto: dependencias, config de app"
    }
  end

  def endpoint_config(opts) do
    scheme = Keyword.get(opts, :scheme, "http")
    host = Keyword.get(opts, :host, "localhost")
    port = Keyword.get(opts, :port, 4000)
    "#{scheme}://#{host}:#{port}"
  end

  def mix_task(task) do
    tasks = %{
      "phx.server" => "Inicia el servidor de desarrollo Phoenix",
      "phx.routes" => "Lista todas las rutas definidas en el router",
      "ecto.migrate" => "Ejecuta las migraciones pendientes de la BD",
      "ecto.create" => "Crea la base de datos configurada",
      "phx.gen.html" => "Genera controller, views, templates y migración para recurso HTML",
      "phx.gen.json" => "Genera controller y view JSON para recurso API"
    }

    Map.get(tasks, task, "Tarea desconocida: #{task}")
  end
end
ELIXIR,
        ];

        // ── 2 · Rutas y Controllers ─────────────────────────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Router y Controllers de Phoenix',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Implementa un mini-router y controller simplificado de Phoenix.

```elixir
# parse_route/1 → parsea string de ruta "GET /users/:id"
# Retorna: %{method: "GET", path: "/users/:id", segments: ["users", ":id"]}

# match_route/2 → recibe una ruta definida y un path real
# match_route(%{path: "/users/:id"}, "/users/42")
# → %{matched: true, params: %{"id" => "42"}}
# Si no coincide: %{matched: false, params: %{}}

# controller_action/2 → simula acciones CRUD
# controller_action("users", "index") → "Listando todos los users"
# controller_action("users", "show")  → "Mostrando un user"
# Acciones: index, show, new, create, edit, update, delete
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PhoenixRouter do
  def parse_route(route_string) do
    # Parsea "GET /users/:id" a mapa
  end

  def match_route(route_def, actual_path) do
    # Compara ruta definida con path real
  end

  def controller_action(resource, action) do
    # Simula acciones CRUD de un controller
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PhoenixRouter do
  def parse_route(route_string) do
    [method | path_parts] = String.split(route_string, " ", trim: true)
    path = Enum.join(path_parts, " ")
    segments = path |> String.split("/", trim: true)
    %{method: method, path: path, segments: segments}
  end

  def match_route(route_def, actual_path) do
    def_segments = route_def.path |> String.split("/", trim: true)
    actual_segments = actual_path |> String.split("/", trim: true)

    if length(def_segments) != length(actual_segments) do
      %{matched: false, params: %{}}
    else
      params =
        Enum.zip(def_segments, actual_segments)
        |> Enum.reduce(%{}, fn
          {":" <> key, value}, acc -> Map.put(acc, key, value)
          {same, same}, acc -> acc
          _, _acc -> throw(:no_match)
        end)

      %{matched: true, params: params}
    end
  catch
    :no_match -> %{matched: false, params: %{}}
  end

  def controller_action(resource, action) do
    case action do
      "index"  -> "Listando todos los #{resource}"
      "show"   -> "Mostrando un #{singularize(resource)}"
      "new"    -> "Formulario para nuevo #{singularize(resource)}"
      "create" -> "Creando #{singularize(resource)}"
      "edit"   -> "Formulario para editar #{singularize(resource)}"
      "update" -> "Actualizando #{singularize(resource)}"
      "delete" -> "Eliminando #{singularize(resource)}"
      _        -> "Acción desconocida: #{action}"
    end
  end

  defp singularize(name) do
    if String.ends_with?(name, "s"),
      do: String.slice(name, 0..-2//1),
      else: name
  end
end
ELIXIR,
        ];

        // ── 3 · Vistas y Templates (HEEx) ──────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Templates y componentes HEEx',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula el sistema de templates y componentes de Phoenix.

```elixir
# render_assigns/2 → recibe template string y assigns map
# render_assigns("Hola <%= @name %>", %{name: "Juan"})
# → "Hola Juan"
# Reemplaza todas las ocurrencias de <%= @key %>

# escape_html/1 → escapa caracteres HTML peligrosos
# < → &lt;  > → &gt;  & → &amp;  " → &quot;

# component_attrs/1 → recibe keyword list de atributos
# [class: "btn", id: "submit", disabled: true]
# → "class=\"btn\" id=\"submit\" disabled"
# Valores booleanos true solo ponen el nombre, false los omite
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PhoenixTemplates do
  def render_assigns(template, assigns) do
    # Reemplaza <%= @key %> con valores del assigns map
  end

  def escape_html(text) do
    # Escapa caracteres HTML peligrosos
  end

  def component_attrs(attrs) do
    # Convierte keyword list a atributos HTML string
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PhoenixTemplates do
  def render_assigns(template, assigns) do
    Enum.reduce(assigns, template, fn {key, value}, acc ->
      String.replace(acc, "<%= @#{key} %>", to_string(value))
    end)
  end

  def escape_html(text) do
    text
    |> String.replace("&", "&amp;")
    |> String.replace("<", "&lt;")
    |> String.replace(">", "&gt;")
    |> String.replace("\"", "&quot;")
  end

  def component_attrs(attrs) do
    attrs
    |> Enum.reduce([], fn
      {_key, false}, acc -> acc
      {key, true}, acc -> [to_string(key) | acc]
      {key, value}, acc -> ["#{key}=\"#{value}\"" | acc]
    end)
    |> Enum.reverse()
    |> Enum.join(" ")
  end
end
ELIXIR,
        ];

        // ── 4 · Ecto y Modelos ──────────────────────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Schemas y Changesets de Ecto',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula la validación de changesets de Ecto.

```elixir
# validate_changeset/2 → recibe un mapa de datos y lista de campos requeridos
# Retorna: %{valid?: true/false, errors: [...], changes: %{...}}
# Si un campo requerido falta o está vacío → error: {campo, "can't be blank"}

# cast_fields/2 → recibe datos (mapa string keys) y campos permitidos (atoms)
# Convierte solo los campos permitidos a atom keys
# cast_fields(%{"name" => "Ana", "admin" => true}, [:name, :email])
# → %{name: "Ana"}

# validate_format/3 → valida un valor contra un regex pattern
# Retorna {:ok, value} o {:error, "has invalid format"}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule EctoSim do
  def validate_changeset(data, required_fields) do
    # Valida que los campos requeridos existan y no estén vacíos
  end

  def cast_fields(params, allowed_fields) do
    # Filtra y convierte campos string a atom keys
  end

  def validate_format(value, pattern) do
    # Valida formato con regex
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule EctoSim do
  def validate_changeset(data, required_fields) do
    errors =
      required_fields
      |> Enum.filter(fn field ->
        value = Map.get(data, field)
        is_nil(value) or value == ""
      end)
      |> Enum.map(fn field -> {field, "can't be blank"} end)

    changes =
      data
      |> Enum.reject(fn {_k, v} -> is_nil(v) or v == "" end)
      |> Map.new()

    %{valid?: errors == [], errors: errors, changes: changes}
  end

  def cast_fields(params, allowed_fields) do
    allowed_strings = Enum.map(allowed_fields, &to_string/1)

    params
    |> Enum.filter(fn {k, _v} -> k in allowed_strings end)
    |> Enum.map(fn {k, v} -> {String.to_atom(k), v} end)
    |> Map.new()
  end

  def validate_format(value, pattern) do
    if Regex.match?(pattern, value) do
      {:ok, value}
    else
      {:error, "has invalid format"}
    end
  end
end
ELIXIR,
        ];

        // ── 5 · Formularios y Validación ────────────────────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Formularios con Changesets',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula el manejo de formularios con changesets en Phoenix.

```elixir
# build_form/2 → recibe un schema (mapa) y params del form (mapa)
# Retorna: %{data: schema, params: params, errors: [], valid?: true}

# validate_required/2 → recibe form_data y lista de campos requeridos
# Agrega errores para campos faltantes. Retorna form_data actualizado.

# validate_length/3 → recibe form_data, campo, y opts [min: n, max: m]
# Valida longitud del string. Agrega error si falla.
# Errores: "should be at least N character(s)"
#          "should be at most N character(s)"
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PhoenixForms do
  def build_form(schema, params) do
    # Construye estructura de formulario
  end

  def validate_required(form_data, fields) do
    # Valida campos requeridos
  end

  def validate_length(form_data, field, opts) do
    # Valida longitud de campos string
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PhoenixForms do
  def build_form(schema, params) do
    %{data: schema, params: params, errors: [], valid?: true}
  end

  def validate_required(form_data, fields) do
    new_errors =
      Enum.reduce(fields, [], fn field, acc ->
        value = Map.get(form_data.params, field) || Map.get(form_data.params, to_string(field))
        if is_nil(value) or value == "" do
          [{field, "can't be blank"} | acc]
        else
          acc
        end
      end)
      |> Enum.reverse()

    errors = form_data.errors ++ new_errors
    %{form_data | errors: errors, valid?: errors == []}
  end

  def validate_length(form_data, field, opts) do
    value = Map.get(form_data.params, field) || Map.get(form_data.params, to_string(field), "")
    len = String.length(to_string(value))
    min = Keyword.get(opts, :min)
    max = Keyword.get(opts, :max)

    new_errors =
      cond do
        min && len < min -> [{field, "should be at least #{min} character(s)"}]
        max && len > max -> [{field, "should be at most #{max} character(s)"}]
        true -> []
      end

    errors = form_data.errors ++ new_errors
    %{form_data | errors: errors, valid?: errors == []}
  end
end
ELIXIR,
        ];

        // ── 6 · LiveView: Fundamentos ───────────────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulación de LiveView',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula el ciclo de vida de un LiveView.

```elixir
# mount/1 → recibe params, retorna socket con assigns iniciales
# mount(%{user: "Ana"}) → %{assigns: %{user: "Ana", count: 0, mounted: true}}

# handle_event/3 → recibe evento, payload y socket
# Eventos: "increment" → count + 1
#           "decrement" → count - 1
#           "reset"     → count = 0
# Retorna socket actualizado

# render_diff/2 → compara dos assigns y retorna solo los cambios
# render_diff(%{a: 1, b: 2}, %{a: 1, b: 3, c: 4})
# → %{b: 3, c: 4}   (solo lo que cambió o se agregó)
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule LiveViewSim do
  def mount(params) do
    # Inicializa socket con assigns
  end

  def handle_event(event, _payload, socket) do
    # Maneja eventos del usuario
  end

  def render_diff(old_assigns, new_assigns) do
    # Calcula diferencias entre assigns
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule LiveViewSim do
  def mount(params) do
    assigns = Map.merge(%{count: 0, mounted: true}, params)
    %{assigns: assigns}
  end

  def handle_event(event, _payload, socket) do
    count = socket.assigns.count

    new_count =
      case event do
        "increment" -> count + 1
        "decrement" -> count - 1
        "reset"     -> 0
        _           -> count
      end

    put_in(socket, [:assigns, :count], new_count)
  end

  def render_diff(old_assigns, new_assigns) do
    new_assigns
    |> Enum.reject(fn {k, v} -> Map.get(old_assigns, k) == v end)
    |> Map.new()
  end
end
ELIXIR,
        ];

        // ── 7 · LiveView Avanzado ───────────────────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'LiveComponents y Streams',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula LiveComponents y el sistema de streams de LiveView.

```elixir
# component_lifecycle/2 → simula update de un LiveComponent
# Recibe assigns actuales y nuevos assigns
# Merge y retorna %{assigns: merged, updated_at: :os.system_time(:second)}

# stream_insert/3 → simula stream_insert para listas eficientes
# Recibe stream (lista), item y posición (:append o :prepend)
# stream_insert([%{id: 1}], %{id: 2}, :append) → [%{id: 1}, %{id: 2}]
# Si el item ya existe (mismo id), lo reemplaza en su posición

# stream_delete/2 → elimina item del stream por id
# Retorna stream sin el item
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule LiveComponents do
  def component_lifecycle(current_assigns, new_assigns) do
    # Simula update del componente
  end

  def stream_insert(stream, item, position \\ :append) do
    # Inserta o reemplaza item en el stream
  end

  def stream_delete(stream, item_id) do
    # Elimina item por id
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule LiveComponents do
  def component_lifecycle(current_assigns, new_assigns) do
    merged = Map.merge(current_assigns, new_assigns)
    %{assigns: merged, updated_at: :os.system_time(:second)}
  end

  def stream_insert(stream, item, position \\ :append) do
    filtered = Enum.reject(stream, fn existing -> existing.id == item.id end)

    case position do
      :append  -> filtered ++ [item]
      :prepend -> [item | filtered]
    end
  end

  def stream_delete(stream, item_id) do
    Enum.reject(stream, fn item -> item.id == item_id end)
  end
end
ELIXIR,
        ];

        // ── 8 · Autenticación ───────────────────────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de autenticación',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula el sistema de autenticación generado por `mix phx.gen.auth`.

```elixir
# hash_password/1 → simula hashing: "hashed_" <> password
# Si password tiene menos de 8 caracteres → {:error, "too short"}
# Caso exitoso → {:ok, "hashed_" <> password}

# verify_password/2 → verifica password contra hash
# verify_password("secret123", "hashed_secret123") → true

# generate_token/1 → genera token simulado para usuario
# Recibe user_id (integer). Retorna "token_<user_id>_<timestamp>"
# donde timestamp = :os.system_time(:second)

# authenticate/2 → recibe lista de users y token string
# Cada user: %{id: n, token: "token_..."}
# Retorna {:ok, user} o {:error, :invalid_token}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PhoenixAuth do
  def hash_password(password) do
    # Simula hashing de password
  end

  def verify_password(password, hash) do
    # Verifica password contra hash
  end

  def generate_token(user_id) do
    # Genera token para sesión
  end

  def authenticate(users, token) do
    # Busca usuario por token
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PhoenixAuth do
  def hash_password(password) when byte_size(password) < 8 do
    {:error, "too short"}
  end

  def hash_password(password) do
    {:ok, "hashed_#{password}"}
  end

  def verify_password(password, hash) do
    "hashed_#{password}" == hash
  end

  def generate_token(user_id) do
    timestamp = :os.system_time(:second)
    "token_#{user_id}_#{timestamp}"
  end

  def authenticate(users, token) do
    case Enum.find(users, fn user -> user.token == token end) do
      nil  -> {:error, :invalid_token}
      user -> {:ok, user}
    end
  end
end
ELIXIR,
        ];

        // ── 9 · Channels y WebSockets ───────────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Phoenix Channels',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula el sistema de Channels de Phoenix.

```elixir
# join_channel/2 → simula join a un topic
# Recibe topic string y user_id
# Retorna: %{topic: topic, user_id: user_id, joined_at: timestamp, messages: []}

# handle_in/3 → simula recibir un mensaje en el channel
# Recibe channel_state, event string, y payload map
# Agrega %{event: event, payload: payload, at: timestamp} a messages
# Retorna channel_state actualizado

# broadcast/3 → simula broadcast a todos los suscriptores
# Recibe lista de channel_states (mismo topic), event, payload
# Retorna lista de channel_states con el mensaje agregado a cada uno
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PhoenixChannels do
  def join_channel(topic, user_id) do
    # Simula unirse a un channel
  end

  def handle_in(channel_state, event, payload) do
    # Maneja mensaje entrante
  end

  def broadcast(channels, event, payload) do
    # Envía mensaje a todos los suscriptores
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PhoenixChannels do
  def join_channel(topic, user_id) do
    %{
      topic: topic,
      user_id: user_id,
      joined_at: :os.system_time(:second),
      messages: []
    }
  end

  def handle_in(channel_state, event, payload) do
    message = %{
      event: event,
      payload: payload,
      at: :os.system_time(:second)
    }

    %{channel_state | messages: channel_state.messages ++ [message]}
  end

  def broadcast(channels, event, payload) do
    message = %{event: event, payload: payload, at: :os.system_time(:second)}

    Enum.map(channels, fn ch ->
      %{ch | messages: ch.messages ++ [message]}
    end)
  end
end
ELIXIR,
        ];

        // ── 10 · PubSub y Tiempo Real ───────────────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema PubSub',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Implementa un sistema PubSub simplificado.

```elixir
# new/0 → crea estado PubSub: %{topics: %{}}

# subscribe/3 → suscribe subscriber_id a un topic
# Agrega el subscriber al set del topic
# Retorna estado actualizado

# unsubscribe/3 → desuscribe subscriber_id de un topic
# Retorna estado actualizado

# publish/3 → publica mensaje a un topic
# Retorna: {pubsub_actualizado, [lista de subscriber_ids notificados]}
# Si el topic no tiene suscriptores: {pubsub, []}

# subscribers/2 → lista suscriptores de un topic
# Retorna lista de subscriber_ids o [] si no existe
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule SimplePubSub do
  def new, do: # Estado inicial

  def subscribe(pubsub, topic, subscriber_id) do
    # Suscribe a un topic
  end

  def unsubscribe(pubsub, topic, subscriber_id) do
    # Desuscribe de un topic
  end

  def publish(pubsub, topic, _message) do
    # Publica mensaje, retorna {state, notified_ids}
  end

  def subscribers(pubsub, topic) do
    # Lista suscriptores de un topic
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule SimplePubSub do
  def new, do: %{topics: %{}}

  def subscribe(pubsub, topic, subscriber_id) do
    current = Map.get(pubsub.topics, topic, MapSet.new())
    updated = MapSet.put(current, subscriber_id)
    %{pubsub | topics: Map.put(pubsub.topics, topic, updated)}
  end

  def unsubscribe(pubsub, topic, subscriber_id) do
    current = Map.get(pubsub.topics, topic, MapSet.new())
    updated = MapSet.delete(current, subscriber_id)
    %{pubsub | topics: Map.put(pubsub.topics, topic, updated)}
  end

  def publish(pubsub, topic, _message) do
    subs = Map.get(pubsub.topics, topic, MapSet.new())
    {pubsub, MapSet.to_list(subs)}
  end

  def subscribers(pubsub, topic) do
    pubsub.topics
    |> Map.get(topic, MapSet.new())
    |> MapSet.to_list()
  end
end
ELIXIR,
        ];

        // ── 11 · APIs REST con Phoenix ──────────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'API REST JSON',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula controladores de API REST en Phoenix.

```elixir
# json_response/2 → construye respuesta JSON
# Recibe status (:ok | :created | :not_found | :error) y data (map)
# :ok → %{status: 200, body: data}
# :created → %{status: 201, body: data}
# :not_found → %{status: 404, body: %{error: "Not found"}}
# :error → %{status: 422, body: %{errors: data}}

# paginate/3 → pagina una lista de items
# Recibe lista, page (1-based), per_page
# Retorna: %{data: items_de_la_pagina, meta: %{page: n, per_page: m, total: t, total_pages: tp}}

# serialize/2 → convierte struct a mapa con solo los campos indicados
# serialize(%{id: 1, name: "A", password: "x"}, [:id, :name])
# → %{id: 1, name: "A"}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PhoenixAPI do
  def json_response(status, data) do
    # Construye respuesta JSON con status HTTP
  end

  def paginate(items, page, per_page) do
    # Pagina lista de items
  end

  def serialize(struct, fields) do
    # Serializa struct a mapa con campos seleccionados
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PhoenixAPI do
  def json_response(status, data) do
    case status do
      :ok        -> %{status: 200, body: data}
      :created   -> %{status: 201, body: data}
      :not_found -> %{status: 404, body: %{error: "Not found"}}
      :error     -> %{status: 422, body: %{errors: data}}
    end
  end

  def paginate(items, page, per_page) do
    total = length(items)
    total_pages = max(ceil(total / per_page), 1)
    offset = (page - 1) * per_page
    data = items |> Enum.drop(offset) |> Enum.take(per_page)

    %{
      data: data,
      meta: %{page: page, per_page: per_page, total: total, total_pages: total_pages}
    }
  end

  def serialize(struct, fields) do
    Map.take(struct, fields)
  end
end
ELIXIR,
        ];

        // ── 12 · Plugs y Middleware ─────────────────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Creación de Plugs',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula el sistema de Plugs de Phoenix.

```elixir
# plug_pipeline/2 → ejecuta una lista de plugs sobre un conn
# Cada plug es una función (conn → conn). Ejecuta en orden.
# Si un conn tiene halted: true, detiene la pipeline y retorna.
# Retorna conn final.

# auth_plug/1 → plug que verifica autenticación
# Si conn tiene :current_user en assigns → pasa (retorna conn)
# Si no → agrega status: 401, halted: true

# logger_plug/1 → plug que agrega log al conn
# Agrega al campo :logs (lista) un string: "#{method} #{path} at #{timestamp}"
# conn tiene :method y :path
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PhoenixPlugs do
  def plug_pipeline(conn, plugs) do
    # Ejecuta lista de plugs en secuencia
  end

  def auth_plug(conn) do
    # Verifica autenticación
  end

  def logger_plug(conn) do
    # Agrega log entry al conn
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PhoenixPlugs do
  def plug_pipeline(conn, plugs) do
    Enum.reduce_while(plugs, conn, fn plug, acc ->
      result = plug.(acc)
      if Map.get(result, :halted, false) do
        {:halt, result}
      else
        {:cont, result}
      end
    end)
  end

  def auth_plug(conn) do
    if Map.has_key?(Map.get(conn, :assigns, %{}), :current_user) do
      conn
    else
      conn
      |> Map.put(:status, 401)
      |> Map.put(:halted, true)
    end
  end

  def logger_plug(conn) do
    log = "#{conn.method} #{conn.path} at #{:os.system_time(:second)}"
    logs = Map.get(conn, :logs, [])
    Map.put(conn, :logs, logs ++ [log])
  end
end
ELIXIR,
        ];

        // ── 13 · Contextos (Bounded Contexts) ──────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Contextos de dominio',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula el patrón de Contextos de Phoenix.

```elixir
# Implementa un módulo Catalog que gestiona productos en memoria.
# Usa un agente (Agent o mapa simple) como almacenamiento.

# list_products/1 → recibe store (lista de productos), retorna todos
# get_product/2  → recibe store y id, retorna {:ok, product} o {:error, :not_found}
# create_product/2 → recibe store y attrs map (%{name: x, price: y})
#   Genera id autoincremental (max id + 1 o 1 si vacío)
#   Retorna {updated_store, product_creado}
# update_product/3 → recibe store, id, attrs. Merge attrs en el producto.
#   Retorna {updated_store, {:ok, product}} o {store, {:error, :not_found}}
# delete_product/2 → recibe store e id.
#   Retorna {updated_store, :ok} o {store, {:error, :not_found}}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule Catalog do
  def list_products(store), do: # retorna todos

  def get_product(store, id) do
    # Busca por id
  end

  def create_product(store, attrs) do
    # Crea producto con id autoincremental
  end

  def update_product(store, id, attrs) do
    # Actualiza producto existente
  end

  def delete_product(store, id) do
    # Elimina producto por id
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule Catalog do
  def list_products(store), do: store

  def get_product(store, id) do
    case Enum.find(store, fn p -> p.id == id end) do
      nil     -> {:error, :not_found}
      product -> {:ok, product}
    end
  end

  def create_product(store, attrs) do
    id =
      case store do
        [] -> 1
        _  -> (store |> Enum.map(& &1.id) |> Enum.max()) + 1
      end

    product = Map.put(attrs, :id, id)
    {store ++ [product], product}
  end

  def update_product(store, id, attrs) do
    case Enum.find_index(store, fn p -> p.id == id end) do
      nil ->
        {store, {:error, :not_found}}

      index ->
        updated = Map.merge(Enum.at(store, index), attrs)
        new_store = List.replace_at(store, index, updated)
        {new_store, {:ok, updated}}
    end
  end

  def delete_product(store, id) do
    case Enum.find(store, fn p -> p.id == id end) do
      nil -> {store, {:error, :not_found}}
      _   -> {Enum.reject(store, fn p -> p.id == id end), :ok}
    end
  end
end
ELIXIR,
        ];

        // ── 14 · Testing en Phoenix ─────────────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Testing helpers y assertions',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Implementa helpers de testing simulados para Phoenix.

```elixir
# assert_response/2 → verifica status de respuesta
# Recibe conn (mapa con :status) y expected status (integer)
# Retorna :ok si coincide, raise "Expected #{expected}, got #{actual}" si no

# build_conn/3 → construye un conn de test
# Recibe method (string), path (string) y params (map, default %{})
# Retorna: %{method: method, path: path, params: params, status: nil,
#            assigns: %{}, resp_body: nil, halted: false}

# assert_json/2 → verifica que resp_body contenga las keys esperadas
# Recibe conn y lista de keys (atoms)
# Retorna :ok si todas existen en resp_body (que es un mapa)
# Raise "Missing keys: [...]" con las faltantes
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PhoenixTest do
  def assert_response(conn, expected_status) do
    # Verifica status HTTP
  end

  def build_conn(method, path, params \\ %{}) do
    # Construye conn de test
  end

  def assert_json(conn, expected_keys) do
    # Verifica keys en respuesta JSON
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PhoenixTest do
  def assert_response(conn, expected_status) do
    if conn.status == expected_status do
      :ok
    else
      raise "Expected #{expected_status}, got #{conn.status}"
    end
  end

  def build_conn(method, path, params \\ %{}) do
    %{
      method: method,
      path: path,
      params: params,
      status: nil,
      assigns: %{},
      resp_body: nil,
      halted: false
    }
  end

  def assert_json(conn, expected_keys) do
    body = conn.resp_body || %{}
    present = Map.keys(body) |> MapSet.new()
    expected = MapSet.new(expected_keys)
    missing = MapSet.difference(expected, present) |> MapSet.to_list()

    if missing == [] do
      :ok
    else
      raise "Missing keys: #{inspect(missing)}"
    end
  end
end
ELIXIR,
        ];

        // ── 15 · GenServer en Phoenix ───────────────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'GenServer para cache en Phoenix',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula un cache en memoria usando el patrón GenServer (sin proceso real).

```elixir
# init/0 → retorna estado inicial del cache: %{store: %{}, hits: 0, misses: 0}

# handle_get/2 → recibe state y key
# Si existe: retorna {valor, state con hits+1}
# Si no: retorna {nil, state con misses+1}

# handle_put/3 → recibe state, key y value
# Almacena y retorna {:ok, state_actualizado}

# handle_delete/2 → recibe state y key
# Elimina key si existe. Retorna {:ok, state_actualizado}

# stats/1 → retorna %{size: n, hits: h, misses: m, hit_rate: float}
# hit_rate = hits / (hits + misses). Si ambos son 0, hit_rate = 0.0
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule CacheServer do
  def init do
    # Estado inicial del cache
  end

  def handle_get(state, key) do
    # Busca en cache
  end

  def handle_put(state, key, value) do
    # Almacena en cache
  end

  def handle_delete(state, key) do
    # Elimina del cache
  end

  def stats(state) do
    # Estadísticas del cache
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule CacheServer do
  def init do
    %{store: %{}, hits: 0, misses: 0}
  end

  def handle_get(state, key) do
    case Map.fetch(state.store, key) do
      {:ok, value} -> {value, %{state | hits: state.hits + 1}}
      :error       -> {nil, %{state | misses: state.misses + 1}}
    end
  end

  def handle_put(state, key, value) do
    {:ok, %{state | store: Map.put(state.store, key, value)}}
  end

  def handle_delete(state, key) do
    {:ok, %{state | store: Map.delete(state.store, key)}}
  end

  def stats(state) do
    total = state.hits + state.misses

    hit_rate =
      if total == 0, do: 0.0, else: state.hits / total

    %{
      size: map_size(state.store),
      hits: state.hits,
      misses: state.misses,
      hit_rate: hit_rate
    }
  end
end
ELIXIR,
        ];

        // ── 16 · Oban: Background Jobs ──────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulación de Job Queue',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula un sistema de background jobs inspirado en Oban.

```elixir
# new_queue/0 → %{jobs: [], completed: [], failed: [], next_id: 1}

# enqueue/3 → recibe queue, worker (string) y args (map)
# Crea job: %{id: next_id, worker: worker, args: args, status: :available,
#             attempts: 0, max_attempts: 3}
# Retorna {queue_actualizada, job}

# process_next/2 → recibe queue y perform_fn (función job → :ok | {:error, reason})
# Toma el primer job :available, lo ejecuta.
# Si :ok → mueve a completed con status: :completed
# Si {:error, r} → incrementa attempts.
#   Si attempts >= max_attempts → mueve a failed con status: :discarded
#   Sino → lo deja :available para reintentar
# Retorna {queue, {:ok, job}} o {queue, {:error, reason, job}} o {queue, :empty}

# queue_stats/1 → %{available: n, completed: n, failed: n}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule JobQueue do
  def new_queue do
    # Estado inicial de la cola
  end

  def enqueue(queue, worker, args) do
    # Encola un nuevo job
  end

  def process_next(queue, perform_fn) do
    # Procesa el siguiente job disponible
  end

  def queue_stats(queue) do
    # Estadísticas de la cola
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule JobQueue do
  def new_queue do
    %{jobs: [], completed: [], failed: [], next_id: 1}
  end

  def enqueue(queue, worker, args) do
    job = %{
      id: queue.next_id,
      worker: worker,
      args: args,
      status: :available,
      attempts: 0,
      max_attempts: 3
    }

    updated = %{queue | jobs: queue.jobs ++ [job], next_id: queue.next_id + 1}
    {updated, job}
  end

  def process_next(queue, perform_fn) do
    case Enum.find_index(queue.jobs, fn j -> j.status == :available end) do
      nil ->
        {queue, :empty}

      index ->
        job = Enum.at(queue.jobs, index)
        job = %{job | attempts: job.attempts + 1}

        case perform_fn.(job) do
          :ok ->
            completed_job = %{job | status: :completed}
            jobs = List.delete_at(queue.jobs, index)
            updated = %{queue | jobs: jobs, completed: queue.completed ++ [completed_job]}
            {updated, {:ok, completed_job}}

          {:error, reason} ->
            if job.attempts >= job.max_attempts do
              failed_job = %{job | status: :discarded}
              jobs = List.delete_at(queue.jobs, index)
              updated = %{queue | jobs: jobs, failed: queue.failed ++ [failed_job]}
              {updated, {:error, reason, failed_job}}
            else
              jobs = List.replace_at(queue.jobs, index, job)
              updated = %{queue | jobs: jobs}
              {updated, {:error, reason, job}}
            end
        end
    end
  end

  def queue_stats(queue) do
    available = Enum.count(queue.jobs, fn j -> j.status == :available end)
    %{available: available, completed: length(queue.completed), failed: length(queue.failed)}
  end
end
ELIXIR,
        ];

        // ── 17 · Seguridad ──────────────────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Seguridad en Phoenix',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Implementa funciones de seguridad para aplicaciones Phoenix.

```elixir
# csrf_token/0 → genera token CSRF simulado
# Retorna string aleatorio de 32 caracteres (hex)
# Usa :crypto.strong_rand_bytes(16) |> Base.encode16(case: :lower)

# verify_csrf/2 → compara token del form con token de sesión
# Retorna :ok o {:error, :invalid_csrf_token}

# sanitize_input/1 → limpia input peligroso
# Elimina tags HTML/script, trim whitespace
# Remueve patrones: <script>...</script>, <tag>, </tag>, <%...%>

# rate_limit/3 → recibe mapa de contadores %{ip => count}, ip y límite
# Si count < límite → {:allow, updated_counters}
# Si count >= límite → {:deny, counters}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PhoenixSecurity do
  def csrf_token do
    # Genera token CSRF
  end

  def verify_csrf(form_token, session_token) do
    # Verifica CSRF token
  end

  def sanitize_input(input) do
    # Limpia input peligroso
  end

  def rate_limit(counters, ip, limit) do
    # Rate limiting por IP
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PhoenixSecurity do
  def csrf_token do
    :crypto.strong_rand_bytes(16) |> Base.encode16(case: :lower)
  end

  def verify_csrf(form_token, session_token) do
    if form_token == session_token do
      :ok
    else
      {:error, :invalid_csrf_token}
    end
  end

  def sanitize_input(input) do
    input
    |> String.replace(~r/<script[^>]*>.*?<\/script>/is, "")
    |> String.replace(~r/<%.*?%>/s, "")
    |> String.replace(~r/<\/?[^>]+>/, "")
    |> String.trim()
  end

  def rate_limit(counters, ip, limit) do
    count = Map.get(counters, ip, 0)

    if count < limit do
      {:allow, Map.put(counters, ip, count + 1)}
    else
      {:deny, counters}
    end
  end
end
ELIXIR,
        ];

        // ── 18 · Deploy y Producción ────────────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Deploy y configuración de producción',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula configuración y herramientas de deploy para Phoenix.

```elixir
# runtime_config/1 → lee variables de entorno de un mapa (simula System.get_env)
# Recibe mapa %{"DATABASE_URL" => "...", "SECRET_KEY_BASE" => "...", ...}
# Retorna: %{database_url: url, secret_key_base: key, port: port, pool_size: size}
# PORT default "4000", POOL_SIZE default "10" (convertir a integer)
# Si DATABASE_URL o SECRET_KEY_BASE faltan → raise "Missing required env: ..."

# health_check/1 → simula health check del sistema
# Recibe mapa %{database: :ok|:error, migrations: :ok|:pending}
# Retorna: %{status: :healthy|:unhealthy, checks: mapa_con_resultados}
# Es :healthy solo si todos son :ok

# release_commands/0 → retorna lista de comandos de deploy
# ["migrate", "seed", "create_admin"]
# Como mapa: [{cmd, descripción}]
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PhoenixDeploy do
  def runtime_config(env) do
    # Lee configuración de entorno
  end

  def health_check(services) do
    # Verifica salud del sistema
  end

  def release_commands do
    # Lista de comandos de release
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PhoenixDeploy do
  def runtime_config(env) do
    database_url = Map.get(env, "DATABASE_URL") ||
      raise "Missing required env: DATABASE_URL"

    secret_key_base = Map.get(env, "SECRET_KEY_BASE") ||
      raise "Missing required env: SECRET_KEY_BASE"

    port = env |> Map.get("PORT", "4000") |> String.to_integer()
    pool_size = env |> Map.get("POOL_SIZE", "10") |> String.to_integer()

    %{
      database_url: database_url,
      secret_key_base: secret_key_base,
      port: port,
      pool_size: pool_size
    }
  end

  def health_check(services) do
    all_ok = Enum.all?(services, fn {_k, v} -> v == :ok end)

    %{
      status: if(all_ok, do: :healthy, else: :unhealthy),
      checks: services
    }
  end

  def release_commands do
    [
      {"migrate", "Ejecuta migraciones pendientes de Ecto"},
      {"seed", "Ejecuta seeds para poblar datos iniciales"},
      {"create_admin", "Crea usuario administrador del sistema"}
    ]
  end
end
ELIXIR,
        ];

        // ── 19 · Preguntas de Entrevista ────────────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Patrones de entrevista Phoenix',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Implementa patrones comunes en preguntas de entrevista sobre Phoenix.

```elixir
# explain_architecture/0 → retorna mapa con las capas de Phoenix
# Claves: :endpoint, :router, :controller, :view, :context, :schema
# Valores: descripción de una línea de cada capa

# compare_frameworks/1 → recibe framework ("rails"|"django"|"express"|"nextjs")
# Retorna mapa: %{framework: name, similarities: [str], differences: [str],
#                 phoenix_advantage: str}
# Si framework desconocido → %{error: "Unknown framework"}

# design_system/1 → recibe tipo de app ("chat"|"ecommerce"|"iot"|"api")
# Retorna: %{type: tipo, components: [lista de componentes Phoenix a usar],
#            reason: str explicando por qué Phoenix es bueno para esto}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PhoenixInterview do
  def explain_architecture do
    # Capas de la arquitectura Phoenix
  end

  def compare_frameworks(framework) do
    # Compara Phoenix con otro framework
  end

  def design_system(app_type) do
    # Diseña sistema usando componentes Phoenix
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PhoenixInterview do
  def explain_architecture do
    %{
      endpoint: "Punto de entrada HTTP: maneja conexiones, SSL, parseo de requests",
      router: "Enruta requests a controllers/LiveViews según path y método HTTP",
      controller: "Recibe requests, invoca contextos de negocio, envía respuestas",
      view: "Prepara datos para presentación y renderiza templates HEEx",
      context: "Módulo de lógica de negocio que encapsula un dominio (bounded context)",
      schema: "Define estructura de datos y validaciones con Ecto (mapea a tabla SQL)"
    }
  end

  def compare_frameworks(framework) do
    case framework do
      "rails" ->
        %{
          framework: "rails",
          similarities: ["MVC", "Generators", "Migraciones", "Convention over configuration"],
          differences: ["Concurrencia BEAM vs threads Ruby", "LiveView vs Hotwire", "Immutabilidad"],
          phoenix_advantage: "Concurrencia masiva sin sacrificar productividad del desarrollador"
        }

      "django" ->
        %{
          framework: "django",
          similarities: ["Baterías incluidas", "ORM (Ecto vs Django ORM)", "Admin/auth generators"],
          differences: ["Functional vs OOP", "Channels nativos vs Django Channels", "Pattern matching"],
          phoenix_advantage: "WebSockets y real-time nativos sin dependencias externas"
        }

      "express" ->
        %{
          framework: "express",
          similarities: ["Middleware/Plugs", "JSON APIs", "Routing"],
          differences: ["Fault tolerance BEAM", "LiveView vs SPA", "Supervision trees"],
          phoenix_advantage: "Fault tolerance y supervisión sin crash de toda la aplicación"
        }

      "nextjs" ->
        %{
          framework: "nextjs",
          similarities: ["SSR", "Full-stack", "Real-time capable"],
          differences: ["LiveView vs React hydration", "Backend completo", "Sin JavaScript"],
          phoenix_advantage: "Stack unificado servidor: sin separación frontend/backend"
        }

      _ ->
        %{error: "Unknown framework"}
    end
  end

  def design_system(app_type) do
    case app_type do
      "chat" ->
        %{
          type: "chat",
          components: ["Channels", "PubSub", "LiveView", "Presence", "PostgreSQL"],
          reason: "Channels y Presence manejan miles de conexiones simultáneas eficientemente"
        }

      "ecommerce" ->
        %{
          type: "ecommerce",
          components: ["LiveView", "Ecto Multi", "Oban", "Contexts", "PostgreSQL"],
          reason: "Ecto Multi para transacciones seguras y Oban para jobs de pagos/emails"
        }

      "iot" ->
        %{
          type: "iot",
          components: ["Channels", "GenServer", "Supervisors", "PubSub", "TimescaleDB"],
          reason: "BEAM maneja millones de conexiones IoT con procesos livianos y fault tolerance"
        }

      "api" ->
        %{
          type: "api",
          components: ["Controllers JSON", "Pipeline :api", "Guardian/JWT", "Absinthe GraphQL"],
          reason: "Pipeline :api sin overhead de sesión/CSRF, con serialización JSON eficiente"
        }

      _ ->
        %{type: app_type, components: [], reason: "Tipo de aplicación no reconocido"}
    end
  end
end
ELIXIR,
        ];

        return $ex;
    }
}
