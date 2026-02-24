# Performance y Optimización

React es rápido por defecto gracias al Virtual DOM, pero aplicaciones grandes pueden sufrir re-renders innecesarios. Esta lección cubre las técnicas clave para optimizar el rendimiento.

---

## ¿Cuándo optimizar?

> **Regla de oro**: primero haz que funcione, luego haz que sea rápido. Mide antes de optimizar.

Señales de que necesitas optimizar:
- Listas con cientos o miles de elementos
- Inputs que se sienten lentos al escribir
- Animaciones que no son fluidas (< 60 fps)
- Re-renders visibles en React DevTools Profiler

---

## React.memo: evitar re-renders de hijos

`React.memo` envuelve un componente para que solo se re-renderice si sus **props cambian**:

```tsx
import { memo } from 'react'

interface ProductCardProps {
  name: string
  price: number
  onBuy: () => void
}

const ProductCard = memo(function ProductCard({ name, price, onBuy }: ProductCardProps) {
  console.log(`Renderizando: ${name}`)
  return (
    <div className="card">
      <h3>{name}</h3>
      <p>${price}</p>
      <button onClick={onBuy}>Comprar</button>
    </div>
  )
})
```

### Custom comparator

```tsx
const HeavyComponent = memo(MyComponent, (prevProps, nextProps) => {
  // Retorna true si NO debe re-renderizar (props iguales)
  return prevProps.id === nextProps.id && prevProps.name === nextProps.name
})
```

### ¿Cuándo usar memo?

- ✅ Componentes que reciben las mismas props frecuentemente
- ✅ Componentes costosos de renderizar (listas, gráficos)
- ❌ Componentes simples y baratos (un `<p>` o `<span>`)
- ❌ Componentes cuyas props cambian en cada render (sin `useCallback`)

---

## useMemo: cachear cálculos

```tsx
function SearchResults({ items, query }: { items: Item[]; query: string }) {
  // ✅ Solo recalcula cuando items o query cambian
  const filtered = useMemo(() => {
    return items
      .filter(item => item.name.toLowerCase().includes(query.toLowerCase()))
      .sort((a, b) => a.name.localeCompare(b.name))
  }, [items, query])

  return (
    <ul>
      {filtered.map(item => <li key={item.id}>{item.name}</li>)}
    </ul>
  )
}
```

---

## useCallback: estabilizar funciones

Sin `useCallback`, cada render crea una nueva referencia de función, rompiendo `memo` en los hijos:

```tsx
function TodoList() {
  const [todos, setTodos] = useState<Todo[]>([])

  // ✅ Referencia estable — no cambia entre renders
  const handleToggle = useCallback((id: number) => {
    setTodos(prev => prev.map(t => t.id === id ? { ...t, done: !t.done } : t))
  }, [])

  const handleDelete = useCallback((id: number) => {
    setTodos(prev => prev.filter(t => t.id !== id))
  }, [])

  return (
    <ul>
      {todos.map(todo => (
        <TodoItem
          key={todo.id}
          todo={todo}
          onToggle={handleToggle}
          onDelete={handleDelete}
        />
      ))}
    </ul>
  )
}

const TodoItem = memo(function TodoItem({ todo, onToggle, onDelete }: TodoItemProps) {
  return (
    <li>
      <span onClick={() => onToggle(todo.id)}>{todo.text}</span>
      <button onClick={() => onDelete(todo.id)}>🗑️</button>
    </li>
  )
})
```

---

## Lazy loading y Code splitting

Carga componentes solo cuando se necesitan con `React.lazy()`:

```tsx
import { lazy, Suspense } from 'react'

// El componente se descarga solo cuando se necesita renderizar
const AdminPanel = lazy(() => import('./pages/AdminPanel'))
const Charts = lazy(() => import('./components/Charts'))
const Settings = lazy(() => import('./pages/Settings'))

function App() {
  return (
    <Suspense fallback={<div className="spinner">Cargando...</div>}>
      <Routes>
        <Route path="/admin" element={<AdminPanel />} />
        <Route path="/charts" element={<Charts />} />
        <Route path="/settings" element={<Settings />} />
      </Routes>
    </Suspense>
  )
}
```

### Lazy loading condicional

