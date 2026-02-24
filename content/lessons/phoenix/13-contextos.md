# Contextos y Arquitectura en Phoenix

## Introducción

Los contextos en Phoenix son módulos que agrupan funcionalidad relacionada y exponen una API pública clara. Actúan como fronteras (boundaries) entre distintas áreas del dominio de negocio, promoviendo la separación de responsabilidades.

## ¿Qué Son los Contexts?

Un contexto es un módulo Elixir que encapsula la lógica de un dominio específico:

```elixir
defmodule MyApp.Catalogo do
  alias MyApp.Repo
  alias MyApp.Catalogo.Producto

  def list_productos do
    Repo.all(Producto)
  end

  def get_producto!(id), do: Repo.get!(Producto, id)

  def create_producto(attrs \\ %{}) do
    %Producto{}
    |> Producto.changeset(attrs)
    |> Repo.insert()
  end

  def update_producto(%Producto{} = producto, attrs) do
    producto
    |> Producto.changeset(attrs)
    |> Repo.update()
  end

  def delete_producto(%Producto{} = producto) do
    Repo.delete(producto)
  end
end
```

## mix phx.gen.context

Phoenix genera contextos automáticamente con generadores:

```elixir
# Genera contexto, schema y migración
mix phx.gen.context Catalogo Producto productos nombre:string precio:decimal stock:integer

# Genera contexto con HTML completo
mix phx.gen.html Catalogo Producto productos nombre:string precio:decimal

# Genera contexto con API JSON
mix phx.gen.json Catalogo Producto productos nombre:string precio:decimal
```

Esto crea la estructura:

```elixir
# lib/my_app/catalogo.ex          -> Contexto (API pública)
# lib/my_app/catalogo/producto.ex  -> Schema Ecto
# priv/repo/migrations/xxx.exs    -> Migración
```

## Diseño de Fronteras (Boundary Design)

Las fronteras definen cómo interactúan los contextos entre sí:

```elixir
# Contexto Cuentas - maneja usuarios y autenticación
defmodule MyApp.Cuentas do
  def get_usuario!(id), do: Repo.get!(Usuario, id)
  def autenticar(email, password), do: # ...
end

# Contexto Pedidos - maneja órdenes y compras
defmodule MyApp.Pedidos do
  alias MyApp.Cuentas

  def crear_pedido(%{usuario_id: usuario_id} = attrs) do
    usuario = Cuentas.get_usuario!(usuario_id)

    %Pedido{}
    |> Pedido.changeset(attrs)
    |> Ecto.Changeset.put_assoc(:usuario, usuario)
    |> Repo.insert()
  end
end

# Contexto Notificaciones - envía emails y alertas
defmodule MyApp.Notificaciones do
  def notificar_pedido_creado(pedido) do
    pedido
    |> MyApp.Emails.confirmacion_pedido()
    |> MyApp.Mailer.deliver()
  end
end
```

## Separación de Responsabilidades

Cada contexto debe tener una responsabilidad única y bien definida:

```elixir
# MAL: Todo junto en un solo módulo
defmodule MyApp.Todo do
  def crear_usuario(attrs), do: # ...
  def crear_producto(attrs), do: # ...
  def crear_pedido(attrs), do: # ...
  def enviar_email(usuario), do: # ...
end

# BIEN: Separado por dominio
defmodule MyApp.Cuentas do
  # Solo usuarios, roles, autenticación
end

defmodule MyApp.Catalogo do
  # Solo productos, categorías, inventario
end

defmodule MyApp.Pedidos do
  # Solo órdenes, items, pagos
end
```

## API Pública del Contexto

La API pública del contexto debe ser clara, documentada y estable:

```elixir
defmodule MyApp.Blog do
  @moduledoc """
  Contexto del Blog. Gestiona artículos, comentarios y etiquetas.
  """

  alias MyApp.Blog.{Articulo, Comentario}

  @doc "Lista artículos publicados con paginación."
  def list_articulos_publicados(opts \\ []) do
    page = Keyword.get(opts, :page, 1)
    per_page = Keyword.get(opts, :per_page, 10)

    Articulo
    |> where([a], a.publicado == true)
    |> order_by([a], desc: a.published_at)
    |> limit(^per_page)
    |> offset(^((page - 1) * per_page))
    |> Repo.all()
  end

  @doc "Crea un artículo asociado a un autor."
  def create_articulo(autor, attrs) do
    %Articulo{autor_id: autor.id}
    |> Articulo.changeset(attrs)
    |> Repo.insert()
  end

  @doc "Agrega un comentario a un artículo."
  def agregar_comentario(articulo, attrs) do
    %Comentario{articulo_id: articulo.id}
    |> Comentario.changeset(attrs)
    |> Repo.insert()
  end
end
```

## Refactoring a Contexts

Cuando el código crece, debemos extraer contextos nuevos:

```elixir
# Antes: MyApp.Cuentas manejaba usuarios Y notificaciones
defmodule MyApp.Cuentas do
  def registrar_usuario(attrs) do
    with {:ok, usuario} <- crear_usuario(attrs) do
      enviar_email_bienvenida(usuario)
      {:ok, usuario}
    end
  end
end

# Después: Extraemos notificaciones a su propio contexto
defmodule MyApp.Cuentas do
  def registrar_usuario(attrs) do
    Multi.new()
    |> Multi.insert(:usuario, Usuario.changeset(%Usuario{}, attrs))
    |> Multi.run(:perfil, fn _repo, %{usuario: u} ->
      crear_perfil_default(u)
    end)
    |> Repo.transaction()
  end
end

defmodule MyApp.Notificaciones do
  def bienvenida(usuario) do
    usuario
    |> MyApp.Emails.bienvenida()
    |> MyApp.Mailer.deliver_later()
  end
end
```

Los controladores orquestan los contextos:

```elixir
def create(conn, %{"usuario" => params}) do
  with {:ok, %{usuario: usuario}} <- Cuentas.registrar_usuario(params) do
    Notificaciones.bienvenida(usuario)
    render(conn, :show, usuario: usuario)
  end
end
```

## Resumen

Los contextos en Phoenix son la herramienta principal para organizar la lógica de negocio. Definen fronteras claras entre dominios, exponen APIs públicas estables y promueven la separación de responsabilidades. El generador `mix phx.gen.context` facilita su creación, y el refactoring a contextos es esencial conforme la aplicación crece.
