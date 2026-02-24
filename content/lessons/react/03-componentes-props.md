# Componentes y Props

Los **componentes** son la unidad fundamental de React. Una aplicación React es un árbol de componentes anidados, cada uno responsable de una pieza de la interfaz. Las **props** son el mecanismo para pasar datos de un componente padre a uno hijo.

---

## ¿Qué es un componente?

Un componente es una **función JavaScript/TypeScript** que retorna JSX:

```tsx
function Saludo() {
  return <h1>¡Hola mundo!</h1>
}
```

### Reglas de los componentes

1. **Nombre en PascalCase**: `MiComponente`, no `miComponente`
2. **Retorna JSX** (o `null` para no renderizar nada)
3. **Un archivo por componente** (convención)
4. **Funciones puras** respecto a sus props: mismas props → mismo output

---

## Props: pasar datos al componente

Las props son **argumentos** que recibe un componente desde su padre:

```tsx
// Definir el componente con props
interface SaludoProps {
  nombre: string
  edad: number
}

function Saludo({ nombre, edad }: SaludoProps) {
  return (
    <p>Hola, {nombre}. Tienes {edad} años.</p>
  )
}

// Usar el componente
function App() {
  return <Saludo nombre="Ana" edad={28} />
}
```

### Props son de solo lectura

```tsx
// ❌ NUNCA modifiques las props
function Malo({ nombre }: { nombre: string }) {
  nombre = 'Otro' // ¡Error conceptual!
  return <p>{nombre}</p>
}
```

> React sigue el principio de **flujo de datos unidireccional**: los datos bajan del padre al hijo a través de props. El hijo **nunca** modifica las props que recibe.

---

## Desestructuración de props

```tsx
// Sin desestructurar
function Card(props: CardProps) {
  return <h2>{props.title}</h2>
}

// Con desestructuración (recomendado)
function Card({ title, description, image }: CardProps) {
  return (
    <article>
      <img src={image} alt={title} />
      <h2>{title}</h2>
      <p>{description}</p>
    </article>
  )
}
```

---

## Props por defecto

```tsx
interface ButtonProps {
  label: string
  variant?: 'primary' | 'secondary' | 'danger'
  size?: 'sm' | 'md' | 'lg'
  disabled?: boolean
}

function Button({
  label,
  variant = 'primary',
  size = 'md',
  disabled = false,
}: ButtonProps) {
  return (
    <button
      className={`btn btn-${variant} btn-${size}`}
      disabled={disabled}
    >
      {label}
    </button>
  )
}

// Uso — solo label es obligatorio:
<Button label="Enviar" />
<Button label="Borrar" variant="danger" size="lg" />
```

---

## La prop especial `children`

`children` permite pasar contenido JSX **dentro** de un componente:

```tsx
interface CardProps {
  title: string
  children: React.ReactNode
}

function Card({ title, children }: CardProps) {
  return (
    <div className="card">
      <h2 className="card-title">{title}</h2>
      <div className="card-body">
        {children}
      </div>
    </div>
  )
}

// Uso:
<Card title="Mi tarjeta">
  <p>Este contenido se pasa como children</p>
  <button>Acción</button>
</Card>
```

### Tipos comunes para children

| Tipo | Uso |
|---|---|
| `React.ReactNode` | Cualquier cosa renderizable (string, number, JSX, null, array) |
| `React.ReactElement` | Solo elementos JSX (más restrictivo) |
| `string` | Solo texto |
| `() => React.ReactNode` | Render prop (función que retorna JSX) |

---

## Composición de componentes

React favorece la **composición** sobre la herencia. Combinas componentes como piezas de LEGO:

```tsx
function Avatar({ src, alt }: { src: string; alt: string }) {
  return <img className="avatar" src={src} alt={alt} />
}

function UserInfo({ name, role }: { name: string; role: string }) {
  return (
    <div>
      <strong>{name}</strong>
      <span className="role">{role}</span>
    </div>
  )
}

function UserCard({ user }: { user: User }) {
  return (
    <Card title="Perfil">
      <Avatar src={user.avatar} alt={user.name} />
      <UserInfo name={user.name} role={user.role} />
    </Card>
  )
}
```

---

## Comunicación hijo → padre (callbacks)

