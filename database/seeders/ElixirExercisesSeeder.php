<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ElixirExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'elixir-funcional')->first();

        if (! $course) {
            $this->command->warn('Elixir course not found. Run CourseSeeder + ElixirLessonSeeder first.');
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

        $this->command->info('Elixir exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── 1 · Introducción a Elixir ───────────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Primeros pasos con Elixir',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Implementa funciones básicas para familiarizarte con Elixir.

```elixir
# greet/1 → recibe nombre, retorna "¡Hola, {nombre}!"
# greet("Ana") → "¡Hola, Ana!"

# elixir_info/0 → retorna mapa con info del lenguaje
# %{name: "Elixir", platform: "BEAM", paradigm: "functional", creator: "José Valim"}

# iex_command/1 → simula comandos de IEx
# "h" → "Muestra ayuda"
# "i" → "Inspecciona valor"
# "c" → "Compila archivo"
# "r" → "Recarga módulo"
# otro → "Comando desconocido"
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule ElixirIntro do
  def greet(name) do
    # Retorna saludo personalizado
  end

  def elixir_info do
    # Retorna mapa con información de Elixir
  end

  def iex_command(cmd) do
    # Simula comandos de IEx
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule ElixirIntro do
  def greet(name) do
    "¡Hola, #{name}!"
  end

  def elixir_info do
    %{
      name: "Elixir",
      platform: "BEAM",
      paradigm: "functional",
      creator: "José Valim"
    }
  end

  def iex_command(cmd) do
    case cmd do
      "h" -> "Muestra ayuda"
      "i" -> "Inspecciona valor"
      "c" -> "Compila archivo"
      "r" -> "Recarga módulo"
      _   -> "Comando desconocido"
    end
  end
end
ELIXIR,
        ];

        // ── 2 · Tipos de Datos y Variables ──────────────────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Tipos de datos en Elixir',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Trabaja con los tipos de datos básicos de Elixir.

```elixir
# type_of/1 → retorna el tipo como átomo
# type_of(:ok) → :atom
# type_of("hi") → :string (binary)
# type_of(42) → :integer
# type_of(3.14) → :float
# type_of(true) → :boolean
# type_of([1,2]) → :list
# type_of({1,2}) → :tuple
# type_of(%{}) → :map

# atom_to_string/1 → convierte átomo a string sin los dos puntos
# atom_to_string(:hello) → "hello"

# parse_bool/1 → parsea string a booleano
# "true" → true, "false" → false, otro → :error
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule ElixirTypes do
  def type_of(value) do
    # Retorna el tipo como átomo
  end

  def atom_to_string(atom) do
    # Convierte átomo a string
  end

  def parse_bool(str) do
    # Parsea string a booleano
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule ElixirTypes do
  def type_of(value) when is_atom(value) and value in [true, false], do: :boolean
  def type_of(value) when is_atom(value), do: :atom
  def type_of(value) when is_binary(value), do: :string
  def type_of(value) when is_integer(value), do: :integer
  def type_of(value) when is_float(value), do: :float
  def type_of(value) when is_list(value), do: :list
  def type_of(value) when is_tuple(value), do: :tuple
  def type_of(value) when is_map(value), do: :map
  def type_of(_), do: :unknown

  def atom_to_string(atom) do
    Atom.to_string(atom)
  end

  def parse_bool("true"), do: true
  def parse_bool("false"), do: false
  def parse_bool(_), do: :error
end
ELIXIR,
        ];

        // ── 3 · Colecciones ─────────────────────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Colecciones de Elixir',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Practica con listas, tuplas, maps y keyword lists.

```elixir
# list_operations/1 → recibe lista, retorna mapa con operaciones
# %{head: primer_elem, tail: resto, length: longitud, reversed: lista_invertida}
# Si lista vacía: %{head: nil, tail: [], length: 0, reversed: []}

# keyword_get/3 → obtiene valor de keyword list con default
# keyword_get([name: "Ana", age: 25], :name, nil) → "Ana"
# keyword_get([name: "Ana"], :city, "Unknown") → "Unknown"

# map_transform/2 → transforma valores de un mapa con función
# map_transform(%{a: 1, b: 2}, fn x -> x * 2 end) → %{a: 2, b: 4}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule ElixirCollections do
  def list_operations(list) do
    # Retorna mapa con operaciones sobre la lista
  end

  def keyword_get(kwlist, key, default) do
    # Obtiene valor de keyword list
  end

  def map_transform(map, fun) do
    # Transforma valores del mapa
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule ElixirCollections do
  def list_operations([]) do
    %{head: nil, tail: [], length: 0, reversed: []}
  end

  def list_operations([head | tail] = list) do
    %{
      head: head,
      tail: tail,
      length: length(list),
      reversed: Enum.reverse(list)
    }
  end

  def keyword_get(kwlist, key, default) do
    Keyword.get(kwlist, key, default)
  end

  def map_transform(map, fun) do
    Map.new(map, fn {k, v} -> {k, fun.(v)} end)
  end
end
ELIXIR,
        ];

        // ── 4 · Pattern Matching Avanzado ───────────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Pattern matching en profundidad',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Domina el pattern matching, corazón de Elixir.

```elixir
# extract_user/1 → extrae datos de tupla de usuario
# extract_user({:user, "Ana", 25, :admin})
# → %{name: "Ana", age: 25, role: :admin}
# Si no coincide el patrón → :invalid_format

# match_list/1 → analiza estructura de lista
# [] → :empty
# [x] → {:single, x}
# [a, b] → {:pair, a, b}
# [h | t] con length > 2 → {:many, h, length(t)}

# pin_match/2 → usa pin operator para comparar
# pin_match(5, [3, 5, 7]) → true  (5 está en la lista)
# pin_match(9, [3, 5, 7]) → false
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PatternMatching do
  def extract_user(tuple) do
    # Extrae datos de tupla de usuario
  end

  def match_list(list) do
    # Analiza estructura de lista
  end

  def pin_match(value, list) do
    # Verifica si value está en list usando pattern matching
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PatternMatching do
  def extract_user({:user, name, age, role}) do
    %{name: name, age: age, role: role}
  end

  def extract_user(_), do: :invalid_format

  def match_list([]), do: :empty
  def match_list([x]), do: {:single, x}
  def match_list([a, b]), do: {:pair, a, b}
  def match_list([h | t]), do: {:many, h, length(t)}

  def pin_match(value, list) do
    Enum.any?(list, fn
      ^value -> true
      _ -> false
    end)
  end
end
ELIXIR,
        ];

        // ── 5 · Funciones ───────────────────────────────────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Funciones y el pipe operator',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Practica con funciones anónimas, captura y pipe.

