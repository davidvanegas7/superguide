# Context API y Estado Global

Cuando múltiples componentes en distintas partes del árbol necesitan acceder a los mismos datos, la **Context API** permite compartir estado sin pasar props por cada nivel intermedio (**prop drilling**).

---

## El problema del prop drilling

```tsx
// Sin Context: las props viajan por componentes que no las usan
function App() {
  const [user, setUser] = useState<User>(currentUser)
  return <Layout user={user} onLogout={() => setUser(null)} />
}

function Layout({ user, onLogout }) {
  return <Header user={user} onLogout={onLogout} />  // Layout no usa user, solo lo pasa
}

function Header({ user, onLogout }) {
  return <UserMenu user={user} onLogout={onLogout} />  // Header tampoco
}

function UserMenu({ user, onLogout }) {
  // El único que realmente necesita user y onLogout
  return <span>{user.name} <button onClick={onLogout}>Salir</button></span>
}
```

Con Context, `UserMenu` accede directamente al usuario sin que `Layout` ni `Header` lo transporten.

---

## Crear un Context paso a paso

### 1. Definir el tipo y crear el contexto

```tsx
import { createContext, useContext, useState, useCallback } from 'react'

interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'user'
}

interface AuthContextType {
  user: User | null
  isAuthenticated: boolean
  login: (email: string, password: string) => Promise<void>
  logout: () => void
}

const AuthContext = createContext<AuthContextType | null>(null)
```

### 2. Crear el Provider

```tsx
function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null)

  const login = useCallback(async (email: string, password: string) => {
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    })
    if (!res.ok) throw new Error('Credenciales inválidas')
    const data = await res.json()
    setUser(data.user)
    localStorage.setItem('token', data.token)
  }, [])

  const logout = useCallback(() => {
    setUser(null)
    localStorage.removeItem('token')
  }, [])

  return (
    <AuthContext.Provider value={{
      user,
      isAuthenticated: !!user,
      login,
      logout,
    }}>
      {children}
    </AuthContext.Provider>
  )
}
```

### 3. Custom hook para consumir

```tsx
function useAuth() {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth debe usarse dentro de <AuthProvider>')
  }
  return context
}
```

### 4. Envolver la app y consumir

```tsx
// main.tsx
function App() {
  return (
    <AuthProvider>
      <Router />
    </AuthProvider>
  )
}

// En cualquier componente:
function Navbar() {
  const { user, isAuthenticated, logout } = useAuth()

  return (
    <nav>
      {isAuthenticated ? (
        <>
          <span>Hola, {user!.name}</span>
          <button onClick={logout}>Cerrar sesión</button>
        </>
      ) : (
        <Link to="/login">Iniciar sesión</Link>
      )}
    </nav>
  )
}
```

---

## Múltiples Contexts

Crea un contexto por **dominio** o **responsabilidad**:

```tsx
function App() {
  return (
    <AuthProvider>
      <ThemeProvider>
        <NotificationProvider>
          <CartProvider>
            <Router />
          </CartProvider>
        </NotificationProvider>
      </ThemeProvider>
    </AuthProvider>
  )
}
```

### Context de tema completo

```tsx
type Theme = 'light' | 'dark'

interface ThemeContextType {
  theme: Theme
  toggleTheme: () => void
  colors: { bg: string; text: string; primary: string }
}

const themes = {
  light: { bg: '#ffffff', text: '#1a1a1a', primary: '#3b82f6' },
  dark:  { bg: '#1a1a1a', text: '#f5f5f5', primary: '#60a5fa' },
}

const ThemeContext = createContext<ThemeContextType | null>(null)

function ThemeProvider({ children }: { children: React.ReactNode }) {
  const [theme, setTheme] = useState<Theme>(() => {
    return (localStorage.getItem('theme') as Theme) || 'light'
  })

  const toggleTheme = useCallback(() => {
    setTheme(prev => {
      const next = prev === 'light' ? 'dark' : 'light'
      localStorage.setItem('theme', next)
      return next
    })
  }, [])

  return (
    <ThemeContext.Provider value={{ theme, toggleTheme, colors: themes[theme] }}>
      {children}
    </ThemeContext.Provider>
  )
}

function useTheme() {
  const ctx = useContext(ThemeContext)
  if (!ctx) throw new Error('useTheme debe usarse dentro de ThemeProvider')
  return ctx
}
```

---

## Context + useReducer (patrón escalable)

Para estado complejo, combinar Context con `useReducer` es un patrón potente (mini Redux):

