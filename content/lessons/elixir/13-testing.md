# Testing con ExUnit en Elixir

ExUnit es el framework de testing integrado en Elixir. Viene incluido sin necesidad de dependencias adicionales y proporciona herramientas para escribir tests unitarios, de integración y basados en propiedades. La cultura de testing en Elixir es fuerte, y herramientas como doctests hacen que documentar y testear sean la misma actividad.

## Configuración Básica

Todo proyecto Elixir viene con ExUnit configurado:

```elixir
# test/test_helper.exs
ExUnit.start()

# test/mi_modulo_test.exs
defmodule MiModuloTest do
  use ExUnit.Case

  test "suma dos números" do
    assert 2 + 3 == 5
  end
end
```

Ejecutar tests:

```elixir
# mix test                    — Todos los tests
# mix test test/mi_test.exs   — Un archivo específico
# mix test test/mi_test.exs:5 — Una línea específica
# mix test --trace            — Output detallado
# mix test --stale            — Solo tests afectados por cambios
```

## Assertions

ExUnit proporciona macros de aserción expresivas:

```elixir
defmodule AssercionesTest do
  use ExUnit.Case

  test "assertions básicas" do
    assert 1 + 1 == 2
    refute 1 + 1 == 3

    assert "hola" =~ "ho"       # Regex o substring
    assert_in_delta 3.14, 3.1, 0.1  # Comparación con tolerancia
  end

  test "assertions con pattern matching" do
    resultado = {:ok, %{nombre: "Ana"}}
    assert {:ok, %{nombre: nombre}} = resultado
    assert nombre == "Ana"
  end

  test "assertions de excepciones" do
    assert_raise ArithmeticError, fn ->
      1 / 0
    end

    assert_raise ArgumentError, "argumento inválido", fn ->
      raise ArgumentError, "argumento inválido"
    end
  end

  test "assertions de mensajes" do
    send(self(), {:evento, :usuario_creado})

    assert_received {:evento, :usuario_creado}
    refute_received {:evento, :usuario_eliminado}
  end
end
```

## Setup y Describe

`setup` ejecuta código antes de cada test, y `describe` agrupa tests relacionados:

```elixir
defmodule CalculadoraTest do
  use ExUnit.Case

  # Setup global para todos los tests del módulo
  setup do
    calculadora = %{precision: 2}
    {:ok, calc: calculadora}
  end

  describe "operaciones básicas" do
    test "suma correctamente", %{calc: calc} do
      resultado = Float.round(2.1 + 3.2, calc.precision)
      assert resultado == 5.3
    end

    test "resta correctamente", %{calc: _calc} do
      assert 10 - 3 == 7
    end
  end

  describe "validaciones" do
    setup do
      # Setup adicional solo para este grupo
      {:ok, limites: %{min: 0, max: 1000}}
    end

    test "rechaza valores negativos", %{limites: limites} do
      assert -5 < limites.min
    end

    test "acepta valores en rango", %{limites: limites} do
      assert 500 >= limites.min and 500 <= limites.max
    end
  end
end
```

## Setup con Contexto Compartido

`setup_all` se ejecuta una sola vez antes de todos los tests del módulo:

```elixir
defmodule IntegracionTest do
  use ExUnit.Case

  setup_all do
    # Se ejecuta una vez antes de todos los tests
    {:ok, pid} = MiApp.Cache.start_link([])
    %{cache_pid: pid}
  end

  setup %{cache_pid: pid} do
    # Se ejecuta antes de cada test
    MiApp.Cache.limpiar(pid)
    :ok
  end

  test "almacena y recupera valores", %{cache_pid: pid} do
    MiApp.Cache.put(pid, :clave, "valor")
    assert MiApp.Cache.get(pid, :clave) == "valor"
  end
end
```

## Doctests

Los doctests permiten escribir tests directamente en la documentación:

```elixir
defmodule Matematica do
  @doc """
  Calcula el factorial de un número.

  ## Ejemplos

      iex> Matematica.factorial(0)
      1

      iex> Matematica.factorial(5)
      120

      iex> Matematica.factorial(10)
      3628800
  """
  def factorial(0), do: 1
  def factorial(n) when n > 0, do: n * factorial(n - 1)

  @doc """
  Verifica si un número es primo.

  ## Ejemplos

      iex> Matematica.primo?(2)
      true

      iex> Matematica.primo?(4)
      false

      iex> Matematica.primo?(17)
      true
  """
  def primo?(n) when n < 2, do: false
  def primo?(2), do: true
  def primo?(n) do
    not Enum.any?(2..trunc(:math.sqrt(n)), &(rem(n, &1) == 0))
  end
end

# En el test:
defmodule MatematicaTest do
  use ExUnit.Case
  doctest Matematica
end
```

## Mocking con Mox

La biblioteca Mox permite crear mocks basados en behaviours:

```elixir
# Definir un behaviour
defmodule MiApp.ClienteHTTP do
  @callback get(String.t()) :: {:ok, map()} | {:error, term()}
end

# En test/test_helper.exs
Mox.defmock(MiApp.MockHTTP, for: MiApp.ClienteHTTP)

# En el test
defmodule MiApp.ServicioTest do
  use ExUnit.Case
  import Mox

  setup :verify_on_exit!

  test "procesa respuesta exitosa" do
    expect(MiApp.MockHTTP, :get, fn url ->
      assert url == "https://api.example.com/datos"
      {:ok, %{status: 200, body: ~s({"nombre": "test"})}}
    end)

    assert {:ok, _} = MiApp.Servicio.obtener_datos(MiApp.MockHTTP)
  end

  test "maneja error de red" do
    expect(MiApp.MockHTTP, :get, fn _url ->
      {:error, :timeout}
    end)

    assert {:error, :timeout} = MiApp.Servicio.obtener_datos(MiApp.MockHTTP)
  end
end
```

## Tags y Filtros

Los tags permiten categorizar y filtrar tests:

```elixir
defmodule LentoTest do
  use ExUnit.Case

  @tag :integracion
  test "test de integración con base de datos" do
    # ...
  end

  @tag :slow
  @tag timeout: 120_000
  test "operación lenta" do
    # ...
  end

  @tag :skip
  test "test deshabilitado" do
    # No se ejecutará
  end
end

# Ejecutar solo tests con un tag:
# mix test --only integracion
# mix test --exclude slow
```

## Resumen

ExUnit proporciona un framework completo para testing en Elixir: assertions expresivas que aprovechan el pattern matching, `describe` y `setup` para organizar tests, doctests para unificar documentación y verificación, mocking con Mox para aislar dependencias, y tags para categorizar y filtrar la ejecución. La integración profunda de ExUnit con el lenguaje y Mix hace que testear sea una actividad natural y productiva en el desarrollo con Elixir.