```elixir
# apply_twice/2 → aplica función dos veces
# apply_twice(5, fn x -> x * 2 end) → 20

# compose/2 → compone dos funciones (f después g)
# f = fn x -> x + 1 end
# g = fn x -> x * 2 end
# compose(f, g).(3) → 7  # (3 * 2) + 1

# pipeline_transform/1 → transforma string usando pipes
# Recibe string. Aplica: trim → downcase → split por " " → length
# pipeline_transform("  Hola Mundo  ") → 2
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule ElixirFunctions do
  def apply_twice(value, fun) do
    # Aplica función dos veces
  end

  def compose(f, g) do
    # Retorna función compuesta
  end

  def pipeline_transform(str) do
    # Transforma string usando pipe operator
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule ElixirFunctions do
  def apply_twice(value, fun) do
    value |> fun.() |> fun.()
  end

  def compose(f, g) do
    fn x -> f.(g.(x)) end
  end

  def pipeline_transform(str) do
    str
    |> String.trim()
    |> String.downcase()
    |> String.split(" ")
    |> length()
  end
end
ELIXIR,
        ];

        // ── 6 · Módulos y Structs ───────────────────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Módulos y Structs',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Define módulos y structs personalizados.

```elixir
# Define el struct User con campos: name, email, role (default :user)

# new_user/2 → crea User con name y email
# new_user("Ana", "ana@mail.com")
# → %User{name: "Ana", email: "ana@mail.com", role: :user}

# promote/1 → cambia role a :admin
# promote(%User{name: "Ana", email: "a@b.com", role: :user})
# → %User{name: "Ana", email: "a@b.com", role: :admin}

# valid_email?/1 → verifica que email contenga @
# valid_email?(%User{email: "test@mail.com"}) → true
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule User do
  # Define el struct con campos: name, email, role

  def new_user(name, email) do
    # Crea nuevo User
  end

  def promote(user) do
    # Promueve a admin
  end

  def valid_email?(user) do
    # Verifica email contiene @
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule User do
  defstruct [:name, :email, role: :user]

  def new_user(name, email) do
    %User{name: name, email: email}
  end

  def promote(%User{} = user) do
    %User{user | role: :admin}
  end

  def valid_email?(%User{email: email}) do
    String.contains?(email, "@")
  end
