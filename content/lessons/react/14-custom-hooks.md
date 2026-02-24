# Custom Hooks

Los **custom hooks** son funciones JavaScript que encapsulan lógica reutilizable con hooks de React. Su nombre empieza con `use` y pueden usar cualquier hook internamente. Son la forma principal de **compartir lógica** entre componentes sin duplicar código.

---

## ¿Qué es un custom hook?

Un custom hook es simplemente una función que:

1. Su nombre empieza con `use` (convención obligatoria)
2. Puede llamar otros hooks (`useState`, `useEffect`, etc.)
3. Retorna lo que necesite el componente consumidor

```tsx
// ❌ Esto NO es un hook (no empieza con "use")
function getWindowSize() {
  const [size, setSize] = useState({ width: 0, height: 0 }) // Error en runtime
  return size
}

// ✅ Esto SÍ es un hook
function useWindowSize() {
  const [size, setSize] = useState({ width: window.innerWidth, height: window.innerHeight })

  useEffect(() => {
    const handler = () => setSize({ width: window.innerWidth, height: window.innerHeight })
    window.addEventListener('resize', handler)
    return () => window.removeEventListener('resize', handler)
  }, [])

  return size
}
```

---

## Hook: useLocalStorage

Sincroniza estado con `localStorage`:

```tsx
function useLocalStorage<T>(key: string, initialValue: T) {
  const [storedValue, setStoredValue] = useState<T>(() => {
    try {
      const item = localStorage.getItem(key)
      return item ? JSON.parse(item) : initialValue
    } catch {
      return initialValue
    }
  })

  const setValue = (value: T | ((prev: T) => T)) => {
    const valueToStore = value instanceof Function ? value(storedValue) : value
    setStoredValue(valueToStore)
    localStorage.setItem(key, JSON.stringify(valueToStore))
  }

  return [storedValue, setValue] as const
}

// Uso:
function Settings() {
  const [theme, setTheme] = useLocalStorage<'light' | 'dark'>('theme', 'light')

  return (
    <button onClick={() => setTheme(t => t === 'light' ? 'dark' : 'light')}>
      Tema: {theme}
    </button>
  )
}
```

---

## Hook: useFetch

Fetching de datos genérico:

```tsx
interface UseFetchResult<T> {
  data: T | null
  loading: boolean
  error: string | null
  refetch: () => void
}

function useFetch<T>(url: string): UseFetchResult<T> {
  const [data, setData] = useState<T | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const fetchData = useCallback(async () => {
    const controller = new AbortController()

    try {
      setLoading(true)
      setError(null)
      const res = await fetch(url, { signal: controller.signal })
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      const json = await res.json()
      setData(json)
    } catch (err) {
      if (err instanceof Error && err.name !== 'AbortError') {
        setError(err.message)
      }
    } finally {
      setLoading(false)
    }

    return () => controller.abort()
  }, [url])

  useEffect(() => {
    fetchData()
  }, [fetchData])

  return { data, loading, error, refetch: fetchData }
}

// Uso:
function UserList() {
  const { data: users, loading, error, refetch } = useFetch<User[]>('/api/users')

  if (loading) return <p>Cargando...</p>
  if (error) return <p>Error: {error} <button onClick={refetch}>Reintentar</button></p>

  return <ul>{users?.map(u => <li key={u.id}>{u.name}</li>)}</ul>
}
```

---

## Hook: useDebounce

Retrasa un valor hasta que deje de cambiar:

```tsx
function useDebounce<T>(value: T, delay: number): T {
  const [debouncedValue, setDebouncedValue] = useState(value)

  useEffect(() => {
    const handler = setTimeout(() => setDebouncedValue(value), delay)
    return () => clearTimeout(handler)
  }, [value, delay])

  return debouncedValue
}

// Uso: buscar solo después de que el usuario deje de escribir por 300ms
function SearchBar() {
  const [query, setQuery] = useState('')
  const debouncedQuery = useDebounce(query, 300)

  useEffect(() => {
    if (debouncedQuery) {
      searchAPI(debouncedQuery).then(setResults)
    }
  }, [debouncedQuery])

  return <input value={query} onChange={e => setQuery(e.target.value)} />
}
```

---

## Hook: useToggle

Estado booleano con toggle:

