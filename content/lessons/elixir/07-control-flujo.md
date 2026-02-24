# Control de Flujo en Elixir

El control de flujo en Elixir se basa fuertemente en el pattern matching, lo que resulta en un código más declarativo que imperativo. En lugar de largas cadenas de if-else, Elixir favorece construcciones como `case`, `cond` y `with` que expresan la intención del código de forma clara.

## case

`case` compara un valor contra múltiples patrones y ejecuta el bloque correspondiente al primero que coincida:

```elixir
resultado = {:ok, %{nombre: "Elixir", version: "1.16"}}

case resultado do
  {:ok, %{nombre: nombre, version: version}} ->
    "#{nombre} v#{version} encontrado"

  {:error, razon} ->
    "Error: #{razon}"

  _ ->
    "Resultado desconocido"
end
# => "Elixir v1.16 encontrado"
```

`case` admite guards para refinar los patrones:

```elixir
def clasificar_edad(edad) do
  case edad do
    n when n < 0 -> :invalido
    n when n < 13 -> :niño
    n when n < 18 -> :adolescente
    n when n < 65 -> :adulto
    _ -> :senior
  end
end
```

## cond

`cond` evalúa una serie de condiciones booleanas y ejecuta la primera que sea verdadera. Es útil cuando no hay un valor específico contra el cual hacer match:

```elixir
def calcular_descuento(total, es_miembro) do
  cond do
    total > 1000 and es_miembro -> total * 0.20
    total > 1000 -> total * 0.10
    total > 500 and es_miembro -> total * 0.10
    total > 500 -> total * 0.05
    es_miembro -> total * 0.03
    true -> 0  # Cláusula por defecto (como else)
  end
end

calcular_descuento(1500, true)   # => 300.0
calcular_descuento(600, false)   # => 30.0
```

## if / unless

`if` y `unless` son para condiciones simples. En Elixir son macros, no construcciones del lenguaje:

```elixir
# if básico
if edad >= 18 do
  "Mayor de edad"
else
  "Menor de edad"
end

# Forma abreviada
if edad >= 18, do: "Mayor", else: "Menor"

# unless (inverso de if)
unless lista_vacia?(lista) do
  procesar(lista)
end

# if retorna un valor
mensaje = if conectado?, do: "Online", else: "Offline"
```

Nota: evita anidar múltiples `if`. Usa `case` o `cond` en su lugar.

## with

`with` es ideal para encadenar operaciones que pueden fallar, evitando pirámides de case anidados:

```elixir
defmodule Registro do
  def crear_usuario(params) do
    with {:ok, email} <- validar_email(params["email"]),
         {:ok, nombre} <- validar_nombre(params["nombre"]),
         {:ok, usuario} <- guardar_usuario(%{email: email, nombre: nombre}) do
      {:ok, usuario}
    else
      {:error, :email_invalido} ->
        {:error, "El email no es válido"}
      {:error, :nombre_vacio} ->
        {:error, "El nombre no puede estar vacío"}
      {:error, :duplicado} ->
        {:error, "El usuario ya existe"}
    end
  end

  defp validar_email(email) do
    if String.contains?(email, "@"), do: {:ok, email}, else: {:error, :email_invalido}
  end

  defp validar_nombre(nombre) do
    if nombre && String.length(nombre) > 0, do: {:ok, nombre}, else: {:error, :nombre_vacio}
  end

  defp guardar_usuario(datos) do
    {:ok, Map.put(datos, :id, :rand.uniform(1000))}
  end
end
```

Si algún paso no coincide con el patrón `{:ok, _}`, `with` salta directamente al bloque `else`.

## Pattern Matching en el Control de Flujo

El pattern matching se integra en todas las estructuras de control:

```elixir
# En asignaciones condicionales
{:ok, contenido} = File.read("config.json")

# En funciones con múltiples cláusulas
defmodule API do
  def manejar_respuesta({:ok, %{status: 200, body: body}}) do
    {:ok, Jason.decode!(body)}
  end

  def manejar_respuesta({:ok, %{status: 404}}) do
    {:error, :no_encontrado}
  end

  def manejar_respuesta({:ok, %{status: status}}) when status >= 500 do
    {:error, :error_servidor}
  end

  def manejar_respuesta({:error, razon}) do
    {:error, razon}
  end
end
```

## Comprehensions (for)

Las comprehensions combinan generación, filtrado y transformación en una sola expresión:

```elixir
# Básica
for n <- 1..10, do: n * n
# => [1, 4, 9, 16, 25, 36, 49, 64, 81, 100]

# Con filtro
for n <- 1..20, rem(n, 3) == 0, do: n
# => [3, 6, 9, 12, 15, 18]

# Múltiples generadores (producto cartesiano)
for x <- [:a, :b], y <- [1, 2], do: {x, y}
# => [{:a, 1}, {:a, 2}, {:b, 1}, {:b, 2}]

# Con pattern matching
usuarios = [%{nombre: "Ana", activo: true}, %{nombre: "Bob", activo: false}]
for %{nombre: nombre, activo: true} <- usuarios, do: nombre
# => ["Ana"]

# Con :into para cambiar la colección resultado
for {k, v} <- %{a: 1, b: 2}, into: %{}, do: {k, v * 10}
# => %{a: 10, b: 20}
```

## Resumen

El control de flujo en Elixir se distingue por su integración profunda con el pattern matching. `case` permite ramificar basándose en patrones, `cond` evalúa condiciones booleanas, `with` encadena operaciones fallibles de forma limpia, y las comprehensions combinan iteración con filtrado y transformación. Estas herramientas eliminan la necesidad de estructuras de control anidadas y producen código más legible y mantenible.
