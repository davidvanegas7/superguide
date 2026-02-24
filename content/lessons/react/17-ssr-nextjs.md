# SSR con Next.js

**Next.js** es el framework de React más popular para producción. Añade **Server-Side Rendering (SSR)**, **Static Site Generation (SSG)**, **Server Components**, routing basado en archivos y mucho más.

---

## ¿Por qué Next.js?

| Problema | Solución de Next.js |
|---|---|
| SEO pobre en SPA | SSR / SSG generan HTML en el servidor |
| Primera carga lenta | HTML pre-renderizado, hidratación progresiva |
| Routing manual | File-based routing automático |
| Config de Webpack/Vite | Zero config, optimizado de fábrica |
| API backend separada | Route Handlers integrados |
| Carga de datos dispersa | Server Components + data fetching en servidor |

---

## Instalación

```bash
npx create-next-app@latest mi-app --typescript --tailwind --app --src-dir
cd mi-app
npm run dev
```

---

## App Router (Next.js 13+)

El **App Router** es el sistema moderno de Next.js. Usa la carpeta `app/` con convenciones de archivos:

```
src/app/
├── layout.tsx          ← Layout raíz (envuelve toda la app)
├── page.tsx            ← Página de inicio (/)
├── loading.tsx         ← UI de carga automática
├── error.tsx           ← UI de error automática
├── not-found.tsx       ← Página 404
├── about/
│   └── page.tsx        ← /about
├── blog/
│   ├── page.tsx        ← /blog
│   └── [slug]/
│       └── page.tsx    ← /blog/mi-articulo (ruta dinámica)
├── dashboard/
│   ├── layout.tsx      ← Layout anidado para dashboard
│   ├── page.tsx        ← /dashboard
│   └── settings/
│       └── page.tsx    ← /dashboard/settings
└── api/
    └── users/
        └── route.ts    ← API endpoint: GET/POST /api/users
```

### Archivos especiales

| Archivo | Propósito |
|---|---|
| `page.tsx` | Define una ruta y su UI |
| `layout.tsx` | Layout compartido (persiste entre navegaciones) |
| `loading.tsx` | Fallback de Suspense automático |
| `error.tsx` | Error boundary automático |
| `not-found.tsx` | Página 404 personalizada |
| `route.ts` | API endpoint (Route Handler) |

---

## Server Components vs Client Components

En Next.js, los componentes son **Server Components por defecto**:

### Server Components (default)

```tsx
// app/products/page.tsx — Server Component
// Se ejecuta SOLO en el servidor, nunca se envía al cliente

async function ProductsPage() {
  // Puedes hacer fetch directamente, sin useEffect
  const products = await fetch('https://api.example.com/products').then(r => r.json())

  // Puedes acceder a la base de datos directamente
  // const products = await db.product.findMany()

  return (
    <div>
      <h1>Productos</h1>
      <ul>
        {products.map((p: Product) => (
          <li key={p.id}>{p.name} — ${p.price}</li>
        ))}
      </ul>
    </div>
  )
}

export default ProductsPage
```

### Client Components

```tsx
// components/Counter.tsx — Client Component
'use client'  // ← Esta directiva lo convierte en Client Component

import { useState } from 'react'

export default function Counter() {
  const [count, setCount] = useState(0)

  return (
    <div>
      <p>{count}</p>
      <button onClick={() => setCount(c => c + 1)}>+1</button>
    </div>
  )
}
```

### ¿Cuándo usar cada uno?

| Necesito... | Server Component | Client Component |
|---|---|---|
| Fetch de datos | ✅ | 🟡 (con useEffect o React Query) |
| Acceso a BD directa | ✅ | ❌ |
| useState / useEffect | ❌ | ✅ |
| Event handlers (onClick) | ❌ | ✅ |
| Browser APIs (localStorage) | ❌ | ✅ |
| Reducir bundle del cliente | ✅ | ❌ |

> **Patrón**: mantén los Server Components como "contenedores" que cargan datos, y pasa los datos a Client Components interactivos.

---

## Data Fetching en Server Components

### fetch con caché