end
ELIXIR,
        ];

        // ── 7 · Control de Flujo ────────────────────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Control de flujo',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Usa case, cond, with y comprehensions.

```elixir
# grade_letter/1 → retorna letra según nota (case)
# 90-100 → "A", 80-89 → "B", 70-79 → "C", 60-69 → "D", <60 → "F"

# fizzbuzz/1 → implementa fizzbuzz con cond
# Divisible por 15 → "FizzBuzz"
# Divisible por 3 → "Fizz"
# Divisible por 5 → "Buzz"
# Otro → el número como string

# even_squares/1 → usa comprehension
# Recibe lista de números, retorna lista con cuadrados de los pares
# even_squares([1, 2, 3, 4]) → [4, 16]
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule ControlFlow do
  def grade_letter(score) do
    # Usa case para determinar letra
  end

  def fizzbuzz(n) do
    # Usa cond para fizzbuzz
  end

  def even_squares(list) do
    # Usa comprehension para cuadrados de pares
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule ControlFlow do
  def grade_letter(score) do
    case score do
      s when s >= 90 and s <= 100 -> "A"
      s when s >= 80 -> "B"
      s when s >= 70 -> "C"
      s when s >= 60 -> "D"
      _ -> "F"
    end
  end

  def fizzbuzz(n) do
    cond do
      rem(n, 15) == 0 -> "FizzBuzz"
      rem(n, 3) == 0  -> "Fizz"
      rem(n, 5) == 0  -> "Buzz"
      true            -> to_string(n)
    end
  end

  def even_squares(list) do
    for x <- list, rem(x, 2) == 0, do: x * x
  end
end
ELIXIR,
        ];

        // ── 8 · Recursión y Enumerables ─────────────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Recursión y Enum/Stream',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Implementa funciones recursivas y usa Enum/Stream.

```elixir
# sum_recursive/1 → suma lista usando recursión con acumulador
# sum_recursive([1, 2, 3, 4]) → 10

# factorial/1 → calcula factorial recursivamente
# factorial(5) → 120

# lazy_transform/1 → usa Stream para transformación lazy
# Recibe lista, retorna Stream que: filtra pares → multiplica x3
# lazy_transform([1,2,3,4]) |> Enum.to_list() → [6, 12]
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule Recursion do
  def sum_recursive(list) do
    # Suma con recursión y acumulador
  end

  def factorial(n) do
    # Factorial recursivo
  end

  def lazy_transform(list) do
    # Stream: filtrar pares, multiplicar x3
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule Recursion do
  def sum_recursive(list), do: do_sum(list, 0)

  defp do_sum([], acc), do: acc
  defp do_sum([h | t], acc), do: do_sum(t, acc + h)

  def factorial(0), do: 1
  def factorial(n) when n > 0, do: n * factorial(n - 1)

  def lazy_transform(list) do
    list
    |> Stream.filter(&(rem(&1, 2) == 0))
    |> Stream.map(&(&1 * 3))
  end
end
ELIXIR,
        ];

        // ── 9 · Procesos y Concurrencia ─────────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Procesos y mensajería',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula el modelo de actores de Elixir.

```elixir
# spawn_message/0 → simula spawn y send
# Retorna tupla {pid, mailbox} donde:
# pid = make_ref() (simulamos PID)
# mailbox = [] (lista de mensajes)

# send_message/2 → agrega mensaje al mailbox
# send_message({pid, mailbox}, :hello) → {pid, [:hello]}

# receive_message/1 → saca primer mensaje del mailbox
# receive_message({pid, [:a, :b]}) → {:a, {pid, [:b]}}
# Si vacío → {:empty, state}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule ProcessSim do
  def spawn_message do
    # Crea proceso simulado
  end

  def send_message({pid, mailbox}, msg) do
    # Envía mensaje al mailbox
  end

  def receive_message({pid, mailbox}) do
    # Recibe primer mensaje
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule ProcessSim do
  def spawn_message do
    {make_ref(), []}
  end

  def send_message({pid, mailbox}, msg) do
    {pid, mailbox ++ [msg]}
  end

  def receive_message({pid, []}) do
    {:empty, {pid, []}}
  end

  def receive_message({pid, [msg | rest]}) do
    {msg, {pid, rest}}
  end
end
ELIXIR,
        ];

        // ── 10 · GenServer ──────────────────────────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulación de GenServer',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula el comportamiento de GenServer sin procesos reales.

```elixir
# init/1 → recibe valor inicial, retorna {:ok, state}
# init(0) → {:ok, %{counter: 0}}

