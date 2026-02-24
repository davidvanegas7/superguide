# Formularios en Phoenix

Phoenix proporciona un sistema robusto para construir formularios HTML que se integran directamente con los changesets de Ecto. Esto permite validaciones en tiempo real, manejo de errores intuitivo y soporte nativo para carga de archivos.

## El Componente form

En Phoenix 1.7+, los formularios se construyen usando el componente `<.form>` junto con function components para los campos:

```elixir
# En la plantilla HEEx
<.form :let={f} for={@changeset} action={~p"/productos"}>
  <.input field={f[:nombre]} type="text" label="Nombre del producto" />
  <.input field={f[:descripcion]} type="textarea" label="Descripción" />
  <.input field={f[:precio]} type="number" label="Precio" step="0.01" />
  <.input field={f[:categoria_id]} type="select" label="Categoría"
    options={@categorias} />
  <.input field={f[:activo]} type="checkbox" label="Activo" />

  <:actions>
    <.button>Guardar Producto</.button>
  </:actions>
</.form>
```

## La Función to_form

`to_form` convierte un changeset u otros datos en una estructura que el componente de formulario puede utilizar:

```elixir
# En un controller tradicional
def new(conn, _params) do
  changeset = Catalogo.cambio_producto(%Producto{})
  render(conn, :new, changeset: changeset)
end

# En un LiveView
def mount(_params, _session, socket) do
  changeset = Catalogo.cambio_producto(%Producto{})
  form = to_form(changeset)
  {:ok, assign(socket, form: form)}
end

# to_form también acepta mapas simples
form = to_form(%{"nombre" => "", "email" => ""})

# O con opciones adicionales
form = to_form(changeset, as: :producto, id: "form-producto")
```

## Changesets en Formularios

Los changesets alimentan los formularios proporcionando valores iniciales, validaciones y errores:

```elixir
defmodule MiApp.Catalogo do
  alias MiApp.Catalogo.Producto
  alias MiApp.Repo

  def cambio_producto(%Producto{} = producto, attrs \\ %{}) do
    Producto.changeset(producto, attrs)
  end

  def crear_producto(attrs) do
    %Producto{}
    |> Producto.changeset(attrs)
    |> Repo.insert()
  end
end

# En el controller
def create(conn, %{"producto" => producto_params}) do
  case Catalogo.crear_producto(producto_params) do
    {:ok, producto} ->
      conn
      |> put_flash(:info, "Producto creado.")
      |> redirect(to: ~p"/productos/#{producto}")

    {:error, %Ecto.Changeset{} = changeset} ->
      render(conn, :new, changeset: changeset)
  end
end
```

## Validaciones en Formularios

Las validaciones del changeset se reflejan automáticamente en el formulario mostrando errores:

```elixir
defmodule MiApp.Catalogo.Producto do
  use Ecto.Schema
  import Ecto.Changeset

  schema "productos" do
    field :nombre, :string
    field :precio, :decimal
    field :stock, :integer
    timestamps()
  end

  def changeset(producto, attrs) do
    producto
    |> cast(attrs, [:nombre, :precio, :stock])
    |> validate_required([:nombre, :precio], message: "es obligatorio")
    |> validate_length(:nombre, min: 3, message: "debe tener al menos 3 caracteres")
    |> validate_number(:precio, greater_than: 0, message: "debe ser mayor a cero")
    |> unique_constraint(:nombre, message: "ya existe un producto con ese nombre")
  end
end
```

## Manejo de Errores

Phoenix muestra los errores de validación automáticamente junto a cada campo del formulario:

```elixir
# El componente input de CoreComponents maneja errores automáticamente
def input(assigns) do
  ~H"""
  <div phx-feedback-for={@name}>
    <label for={@id}><%= @label %></label>
    <input type={@type} name={@name} id={@id} value={@value}
      class={["input", @errors != [] && "input-error"]} />
    <.error :for={msg <- @errors}><%= msg %></.error>
  </div>
  """
end

def error(assigns) do
  ~H"""
  <p class="error-mensaje">
    <%= render_slot(@inner_block) %>
  </p>
  """
end

# Uso en la plantilla: los errores aparecen automáticamente
<.form :let={f} for={@changeset} action={~p"/productos"}>
  <.input field={f[:nombre]} type="text" label="Nombre" />
  <!-- Si hay error, aparece: "es obligatorio" debajo del campo -->
</.form>
```

## Carga de Archivos con allow_upload

Phoenix LiveView ofrece soporte nativo para subir archivos con `allow_upload`:

```elixir
defmodule MiAppWeb.ProductoLive.FormComponent do
  use MiAppWeb, :live_component

  def mount(socket) do
    {:ok,
     socket
     |> allow_upload(:imagen,
       accept: ~w(.jpg .jpeg .png .webp),
       max_entries: 3,
       max_file_size: 5_000_000
     )}
  end

  def render(assigns) do
    ~H"""
    <.form for={@form} phx-change="validate" phx-submit="save" phx-target={@myself}>
      <.input field={@form[:nombre]} type="text" label="Nombre" />
      <.live_file_input upload={@uploads.imagen} />

      <%= for entry <- @uploads.imagen.entries do %>
        <div class="preview">
          <.live_img_preview entry={entry} width="100" />
          <button phx-click="cancel-upload" phx-value-ref={entry.ref}
            phx-target={@myself}>&times;</button>
        </div>
      <% end %>

      <.button>Subir</.button>
    </.form>
    """
  end
end
```

## Live Uploads

Los live uploads procesan los archivos en el servidor cuando se envía el formulario:

```elixir
def handle_event("save", %{"producto" => params}, socket) do
  imagenes =
    consume_uploaded_entries(socket, :imagen, fn %{path: path}, entry ->
      dest = Path.join(["priv", "static", "uploads", entry.client_name])
      File.cp!(path, dest)
      {:ok, ~p"/uploads/#{entry.client_name}"}
    end)

  params = Map.put(params, "imagenes", imagenes)

  case Catalogo.crear_producto(params) do
    {:ok, producto} ->
      {:noreply,
       socket
       |> put_flash(:info, "Producto creado con imágenes.")
       |> push_navigate(to: ~p"/productos/#{producto}")}

    {:error, changeset} ->
      {:noreply, assign(socket, form: to_form(changeset))}
  end
end

def handle_event("cancel-upload", %{"ref" => ref}, socket) do
  {:noreply, cancel_upload(socket, :imagen, ref)}
end
```

## Resumen

Phoenix integra formularios con changesets de Ecto de forma fluida. El componente `<.form>` con `to_form` genera formularios tipados, los changesets proveen validaciones que se reflejan automáticamente como errores en la interfaz. Para carga de archivos, `allow_upload` de LiveView ofrece una solución nativa con previsualizaciones, validación de tipo/tamaño y procesamiento en el servidor mediante `consume_uploaded_entries`.