```tsx
// Se cachea automáticamente (equivale a SSG)
const data = await fetch('https://api.example.com/posts')

// Revalidar cada 60 segundos (ISR — Incremental Static Regeneration)
const data = await fetch('https://api.example.com/posts', {
  next: { revalidate: 60 },
})

// Sin caché (equivale a SSR — siempre fresco)
const data = await fetch('https://api.example.com/posts', {
  cache: 'no-store',
})
```

### Rutas dinámicas

```tsx
// app/blog/[slug]/page.tsx
interface Props {
  params: { slug: string }
}

export default async function BlogPost({ params }: Props) {
  const post = await fetch(`https://api.example.com/posts/${params.slug}`).then(r => r.json())

  return (
    <article>
      <h1>{post.title}</h1>
      <p>{post.content}</p>
    </article>
  )
}

// Generar páginas estáticas en build time
export async function generateStaticParams() {
  const posts = await fetch('https://api.example.com/posts').then(r => r.json())
  return posts.map((post: Post) => ({ slug: post.slug }))
}
```

---

## Layouts

```tsx
// app/layout.tsx — Layout raíz (obligatorio)
export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="es">
      <body>
        <nav>
          <a href="/">Inicio</a>
          <a href="/about">Acerca</a>
        </nav>
        <main>{children}</main>
        <footer>© 2026</footer>
      </body>
    </html>
  )
}

// app/dashboard/layout.tsx — Layout anidado
export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex">
      <aside>
        <nav>Sidebar del dashboard</nav>
      </aside>
      <section className="flex-1">{children}</section>
    </div>
  )
}
```

---

## Loading y Error UI

```tsx
// app/products/loading.tsx — Se muestra automáticamente mientras carga
export default function Loading() {
  return <div className="spinner">Cargando productos...</div>
}

// app/products/error.tsx — Se muestra automáticamente si hay error
'use client'

export default function Error({ error, reset }: {
  error: Error & { digest?: string }
  reset: () => void
}) {
  return (
    <div>
      <h2>Algo salió mal</h2>
      <p>{error.message}</p>
      <button onClick={reset}>Reintentar</button>
    </div>
  )
}
```

---

## Route Handlers (API)

```tsx
// app/api/users/route.ts
import { NextRequest, NextResponse } from 'next/server'

export async function GET() {
  const users = await db.user.findMany()
  return NextResponse.json(users)
}

export async function POST(request: NextRequest) {
  const body = await request.json()
  const user = await db.user.create({ data: body })
  return NextResponse.json(user, { status: 201 })
}

// app/api/users/[id]/route.ts
export async function GET(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  const user = await db.user.findUnique({ where: { id: Number(params.id) } })
  if (!user) return NextResponse.json({ error: 'Not found' }, { status: 404 })
  return NextResponse.json(user)
}
```

---

## Server Actions

Las Server Actions permiten mutar datos desde el cliente sin crear API endpoints:

```tsx
// app/actions.ts
'use server'

export async function createPost(formData: FormData) {
  const title = formData.get('title') as string
  const body = formData.get('body') as string

  await db.post.create({ data: { title, body } })
  revalidatePath('/posts')
}

// app/posts/new/page.tsx
import { createPost } from '../actions'

export default function NewPost() {
  return (
    <form action={createPost}>
      <input name="title" required />
      <textarea name="body" required />
      <button type="submit">Crear</button>
    </form>
  )
}
```

---

## Metadata y SEO

```tsx
// app/layout.tsx
import type { Metadata } from 'next'

export const metadata: Metadata = {
  title: {
    template: '%s | Mi App',
    default: 'Mi App',
  },
  description: 'Descripción de mi aplicación',
}

// app/about/page.tsx — Override por página
export const metadata: Metadata = {
  title: 'Acerca de',  // Renderiza: "Acerca de | Mi App"
  description: 'Información sobre nosotros',
}
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| App Router | File-based routing con `app/` |
| Server Components | Por defecto, renderizados en servidor, sin JS al cliente |
| Client Components | `'use client'`, con hooks e interactividad |
| `fetch` en servidor | Caché automática, revalidación configurable |
| Layouts | Persistentes entre navegaciones, anidables |
| `loading.tsx` / `error.tsx` | UI automáticas de carga y error |
| Route Handlers | API endpoints en `route.ts` |
| Server Actions | Mutaciones sin API explícita |
| Metadata | SEO declarativo por ruta |