# handle_call/2 → maneja llamadas síncronas
# handle_call(:get, state) → {:reply, state.counter, state}
# handle_call({:add, n}, state) → {:reply, :ok, %{state | counter: state.counter + n}}

# handle_cast/2 → maneja llamadas asíncronas
# handle_cast(:reset, state) → {:noreply, %{state | counter: 0}}
# handle_cast({:set, n}, state) → {:noreply, %{state | counter: n}}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule CounterServer do
  def init(initial_value) do
    # Inicializa estado
  end

  def handle_call(request, state) do
    # Maneja calls síncronos
  end

  def handle_cast(request, state) do
    # Maneja casts asíncronos
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule CounterServer do
  def init(initial_value) do
    {:ok, %{counter: initial_value}}
  end

  def handle_call(:get, state) do
    {:reply, state.counter, state}
  end

  def handle_call({:add, n}, state) do
    {:reply, :ok, %{state | counter: state.counter + n}}
  end

  def handle_cast(:reset, state) do
    {:noreply, %{state | counter: 0}}
  end

  def handle_cast({:set, n}, state) do
    {:noreply, %{state | counter: n}}
  end
end
ELIXIR,
        ];

        // ── 11 · Supervisión y OTP ──────────────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Supervisores y estrategias',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula el comportamiento de supervisores OTP.

```elixir
# child_spec/2 → crea especificación de child
# child_spec(:worker1, MyWorker)
# → %{id: :worker1, start: {MyWorker, :start_link, []}, restart: :permanent}

# supervisor_strategy/1 → describe estrategia
# :one_for_one → "Reinicia solo el proceso fallido"
# :one_for_all → "Reinicia todos los children"
# :rest_for_one → "Reinicia el fallido y los iniciados después"

# supervision_tree/1 → simula árbol dado lista de children
# Recibe lista de %{id, status} donde status es :running | :crashed
# Retorna: %{total: n, running: n, crashed: n, health: :healthy|:degraded|:critical}
# healthy si crashed=0, degraded si crashed<total/2, critical si crashed>=total/2
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule SupervisorSim do
  def child_spec(id, module) do
    # Crea child spec
  end

  def supervisor_strategy(strategy) do
    # Describe estrategia
  end

  def supervision_tree(children) do
    # Analiza estado del árbol
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule SupervisorSim do
  def child_spec(id, module) do
    %{
      id: id,
      start: {module, :start_link, []},
      restart: :permanent
    }
  end

  def supervisor_strategy(:one_for_one), do: "Reinicia solo el proceso fallido"
  def supervisor_strategy(:one_for_all), do: "Reinicia todos los children"
  def supervisor_strategy(:rest_for_one), do: "Reinicia el fallido y los iniciados después"
  def supervisor_strategy(_), do: "Estrategia desconocida"

  def supervision_tree(children) do
    total = length(children)
    crashed = Enum.count(children, fn c -> c.status == :crashed end)
    running = total - crashed

    health =
      cond do
        crashed == 0 -> :healthy
        crashed < total / 2 -> :degraded
        true -> :critical
      end

    %{total: total, running: running, crashed: crashed, health: health}
  end
end
ELIXIR,
        ];

        // ── 12 · Mix y Gestión de Proyectos ─────────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mix y configuración',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula operaciones comunes de Mix.

```elixir
# mix_task/1 → describe lo que hace cada task
# "new" → "Crea nuevo proyecto Elixir"
# "deps.get" → "Descarga dependencias"
# "compile" → "Compila el proyecto"
# "test" → "Ejecuta tests"
# "format" → "Formatea código"
# otro → "Task desconocida"

# parse_mix_exs/1 → parsea string simple de mix.exs
# Recibe string tipo "app: :myapp\nversion: 1.0.0\nelixir: ~> 1.14"
# Retorna: %{app: "myapp", version: "1.0.0", elixir: "~> 1.14"}

