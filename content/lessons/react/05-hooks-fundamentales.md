# Hooks Fundamentales: useState y useEffect

Los **hooks** son funciones especiales de React que permiten usar estado, efectos secundarios y otras features en componentes funcionales. `useState` y `useEffect` son los dos hooks que usarás en prácticamente todos tus componentes.

---

## Reglas de los Hooks

Antes de profundizar, las reglas son **inquebrantables**:

1. **Solo se llaman en el nivel superior** del componente (nunca dentro de `if`, `for` o funciones anidadas)
2. **Solo se llaman desde componentes de React o custom hooks** (nunca desde funciones JavaScript normales)

```tsx
// ❌ NUNCA dentro de condicionales
function Bad() {
  if (condition) {
    const [value, setValue] = useState(0) // ¡Error!
  }
}

// ✅ Siempre al nivel superior
function Good() {
  const [value, setValue] = useState(0)
  // La lógica condicional va DENTRO del componente, no alrededor del hook
}
```

**¿Por qué?** React identifica cada hook por su **orden de llamada**. Si un hook se salta por un `if`, el orden cambia entre renders y React confunde los estados.

---

## useState — Repaso avanzado

### Estado lazy (inicialización costosa)

Si el valor inicial requiere un cálculo pesado, pasa una **función** en vez de un valor:

```tsx
// ❌ Se ejecuta en CADA render (desperdicio)
const [data, setData] = useState(expensiveCalculation())

// ✅ Se ejecuta SOLO en el primer render
const [data, setData] = useState(() => expensiveCalculation())
```

### Múltiples estados vs objeto

```tsx
// Opción A: múltiples useState (recomendado para estados independientes)
const [name, setName] = useState('')
const [age, setAge] = useState(0)
const [isActive, setIsActive] = useState(true)

// Opción B: un objeto (cuando los valores están relacionados)
const [form, setForm] = useState({ name: '', age: 0, isActive: true })
```

> **Guía**: si cambias un campo sin necesitar los demás, usa estados separados. Si siempre los cambias juntos, usa un objeto.

---

## useEffect — Efectos secundarios

`useEffect` ejecuta código **después del renderizado**. Es el lugar para:

- Hacer peticiones HTTP (fetch de datos)
- Suscribirse a eventos (WebSocket, resize, scroll)
- Manipular el DOM directamente
- Configurar timers (setTimeout, setInterval)

### Sintaxis

```tsx
useEffect(() => {
  // Código del efecto (se ejecuta después del render)

  return () => {
    // Cleanup (se ejecuta al desmontar o antes del siguiente efecto)
  }
}, [dependencias]) // Array de dependencias
```

---

## Array de dependencias

El array de dependencias controla **cuándo** se ejecuta el efecto:

### Sin array → Se ejecuta en CADA render

```tsx
useEffect(() => {
  console.log('Se ejecuta en cada render')
})
```

### Array vacío `[]` → Solo al montar (equivalente a `componentDidMount`)

```tsx
useEffect(() => {
  console.log('Solo al montar el componente')
  fetchInitialData()
}, []) // [] = sin dependencias = solo una vez
```

### Con dependencias → Cuando cambia alguna

```tsx
useEffect(() => {
  console.log('userId cambió, recargando datos...')
  fetchUser(userId)
}, [userId]) // se ejecuta cuando userId cambia
```

---

## Ejemplo: Fetching de datos

```tsx
interface User {
  id: number
  name: string
  email: string
}

function UserProfile({ userId }: { userId: number }) {
  const [user, setUser] = useState<User | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    setLoading(true)
    setError(null)

    fetch(`https://jsonplaceholder.typicode.com/users/${userId}`)
      .then(res => {
        if (!res.ok) throw new Error('Error al cargar usuario')
        return res.json()
      })
      .then((data: User) => setUser(data))
      .catch(err => setError(err.message))
      .finally(() => setLoading(false))
  }, [userId]) // Se re-ejecuta cuando cambia userId

  if (loading) return <p>Cargando...</p>
  if (error) return <p className="error">{error}</p>
  if (!user) return null

  return (
    <div>
      <h2>{user.name}</h2>
      <p>{user.email}</p>
    </div>
  )
}
```

---

## Cleanup: limpiar efectos

El cleanup es crucial para evitar **memory leaks** y comportamiento inesperado:

### Limpiar subscripciones

```tsx
useEffect(() => {
  const handleResize = () => {
    setWindowWidth(window.innerWidth)
  }

  window.addEventListener('resize', handleResize)

  // Cleanup: se ejecuta al desmontar o antes del siguiente efecto
  return () => {
    window.removeEventListener('resize', handleResize)
  }
}, [])
```

### Limpiar timers

```tsx
useEffect(() => {
  const intervalId = setInterval(() => {
    setSeconds(prev => prev + 1)
  }, 1000)

  return () => clearInterval(intervalId)
}, [])
```

### Cancelar fetch con AbortController

```tsx
useEffect(() => {
  const controller = new AbortController()

  fetch(`/api/search?q=${query}`, { signal: controller.signal })
    .then(res => res.json())
    .then(data => setResults(data))
    .catch(err => {
      if (err.name !== 'AbortError') {
        setError(err.message)
      }
    })

  return () => controller.abort() // Cancela la petición si el componente se desmonta
}, [query])
```

---

## Ciclo de vida del efecto

```
Componente se monta
    → Render inicial
    → useEffect se ejecuta (sin cleanup en el primer render)

