# Manejo de Errores en Elixir

El manejo de errores en Elixir sigue una filosofía diferente a la de otros lenguajes. En lugar de usar excepciones como mecanismo principal de control de flujo, Elixir favorece el uso de tuplas `{:ok, valor}` / `{:error, razon}` para errores esperados, reservando las excepciones para situaciones verdaderamente excepcionales. Esta distinción es fundamental para escribir código idiomático.

## Tuplas {:ok, valor} y {:error, razon}

El patrón más común para manejar operaciones que pueden fallar:

```elixir
defmodule Cuentas do
  def autenticar(email, password) do
    case buscar_usuario(email) do
      nil ->
        {:error, :usuario_no_encontrado}
      usuario ->
        if verificar_password(usuario, password) do
          {:ok, usuario}
        else
          {:error, :password_incorrecta}
        end
    end
  end

  # Uso con pattern matching
  def login(email, password) do
    case autenticar(email, password) do
      {:ok, usuario} ->
        IO.puts("Bienvenido, #{usuario.nombre}")
        {:ok, generar_token(usuario)}
      {:error, :usuario_no_encontrado} ->
        {:error, "No existe una cuenta con ese email"}
      {:error, :password_incorrecta} ->
        {:error, "Contraseña incorrecta"}
    end
  end
end
```

## Funciones bang (!)

Por convención, las funciones que terminan en `!` lanzan excepciones en lugar de retornar tuplas:

```elixir
defmodule Archivo do
  def leer(ruta) do
    case File.read(ruta) do
      {:ok, contenido} -> {:ok, contenido}
      {:error, :enoent} -> {:error, "Archivo no encontrado: #{ruta}"}
      {:error, razon} -> {:error, "Error al leer: #{razon}"}
    end
  end

  def leer!(ruta) do
    case leer(ruta) do
      {:ok, contenido} -> contenido
      {:error, mensaje} -> raise mensaje
    end
  end
end

# Uso seguro
{:ok, datos} = Archivo.leer("config.json")

# Uso que puede lanzar excepción
datos = Archivo.leer!("config.json")
```

## try / rescue

`try/rescue` captura excepciones. Se usa para errores inesperados o cuando se interactúa con código que lanza excepciones:

```elixir
defmodule Parser do
  def parsear_json(texto) do
    try do
      {:ok, Jason.decode!(texto)}
    rescue
      Jason.DecodeError ->
        {:error, "JSON inválido"}
      e in ArgumentError ->
        {:error, "Argumento inválido: #{e.message}"}
    end
  end

  def dividir(a, b) do
    try do
      {:ok, a / b}
    rescue
      ArithmeticError ->
        {:error, "No se puede dividir por cero"}
    end
  end
end
```

## Excepciones Personalizadas

Puedes definir tus propias excepciones con `defexception`:

```elixir
defmodule MiApp.ErrorValidacion do
  defexception [:mensaje, :campo, :valor]

  @impl true
  def message(%__MODULE__{mensaje: msg, campo: campo}) do
    "Error de validación en '#{campo}': #{msg}"
  end
end

defmodule MiApp.ErrorAutorizacion do
  defexception [:recurso, :accion]

  @impl true
  def message(%__MODULE__{recurso: r, accion: a}) do
    "No autorizado para #{a} en #{r}"
  end
end

# Uso
raise MiApp.ErrorValidacion,
  mensaje: "No puede estar vacío",
  campo: :email,
  valor: nil

# Captura específica
try do
  validar_datos(params)
rescue
  e in MiApp.ErrorValidacion ->
    {:error, Exception.message(e)}
  e in MiApp.ErrorAutorizacion ->
    {:error, :no_autorizado, Exception.message(e)}
end
```

## throw / catch

`throw/catch` es un mecanismo de control de flujo para salir tempranamente de una operación profundamente anidada. Se usa raramente:

```elixir
defmodule Buscador do
  def buscar_en_arbol(arbol, objetivo) do
    try do
      recorrer(arbol, objetivo)
      :no_encontrado
    catch
      {:encontrado, nodo} -> {:ok, nodo}
    end
  end

  defp recorrer(nil, _objetivo), do: :ok
  defp recorrer(%{valor: valor} = nodo, objetivo) when valor == objetivo do
    throw({:encontrado, nodo})
  end
  defp recorrer(%{izquierda: izq, derecha: der}, objetivo) do
    recorrer(izq, objetivo)
    recorrer(der, objetivo)
  end
end
```

## with para Encadenar Operaciones

`with` es la forma idiomática de encadenar operaciones que pueden fallar:

```elixir
defmodule Pedido do
  def crear(params) do
    with {:ok, usuario} <- validar_usuario(params.usuario_id),
         {:ok, productos} <- validar_productos(params.productos),
         {:ok, total} <- calcular_total(productos),
         {:ok, pago} <- procesar_pago(usuario, total),
         {:ok, pedido} <- guardar_pedido(usuario, productos, pago) do
      enviar_confirmacion(usuario, pedido)
      {:ok, pedido}
    else
      {:error, :usuario_no_encontrado} ->
        {:error, "Usuario no válido"}
      {:error, :producto_sin_stock} ->
        {:error, "Producto sin stock disponible"}
      {:error, :pago_rechazado} ->
        {:error, "El pago fue rechazado"}
      {:error, razon} ->
        {:error, "Error inesperado: #{inspect(razon)}"}
    end
  end
end
```

## after y Limpieza de Recursos

`after` garantiza que el código de limpieza se ejecute:

```elixir
defmodule ConexionBD do
  def ejecutar_query(query) do
    conexion = obtener_conexion()
    try do
      resultado = ejecutar(conexion, query)
      {:ok, resultado}
    rescue
      e in DBError ->
        {:error, e.message}
    after
      liberar_conexion(conexion)
      # Siempre se ejecuta, haya error o no
    end
  end
end
```

## Buenas Prácticas

```elixir
# ✅ BIEN: Usar tuplas para errores esperados
def buscar_usuario(id) do
  case Repo.get(Usuario, id) do
    nil -> {:error, :no_encontrado}
    usuario -> {:ok, usuario}
  end
end

# ✅ BIEN: Proporcionar versión bang
def buscar_usuario!(id) do
  case buscar_usuario(id) do
    {:ok, usuario} -> usuario
    {:error, _} -> raise "Usuario #{id} no encontrado"
  end
end

# ❌ MAL: Usar excepciones para control de flujo normal
def buscar_usuario_mal(id) do
  try do
    Repo.get!(Usuario, id)
  rescue
    Ecto.NoResultsError -> nil
  end
end
```

## Resumen

El manejo de errores en Elixir se basa en dos pilares: las tuplas `{:ok, valor}` / `{:error, razon}` para errores esperados como parte del flujo normal, y las excepciones con `try/rescue` para situaciones verdaderamente inesperadas. El operador `with` simplifica el encadenamiento de operaciones fallibles, y la convención de funciones bang (`!`) proporciona una API clara donde el desarrollador elige cómo manejar los errores. Esta separación explícita entre lo esperado y lo excepcional produce código más robusto y predecible.