# env_config/1 → retorna config según ambiente
# :dev → %{debug: true, log_level: :debug}
# :test → %{debug: false, log_level: :warn}
# :prod → %{debug: false, log_level: :info}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule MixSim do
  def mix_task(task) do
    # Describe task de mix
  end

  def parse_mix_exs(content) do
    # Parsea contenido de mix.exs simplificado
  end

  def env_config(env) do
    # Retorna config según ambiente
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule MixSim do
  def mix_task(task) do
    case task do
      "new"      -> "Crea nuevo proyecto Elixir"
      "deps.get" -> "Descarga dependencias"
      "compile"  -> "Compila el proyecto"
      "test"     -> "Ejecuta tests"
      "format"   -> "Formatea código"
      _          -> "Task desconocida"
    end
  end

  def parse_mix_exs(content) do
    content
    |> String.split("\n", trim: true)
    |> Enum.reduce(%{}, fn line, acc ->
      case String.split(line, ": ", parts: 2) do
        [key, value] ->
          key = key |> String.trim() |> String.to_atom()
          value = value |> String.trim() |> String.trim_leading(":") |> String.trim()
          Map.put(acc, key, value)
        _ ->
          acc
      end
    end)
  end

  def env_config(:dev), do: %{debug: true, log_level: :debug}
  def env_config(:test), do: %{debug: false, log_level: :warn}
  def env_config(:prod), do: %{debug: false, log_level: :info}
  def env_config(_), do: %{}
end
ELIXIR,
        ];

        // ── 13 · Testing con ExUnit ─────────────────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Testing con ExUnit',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula assertions y patrones de testing.

```elixir
# assert_equal/2 → compara dos valores
# Si iguales → :ok
# Si diferentes → {:error, %{expected: a, actual: b}}

# assert_match/2 → verifica pattern matching
# assert_match({:ok, _}, {:ok, 42}) → :ok
# assert_match({:ok, _}, {:error, :fail}) → {:error, :no_match}
# Puedes usar este truco: usar una función que intente match

# run_tests/1 → ejecuta lista de tests
# Cada test es {name, fun} donde fun retorna :ok o {:error, reason}
# Retorna: %{passed: n, failed: n, results: [{name, :pass|{:fail, reason}}]}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule TestRunner do
  def assert_equal(expected, actual) do
    # Compara valores
  end

  def assert_match(pattern_fn, value) do
    # Verifica match usando función
  end

  def run_tests(tests) do
    # Ejecuta lista de tests
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule TestRunner do
  def assert_equal(expected, actual) do
    if expected == actual do
      :ok
    else
      {:error, %{expected: expected, actual: actual}}
    end
  end

  def assert_match(pattern_fn, value) do
    try do
      if pattern_fn.(value), do: :ok, else: {:error, :no_match}
    rescue
      _ -> {:error, :no_match}
    end
  end

  def run_tests(tests) do
    results =
      Enum.map(tests, fn {name, fun} ->
        case fun.() do
          :ok -> {name, :pass}
          {:error, reason} -> {name, {:fail, reason}}
        end
      end)

    passed = Enum.count(results, fn {_, r} -> r == :pass end)
    failed = length(results) - passed

    %{passed: passed, failed: failed, results: results}
  end
end
ELIXIR,
        ];

        // ── 14 · Ecto y Bases de Datos ──────────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Changesets y queries Ecto',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula operaciones de Ecto sin base de datos real.

```elixir
# changeset/3 → crea changeset simulado
# Recibe: struct actual, params (mapa), campos requeridos (lista)
# Retorna: %{data: struct, changes: params_filtrados, errors: [], valid?: true/false}
# Error si campo requerido falta o está vacío: {campo, "can't be blank"}

# validate_length/4 → valida longitud de campo string en changeset
# Recibe: changeset, campo, min, max
# Agrega error si no cumple: {campo, "length must be between #{min} and #{max}"}