Props/estado cambian
    → Re-render
    → Cleanup del efecto ANTERIOR se ejecuta
    → useEffect se ejecuta con los nuevos valores

Componente se desmonta
    → Cleanup final se ejecuta
```

---

## Errores comunes con useEffect

### 1. Bucle infinito

```tsx
// ❌ Bucle infinito: setData → re-render → useEffect → setData → ...
useEffect(() => {
  fetch('/api/data')
    .then(res => res.json())
    .then(data => setData(data))
}) // ¡Sin array de dependencias!

// ✅ Depende solo de lo necesario
useEffect(() => {
  fetch('/api/data')
    .then(res => res.json())
    .then(data => setData(data))
}, []) // Solo al montar
```

### 2. Objetos/arrays como dependencias

```tsx
// ❌ Se re-ejecuta en CADA render (nuevo objeto cada vez)
const options = { page: 1, limit: 10 }
useEffect(() => {
  fetchData(options)
}, [options]) // options es un nuevo objeto en cada render

// ✅ Usa valores primitivos como dependencias
useEffect(() => {
  fetchData({ page, limit })
}, [page, limit])
```

### 3. Closure sobre estado stale

```tsx
// ❌ Captura el valor de count del render en que se creó
useEffect(() => {
  const id = setInterval(() => {
    console.log(count) // Siempre el valor del primer render
  }, 1000)
  return () => clearInterval(id)
}, []) // [] = nunca se actualiza el closure

// ✅ Usa la forma funcional del setter
useEffect(() => {
  const id = setInterval(() => {
    setCount(prev => prev + 1) // Siempre tiene el valor actual
  }, 1000)
  return () => clearInterval(id)
}, [])
```

---

## Múltiples useEffect

Agrupa efectos por **propósito**, no pongas todo en un solo useEffect:

```tsx
function Dashboard({ userId }: { userId: number }) {
  const [user, setUser] = useState(null)
  const [posts, setPosts] = useState([])

  // Efecto 1: cargar datos del usuario
  useEffect(() => {
    fetchUser(userId).then(setUser)
  }, [userId])

  // Efecto 2: cargar posts del usuario
  useEffect(() => {
    fetchPosts(userId).then(setPosts)
  }, [userId])

  // Efecto 3: actualizar el título del documento
  useEffect(() => {
    document.title = user ? `${user.name} - Dashboard` : 'Dashboard'
  }, [user])
}
```

---

## useEffect vs manejadores de eventos

| Qué hace | useEffect | Event handler |
|---|---|---|
| Sincronizar con sistema externo | ✅ | ❌ |
| Responder a acción del usuario | ❌ | ✅ |
| Fetch al montar/cambiar deps | ✅ | ❌ |
| Enviar formulario | ❌ | ✅ |
| Abrir un modal al hacer clic | ❌ | ✅ |
| Actualizar título del DOM | ✅ | ❌ |

> **Regla**: si algo sucede **porque el usuario hizo algo**, va en un event handler. Si algo sucede **porque el componente se mostró o una prop cambió**, va en useEffect.

---

## Ejemplo completo: Temporizador

```tsx
import { useState, useEffect } from 'react'

function Timer() {
  const [seconds, setSeconds] = useState(0)
  const [isRunning, setIsRunning] = useState(false)

  useEffect(() => {
    if (!isRunning) return

    const id = setInterval(() => {
      setSeconds(prev => prev + 1)
    }, 1000)

    return () => clearInterval(id)
  }, [isRunning]) // solo depende de isRunning

  const formatTime = (s: number): string => {
    const mins = Math.floor(s / 60)
    const secs = s % 60
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  }

  return (
    <div>
      <h1>{formatTime(seconds)}</h1>
      <button onClick={() => setIsRunning(!isRunning)}>
        {isRunning ? '⏸ Pausar' : '▶️ Iniciar'}
      </button>
      <button onClick={() => { setIsRunning(false); setSeconds(0) }}>
        🔄 Reset
      </button>
    </div>
  )
}
```

---

## Resumen

| Hook | Propósito |
|---|---|
| `useState(initialValue)` | Estado local del componente |
| `useState(() => compute())` | Inicialización lazy (costo único) |
| `useEffect(fn, [])` | Ejecutar al montar (fetch inicial, suscripciones) |
| `useEffect(fn, [dep])` | Ejecutar cuando `dep` cambia |
| `useEffect(fn)` | Ejecutar en cada render (raro, casi nunca lo quieres) |
| Cleanup `return () => {}` | Limpiar timers, listeners, fetch al desmontar |
