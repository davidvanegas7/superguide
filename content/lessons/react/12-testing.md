# Testing en React

El testing asegura que tu código funciona correctamente y no se rompe al hacer cambios. En React, las herramientas estándar son **Vitest** (test runner) y **React Testing Library** (testing de componentes).

---

## Herramientas principales

| Herramienta | Propósito |
|---|---|
| **Vitest** | Test runner rápido (compatible con Jest API) |
| **React Testing Library (RTL)** | Testear componentes como lo haría un usuario |
| **@testing-library/user-event** | Simular interacciones realistas |
| **@testing-library/jest-dom** | Matchers adicionales para el DOM |
| **MSW (Mock Service Worker)** | Interceptar peticiones HTTP en tests |

### Instalación

```bash
npm install -D vitest @testing-library/react @testing-library/jest-dom @testing-library/user-event jsdom
```

### Configuración (vite.config.ts)

```typescript
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: './src/test/setup.ts',
  },
})
```

```typescript
// src/test/setup.ts
import '@testing-library/jest-dom'
```

---

## Filosofía de React Testing Library

> "Cuanto más se parezcan tus tests a cómo el usuario usa tu software, más confianza te darán."

- ✅ Buscar elementos por **texto, label, rol** (como un usuario)
- ❌ No buscar por **clase CSS, ID o estructura interna** (implementación)
- ✅ Testear **comportamiento** (qué ve el usuario)
- ❌ No testear **implementación** (estado interno, métodos privados)

---

## Queries de RTL (cómo buscar elementos)

| Query | Uso | Falla si no encuentra |
|---|---|---|
| `getByText('Hola')` | Texto visible | ✅ Sí |
| `getByRole('button', { name: 'Enviar' })` | Rol accesible | ✅ Sí |
| `getByLabelText('Email')` | Label de formulario | ✅ Sí |
| `getByPlaceholderText('Buscar...')` | Placeholder | ✅ Sí |
| `getByTestId('my-id')` | `data-testid` (último recurso) | ✅ Sí |
| `queryByText('Hola')` | Igual pero retorna `null` si no existe | ❌ No |
| `findByText('Hola')` | Async: espera a que aparezca | ✅ Sí (await) |

### Prioridad recomendada

1. `getByRole` — El más accesible y robusto
2. `getByLabelText` — Para inputs de formulario
3. `getByText` — Para texto estático
4. `getByPlaceholderText` — Alternativa para inputs
5. `getByTestId` — Último recurso cuando no hay otra opción

---

## Test unitario: funciones puras

```tsx
// utils/format.ts
export function formatPrice(amount: number): string {
  return `$${amount.toFixed(2)}`
}

export function capitalize(str: string): string {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

// utils/format.test.ts
import { formatPrice, capitalize } from './format'

describe('formatPrice', () => {
  it('formatea un número como precio', () => {
    expect(formatPrice(29.9)).toBe('$29.90')
  })

  it('maneja cero', () => {
    expect(formatPrice(0)).toBe('$0.00')
  })

  it('maneja números grandes', () => {
    expect(formatPrice(1234.5)).toBe('$1234.50')
  })
})

describe('capitalize', () => {
  it('capitaliza la primera letra', () => {
    expect(capitalize('react')).toBe('React')
  })

  it('retorna string vacío para input vacío', () => {
    expect(capitalize('')).toBe('')
  })
})
```

---

## Test de componente: renderizado

```tsx
// Greeting.tsx
function Greeting({ name }: { name: string }) {
  return <h1>Hola, {name}!</h1>
}

// Greeting.test.tsx
import { render, screen } from '@testing-library/react'
import Greeting from './Greeting'

describe('Greeting', () => {
  it('muestra el saludo con el nombre', () => {
    render(<Greeting name="Ana" />)

    expect(screen.getByText('Hola, Ana!')).toBeInTheDocument()
  })

  it('renderiza como heading', () => {
    render(<Greeting name="Bob" />)

    expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Hola, Bob!')
  })
})
```

---

## Test de interacción: user events