# build_query/2 → construye query simulada
# Recibe tabla (string) y conditions (keyword list)
# build_query("users", [name: "Ana", active: true])
# → "SELECT * FROM users WHERE name = 'Ana' AND active = true"
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule EctoSim do
  def changeset(struct, params, required) do
    # Crea changeset con validación
  end

  def validate_length(changeset, field, min, max) do
    # Valida longitud de campo
  end

  def build_query(table, conditions) do
    # Construye query SQL simulada
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule EctoSim do
  def changeset(struct, params, required) do
    errors =
      Enum.reduce(required, [], fn field, acc ->
        value = Map.get(params, field) || Map.get(params, to_string(field))
        if is_nil(value) or value == "" do
          [{field, "can't be blank"} | acc]
        else
          acc
        end
      end)
      |> Enum.reverse()

    %{
      data: struct,
      changes: params,
      errors: errors,
      valid?: errors == []
    }
  end

  def validate_length(changeset, field, min, max) do
    value = Map.get(changeset.changes, field, "")
    len = String.length(to_string(value))

    if len >= min and len <= max do
      changeset
    else
      error = {field, "length must be between #{min} and #{max}"}
      %{changeset | errors: changeset.errors ++ [error], valid?: false}
    end
  end

  def build_query(table, conditions) do
    where_clause =
      conditions
      |> Enum.map(fn {k, v} ->
        val = if is_binary(v), do: "'#{v}'", else: to_string(v)
        "#{k} = #{val}"
      end)
      |> Enum.join(" AND ")

    "SELECT * FROM #{table} WHERE #{where_clause}"
  end
end
ELIXIR,
        ];

        // ── 15 · Metaprogramación ───────────────────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Metaprogramación y macros',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula conceptos de metaprogramación de Elixir.

```elixir
# quote_ast/1 → simula quote, retorna representación AST simplificada
# quote_ast("1 + 2") → {:+, [1, 2]}
# quote_ast("x * y") → {:*, [:x, :y]}
# Solo soporta operaciones simples: +, -, *, /

# unquote_ast/1 → "evalúa" AST simple
# unquote_ast({:+, [1, 2]}) → 3
# unquote_ast({:*, [3, 4]}) → 12

# define_getter/1 → genera código de función getter como string
# define_getter(:name) → "def name(struct), do: struct.name"
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule MetaSim do
  def quote_ast(expr_string) do
    # Parsea expresión simple a AST
  end

  def unquote_ast(ast) do
    # Evalúa AST simple
  end

  def define_getter(field) do
    # Genera código de getter
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule MetaSim do
  def quote_ast(expr_string) do
    # Busca operadores en orden de precedencia
    cond do
      String.contains?(expr_string, "+") ->
        [a, b] = String.split(expr_string, "+", parts: 2)
        {:+, [parse_operand(a), parse_operand(b)]}

      String.contains?(expr_string, "-") ->
        [a, b] = String.split(expr_string, "-", parts: 2)
        {:-, [parse_operand(a), parse_operand(b)]}

      String.contains?(expr_string, "*") ->
        [a, b] = String.split(expr_string, "*", parts: 2)
        {:*, [parse_operand(a), parse_operand(b)]}

      String.contains?(expr_string, "/") ->
        [a, b] = String.split(expr_string, "/", parts: 2)
        {:/, [parse_operand(a), parse_operand(b)]}

      true ->
        {:error, :unsupported}
    end
  end

  defp parse_operand(str) do
    str = String.trim(str)
    case Integer.parse(str) do
      {n, ""} -> n
      _ -> String.to_atom(str)
    end
  end

  def unquote_ast({:+, [a, b]}), do: a + b
  def unquote_ast({:-, [a, b]}), do: a - b
  def unquote_ast({:*, [a, b]}), do: a * b
  def unquote_ast({:/, [a, b]}), do: div(a, b)

  def define_getter(field) do
    "def #{field}(struct), do: struct.#{field}"
  end
end
ELIXIR,
        ];

        // ── 16 · Protocolos y Behaviours ────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Protocolos y Behaviours',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula el polimorfismo de Elixir.

```elixir
# protocol_dispatch/2 → simula dispatch de protocolo
# Recibe nombre de protocolo (atom) y valor
# Retorna el tipo que manejaría ese valor
# protocol_dispatch(:String.Chars, 123) → Integer
# protocol_dispatch(:String.Chars, "hi") → BitString
# protocol_dispatch(:Enumerable, [1,2]) → List
# protocol_dispatch(:Enumerable, %{}) → Map

# behaviour_callbacks/1 → lista callbacks de behaviour conocido
# :GenServer → [:init, :handle_call, :handle_cast, :handle_info]
# :Supervisor → [:init]
# :Application → [:start, :stop]

