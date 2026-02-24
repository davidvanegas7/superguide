# JSX y Rendering

**JSX** (JavaScript XML) es la extensión de sintaxis que permite escribir HTML dentro de JavaScript. Es la piedra angular de React: cada componente devuelve JSX que describe lo que se debe renderizar.

---

## ¿Qué es JSX?

JSX **no** es HTML. Es una extensión de sintaxis que Babel/SWC transforma en llamadas a `React.createElement()`:

```tsx
// Esto es JSX:
const element = <h1 className="title">Hola mundo</h1>

// Se transforma en:
const element = React.createElement('h1', { className: 'title' }, 'Hola mundo')
```

> Desde React 17, no necesitas importar `React` en cada archivo — el nuevo JSX Transform lo hace automáticamente.

---

## Reglas de JSX

### 1. Siempre retorna un solo elemento raíz

```tsx
// ❌ Error: múltiples elementos raíz
return (
  <h1>Título</h1>
  <p>Párrafo</p>
)

// ✅ Envuelve en un contenedor
return (
  <div>
    <h1>Título</h1>
    <p>Párrafo</p>
  </div>
)

// ✅ O usa un Fragment (no genera nodo DOM extra)
return (
  <>
    <h1>Título</h1>
    <p>Párrafo</p>
  </>
)
```

### 2. Cierra todas las etiquetas

```tsx
// ❌ Error en JSX
<img src="foto.jpg">
<br>
<input type="text">

// ✅ Correcto
<img src="foto.jpg" />
<br />
<input type="text" />
```

### 3. Usa `className` en vez de `class`

```tsx
// ❌ class es palabra reservada en JavaScript
<div class="container">

// ✅ Correcto
<div className="container">
```

### 4. Usa `htmlFor` en vez de `for`

```tsx
<label htmlFor="email">Email</label>
<input id="email" type="email" />
```

### 5. camelCase para atributos

```tsx
// HTML: onclick, tabindex, readonly
// JSX:  onClick, tabIndex, readOnly

<button onClick={handleClick} tabIndex={0}>
  Haz clic
</button>
```

---

## Expresiones en JSX con `{}`

Las llaves `{}` permiten insertar cualquier **expresión** JavaScript dentro de JSX:

### Variables y operaciones

```tsx
const nombre = 'Ana'
const precio = 29.99

return (
  <div>
    <p>Hola, {nombre}</p>
    <p>Precio con IVA: ${(precio * 1.21).toFixed(2)}</p>
    <p>Fecha: {new Date().toLocaleDateString()}</p>
  </div>
)
```

### Llamadas a funciones

```tsx
function formatearNombre(nombre: string, apellido: string) {
  return `${nombre} ${apellido}`.toUpperCase()
}

return <h1>{formatearNombre('Ana', 'García')}</h1>
```

### Operador ternario (condicional inline)

```tsx
const isLoggedIn = true

return (
  <div>
    {isLoggedIn ? <p>Bienvenido</p> : <p>Inicia sesión</p>}
  </div>
)
```

---

## Renderizado condicional

### Con operador ternario

```tsx
function Saludo({ isAdmin }: { isAdmin: boolean }) {
  return (
    <div>
      {isAdmin ? (
        <h1>Panel de administración</h1>
      ) : (
        <h1>Bienvenido, usuario</h1>
      )}
    </div>
  )
}
```

### Con `&&` (short-circuit)

```tsx
function Notificacion({ count }: { count: number }) {
  return (
    <div>
      {count > 0 && <span className="badge">{count} nuevas</span>}
    </div>
  )
}
```

> **Cuidado con `&&` y números**: `{0 && <span>texto</span>}` renderiza `0` en el DOM. Usa `{count > 0 && ...}` en vez de `{count && ...}`.

### Con retorno temprano

```tsx
function UserProfile({ user }: { user: User | null }) {
  if (!user) return <p>Cargando...</p>

  return (
    <div>
      <h1>{user.name}</h1>
      <p>{user.email}</p>
    </div>
  )
}
```

---

## Renderizado de listas con `map()`

```tsx
interface Producto {
  id: number
  nombre: string
  precio: number
}

function ListaProductos({ productos }: { productos: Producto[] }) {
  return (
    <ul>
      {productos.map((producto) => (
        <li key={producto.id}>
          {producto.nombre} — ${producto.precio}
        </li>
      ))}
    </ul>
  )
}
```

### La prop `key`

React usa `key` para identificar qué elementos cambiaron, se agregaron o se eliminaron en una lista:

```tsx
// ✅ Usa un ID único y estable
{items.map(item => <Card key={item.id} data={item} />)}

// ❌ Nunca uses el índice del array como key si la lista puede reordenarse
{items.map((item, index) => <Card key={index} data={item} />)}

// ❌ Nunca uses Math.random()
{items.map(item => <Card key={Math.random()} data={item} />)}
```

**¿Por qué?** Sin una `key` estable, React no puede optimizar la reconciliación del Virtual DOM y puede perder estado interno de los componentes hijos.

---

## Estilos en JSX

### Inline styles (objeto JavaScript)

```tsx
const estilos = {
  backgroundColor: '#f0f0f0',  // camelCase, no kebab-case
  padding: '16px',
  borderRadius: '8px',
  fontSize: '14px',
}

return <div style={estilos}>Contenido estilizado</div>

// También inline:
<div style={{ color: 'red', fontWeight: 'bold' }}>Texto rojo</div>
```

### Clases CSS

```tsx
import './MiComponente.css'

function MiComponente() {
  const isActive = true

  return (
    <div className={`card ${isActive ? 'active' : ''}`}>
      Contenido
    </div>
  )
}
```

### Clases dinámicas con template literals

```tsx
<button className={`btn btn-${variant} ${disabled ? 'btn-disabled' : ''}`}>
  {label}
</button>
```

---

## Fragments

Los Fragments permiten agrupar elementos sin agregar nodos extra al DOM:

```tsx
import { Fragment } from 'react'

// Syntax corta (más común):
return (
  <>
    <h1>Título</h1>
    <p>Párrafo</p>
  </>
)

// Syntax completa (necesaria cuando quieres pasar key):
return (
  <Fragment>
    {items.map(item => (
      <Fragment key={item.id}>
        <dt>{item.term}</dt>
        <dd>{item.definition}</dd>
      </Fragment>
    ))}
  </Fragment>
)
```

---

## Cómo funciona el rendering

### El Virtual DOM

1. Tu componente retorna JSX → React crea un **árbol virtual** (objetos JavaScript ligeros)
2. Cuando el estado cambia → React crea un **nuevo** árbol virtual
3. React **compara** (diffing) el árbol anterior con el nuevo
4. Solo aplica al DOM real los **cambios mínimos** necesarios (reconciliación)

```
Estado cambia → Nuevo Virtual DOM → Diffing → Patch al DOM real
```

### ¿Cuándo se re-renderiza un componente?

Un componente se re-renderiza cuando:

1. **Su estado cambia** (`useState`, `useReducer`)
2. **Sus props cambian** (el padre le pasa valores nuevos)
3. **Su padre se re-renderiza** (aunque las props sean las mismas — a menos que uses `React.memo`)
4. **El contexto que consume cambia** (`useContext`)

---

## JSX vs Templates (Angular/Vue)

| Aspecto | JSX (React) | Templates (Angular/Vue) |
|---|---|---|
| **Condicionales** | `{cond ? <A/> : <B/>}` | `*ngIf` / `v-if` |
| **Listas** | `{arr.map(x => <X/>)}` | `*ngFor` / `v-for` |
| **Binding** | `onClick={fn}` | `(click)="fn()"` |
| **Interpolación** | `{variable}` | `{{ variable }}` |
| **Flexibilidad** | Todo es JavaScript | Directivas especiales |
| **Tipado** | TypeScript nativo | TypeScript con limitaciones en template |

---

## Ejemplo completo: Tarjeta de producto

```tsx
interface Product {
  id: number
  name: string
  price: number
  inStock: boolean
  tags: string[]
}

function ProductCard({ product }: { product: Product }) {
  return (
    <article className="product-card">
      <h2>{product.name}</h2>
      <p className="price">${product.price.toFixed(2)}</p>

      {product.inStock ? (
        <span className="badge badge-green">En stock</span>
      ) : (
        <span className="badge badge-red">Agotado</span>
      )}

      {product.tags.length > 0 && (
        <div className="tags">
          {product.tags.map((tag) => (
            <span key={tag} className="tag">{tag}</span>
          ))}
        </div>
      )}

      <button disabled={!product.inStock}>
        {product.inStock ? 'Añadir al carrito' : 'No disponible'}
      </button>
    </article>
  )
}
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| **JSX** | Extensión de sintaxis que permite HTML en JavaScript |
| **`{}`** | Insertar expresiones JavaScript en JSX |
| **`key`** | Identificador único para elementos en listas |
| **Fragment** | `<> </>` agrupa sin nodo DOM extra |
| **className** | Equivalente a `class` de HTML |
| **Renderizado condicional** | Ternario, `&&`, retorno temprano |
| **Virtual DOM** | React compara árboles virtuales y aplica cambios mínimos |