```tsx
function useToggle(initial = false) {
  const [value, setValue] = useState(initial)

  const toggle = useCallback(() => setValue(v => !v), [])
  const setTrue = useCallback(() => setValue(true), [])
  const setFalse = useCallback(() => setValue(false), [])

  return { value, toggle, setTrue, setFalse } as const
}

// Uso:
function Modal() {
  const { value: isOpen, toggle, setFalse: close } = useToggle()

  return (
    <>
      <button onClick={toggle}>Abrir modal</button>
      {isOpen && (
        <div className="modal">
          <p>Contenido del modal</p>
          <button onClick={close}>Cerrar</button>
        </div>
      )}
    </>
  )
}
```

---

## Hook: useMediaQuery

Detectar breakpoints CSS:

```tsx
function useMediaQuery(query: string): boolean {
  const [matches, setMatches] = useState(() => window.matchMedia(query).matches)

  useEffect(() => {
    const media = window.matchMedia(query)
    const handler = (e: MediaQueryListEvent) => setMatches(e.matches)

    media.addEventListener('change', handler)
    setMatches(media.matches)

    return () => media.removeEventListener('change', handler)
  }, [query])

  return matches
}

// Uso:
function Layout() {
  const isMobile = useMediaQuery('(max-width: 768px)')
  const isDark = useMediaQuery('(prefers-color-scheme: dark)')

  return isMobile ? <MobileNav /> : <DesktopNav />
}
```

---

## Hook: useOnClickOutside

Detectar clics fuera de un elemento:

```tsx
function useOnClickOutside(
  ref: React.RefObject<HTMLElement>,
  handler: () => void
) {
  useEffect(() => {
    const listener = (e: MouseEvent | TouchEvent) => {
      if (!ref.current || ref.current.contains(e.target as Node)) return
      handler()
    }

    document.addEventListener('mousedown', listener)
    document.addEventListener('touchstart', listener)

    return () => {
      document.removeEventListener('mousedown', listener)
      document.removeEventListener('touchstart', listener)
    }
  }, [ref, handler])
}

// Uso:
function Dropdown() {
  const ref = useRef<HTMLDivElement>(null)
  const { value: isOpen, toggle, setFalse: close } = useToggle()

  useOnClickOutside(ref, close)

  return (
    <div ref={ref}>
      <button onClick={toggle}>Menú</button>
      {isOpen && <ul className="dropdown">...</ul>}
    </div>
  )
}
```

---

## Hook: useForm (genérico)

```tsx
function useForm<T extends Record<string, any>>(initialValues: T) {
  const [values, setValues] = useState(initialValues)
  const [errors, setErrors] = useState<Partial<Record<keyof T, string>>>({})
  const [touched, setTouched] = useState<Partial<Record<keyof T, boolean>>>({})

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target
    const checked = (e.target as HTMLInputElement).checked
    setValues(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }))
  }

  const handleBlur = (e: React.FocusEvent<HTMLInputElement>) => {
    setTouched(prev => ({ ...prev, [e.target.name]: true }))
  }

  const reset = () => {
    setValues(initialValues)
    setErrors({})
    setTouched({})
  }

  return { values, errors, touched, handleChange, handleBlur, setErrors, reset }
}
```

---

## Buenas prácticas

| Práctica | Descripción |
|---|---|
| Nombre con `use` | Obligatorio: `useXxx` para que React aplique las reglas de hooks |
| Un hook, una responsabilidad | No mezcles fetching con validación de formularios |
| Retorna lo mínimo necesario | No expongas estado interno que el consumidor no necesita |
| Genérico cuando sea posible | Usa generics TypeScript (`<T>`) para hooks reutilizables |
| Testea el hook aislado | Usa `renderHook` de Testing Library |
| Documenta el contrato | Tipos claros para parámetros y return |

---

## Resumen

| Hook | Propósito |
|---|---|
| `useLocalStorage` | Persistir estado en localStorage |
| `useFetch` | Fetching genérico con loading/error |
| `useDebounce` | Retrasar valor para evitar llamadas excesivas |
| `useToggle` | Estado booleano con helpers |
| `useMediaQuery` | Detectar breakpoints CSS |
| `useOnClickOutside` | Detectar clics fuera de un elemento |
| `useForm` | Manejar estado de formularios genérico |
| `usePrevious` | Recordar el valor anterior de una variable |
