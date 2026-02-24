# React Router

**React Router** es la librería estándar para gestionar la navegación en aplicaciones React SPA (Single Page Application). Permite definir rutas, navegar entre páginas sin recargar y proteger rutas con guards.

---

## Instalación

```bash
npm install react-router-dom
```

---

## Configuración básica

```tsx
import { BrowserRouter, Routes, Route, Link } from 'react-router-dom'

function App() {
  return (
    <BrowserRouter>
      <nav>
        <Link to="/">Inicio</Link>
        <Link to="/about">Acerca de</Link>
        <Link to="/contact">Contacto</Link>
      </nav>

      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/about" element={<About />} />
        <Route path="/contact" element={<Contact />} />
        <Route path="*" element={<NotFound />} />
      </Routes>
    </BrowserRouter>
  )
}
```

> La ruta `*` captura cualquier ruta no definida (página 404).

---

## Link vs NavLink

```tsx
import { Link, NavLink } from 'react-router-dom'

// Link: navegación básica
<Link to="/products">Productos</Link>

// NavLink: agrega clase "active" automáticamente cuando la ruta coincide
<NavLink
  to="/products"
  className={({ isActive }) => isActive ? 'nav-active' : ''}
>
  Productos
</NavLink>
```

---

## Rutas con parámetros

```tsx
// Definir la ruta
<Route path="/users/:userId" element={<UserProfile />} />

// Componente que lee el parámetro
import { useParams } from 'react-router-dom'

function UserProfile() {
  const { userId } = useParams<{ userId: string }>()

  return <h1>Perfil del usuario: {userId}</h1>
}
```

---

## Rutas anidadas (Nested Routes)

```tsx
function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Layout />}>
          <Route index element={<Home />} />
          <Route path="products" element={<Products />}>
            <Route index element={<ProductList />} />
            <Route path=":productId" element={<ProductDetail />} />
          </Route>
          <Route path="about" element={<About />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}
```

### Layout con Outlet

```tsx
import { Outlet, Link } from 'react-router-dom'

function Layout() {
  return (
    <div>
      <header>
        <nav>
          <Link to="/">Inicio</Link>
          <Link to="/products">Productos</Link>
          <Link to="/about">Acerca</Link>
        </nav>
      </header>

      <main>
        <Outlet />  {/* Aquí se renderizan las rutas hijas */}
      </main>

      <footer>© 2026</footer>
    </div>
  )
}
```

---

## Navegación programática

```tsx
import { useNavigate } from 'react-router-dom'

function LoginForm() {
  const navigate = useNavigate()

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    const success = await login(email, password)

    if (success) {
      navigate('/dashboard')        // Ir a dashboard
      // navigate('/dashboard', { replace: true })  // Reemplaza en historial (no puede volver con "atrás")
    }
  }

  return <form onSubmit={handleSubmit}>...</form>
}

// Navegar hacia atrás
function BackButton() {
  const navigate = useNavigate()
  return <button onClick={() => navigate(-1)}>← Volver</button>
}
```

---

## Query parameters (search params)

```tsx
import { useSearchParams } from 'react-router-dom'

function ProductList() {
  const [searchParams, setSearchParams] = useSearchParams()

  const category = searchParams.get('category') || 'all'
  const page = Number(searchParams.get('page')) || 1

  const handleCategoryChange = (cat: string) => {
    setSearchParams({ category: cat, page: '1' })
    // URL: /products?category=electronics&page=1
  }

  return (
    <div>
      <p>Categoría: {category}, Página: {page}</p>
      <button onClick={() => handleCategoryChange('electronics')}>
        Electrónica
      </button>
    </div>
  )
}
```

---

## Pasar estado entre rutas

```tsx
// Enviar estado
navigate('/checkout', { state: { from: 'cart', total: 99.99 } })

// Recibir estado
import { useLocation } from 'react-router-dom'

function Checkout() {
  const location = useLocation()
  const { from, total } = location.state || {}

  return <p>Desde: {from}, Total: ${total}</p>
}
```

---

## Rutas protegidas (Guards)

```tsx
import { Navigate, Outlet } from 'react-router-dom'

interface ProtectedRouteProps {
  isAuthenticated: boolean
  redirectTo?: string
  children?: React.ReactNode
}

function ProtectedRoute({
  isAuthenticated,
  redirectTo = '/login',
  children,
}: ProtectedRouteProps) {
  if (!isAuthenticated) {
    return <Navigate to={redirectTo} replace />
  }
  return children ? <>{children}</> : <Outlet />
}

// Uso en las rutas:
function App() {
  const { isAuthenticated } = useAuth()

  return (
    <Routes>
      <Route path="/login" element={<Login />} />

      {/* Todas las rutas hijas están protegidas */}
      <Route element={<ProtectedRoute isAuthenticated={isAuthenticated} />}>
        <Route path="/dashboard" element={<Dashboard />} />
        <Route path="/profile" element={<Profile />} />
        <Route path="/settings" element={<Settings />} />
      </Route>
    </Routes>
  )
}
```

### Protección por roles

```tsx
function RoleGuard({ allowedRoles, userRole }: {
  allowedRoles: string[]
  userRole: string
}) {
  if (!allowedRoles.includes(userRole)) {
    return <Navigate to="/unauthorized" replace />
  }
  return <Outlet />
}

// Uso:
<Route element={<RoleGuard allowedRoles={['admin']} userRole={user.role} />}>
  <Route path="/admin" element={<AdminPanel />} />
</Route>
```

---

## Lazy loading de rutas

```tsx
import { lazy, Suspense } from 'react'

// Los componentes se cargan solo cuando se navega a su ruta
const Dashboard = lazy(() => import('./pages/Dashboard'))
const Settings = lazy(() => import('./pages/Settings'))
const AdminPanel = lazy(() => import('./pages/AdminPanel'))

function App() {
  return (
    <BrowserRouter>
      <Suspense fallback={<div>Cargando...</div>}>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/settings" element={<Settings />} />
          <Route path="/admin" element={<AdminPanel />} />
        </Routes>
      </Suspense>
    </BrowserRouter>
  )
}
```

---

## Loader y data fetching (React Router v6.4+)

React Router v6.4+ permite cargar datos **antes** de renderizar la ruta:

```tsx
import { createBrowserRouter, RouterProvider, useLoaderData } from 'react-router-dom'

const router = createBrowserRouter([
  {
    path: '/products/:id',
    element: <ProductDetail />,
    loader: async ({ params }) => {
      const res = await fetch(`/api/products/${params.id}`)
      if (!res.ok) throw new Response('Not Found', { status: 404 })
      return res.json()
    },
    errorElement: <ErrorPage />,
  },
])

function ProductDetail() {
  const product = useLoaderData() as Product
  return <h1>{product.name}</h1>
}

function App() {
  return <RouterProvider router={router} />
}
```

---

## Resumen

| Concepto | Uso |
|---|---|
| `<BrowserRouter>` | Envuelve la app para habilitar routing |
| `<Routes>` + `<Route>` | Define las rutas y sus componentes |
| `<Link>` / `<NavLink>` | Navegación declarativa |
| `useParams()` | Leer parámetros de la URL (`:id`) |
| `useNavigate()` | Navegación programática |
| `useSearchParams()` | Leer/escribir query params |
| `useLocation()` | Acceder a la ubicación actual y state |
| `<Outlet />` | Renderizar rutas hijas en un layout |
| `<Navigate />` | Redirigir declarativamente |
| `lazy()` + `<Suspense>` | Code splitting por ruta |
