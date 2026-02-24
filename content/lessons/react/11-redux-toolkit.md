# Redux Toolkit

**Redux Toolkit (RTK)** es la forma oficial y recomendada de usar Redux. Simplifica enormemente el boilerplate de Redux clásico y ofrece herramientas para manejar estado global complejo, lógica asíncrona y normalización de datos.

---

## ¿Cuándo usar Redux?

| Escenario | ¿Redux? | Alternativa |
|---|---|---|
| Estado compartido entre muchos componentes distantes | ✅ Sí | Context |
| Lógica de estado compleja con muchas acciones | ✅ Sí | useReducer |
| Estado del servidor (datos de API con caché) | ❌ No | TanStack Query |
| Estado local de un formulario | ❌ No | useState |
| Tema, idioma, auth simple | ❌ No | Context |

---

## Instalación

```bash
npm install @reduxjs/toolkit react-redux
```

---

## Conceptos clave

```
Vista → dispatch(Action) → Reducer → Nuevo State → Vista se actualiza
                              ↑
                          Store (único)
```

| Concepto | Descripción |
|---|---|
| **Store** | Contenedor único de todo el estado global |
| **Slice** | Pedazo de estado + sus reducers (reemplaza actions + reducer manual) |
| **Action** | Objeto `{ type, payload }` que describe un cambio |
| **Reducer** | Función pura que calcula el nuevo estado |
| **Selector** | Función que extrae datos del state |
| **Thunk** | Función async para lógica asíncrona |

---

## Crear un Slice

Un slice agrupa un pedazo de estado con sus reducers:

```tsx
import { createSlice, PayloadAction } from '@reduxjs/toolkit'

interface Todo {
  id: number
  text: string
  completed: boolean
}

interface TodosState {
  items: Todo[]
  filter: 'all' | 'active' | 'completed'
}

const initialState: TodosState = {
  items: [],
  filter: 'all',
}

const todosSlice = createSlice({
  name: 'todos',
  initialState,
  reducers: {
    addTodo: (state, action: PayloadAction<string>) => {
      // ✅ RTK usa Immer: puedes "mutar" directamente (Immer lo hace inmutable)
      state.items.push({
        id: Date.now(),
        text: action.payload,
        completed: false,
      })
    },
    toggleTodo: (state, action: PayloadAction<number>) => {
      const todo = state.items.find(t => t.id === action.payload)
      if (todo) todo.completed = !todo.completed
    },
    deleteTodo: (state, action: PayloadAction<number>) => {
      state.items = state.items.filter(t => t.id !== action.payload)
    },
    setFilter: (state, action: PayloadAction<TodosState['filter']>) => {
      state.filter = action.payload
    },
  },
})

// RTK genera los action creators automáticamente
export const { addTodo, toggleTodo, deleteTodo, setFilter } = todosSlice.actions
export default todosSlice.reducer
```

> **Immer**: RTK usa Immer internamente. Escribes código "mutativo" (`state.items.push(...)`) pero Immer produce un nuevo objeto inmutable. No necesitas spread operators.

---

## Configurar el Store

```tsx
import { configureStore } from '@reduxjs/toolkit'
import todosReducer from './features/todos/todosSlice'
import authReducer from './features/auth/authSlice'

export const store = configureStore({
  reducer: {
    todos: todosReducer,
    auth: authReducer,
  },
})

// Tipos inferidos automáticamente
export type RootState = ReturnType<typeof store.getState>
export type AppDispatch = typeof store.dispatch
```

### Proveer el Store a React

```tsx
import { Provider } from 'react-redux'
import { store } from './store'

function App() {
  return (
    <Provider store={store}>
      <MyApp />
    </Provider>
  )
}
```

---

## Hooks tipados

Crea hooks tipados para evitar repetir tipos en cada componente:

```tsx
import { useDispatch, useSelector, TypedUseSelectorHook } from 'react-redux'
import type { RootState, AppDispatch } from './store'

// Usa estos en vez de useDispatch y useSelector directamente
export const useAppDispatch = () => useDispatch<AppDispatch>()
export const useAppSelector: TypedUseSelectorHook<RootState> = useSelector
```

---

## Usar Redux en componentes

