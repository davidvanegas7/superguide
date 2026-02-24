# Hooks Avanzados: useReducer, useContext, useRef, useMemo y useCallback

Más allá de `useState` y `useEffect`, React ofrece hooks que resuelven patrones más complejos: estado con lógica sofisticada, valores compartidos, referencias al DOM y optimización de rendimiento.

---

## useReducer: estado complejo con acciones

`useReducer` es una alternativa a `useState` para gestionar estado con **lógica de actualización compleja** o múltiples sub-valores relacionados:

```tsx
import { useReducer } from 'react'

interface State {
  count: number
  step: number
}

type Action =
  | { type: 'increment' }
  | { type: 'decrement' }
  | { type: 'reset' }
  | { type: 'setStep'; payload: number }

function reducer(state: State, action: Action): State {
  switch (action.type) {
    case 'increment':
      return { ...state, count: state.count + state.step }
    case 'decrement':
      return { ...state, count: state.count - state.step }
    case 'reset':
      return { count: 0, step: 1 }
    case 'setStep':
      return { ...state, step: action.payload }
    default:
      return state
  }
}

function Counter() {
  const [state, dispatch] = useReducer(reducer, { count: 0, step: 1 })

  return (
    <div>
      <p>Contador: {state.count} (step: {state.step})</p>
      <button onClick={() => dispatch({ type: 'increment' })}>+</button>
      <button onClick={() => dispatch({ type: 'decrement' })}>-</button>
      <button onClick={() => dispatch({ type: 'reset' })}>Reset</button>
      <input
        type="number"
        value={state.step}
        onChange={e => dispatch({ type: 'setStep', payload: Number(e.target.value) })}
      />
    </div>
  )
}
```

### ¿useState o useReducer?

| Criterio | useState | useReducer |
|---|---|---|
| Estado simple (boolean, string, number) | ✅ | Overkill |
| Múltiples sub-valores relacionados | Incómodo | ✅ |
| Lógica de actualización compleja | ❌ | ✅ |
| Siguiente estado depende del anterior | Ambos | ✅ más claro |
| Testing de la lógica de estado | Difícil | ✅ Reducer es función pura |

---

## useContext: compartir datos sin prop drilling

**Prop drilling** es pasar props a través de muchos niveles de componentes que no los usan, solo para que lleguen al componente que los necesita.

### Crear un Context

```tsx
import { createContext, useContext, useState } from 'react'

// 1. Crear el contexto con tipo
interface ThemeContextType {
  theme: 'light' | 'dark'
  toggleTheme: () => void
}

const ThemeContext = createContext<ThemeContextType | null>(null)

// 2. Crear el Provider
function ThemeProvider({ children }: { children: React.ReactNode }) {
  const [theme, setTheme] = useState<'light' | 'dark'>('light')

  const toggleTheme = () => {
    setTheme(prev => prev === 'light' ? 'dark' : 'light')
  }

  return (
    <ThemeContext.Provider value={{ theme, toggleTheme }}>
      {children}
    </ThemeContext.Provider>
  )
}

// 3. Custom hook para consumir (recomendado)
function useTheme() {
  const context = useContext(ThemeContext)
  if (!context) {
    throw new Error('useTheme debe usarse dentro de ThemeProvider')
  }
  return context
}
```

### Usar el Context

```tsx
// En App.tsx: envolver con el Provider
function App() {
  return (
    <ThemeProvider>
      <Header />
      <MainContent />
    </ThemeProvider>
  )
}

// En cualquier componente hijo (sin importar la profundidad):
function Header() {
  const { theme, toggleTheme } = useTheme()

  return (
    <header className={`header-${theme}`}>
      <button onClick={toggleTheme}>
        {theme === 'light' ? '🌙' : '☀️'}
      </button>
    </header>
  )
}
```

### Context + useReducer (patrón escalable)

```tsx
interface AuthState {
  user: User | null
  isAuthenticated: boolean
  loading: boolean
}

type AuthAction =
  | { type: 'LOGIN_SUCCESS'; payload: User }
  | { type: 'LOGOUT' }
  | { type: 'SET_LOADING'; payload: boolean }

function authReducer(state: AuthState, action: AuthAction): AuthState {
  switch (action.type) {
    case 'LOGIN_SUCCESS':
      return { user: action.payload, isAuthenticated: true, loading: false }
    case 'LOGOUT':
      return { user: null, isAuthenticated: false, loading: false }
    case 'SET_LOADING':
      return { ...state, loading: action.payload }
    default:
      return state
  }
}

const AuthContext = createContext<{
  state: AuthState
  dispatch: React.Dispatch<AuthAction>
} | null>(null)

function AuthProvider({ children }: { children: React.ReactNode }) {
  const [state, dispatch] = useReducer(authReducer, {
    user: null, isAuthenticated: false, loading: true,
  })

  return (
    <AuthContext.Provider value={{ state, dispatch }}>
      {children}
    </AuthContext.Provider>
  )
}
```

---

## useRef: referencias sin re-render