```tsx
// Counter.tsx
function Counter() {
  const [count, setCount] = useState(0)

  return (
    <div>
      <span data-testid="count">{count}</span>
      <button onClick={() => setCount(c => c + 1)}>Incrementar</button>
      <button onClick={() => setCount(0)}>Reset</button>
    </div>
  )
}

// Counter.test.tsx
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import Counter from './Counter'

describe('Counter', () => {
  it('comienza en 0', () => {
    render(<Counter />)
    expect(screen.getByTestId('count')).toHaveTextContent('0')
  })

  it('incrementa al hacer clic', async () => {
    const user = userEvent.setup()
    render(<Counter />)

    await user.click(screen.getByRole('button', { name: 'Incrementar' }))
    expect(screen.getByTestId('count')).toHaveTextContent('1')

    await user.click(screen.getByRole('button', { name: 'Incrementar' }))
    expect(screen.getByTestId('count')).toHaveTextContent('2')
  })

  it('resetea a 0', async () => {
    const user = userEvent.setup()
    render(<Counter />)

    await user.click(screen.getByRole('button', { name: 'Incrementar' }))
    await user.click(screen.getByRole('button', { name: 'Incrementar' }))
    await user.click(screen.getByRole('button', { name: 'Reset' }))

    expect(screen.getByTestId('count')).toHaveTextContent('0')
  })
})
```

---

## Test de formularios

```tsx
// LoginForm.test.tsx
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

describe('LoginForm', () => {
  it('envía el formulario con email y password', async () => {
    const onSubmit = vi.fn()
    const user = userEvent.setup()

    render(<LoginForm onSubmit={onSubmit} />)

    await user.type(screen.getByLabelText('Email'), 'ana@mail.com')
    await user.type(screen.getByLabelText('Contraseña'), 'secret123')
    await user.click(screen.getByRole('button', { name: 'Entrar' }))

    expect(onSubmit).toHaveBeenCalledWith({
      email: 'ana@mail.com',
      password: 'secret123',
    })
  })

  it('muestra error si el email está vacío', async () => {
    const user = userEvent.setup()
    render(<LoginForm onSubmit={vi.fn()} />)

    await user.click(screen.getByRole('button', { name: 'Entrar' }))

    expect(screen.getByText('Email es requerido')).toBeInTheDocument()
  })
})
```

---

## Test con datos asíncronos

```tsx
// UserProfile.test.tsx
import { render, screen } from '@testing-library/react'

// Mock del fetch global
beforeEach(() => {
  vi.spyOn(global, 'fetch').mockResolvedValue({
    ok: true,
    json: async () => ({ id: 1, name: 'Ana García', email: 'ana@mail.com' }),
  } as Response)
})

afterEach(() => {
  vi.restoreAllMocks()
})

describe('UserProfile', () => {
  it('muestra loading y luego los datos del usuario', async () => {
    render(<UserProfile userId={1} />)

    // Primero muestra loading
    expect(screen.getByText('Cargando...')).toBeInTheDocument()

    // Luego aparece el nombre (findBy espera hasta que aparezca)
    expect(await screen.findByText('Ana García')).toBeInTheDocument()
    expect(screen.queryByText('Cargando...')).not.toBeInTheDocument()
  })
})
```

---

## Mock Service Worker (MSW)

MSW intercepta peticiones HTTP a nivel de red, sin mockear fetch:

```tsx
import { setupServer } from 'msw/node'
import { http, HttpResponse } from 'msw'

const server = setupServer(
  http.get('/api/users/:id', ({ params }) => {
    return HttpResponse.json({
      id: Number(params.id),
      name: 'Ana García',
      email: 'ana@mail.com',
    })
  }),

  http.post('/api/login', async ({ request }) => {
    const body = await request.json()
    if (body.email === 'ana@mail.com') {
      return HttpResponse.json({ token: 'fake-jwt' })
    }
    return HttpResponse.json({ error: 'Invalid' }, { status: 401 })
  })
)

beforeAll(() => server.listen())
afterEach(() => server.resetHandlers())
afterAll(() => server.close())
```

---

## Testing de hooks

```tsx
import { renderHook, act } from '@testing-library/react'
import { useCounter } from './useCounter'

describe('useCounter', () => {
  it('inicia en 0 por defecto', () => {
    const { result } = renderHook(() => useCounter())
    expect(result.current.count).toBe(0)
  })

  it('incrementa correctamente', () => {
    const { result } = renderHook(() => useCounter())

    act(() => {
      result.current.increment()
    })

    expect(result.current.count).toBe(1)
  })

  it('acepta valor inicial', () => {
    const { result } = renderHook(() => useCounter(10))
    expect(result.current.count).toBe(10)
  })
})
```

---

## Resumen

| Tipo de test | Herramienta | Qué testea |
|---|---|---|
| **Unitario** | Vitest | Funciones puras, utils, lógica |
| **Componente** | RTL + Vitest | Renderizado, interacción, formularios |
| **Hook** | `renderHook` | Custom hooks aislados |
| **Integración** | RTL + MSW | Componente con llamadas HTTP |
| **E2E** | Playwright / Cypress | Flujo completo en navegador real |
