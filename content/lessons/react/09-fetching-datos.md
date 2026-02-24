# Fetching de Datos

Cargar datos desde APIs es fundamental en cualquier aplicación React. Esta lección cubre desde `fetch` nativo hasta patrones modernos con **TanStack Query (React Query)** y **Suspense**.

---

## fetch nativo con useEffect

El patrón más básico para cargar datos:

```tsx
import { useState, useEffect } from 'react'

interface Post {
  id: number
  title: string
  body: string
}

function PostList() {
  const [posts, setPosts] = useState<Post[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const controller = new AbortController()

    async function fetchPosts() {
      try {
        setLoading(true)
        const res = await fetch('https://jsonplaceholder.typicode.com/posts?_limit=10', {
          signal: controller.signal,
        })
        if (!res.ok) throw new Error(`HTTP ${res.status}`)
        const data: Post[] = await res.json()
        setPosts(data)
      } catch (err) {
        if (err instanceof Error && err.name !== 'AbortError') {
          setError(err.message)
        }
      } finally {
        setLoading(false)
      }
    }

    fetchPosts()
    return () => controller.abort()
  }, [])

  if (loading) return <p>Cargando posts...</p>
  if (error) return <p className="error">Error: {error}</p>

  return (
    <ul>
      {posts.map(post => (
        <li key={post.id}>{post.title}</li>
      ))}
    </ul>
  )
}
```

### Problemas del patrón manual

- Mucho boilerplate (loading, error, data en cada componente)
- Sin caché: cada vez que montas el componente, vuelve a hacer fetch
- Sin revalidación automática
- Race conditions si el usuario navega rápido

---

## Custom hook: useFetch

Extraer la lógica a un hook reutilizable:

```tsx
function useFetch<T>(url: string) {
  const [data, setData] = useState<T | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const controller = new AbortController()

    setLoading(true)
    setError(null)

    fetch(url, { signal: controller.signal })
      .then(res => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`)
        return res.json()
      })
      .then((data: T) => setData(data))
      .catch(err => {
        if (err.name !== 'AbortError') setError(err.message)
      })
      .finally(() => setLoading(false))

    return () => controller.abort()
  }, [url])

  return { data, loading, error }
}

// Uso:
function UserProfile({ userId }: { userId: number }) {
  const { data: user, loading, error } = useFetch<User>(
    `https://jsonplaceholder.typicode.com/users/${userId}`
  )

  if (loading) return <p>Cargando...</p>
  if (error) return <p>Error: {error}</p>
  if (!user) return null

  return <h1>{user.name}</h1>
}
```

---

## TanStack Query (React Query)

**TanStack Query** es la solución estándar de la industria para data fetching en React. Resuelve caché, revalidación, paginación, mutaciones y mucho más.

### Instalación

```bash
npm install @tanstack/react-query
```

### Configuración

```tsx
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000,    // Datos son "frescos" por 5 minutos
      retry: 2,                      // Reintentar 2 veces en error
    },
  },
})

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <MyApp />
    </QueryClientProvider>
  )
}
```

### useQuery: leer datos

```tsx
import { useQuery } from '@tanstack/react-query'

function PostList() {
  const {
    data: posts,
    isLoading,
    error,
    isError,
    refetch,
  } = useQuery({
    queryKey: ['posts'],                    // Clave única para caché
    queryFn: async () => {                  // Función que obtiene datos
      const res = await fetch('/api/posts')
      if (!res.ok) throw new Error('Error al cargar posts')
      return res.json() as Promise<Post[]>
    },
  })

  if (isLoading) return <p>Cargando...</p>
  if (isError) return <p>Error: {error.message}</p>

  return (
    <div>
      <button onClick={() => refetch()}>Recargar</button>
      <ul>
        {posts?.map(post => <li key={post.id}>{post.title}</li>)}
      </ul>
    </div>
  )
}
```

### Query keys dependientes

```tsx
// La query key incluye el parámetro → React Query cachea por userId
function UserPosts({ userId }: { userId: number }) {
  const { data } = useQuery({
    queryKey: ['users', userId, 'posts'],   // Cada userId tiene su caché
    queryFn: () => fetchUserPosts(userId),
    enabled: userId > 0,                     // No ejecutar si userId es inválido
  })

  return <ul>{data?.map(p => <li key={p.id}>{p.title}</li>)}</ul>
}
```

### useMutation: modificar datos

```tsx
import { useMutation, useQueryClient } from '@tanstack/react-query'

