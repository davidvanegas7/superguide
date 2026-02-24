# Testing en Phoenix

## Introducción

Phoenix incluye un ecosistema de testing completo basado en ExUnit. Desde tests de controladores con `ConnTest` hasta tests de LiveView y channels, cada capa tiene herramientas especializadas.

## ConnTest: Testing de Controladores

`ConnTest` provee helpers para simular peticiones HTTP:

```elixir
defmodule MyAppWeb.ProductoControllerTest do
  use MyAppWeb.ConnCase

  alias MyApp.Catalogo

  @valid_attrs %{nombre: "Laptop", precio: 999.99}
  @invalid_attrs %{nombre: nil, precio: nil}

  setup %{conn: conn} do
    {:ok, conn: put_req_header(conn, "accept", "application/json")}
  end

  describe "GET /api/productos" do
    test "lista todos los productos", %{conn: conn} do
      {:ok, _} = Catalogo.create_producto(@valid_attrs)

      conn = get(conn, ~p"/api/productos")
      assert [%{"nombre" => "Laptop"}] = json_response(conn, 200)["data"]
    end
  end

  describe "POST /api/productos" do
    test "crea producto con datos válidos", %{conn: conn} do
      conn = post(conn, ~p"/api/productos", producto: @valid_attrs)
      assert %{"id" => id} = json_response(conn, 201)["data"]

      conn = get(conn, ~p"/api/productos/#{id}")
      assert %{"nombre" => "Laptop"} = json_response(conn, 200)["data"]
    end

    test "retorna errores con datos inválidos", %{conn: conn} do
      conn = post(conn, ~p"/api/productos", producto: @invalid_attrs)
      assert json_response(conn, 422)["errors"] != %{}
    end
  end
end
```

## Helpers get/post/put/delete

Los helpers simulan cada método HTTP:

```elixir
test "operaciones CRUD completas", %{conn: conn} do
  # Crear
  conn = post(conn, ~p"/api/items", item: %{titulo: "Test"})
  assert %{"id" => id} = json_response(conn, 201)["data"]

  # Leer
  conn = get(conn, ~p"/api/items/#{id}")
  assert json_response(conn, 200)["data"]["titulo"] == "Test"

  # Actualizar
  conn = put(conn, ~p"/api/items/#{id}", item: %{titulo: "Actualizado"})
  assert json_response(conn, 200)["data"]["titulo"] == "Actualizado"

  # Eliminar
  conn = delete(conn, ~p"/api/items/#{id}")
  assert response(conn, 204)
end
```

## LiveViewTest

Testing de LiveView con `live/2` y helpers de interacción:

```elixir
defmodule MyAppWeb.ContadorLiveTest do
  use MyAppWeb.ConnCase
  import Phoenix.LiveViewTest

  test "incrementa el contador", %{conn: conn} do
    {:ok, view, html} = live(conn, ~p"/contador")
    assert html =~ "Valor: 0"

    assert view
           |> element("button", "Incrementar")
           |> render_click() =~ "Valor: 1"
  end

  test "actualiza con formulario", %{conn: conn} do
    {:ok, view, _html} = live(conn, ~p"/buscar")

    resultado =
      view
      |> form("#buscar-form", %{q: "elixir"})
      |> render_submit()

    assert resultado =~ "Resultados para: elixir"
  end

  test "navegación con live_patch", %{conn: conn} do
    {:ok, view, _html} = live(conn, ~p"/productos")

    assert view
           |> element("a", "Siguiente")
           |> render_click()

    assert_patch(view, ~p"/productos?page=2")
  end
end
```

## Channel Testing

Tests para canales WebSocket:

```elixir
defmodule MyAppWeb.SalaChannelTest do
  use MyAppWeb.ChannelCase

  setup do
    {:ok, _, socket} =
      MyAppWeb.UserSocket
      |> socket("user_id", %{user_id: 1})
      |> subscribe_and_join(MyAppWeb.SalaChannel, "sala:lobby")

    %{socket: socket}
  end

  test "enviar mensaje broadcast a todos", %{socket: socket} do
    push(socket, "nuevo_mensaje", %{"body" => "hola"})
    assert_broadcast "nuevo_mensaje", %{"body" => "hola"}
  end

  test "responde con mensajes recientes al unirse", %{socket: _socket} do
    assert_push "mensajes_recientes", %{mensajes: _}
  end
end
```

## DataCase para Contextos

`DataCase` configura el sandbox de base de datos para tests de lógica:

```elixir
defmodule MyApp.CatalogoTest do
  use MyApp.DataCase

  alias MyApp.Catalogo

  describe "productos" do
    test "list_productos/0 retorna todos los productos" do
      producto = producto_fixture()
      assert Catalogo.list_productos() == [producto]
    end

    test "create_producto/1 con datos válidos" do
      attrs = %{nombre: "Monitor", precio: 299.99}
      assert {:ok, producto} = Catalogo.create_producto(attrs)
      assert producto.nombre == "Monitor"
    end

    test "create_producto/1 con datos inválidos" do
      assert {:error, %Ecto.Changeset{}} = Catalogo.create_producto(%{nombre: nil})
    end
  end
end
```

## Factories con ExMachina

ExMachina simplifica la creación de datos de test:

```elixir
defmodule MyApp.Factory do
  use ExMachina.Ecto, repo: MyApp.Repo

  def usuario_factory do
    %MyApp.Cuentas.Usuario{
      nombre: sequence(:nombre, &"Usuario #{&1}"),
      email: sequence(:email, &"user#{&1}@test.com"),
      password_hash: Bcrypt.hash_pwd_salt("password123")
    }
  end

  def producto_factory do
    %MyApp.Catalogo.Producto{
      nombre: sequence(:nombre, &"Producto #{&1}"),
      precio: Decimal.new("29.99"),
      stock: 10
    }
  end
end

# Uso en tests
test "usuario puede comprar producto" do
  usuario = insert(:usuario)
  producto = insert(:producto, precio: Decimal.new("50.00"))
  assert {:ok, _pedido} = Pedidos.crear(usuario, producto)
end
```

## Mocking con Mox

Mox permite definir mocks basados en behaviours:

```elixir
# Definir behaviour
defmodule MyApp.PaymentGateway do
  @callback procesar_pago(map()) :: {:ok, map()} | {:error, String.t()}
end

# En test_helper.exs
Mox.defmock(MyApp.MockPayment, for: MyApp.PaymentGateway)

# En el test
test "procesa pago exitosamente" do
  expect(MyApp.MockPayment, :procesar_pago, fn params ->
    assert params.monto == 100
    {:ok, %{transaccion_id: "tx_123"}}
  end)

  assert {:ok, resultado} = Pedidos.pagar(pedido, MyApp.MockPayment)
  assert resultado.transaccion_id == "tx_123"
end
```

## Resumen

Phoenix ofrece herramientas de testing para cada capa: `ConnTest` para controladores HTTP, `LiveViewTest` para interfaces en tiempo real, `ChannelCase` para WebSockets, `DataCase` para lógica de negocio. ExMachina simplifica factories y Mox provee mocking seguro basado en behaviours.