# impl_check/2 → verifica si implementación tiene todos los callbacks
# Recibe behaviour (atom) y lista de funciones implementadas (atoms)
# Retorna {:ok, :complete} o {:error, missing: [...]}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule PolymorphismSim do
  def protocol_dispatch(protocol, value) do
    # Retorna tipo que maneja el valor
  end

  def behaviour_callbacks(behaviour) do
    # Lista callbacks del behaviour
  end

  def impl_check(behaviour, implemented) do
    # Verifica implementación completa
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule PolymorphismSim do
  def protocol_dispatch(_protocol, value) do
    cond do
      is_integer(value) -> Integer
      is_float(value) -> Float
      is_binary(value) -> BitString
      is_atom(value) -> Atom
      is_list(value) -> List
      is_map(value) -> Map
      is_tuple(value) -> Tuple
      true -> Any
    end
  end

  def behaviour_callbacks(:GenServer) do
    [:init, :handle_call, :handle_cast, :handle_info]
  end

  def behaviour_callbacks(:Supervisor), do: [:init]
  def behaviour_callbacks(:Application), do: [:start, :stop]
  def behaviour_callbacks(_), do: []

  def impl_check(behaviour, implemented) do
    required = behaviour_callbacks(behaviour)
    missing = required -- implemented

    if missing == [] do
      {:ok, :complete}
    else
      {:error, missing: missing}
    end
  end
end
ELIXIR,
        ];

        // ── 17 · Manejo de Errores ──────────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Manejo de errores idiomático',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Implementa patrones de manejo de errores estilo Elixir.

```elixir
# safe_divide/2 → división segura
# safe_divide(10, 2) → {:ok, 5.0}
# safe_divide(10, 0) → {:error, :division_by_zero}

# with_chain/1 → simula with para encadenar operaciones
# Recibe lista de funciones que retornan {:ok, val} o {:error, reason}
# Ejecuta en orden, si alguna falla retorna el error
# Si todas ok, retorna {:ok, valor_final}
# with_chain([fn -> {:ok, 1} end, fn -> {:ok, 2} end]) → {:ok, 2}
# with_chain([fn -> {:ok, 1} end, fn -> {:error, :fail} end]) → {:error, :fail}

# unwrap!/1 → extrae valor o lanza excepción
# unwrap!({:ok, 42}) → 42
# unwrap!({:error, reason}) → raise "Unwrap failed: #{reason}"
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule ErrorHandling do
  def safe_divide(a, b) do
    # División segura
  end

  def with_chain(funs) do
    # Encadena funciones con with-style
  end

  def unwrap!(result) do
    # Extrae valor o lanza excepción
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule ErrorHandling do
  def safe_divide(_a, 0), do: {:error, :division_by_zero}
  def safe_divide(a, b), do: {:ok, a / b}

  def with_chain(funs) do
    Enum.reduce_while(funs, {:ok, nil}, fn fun, _acc ->
      case fun.() do
        {:ok, val} -> {:cont, {:ok, val}}
        {:error, reason} -> {:halt, {:error, reason}}
      end
    end)
  end

  def unwrap!({:ok, value}), do: value
  def unwrap!({:error, reason}) do
    raise "Unwrap failed: #{inspect(reason)}"
  end
end
ELIXIR,
        ];

        // ── 18 · Deploy y Producción ────────────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Deploy y releases',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Simula configuración de deploy para Elixir.

```elixir
# runtime_config/1 → lee config de environment variables
# Recibe mapa simulando System.get_env
# Debe extraer: DATABASE_URL (requerido), SECRET_KEY_BASE (requerido),
#   PORT (default "4000"), POOL_SIZE (default "10")
# Retorna {:ok, %{database_url: ..., secret_key_base: ..., port: int, pool_size: int}}
# Si falta requerido: {:error, "Missing required env: CAMPO"}

# release_command/1 → describe comandos de release
# "migrate" → "Ejecuta migraciones de Ecto"
# "rollback" → "Revierte última migración"
# "remote_console" → "Conecta a nodo en ejecución"
# "eval" → "Evalúa expresión Elixir en el release"