El hijo no puede modificar las props, pero puede **notificar al padre** mediante funciones callback:

```tsx
interface SearchBarProps {
  onSearch: (query: string) => void
  placeholder?: string
}

function SearchBar({ onSearch, placeholder = 'Buscar...' }: SearchBarProps) {
  const [value, setValue] = React.useState('')

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    onSearch(value)  // Notifica al padre
  }

  return (
    <form onSubmit={handleSubmit}>
      <input
        value={value}
        onChange={(e) => setValue(e.target.value)}
        placeholder={placeholder}
      />
      <button type="submit">Buscar</button>
    </form>
  )
}

// El padre maneja el evento:
function App() {
  const handleSearch = (query: string) => {
    console.log('Buscando:', query)
  }

  return <SearchBar onSearch={handleSearch} />
}
```

---

## Spread de props

Puedes pasar todas las props de un objeto con el operador spread:

```tsx
interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label: string
}

function Input({ label, ...rest }: InputProps) {
  return (
    <div className="form-field">
      <label>{label}</label>
      <input {...rest} />
    </div>
  )
}

// Se pasan TODAS las props nativas de <input>:
<Input
  label="Email"
  type="email"
  required
  placeholder="user@example.com"
  onChange={handleChange}
/>
```

---

## Componentes como props

Puedes pasar componentes como props para maximizar la flexibilidad:

```tsx
interface PageLayoutProps {
  header: React.ReactNode
  sidebar: React.ReactNode
  children: React.ReactNode
}

function PageLayout({ header, sidebar, children }: PageLayoutProps) {
  return (
    <div className="layout">
      <header>{header}</header>
      <aside>{sidebar}</aside>
      <main>{children}</main>
    </div>
  )
}

// Uso:
<PageLayout
  header={<NavBar />}
  sidebar={<MenuLateral />}
>
  <h1>Contenido principal</h1>
</PageLayout>
```

---

## Organización de archivos

```
src/
├── components/
│   ├── ui/              ← Componentes genéricos reutilizables
│   │   ├── Button.tsx
│   │   ├── Card.tsx
│   │   └── Input.tsx
│   ├── layout/          ← Estructura de la app
│   │   ├── Header.tsx
│   │   ├── Sidebar.tsx
│   │   └── Footer.tsx
│   └── features/        ← Componentes por feature
│       ├── UserCard.tsx
│       └── ProductList.tsx
├── types/               ← Interfaces y tipos compartidos
│   └── index.ts
└── App.tsx
```

---

## Ejemplo completo: Lista de tareas

```tsx
// types.ts
interface Todo {
  id: number
  text: string
  completed: boolean
}

// TodoItem.tsx
interface TodoItemProps {
  todo: Todo
  onToggle: (id: number) => void
  onDelete: (id: number) => void
}

function TodoItem({ todo, onToggle, onDelete }: TodoItemProps) {
  return (
    <li className={todo.completed ? 'completed' : ''}>
      <input
        type="checkbox"
        checked={todo.completed}
        onChange={() => onToggle(todo.id)}
      />
      <span>{todo.text}</span>
      <button onClick={() => onDelete(todo.id)}>❌</button>
    </li>
  )
}

// TodoList.tsx
interface TodoListProps {
  todos: Todo[]
  onToggle: (id: number) => void
  onDelete: (id: number) => void
}

function TodoList({ todos, onToggle, onDelete }: TodoListProps) {
  if (todos.length === 0) {
    return <p>No hay tareas pendientes 🎉</p>
  }

  return (
    <ul>
      {todos.map((todo) => (
        <TodoItem
          key={todo.id}
          todo={todo}
          onToggle={onToggle}
          onDelete={onDelete}
        />
      ))}
    </ul>
  )
}
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| **Componente** | Función que retorna JSX, nombre en PascalCase |
| **Props** | Datos que el padre pasa al hijo, son de solo lectura |
| **children** | Prop especial para contenido anidado en JSX |
| **Callbacks** | Funciones pasadas como props para comunicación hijo → padre |
| **Composición** | Combinar componentes pequeños para construir UIs complejas |
| **Spread props** | `{...rest}` para pasar props nativas sin listarlas |
| **Tipado** | Interfaces TypeScript para props dan autocompletado y seguridad |