`useRef` crea una referencia mutable que **persiste entre renders** sin causar re-renderizado al cambiar:

### Acceder al DOM

```tsx
import { useRef, useEffect } from 'react'

function AutoFocusInput() {
  const inputRef = useRef<HTMLInputElement>(null)

  useEffect(() => {
    inputRef.current?.focus() // Foco automático al montar
  }, [])

  return <input ref={inputRef} placeholder="Escribe aquí..." />
}
```

### Guardar valores mutables (sin re-render)

```tsx
function StopWatch() {
  const [seconds, setSeconds] = useState(0)
  const intervalRef = useRef<NodeJS.Timeout | null>(null)

  const start = () => {
    if (intervalRef.current) return // Ya corriendo
    intervalRef.current = setInterval(() => {
      setSeconds(prev => prev + 1)
    }, 1000)
  }

  const stop = () => {
    if (intervalRef.current) {
      clearInterval(intervalRef.current)
      intervalRef.current = null
    }
  }

  useEffect(() => {
    return () => stop() // Cleanup al desmontar
  }, [])

  return (
    <div>
      <p>{seconds}s</p>
      <button onClick={start}>Start</button>
      <button onClick={stop}>Stop</button>
    </div>
  )
}
```

### Guardar valor previo

```tsx
function usePrevious<T>(value: T): T | undefined {
  const ref = useRef<T | undefined>(undefined)

  useEffect(() => {
    ref.current = value
  }, [value])

  return ref.current
}

// Uso:
function Counter() {
  const [count, setCount] = useState(0)
  const prevCount = usePrevious(count)

  return (
    <p>
      Ahora: {count}, antes: {prevCount ?? 'N/A'}
    </p>
  )
}
```

---

## useMemo: cachear cálculos costosos

`useMemo` memoriza el **resultado** de un cálculo, solo recalculando cuando cambian sus dependencias:

```tsx
import { useMemo, useState } from 'react'

function ProductList({ products }: { products: Product[] }) {
  const [filter, setFilter] = useState('')
  const [sortBy, setSortBy] = useState<'name' | 'price'>('name')

  // ✅ Solo recalcula si products, filter o sortBy cambian
  const filteredAndSorted = useMemo(() => {
    console.log('Recalculando lista...')
    return products
      .filter(p => p.name.toLowerCase().includes(filter.toLowerCase()))
      .sort((a, b) => sortBy === 'name'
        ? a.name.localeCompare(b.name)
        : a.price - b.price
      )
  }, [products, filter, sortBy])

  return (
    <div>
      <input value={filter} onChange={e => setFilter(e.target.value)} />
      <ul>
        {filteredAndSorted.map(p => (
          <li key={p.id}>{p.name} — ${p.price}</li>
        ))}
      </ul>
    </div>
  )
}
```

### ¿Cuándo usarlo?

- ✅ Cálculos costosos (filtrar/ordenar arrays grandes, cómputos matemáticos)
- ✅ Evitar re-crear objetos/arrays que se pasan como props
- ❌ **No lo uses en todo**: el overhead de useMemo puede ser mayor que el cálculo

---

## useCallback: cachear funciones

`useCallback` memoriza la **referencia** de una función:

```tsx
import { useCallback, useState, memo } from 'react'

// Componente hijo envuelto en memo (solo re-renderiza si sus props cambian)
const ExpensiveButton = memo(({ onClick, label }: {
  onClick: () => void
  label: string
}) => {
  console.log(`Renderizando botón: ${label}`)
  return <button onClick={onClick}>{label}</button>
})

function Parent() {
  const [count, setCount] = useState(0)
  const [text, setText] = useState('')

  // ❌ Sin useCallback: nueva función en cada render → ExpensiveButton re-renderiza
  // const increment = () => setCount(c => c + 1)

  // ✅ Con useCallback: misma referencia entre renders → ExpensiveButton NO re-renderiza
  const increment = useCallback(() => {
    setCount(c => c + 1)
  }, [])

  return (
    <div>
      <p>Count: {count}</p>
      <input value={text} onChange={e => setText(e.target.value)} />
      <ExpensiveButton onClick={increment} label="Incrementar" />
    </div>
  )
}
```

### useMemo vs useCallback

```tsx
// Son equivalentes:
const memoizedValue = useMemo(() => computeExpensive(a, b), [a, b])
const memoizedFn = useCallback((x) => doSomething(x, a), [a])

// useCallback(fn, deps) es azúcar para useMemo(() => fn, deps)
```

---

## Resumen de hooks

| Hook | Propósito | Causa re-render |
|---|---|---|
| `useState` | Estado simple | ✅ Sí |
| `useReducer` | Estado complejo con acciones | ✅ Sí |
| `useEffect` | Efectos secundarios | No (pero puede cambiar estado) |
| `useContext` | Consumir Context | ✅ Sí (cuando el valor cambia) |
| `useRef` | Referencias mutables / DOM | ❌ No |
| `useMemo` | Cachear resultado de cálculo | ❌ No |
| `useCallback` | Cachear referencia de función | ❌ No |