```tsx
// ── Tipos ─────────────────────────────────────────────────────────────────
interface CartItem {
  id: number
  name: string
  price: number
  quantity: number
}

interface CartState {
  items: CartItem[]
  total: number
}

type CartAction =
  | { type: 'ADD_ITEM'; payload: Omit<CartItem, 'quantity'> }
  | { type: 'REMOVE_ITEM'; payload: number }
  | { type: 'UPDATE_QUANTITY'; payload: { id: number; quantity: number } }
  | { type: 'CLEAR' }

// ── Reducer ───────────────────────────────────────────────────────────────
function cartReducer(state: CartState, action: CartAction): CartState {
  switch (action.type) {
    case 'ADD_ITEM': {
      const existing = state.items.find(i => i.id === action.payload.id)
      const items = existing
        ? state.items.map(i => i.id === action.payload.id
            ? { ...i, quantity: i.quantity + 1 }
            : i)
        : [...state.items, { ...action.payload, quantity: 1 }]
      return { items, total: items.reduce((s, i) => s + i.price * i.quantity, 0) }
    }
    case 'REMOVE_ITEM': {
      const items = state.items.filter(i => i.id !== action.payload)
      return { items, total: items.reduce((s, i) => s + i.price * i.quantity, 0) }
    }
    case 'UPDATE_QUANTITY': {
      const items = state.items.map(i =>
        i.id === action.payload.id ? { ...i, quantity: action.payload.quantity } : i
      ).filter(i => i.quantity > 0)
      return { items, total: items.reduce((s, i) => s + i.price * i.quantity, 0) }
    }
    case 'CLEAR':
      return { items: [], total: 0 }
    default:
      return state
  }
}

// ── Provider ──────────────────────────────────────────────────────────────
const CartContext = createContext<{
  state: CartState
  dispatch: React.Dispatch<CartAction>
} | null>(null)

function CartProvider({ children }: { children: React.ReactNode }) {
  const [state, dispatch] = useReducer(cartReducer, { items: [], total: 0 })

  return (
    <CartContext.Provider value={{ state, dispatch }}>
      {children}
    </CartContext.Provider>
  )
}

// ── Hook con acciones de alto nivel ───────────────────────────────────────
function useCart() {
  const ctx = useContext(CartContext)
  if (!ctx) throw new Error('useCart debe usarse dentro de CartProvider')

  const { state, dispatch } = ctx

  return {
    items: state.items,
    total: state.total,
    itemCount: state.items.reduce((s, i) => s + i.quantity, 0),
    addItem: (item: Omit<CartItem, 'quantity'>) =>
      dispatch({ type: 'ADD_ITEM', payload: item }),
    removeItem: (id: number) =>
      dispatch({ type: 'REMOVE_ITEM', payload: id }),
    updateQuantity: (id: number, quantity: number) =>
      dispatch({ type: 'UPDATE_QUANTITY', payload: { id, quantity } }),
    clear: () => dispatch({ type: 'CLEAR' }),
  }
}
```

---

## Cuándo usar Context vs otras soluciones

| Escenario | Solución |
|---|---|
| Tema, idioma, auth global | ✅ Context |
| Estado de un formulario | ❌ useState / useReducer local |
| Datos de servidor (caché, revalidación) | ❌ TanStack Query / SWR |
| Estado global complejo (muchas acciones) | Considerar Redux Toolkit |
| Datos que solo necesita un componente | ❌ Estado local |

---

## Optimización de Context

Cuando el valor del Context cambia, **todos** los consumidores se re-renderizan. Estrategias para evitarlo:

### Separar contexts por frecuencia de cambio

```tsx
// ❌ Un solo context que cambia mucho
<AppContext.Provider value={{ user, theme, notifications, cart }}>

// ✅ Separar en contexts independientes
<AuthProvider>
  <ThemeProvider>
    <NotificationProvider>
      <CartProvider>
```

### Memoizar el value del Provider

```tsx
function MyProvider({ children }) {
  const [value, setValue] = useState(0)

  // ✅ Memoizar para que la referencia no cambie en cada render del provider
  const contextValue = useMemo(() => ({
    value,
    increment: () => setValue(v => v + 1),
  }), [value])

  return (
    <MyContext.Provider value={contextValue}>
      {children}
    </MyContext.Provider>
  )
}
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| `createContext()` | Crea un contexto con valor por defecto |
| `<Context.Provider>` | Provee el valor a los descendientes |
| `useContext()` | Consume el valor del contexto más cercano |
| Custom hook (`useAuth`) | Encapsula `useContext` + validación |
| Context + useReducer | Mini Redux para estado compartido complejo |
| Separar contexts | Optimización: evita re-renders innecesarios |