# health_check/1 → verifica estado de servicios
# Recibe mapa %{db: :ok|:error, cache: :ok|:error}
# Retorna: %{healthy: bool, services: mapa_original}
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule DeploySim do
  def runtime_config(env) do
    # Lee configuración de entorno
  end

  def release_command(cmd) do
    # Describe comando de release
  end

  def health_check(services) do
    # Verifica estado de servicios
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule DeploySim do
  def runtime_config(env) do
    with {:ok, db_url} <- fetch_required(env, "DATABASE_URL"),
         {:ok, secret} <- fetch_required(env, "SECRET_KEY_BASE") do
      port = env |> Map.get("PORT", "4000") |> String.to_integer()
      pool = env |> Map.get("POOL_SIZE", "10") |> String.to_integer()

      {:ok, %{
        database_url: db_url,
        secret_key_base: secret,
        port: port,
        pool_size: pool
      }}
    end
  end

  defp fetch_required(env, key) do
    case Map.get(env, key) do
      nil -> {:error, "Missing required env: #{key}"}
      val -> {:ok, val}
    end
  end

  def release_command("migrate"), do: "Ejecuta migraciones de Ecto"
  def release_command("rollback"), do: "Revierte última migración"
  def release_command("remote_console"), do: "Conecta a nodo en ejecución"
  def release_command("eval"), do: "Evalúa expresión Elixir en el release"
  def release_command(_), do: "Comando desconocido"

  def health_check(services) do
    healthy = Enum.all?(services, fn {_k, v} -> v == :ok end)
    %{healthy: healthy, services: services}
  end
end
ELIXIR,
        ];

        // ── 19 · Preguntas de Entrevista ────────────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Patrones de entrevista Elixir',
            'language'     => 'elixir',
            'description'  => <<<'MD'
Implementa respuestas a preguntas comunes de entrevista.

```elixir
# explain_beam/0 → retorna mapa explicando la BEAM
# %{name: "BEAM", full_name: "...", key_features: [...], languages: [...]}
# key_features: al menos 3 características principales
# languages: lenguajes que corren en BEAM

# compare_concurrency/1 → compara modelo de Elixir con otro
# "python" → %{elixir: "Procesos BEAM livianos", other: "GIL limita threads", advantage: "..."}
# "java" → similar comparando con threads de JVM
# "go" → similar comparando con goroutines

# design_chat_system/0 → diseña sistema de chat con Elixir
# Retorna: %{components: [...], why_elixir: "...", scaling_strategy: "..."}
# components: lista de componentes OTP/Elixir que usarías
```
MD,
            'starter_code' => <<<'ELIXIR'
defmodule ElixirInterview do
  def explain_beam do
    # Explica la BEAM VM
  end

  def compare_concurrency(language) do
    # Compara concurrencia con otro lenguaje
  end

  def design_chat_system do
    # Diseña sistema de chat
  end
end
ELIXIR,
            'solution_code' => <<<'ELIXIR'
defmodule ElixirInterview do
  def explain_beam do
    %{
      name: "BEAM",
      full_name: "Bogdan's Erlang Abstract Machine",
      key_features: [
        "Procesos livianos (no hilos del SO)",
        "Garbage collection por proceso",
        "Tolerancia a fallos con supervisores",
        "Hot code reloading",
        "Distribución nativa entre nodos"
      ],
      languages: ["Erlang", "Elixir", "Gleam", "LFE"]
    }
  end

  def compare_concurrency("python") do
    %{
      elixir: "Procesos BEAM livianos, cero shared state, message passing",
      other: "GIL limita ejecución a un thread, asyncio es cooperativo",
      advantage: "Elixir escala a millones de procesos sin GIL ni locks"
    }
  end

  def compare_concurrency("java") do
    %{
      elixir: "Procesos BEAM con isolated heap, supervisión automática",
      other: "Threads del SO pesados, memoria compartida, sincronización manual",
      advantage: "Elixir evita deadlocks y race conditions por diseño"
    }
  end

  def compare_concurrency("go") do
    %{
      elixir: "Procesos BEAM con OTP, let-it-crash, distribución nativa",
      other: "Goroutines livianas, channels, pero sin supervisión nativa",
      advantage: "Elixir tiene 40 años de OTP para fault tolerance en producción"
    }
  end

  def compare_concurrency(_), do: %{error: "Lenguaje no soportado"}

  def design_chat_system do
    %{
      components: [
        "Phoenix Channels para WebSockets",
        "GenServer por sala para estado",
        "Presence para tracking de usuarios online",
        "PubSub para distribución entre nodos",
        "Supervisor para reinicio automático"
      ],
      why_elixir: "WebSockets nativos, millones de conexiones simultáneas, fault tolerance automático",
      scaling_strategy: "Cluster de nodos BEAM con libcluster, PubSub distribuido, sin estado compartido"
    }
  end
end
ELIXIR,
        ];

        return $ex;
    }
}