```tsx
const HeavyEditor = lazy(() => import('./HeavyEditor'))

function Document() {
  const [isEditing, setIsEditing] = useState(false)

  return (
    <div>
      <button onClick={() => setIsEditing(true)}>Editar</button>
      {isEditing && (
        <Suspense fallback={<p>Cargando editor...</p>}>
          <HeavyEditor />
        </Suspense>
      )}
    </div>
  )
}
```

---

## Virtualización de listas

Para listas con miles de elementos, solo renderizar lo visible:

```tsx
import { useVirtualizer } from '@tanstack/react-virtual'

function VirtualList({ items }: { items: string[] }) {
  const parentRef = useRef<HTMLDivElement>(null)

  const virtualizer = useVirtualizer({
    count: items.length,
    getScrollElement: () => parentRef.current,
    estimateSize: () => 40, // altura estimada de cada fila
  })

  return (
    <div ref={parentRef} style={{ height: '400px', overflow: 'auto' }}>
      <div style={{ height: `${virtualizer.getTotalSize()}px`, position: 'relative' }}>
        {virtualizer.getVirtualItems().map(virtualRow => (
          <div
            key={virtualRow.key}
            style={{
              position: 'absolute',
              top: 0,
              transform: `translateY(${virtualRow.start}px)`,
              height: `${virtualRow.size}px`,
              width: '100%',
            }}
          >
            {items[virtualRow.index]}
          </div>
        ))}
      </div>
    </div>
  )
}
```

---

## useTransition: priorizar actualizaciones

`useTransition` marca actualizaciones como **no urgentes**, permitiendo que la UI siga respondiendo:

```tsx
import { useState, useTransition } from 'react'

function SearchWithTransition() {
  const [query, setQuery] = useState('')
  const [results, setResults] = useState<string[]>([])
  const [isPending, startTransition] = useTransition()

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value
    setQuery(value) // Urgente: actualizar el input inmediatamente

    startTransition(() => {
      // No urgente: filtrar la lista puede esperar
      setResults(filterLargeList(value))
    })
  }

  return (
    <div>
      <input value={query} onChange={handleChange} />
      {isPending && <p>Buscando...</p>}
      <ul>
        {results.map((r, i) => <li key={i}>{r}</li>)}
      </ul>
    </div>
  )
}
```

---

## useDeferredValue

Similar a `useTransition` pero para valores que recibes como props:

```tsx
import { useDeferredValue, useMemo } from 'react'

function SlowList({ query }: { query: string }) {
  const deferredQuery = useDeferredValue(query)

  const items = useMemo(() => {
    return generateHugeList().filter(item =>
      item.includes(deferredQuery)
    )
  }, [deferredQuery])

  return (
    <ul style={{ opacity: query !== deferredQuery ? 0.5 : 1 }}>
      {items.map((item, i) => <li key={i}>{item}</li>)}
    </ul>
  )
}
```

---

## Checklist de optimización

| Técnica | Cuándo usarla |
|---|---|
| `React.memo` | Componente se re-renderiza con mismas props |
| `useMemo` | Cálculo costoso que no cambia frecuentemente |
| `useCallback` | Función pasada como prop a componente `memo` |
| `React.lazy` | Reducir el bundle inicial (code splitting) |
| Virtualización | Listas con 100+ elementos visibles |
| `useTransition` | Actualizaciones pesadas que bloquean el input |
| `useDeferredValue` | Retrasar re-render de parte lenta de la UI |
| `key` estable | Evitar re-montaje innecesario de componentes |
| Mover estado abajo | Localizar estado para reducir scope de re-render |

---

## React DevTools Profiler

```
1. Instala React DevTools (extensión del navegador)
2. Abre la pestaña "Profiler"
3. Haz clic en "Record"
4. Interactúa con la app
5. Detén la grabación
6. Analiza:
   - Qué componentes se re-renderizan
   - Cuánto tiempo toma cada render
   - Por qué se re-renderizó (con "Why did this render?")
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| `memo()` | Evita re-render si props no cambiaron |
| `useMemo()` | Cachea resultado de cálculo |
| `useCallback()` | Cachea referencia de función |
| `lazy()` + `<Suspense>` | Code splitting, carga bajo demanda |
| `useTransition` | Marca actualizaciones como no urgentes |
| `useDeferredValue` | Valor diferido para partes lentas de la UI |
| **Virtualización** | Solo renderizar elementos visibles en listas grandes |
| **Profiler** | Medir antes de optimizar |
