# TypeScript con React

TypeScript es el estándar de la industria para proyectos React profesionales. Proporciona **detección de errores en tiempo de desarrollo**, autocompletado y documentación implícita a través de tipos.

---

## Tipos básicos de componentes

### Componente con props tipadas

```tsx
interface ButtonProps {
  label: string
  variant?: 'primary' | 'secondary' | 'danger'
  disabled?: boolean
  onClick: () => void
}

function Button({ label, variant = 'primary', disabled = false, onClick }: ButtonProps) {
  return (
    <button className={`btn btn-${variant}`} disabled={disabled} onClick={onClick}>
      {label}
    </button>
  )
}
```

### Props con children

```tsx
// React.ReactNode: acepta cualquier cosa renderizable
interface CardProps {
  title: string
  children: React.ReactNode
}

// React.PropsWithChildren: shortcut para agregar children a tus props
type CardProps2 = React.PropsWithChildren<{
  title: string
}>
```

---

## Tipos de eventos

```tsx
function EventExamples() {
  // Evento de clic
  const handleClick = (e: React.MouseEvent<HTMLButtonElement>) => {
    console.log(e.currentTarget.textContent)
  }

  // Evento de cambio en input
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    console.log(e.target.value)
  }

  // Evento de envío de formulario
  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    const formData = new FormData(e.currentTarget)
  }

  // Evento de teclado
  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') console.log('Enter presionado')
  }

  return (
    <form onSubmit={handleSubmit}>
      <input onChange={handleChange} onKeyDown={handleKeyDown} />
      <button onClick={handleClick}>Enviar</button>
    </form>
  )
}
```

---

## Tipado de hooks

### useState

```tsx
// Inferido automáticamente
const [count, setCount] = useState(0)           // number
const [name, setName] = useState('Ana')          // string
const [active, setActive] = useState(true)       // boolean

// Explícito cuando el tipo inicial no es suficiente
const [user, setUser] = useState<User | null>(null)
const [items, setItems] = useState<Item[]>([])
const [status, setStatus] = useState<'idle' | 'loading' | 'error'>('idle')
```

### useRef

```tsx
// Ref al DOM (inicializado como null, no mutable por nosotros)
const inputRef = useRef<HTMLInputElement>(null)
const divRef = useRef<HTMLDivElement>(null)

// Ref mutable (almacenar valor sin re-render)
const timerRef = useRef<number | null>(null)
const countRef = useRef(0) // inferido como MutableRefObject<number>
```

### useReducer

```tsx
interface State {
  count: number
  error: string | null
}

type Action =
  | { type: 'increment'; payload: number }
  | { type: 'decrement'; payload: number }
  | { type: 'reset' }
  | { type: 'error'; payload: string }

function reducer(state: State, action: Action): State {
  // TypeScript sabe exactamente qué payload tiene cada action
  switch (action.type) {
    case 'increment':
      return { ...state, count: state.count + action.payload }
    case 'error':
      return { ...state, error: action.payload }
    case 'reset':
      return { count: 0, error: null }
    default:
      return state
  }
}
```

---

## Componentes genéricos

```tsx
interface ListProps<T> {
  items: T[]
  renderItem: (item: T) => React.ReactNode
  keyExtractor: (item: T) => string | number
  emptyMessage?: string
}

function List<T>({ items, renderItem, keyExtractor, emptyMessage = 'Sin resultados' }: ListProps<T>) {
  if (items.length === 0) return <p>{emptyMessage}</p>

  return (
    <ul>
      {items.map(item => (
        <li key={keyExtractor(item)}>
          {renderItem(item)}
        </li>
      ))}
    </ul>
  )
}

// Uso — T se infiere automáticamente
<List
  items={users}
  keyExtractor={u => u.id}
  renderItem={u => <span>{u.name} ({u.email})</span>}
/>
```

### Select genérico

```tsx
interface SelectProps<T> {
  options: T[]
  value: T | null
  onChange: (value: T) => void
  getLabel: (option: T) => string
  getValue: (option: T) => string
}

function Select<T>({ options, value, onChange, getLabel, getValue }: SelectProps<T>) {
  return (
    <select
      value={value ? getValue(value) : ''}
      onChange={e => {
        const selected = options.find(o => getValue(o) === e.target.value)
        if (selected) onChange(selected)
      }}
    >
      <option value="">Seleccionar...</option>
      {options.map(option => (
        <option key={getValue(option)} value={getValue(option)}>
          {getLabel(option)}
        </option>
      ))}
    </select>
  )
}
```

---

## Extender elementos HTML nativos

```tsx
// Heredar todas las props nativas de <button>
interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary'
  loading?: boolean
}

function Button({ variant = 'primary', loading, children, ...rest }: ButtonProps) {
  return (
    <button className={`btn btn-${variant}`} disabled={loading} {...rest}>
      {loading ? 'Cargando...' : children}
    </button>
  )
}

// Ahora acepta TODAS las props nativas de <button>:
<Button onClick={handleClick} type="submit" variant="primary" aria-label="Guardar">
  Guardar
</Button>
```

### Con forwardRef

```tsx
interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label: string
  error?: string
}

const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ label, error, ...rest }, ref) => {
    return (
      <div>
        <label>{label}</label>
        <input ref={ref} {...rest} />
        {error && <span className="error">{error}</span>}
      </div>
    )
  }
)

Input.displayName = 'Input'
```

---

## Tipos discriminados (Discriminated Unions)

Perfectos para componentes con variantes:

```tsx
type AlertProps =
  | { variant: 'success'; message: string }
  | { variant: 'error'; message: string; onRetry: () => void }
  | { variant: 'loading' }

function Alert(props: AlertProps) {
  switch (props.variant) {
    case 'success':
      return <div className="alert-success">✅ {props.message}</div>
    case 'error':
      return (
        <div className="alert-error">
          ❌ {props.message}
          <button onClick={props.onRetry}>Reintentar</button>
        </div>
      )
    case 'loading':
      return <div className="alert-loading">⏳ Cargando...</div>
  }
}

// TypeScript fuerza las props correctas:
<Alert variant="success" message="Guardado" />           // ✅
<Alert variant="error" message="Falló" onRetry={retry} /> // ✅
<Alert variant="error" message="Falló" />                 // ❌ Falta onRetry
<Alert variant="loading" />                               // ✅
```

---

## Tipos útiles de React

| Tipo | Uso |
|---|---|
| `React.ReactNode` | Cualquier cosa renderizable |
| `React.ReactElement` | Solo elementos JSX |
| `React.FC<Props>` | Tipo de componente funcional (controversia: algunos lo evitan) |
| `React.CSSProperties` | Objeto de estilos inline |
| `React.ComponentProps<typeof MyComp>` | Extraer props de un componente |
| `React.ComponentProps<'button'>` | Props nativas de un elemento HTML |
| `React.PropsWithChildren<P>` | Agrega `children: ReactNode` a `P` |

---

## Resumen

| Concepto | Ejemplo |
|---|---|
| Props tipadas | `interface Props { name: string }` |
| Eventos | `React.MouseEvent<HTMLButtonElement>` |
| useState con tipo | `useState<User \| null>(null)` |
| Componente genérico | `function List<T>({ items }: { items: T[] })` |
| Extender HTML | `extends React.ButtonHTMLAttributes<HTMLButtonElement>` |
| Discriminated unions | Props que cambian según `variant` |
| forwardRef tipado | `React.forwardRef<HTMLInputElement, Props>` |
