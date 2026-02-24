# Protocolos y Behaviours en Elixir

Elixir ofrece dos mecanismos principales para lograr polimorfismo y definir contratos: los protocolos para dispatch basado en el tipo de dato, y los behaviours para definir interfaces que los módulos deben implementar. Aunque pueden parecer similares, resuelven problemas diferentes y se complementan entre sí.

## Protocolos (Protocol)

Un protocolo define un conjunto de funciones que se pueden implementar para diferentes tipos de datos:

```elixir
defprotocol Serializable do
  @doc "Convierte el dato a formato JSON string"
  @fallback_to_any true
  def to_json(dato)
end

# El protocolo por sí solo no tiene implementación,
# necesita defimpl para cada tipo que lo soporte
```

## Implementación con defimpl

Cada tipo puede implementar un protocolo de forma independiente:

```elixir
defimpl Serializable, for: Map do
  def to_json(mapa) do
    pares = Enum.map(mapa, fn {k, v} ->
      "\"#{k}\": #{Serializable.to_json(v)}"
    end)
    "{#{Enum.join(pares, ", ")}}"
  end
end

defimpl Serializable, for: List do
  def to_json(lista) do
    elementos = Enum.map(lista, &Serializable.to_json/1)
    "[#{Enum.join(elementos, ", ")}]"
  end
end

defimpl Serializable, for: BitString do
  def to_json(string), do: "\"#{string}\""
end

defimpl Serializable, for: Integer do
  def to_json(numero), do: Integer.to_string(numero)
end

defimpl Serializable, for: Float do
  def to_json(numero), do: Float.to_string(numero)
end

defimpl Serializable, for: Atom do
  def to_json(nil), do: "null"
  def to_json(true), do: "true"
  def to_json(false), do: "false"
  def to_json(atomo), do: "\"#{Atom.to_string(atomo)}\""
end

defimpl Serializable, for: Any do
  def to_json(dato), do: "\"#{inspect(dato)}\""
end

# Uso
Serializable.to_json(%{nombre: "Ana", edad: 30})
# => "{\"nombre\": \"Ana\", \"edad\": 30}"
```

## Protocolos con Structs

Los structs pueden tener sus propias implementaciones:

```elixir
defmodule Producto do
  defstruct [:nombre, :precio, :sku]
end

defimpl Serializable, for: Producto do
  def to_json(%Producto{nombre: n, precio: p, sku: s}) do
    "{\"tipo\": \"producto\", \"nombre\": \"#{n}\", \"precio\": #{p}, \"sku\": \"#{s}\"}"
  end
end

defimpl String.Chars, for: Producto do
  def to_string(%Producto{nombre: n, precio: p}) do
    "#{n} ($#{p})"
  end
end

producto = %Producto{nombre: "Laptop", precio: 999.99, sku: "LAP-001"}
Serializable.to_json(producto)
# => "{\"tipo\": \"producto\", \"nombre\": \"Laptop\", \"precio\": 999.99, \"sku\": \"LAP-001\"}"
"Producto: #{producto}"
# => "Producto: Laptop ($999.99)"
```

## @derive: Implementación Automática

Algunos protocolos soportan `@derive` para implementación automática:

```elixir
defmodule Usuario do
  @derive {Inspect, only: [:nombre, :email]}
  @derive {Jason.Encoder, only: [:nombre, :email, :id]}
  defstruct [:id, :nombre, :email, :password_hash]
end

# Inspect solo mostrará :nombre y :email, ocultando :password_hash
```

## Behaviours

Los behaviours definen un contrato (interfaz) que los módulos deben cumplir:

```elixir
defmodule MiApp.Notificador do
  @doc "Envía una notificación al destinatario"
  @callback enviar(destinatario :: String.t(), mensaje :: String.t()) ::
    {:ok, String.t()} | {:error, String.t()}

  @callback disponible?() :: boolean()

  @doc "Callback opcional con implementación por defecto"
  @optional_callbacks [disponible?: 0]
end
```

## Implementar un Behaviour

```elixir
defmodule MiApp.NotificadorEmail do
  @behaviour MiApp.Notificador

  @impl MiApp.Notificador
  def enviar(email, mensaje) do
    # Lógica de envío de email
    IO.puts("Enviando email a #{email}: #{mensaje}")
    {:ok, "Email enviado"}
  end

  @impl MiApp.Notificador
  def disponible? do
    # Verificar conexión al servidor SMTP
    true
  end
end

defmodule MiApp.NotificadorSMS do
  @behaviour MiApp.Notificador

  @impl MiApp.Notificador
  def enviar(telefono, mensaje) do
    IO.puts("Enviando SMS a #{telefono}: #{mensaje}")
    {:ok, "SMS enviado"}
  end

  # disponible?/0 es opcional, no lo implementamos
end
```

## Polimorfismo con Behaviours

Los behaviours permiten crear sistemas extensibles donde la implementación se elige en runtime:

```elixir
defmodule MiApp.Notificaciones do
  @doc "Envía notificación usando el adaptador configurado"
  def notificar(destinatario, mensaje) do
    adaptador = Application.get_env(:mi_app, :notificador, MiApp.NotificadorEmail)

    case adaptador.enviar(destinatario, mensaje) do
      {:ok, ref} ->
        IO.puts("Notificación enviada: #{ref}")
        :ok
      {:error, razon} ->
        IO.puts("Error al notificar: #{razon}")
        {:error, razon}
    end
  end
end

# config/config.exs
# config :mi_app, notificador: MiApp.NotificadorEmail

# config/test.exs
# config :mi_app, notificador: MiApp.MockNotificador
```

## Protocolos vs Behaviours

La diferencia fundamental es:

```elixir
# PROTOCOLO: dispatch basado en el TIPO DEL DATO
# "¿Cómo se serializa este dato?"
defprotocol Serializable do
  def to_json(dato)  # El primer argumento determina la implementación
end

# BEHAVIOUR: contrato para un MÓDULO
# "¿Este módulo cumple con la interfaz de notificador?"
defmodule Notificador do
  @callback enviar(String.t(), String.t()) :: {:ok, term()} | {:error, term()}
end
```

| Aspecto | Protocolo | Behaviour |
|---------|-----------|-----------|
| Dispatch | Por tipo de dato | Por módulo |
| Definición | `defprotocol` | `@callback` |
| Implementación | `defimpl` | `@behaviour` + funciones |
| Uso principal | Polimorfismo de datos | Interfaces/contratos |
| Ejemplo | `String.Chars`, `Enumerable` | `GenServer`, `Plug` |

## Protocolos Built-in

Elixir incluye varios protocolos que puedes implementar:

```elixir
# Enumerable: permite usar Enum y Stream
# Collectable: permite usar Enum.into/2 y comprehensions con :into
# Inspect: controla cómo se muestra el dato con inspect/1
# String.Chars: controla la interpolación y to_string/1
# List.Chars: conversión a charlist

defimpl Enumerable, for: MiColeccion do
  def count(_col), do: {:error, __MODULE__}
  def member?(_col, _val), do: {:error, __MODULE__}
  def reduce(col, acc, fun), do: # implementación...
  def slice(_col), do: {:error, __MODULE__}
end
```

## Resumen

Los protocolos y behaviours son las herramientas de polimorfismo en Elixir. Los protocolos permiten dispatch por tipo de dato, extendiendo la funcionalidad de tipos existentes sin modificarlos. Los behaviours definen contratos que los módulos deben cumplir, facilitando la inyección de dependencias y la creación de sistemas extensibles. Juntos, proporcionan un sistema de abstracción poderoso que mantiene la claridad y la seguridad del código.