function CreatePostForm() {
  const queryClient = useQueryClient()

  const mutation = useMutation({
    mutationFn: async (newPost: { title: string; body: string }) => {
      const res = await fetch('/api/posts', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newPost),
      })
      if (!res.ok) throw new Error('Error al crear post')
      return res.json()
    },
    onSuccess: () => {
      // Invalidar la caché para recargar la lista
      queryClient.invalidateQueries({ queryKey: ['posts'] })
    },
  })

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    const formData = new FormData(e.currentTarget)
    mutation.mutate({
      title: formData.get('title') as string,
      body: formData.get('body') as string,
    })
  }

  return (
    <form onSubmit={handleSubmit}>
      <input name="title" required />
      <textarea name="body" required />
      <button disabled={mutation.isPending}>
        {mutation.isPending ? 'Creando...' : 'Crear Post'}
      </button>
      {mutation.isError && <p>Error: {mutation.error.message}</p>}
    </form>
  )
}
```

### Optimistic updates

```tsx
const toggleTodo = useMutation({
  mutationFn: (id: number) => fetch(`/api/todos/${id}/toggle`, { method: 'PATCH' }),
  onMutate: async (id) => {
    // Cancelar queries en curso
    await queryClient.cancelQueries({ queryKey: ['todos'] })

    // Snapshot del estado anterior
    const previous = queryClient.getQueryData<Todo[]>(['todos'])

    // Actualizar optimistamente
    queryClient.setQueryData<Todo[]>(['todos'], old =>
      old?.map(t => t.id === id ? { ...t, done: !t.done } : t) ?? []
    )

    return { previous }
  },
  onError: (_err, _id, context) => {
    // Rollback al estado anterior si falla
    queryClient.setQueryData(['todos'], context?.previous)
  },
  onSettled: () => {
    queryClient.invalidateQueries({ queryKey: ['todos'] })
  },
})
```

---

## Paginación

```tsx
function PaginatedPosts() {
  const [page, setPage] = useState(1)

  const { data, isLoading, isPreviousData } = useQuery({
    queryKey: ['posts', page],
    queryFn: () => fetchPosts(page),
    placeholderData: (previousData) => previousData,  // Mantiene datos anteriores mientras carga
  })

  return (
    <div>
      {isLoading ? (
        <p>Cargando...</p>
      ) : (
        <ul style={{ opacity: isPreviousData ? 0.5 : 1 }}>
          {data?.posts.map(post => <li key={post.id}>{post.title}</li>)}
        </ul>
      )}

      <div>
        <button onClick={() => setPage(p => Math.max(1, p - 1))} disabled={page === 1}>
          Anterior
        </button>
        <span>Página {page}</span>
        <button onClick={() => setPage(p => p + 1)} disabled={!data?.hasMore}>
          Siguiente
        </button>
      </div>
    </div>
  )
}
```

---

## Suspense para data fetching

React 18+ soporta Suspense para mostrar fallbacks mientras se cargan datos:

```tsx
import { Suspense } from 'react'
import { useSuspenseQuery } from '@tanstack/react-query'

function PostListSuspense() {
  // useSuspenseQuery "suspende" el componente hasta que los datos carguen
  const { data: posts } = useSuspenseQuery({
    queryKey: ['posts'],
    queryFn: fetchPosts,
  })

  // No necesitas check de loading — Suspense lo maneja
  return (
    <ul>
      {posts.map(post => <li key={post.id}>{post.title}</li>)}
    </ul>
  )
}

function App() {
  return (
    <Suspense fallback={<p>Cargando posts...</p>}>
      <PostListSuspense />
    </Suspense>
  )
}
```

---

## Axios como alternativa a fetch

```tsx
import axios from 'axios'

const api = axios.create({
  baseURL: 'https://api.example.com',
  headers: { 'Content-Type': 'application/json' },
})

// Interceptor para token JWT
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Uso con React Query:
const { data } = useQuery({
  queryKey: ['users'],
  queryFn: () => api.get<User[]>('/users').then(res => res.data),
})
```

---

## Resumen

| Método | Caché | Revalidación | Paginación | Complejidad |
|---|---|---|---|---|
| `fetch` + `useEffect` | ❌ | Manual | Manual | Baja |
| Custom hook `useFetch` | ❌ | Manual | Manual | Baja |
| **TanStack Query** | ✅ | ✅ Auto | ✅ Built-in | Media |
| SWR (Vercel) | ✅ | ✅ Auto | ✅ Built-in | Media |
| Suspense + Query | ✅ | ✅ Auto | ✅ | Media |