```tsx
import { useAppDispatch, useAppSelector } from '../../hooks'
import { addTodo, toggleTodo, deleteTodo, setFilter } from './todosSlice'

function TodoApp() {
  const dispatch = useAppDispatch()
  const todos = useAppSelector(state => state.todos.items)
  const filter = useAppSelector(state => state.todos.filter)
  const [input, setInput] = useState('')

  const filteredTodos = todos.filter(todo => {
    if (filter === 'active') return !todo.completed
    if (filter === 'completed') return todo.completed
    return true
  })

  const handleAdd = () => {
    if (input.trim()) {
      dispatch(addTodo(input))
      setInput('')
    }
  }

  return (
    <div>
      <input value={input} onChange={e => setInput(e.target.value)} />
      <button onClick={handleAdd}>Agregar</button>

      <div>
        {(['all', 'active', 'completed'] as const).map(f => (
          <button
            key={f}
            onClick={() => dispatch(setFilter(f))}
            className={filter === f ? 'active' : ''}
          >
            {f}
          </button>
        ))}
      </div>

      <ul>
        {filteredTodos.map(todo => (
          <li key={todo.id}>
            <input
              type="checkbox"
              checked={todo.completed}
              onChange={() => dispatch(toggleTodo(todo.id))}
            />
            <span className={todo.completed ? 'done' : ''}>{todo.text}</span>
            <button onClick={() => dispatch(deleteTodo(todo.id))}>🗑️</button>
          </li>
        ))}
      </ul>
    </div>
  )
}
```

---

## Selectors

Los selectors extraen y transforman datos del store:

```tsx
// Selectores simples
const selectTodos = (state: RootState) => state.todos.items
const selectFilter = (state: RootState) => state.todos.filter

// Selector derivado (con lógica)
const selectFilteredTodos = (state: RootState) => {
  const { items, filter } = state.todos
  switch (filter) {
    case 'active': return items.filter(t => !t.completed)
    case 'completed': return items.filter(t => t.completed)
    default: return items
  }
}

// Selector con createSelector (memoizado — evita recálculos innecesarios)
import { createSelector } from '@reduxjs/toolkit'

const selectFilteredTodosMemo = createSelector(
  [selectTodos, selectFilter],
  (todos, filter) => {
    switch (filter) {
      case 'active': return todos.filter(t => !t.completed)
      case 'completed': return todos.filter(t => t.completed)
      default: return todos
    }
  }
)
```

---

## Async Thunks: lógica asíncrona

`createAsyncThunk` maneja peticiones HTTP con estados de carga automáticos:

```tsx
import { createAsyncThunk, createSlice } from '@reduxjs/toolkit'

// Crear el thunk
export const fetchPosts = createAsyncThunk(
  'posts/fetchPosts',
  async (_, { rejectWithValue }) => {
    try {
      const res = await fetch('/api/posts')
      if (!res.ok) throw new Error('Error al cargar')
      return (await res.json()) as Post[]
    } catch (err) {
      return rejectWithValue((err as Error).message)
    }
  }
)

// Manejar en el slice
interface PostsState {
  items: Post[]
  loading: boolean
  error: string | null
}

const postsSlice = createSlice({
  name: 'posts',
  initialState: { items: [], loading: false, error: null } as PostsState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchPosts.pending, (state) => {
        state.loading = true
        state.error = null
      })
      .addCase(fetchPosts.fulfilled, (state, action) => {
        state.loading = false
        state.items = action.payload
      })
      .addCase(fetchPosts.rejected, (state, action) => {
        state.loading = false
        state.error = action.payload as string
      })
  },
})
```

### Usar el thunk

```tsx
function PostList() {
  const dispatch = useAppDispatch()
  const { items, loading, error } = useAppSelector(state => state.posts)

  useEffect(() => {
    dispatch(fetchPosts())
  }, [dispatch])

  if (loading) return <p>Cargando...</p>
  if (error) return <p>Error: {error}</p>

  return (
    <ul>
      {items.map(post => <li key={post.id}>{post.title}</li>)}
    </ul>
  )
}
```

---

## RTK Query (data fetching integrado)

RTK Query es la solución de data fetching integrada en RTK (similar a TanStack Query):

```tsx
import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react'

export const api = createApi({
  reducerPath: 'api',
  baseQuery: fetchBaseQuery({ baseUrl: '/api' }),
  tagTypes: ['Post'],
  endpoints: (builder) => ({
    getPosts: builder.query<Post[], void>({
      query: () => '/posts',
      providesTags: ['Post'],
    }),
    createPost: builder.mutation<Post, Omit<Post, 'id'>>({
      query: (body) => ({ url: '/posts', method: 'POST', body }),
      invalidatesTags: ['Post'],  // Invalida la caché de posts
    }),
  }),
})

export const { useGetPostsQuery, useCreatePostMutation } = api
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| `createSlice` | Define estado + reducers, genera actions automáticamente |
| `configureStore` | Crea el store con middleware incluido |
| `useAppSelector` | Lee datos del store con tipado |
| `useAppDispatch` | Obtiene dispatch tipado |
| `createAsyncThunk` | Maneja lógica async con pending/fulfilled/rejected |
| `createSelector` | Selectores memoizados para derivar datos |
| **Immer** | Permite escribir código "mutativo" que resulta inmutable |
| **RTK Query** | Data fetching integrado con caché e invalidación |
