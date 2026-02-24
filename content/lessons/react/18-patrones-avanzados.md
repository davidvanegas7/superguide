# Patrones Avanzados de React

Los patrones avanzados resuelven problemas de **reutilización de lógica**, **composición flexible** y **separación de responsabilidades** en aplicaciones React a gran escala.

---

## Compound Components

Componentes que trabajan juntos compartiendo estado implícito (como `<select>` y `<option>` en HTML):

```tsx
import { createContext, useContext, useState } from 'react'

// ── Context interno ──────────────────────────────────────────────────────
interface TabsContextType {
  activeTab: string
  setActiveTab: (tab: string) => void
}

const TabsContext = createContext<TabsContextType | null>(null)

function useTabs() {
  const ctx = useContext(TabsContext)
  if (!ctx) throw new Error('Tabs components must be used within <Tabs>')
  return ctx
}

// ── Compound Components ──────────────────────────────────────────────────
function Tabs({ defaultTab, children }: { defaultTab: string; children: React.ReactNode }) {
  const [activeTab, setActiveTab] = useState(defaultTab)

  return (
    <TabsContext.Provider value={{ activeTab, setActiveTab }}>
      <div className="tabs">{children}</div>
    </TabsContext.Provider>
  )
}

function TabList({ children }: { children: React.ReactNode }) {
  return <div className="tab-list" role="tablist">{children}</div>
}

function Tab({ value, children }: { value: string; children: React.ReactNode }) {
  const { activeTab, setActiveTab } = useTabs()
  return (
    <button
      role="tab"
      className={activeTab === value ? 'tab active' : 'tab'}
      onClick={() => setActiveTab(value)}
    >
      {children}
    </button>
  )
}

function TabPanel({ value, children }: { value: string; children: React.ReactNode }) {
  const { activeTab } = useTabs()
  if (activeTab !== value) return null
  return <div role="tabpanel">{children}</div>
}

// Asignar los sub-componentes
Tabs.List = TabList
Tabs.Tab = Tab
Tabs.Panel = TabPanel

// ── Uso ──────────────────────────────────────────────────────────────────
function App() {
  return (
    <Tabs defaultTab="general">
      <Tabs.List>
        <Tabs.Tab value="general">General</Tabs.Tab>
        <Tabs.Tab value="security">Seguridad</Tabs.Tab>
        <Tabs.Tab value="billing">Facturación</Tabs.Tab>
      </Tabs.List>

      <Tabs.Panel value="general">Configuración general...</Tabs.Panel>
      <Tabs.Panel value="security">Configuración de seguridad...</Tabs.Panel>
      <Tabs.Panel value="billing">Métodos de pago...</Tabs.Panel>
    </Tabs>
  )
}
```

---

## Render Props

Un componente que recibe una **función como children** (o prop) para delegar el rendering al consumidor:

```tsx
interface MousePosition {
  x: number
  y: number
}

function MouseTracker({ children }: { children: (pos: MousePosition) => React.ReactNode }) {
  const [position, setPosition] = useState<MousePosition>({ x: 0, y: 0 })

  useEffect(() => {
    const handler = (e: MouseEvent) => setPosition({ x: e.clientX, y: e.clientY })
    window.addEventListener('mousemove', handler)
    return () => window.removeEventListener('mousemove', handler)
  }, [])

  return <>{children(position)}</>
}

// Uso — el consumidor decide qué renderizar con la posición
function App() {
  return (
    <MouseTracker>
      {({ x, y }) => (
        <div>
          <p>Mouse en: ({x}, {y})</p>
          <div
            style={{
              position: 'absolute',
              left: x - 10,
              top: y - 10,
              width: 20,
              height: 20,
              borderRadius: '50%',
              background: 'red',
            }}
          />
        </div>
      )}
    </MouseTracker>
  )
}
```

> **Nota**: Hoy en día, los custom hooks reemplazan la mayoría de casos de render props. El patrón sigue siendo útil cuando necesitas **inyectar JSX**, no solo datos.

---

## Higher-Order Components (HOCs)

Un HOC es una función que toma un componente y retorna uno nuevo con funcionalidad adicional:

```tsx
function withAuth<P extends object>(Component: React.ComponentType<P>) {
  return function AuthenticatedComponent(props: P) {
    const { user, isAuthenticated } = useAuth()

    if (!isAuthenticated) {
      return <Navigate to="/login" replace />
    }

    return <Component {...props} />
  }
}

// Uso:
const ProtectedDashboard = withAuth(Dashboard)
const ProtectedSettings = withAuth(Settings)
```

> **Nota**: Los HOCs son un patrón legacy. Prefiere custom hooks o componentes wrapper. Pero los encontrarás en muchas librerías y código existente.

---

## Controlled vs Uncontrolled pattern

### Hook + componente headless

```tsx
// Hook que contiene toda la lógica (headless)
function useDisclosure(initial = false) {
  const [isOpen, setIsOpen] = useState(initial)

  return {
    isOpen,
    open: useCallback(() => setIsOpen(true), []),
    close: useCallback(() => setIsOpen(false), []),
    toggle: useCallback(() => setIsOpen(prev => !prev), []),
  }
}

// Componente que usa el hook
function Modal({ isOpen, onClose, title, children }: {
  isOpen: boolean
  onClose: () => void
  title: string
  children: React.ReactNode
}) {
  if (!isOpen) return null

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={e => e.stopPropagation()}>
        <header>
          <h2>{title}</h2>
          <button onClick={onClose}>✕</button>
        </header>
        <div className="modal-body">{children}</div>
      </div>
    </div>
  )
}

// Uso combinado:
function App() {
  const modal = useDisclosure()

  return (
    <>
      <button onClick={modal.open}>Abrir modal</button>
      <Modal isOpen={modal.isOpen} onClose={modal.close} title="Confirmar">
        <p>¿Estás seguro?</p>
        <button onClick={modal.close}>Cancelar</button>
        <button onClick={() => { doAction(); modal.close() }}>Confirmar</button>
      </Modal>
    </>
  )
}
```

---

## Provider Pattern (composición de providers)

```tsx
// Problema: Provider hell
function App() {
  return (
    <AuthProvider>
      <ThemeProvider>
        <CartProvider>
          <NotificationProvider>
            <QueryProvider>
              <Router />
            </QueryProvider>
          </NotificationProvider>
        </CartProvider>
      </ThemeProvider>
    </AuthProvider>
  )
}

// Solución: composeProviders
function composeProviders(...providers: React.FC<{ children: React.ReactNode }>[]) {
  return function ComposedProvider({ children }: { children: React.ReactNode }) {
    return providers.reduceRight(
      (child, Provider) => <Provider>{child}</Provider>,
      children
    )
  }
}

const AppProviders = composeProviders(
  AuthProvider,
  ThemeProvider,
  CartProvider,
  NotificationProvider,
  QueryProvider,
)

function App() {
  return (
    <AppProviders>
      <Router />
    </AppProviders>
  )
}
```

---

## Slot Pattern

Pasar múltiples áreas de contenido con nombre:

```tsx
interface PageProps {
  header: React.ReactNode
  sidebar?: React.ReactNode
  footer?: React.ReactNode
  children: React.ReactNode
}

function Page({ header, sidebar, footer, children }: PageProps) {
  return (
    <div className="page">
      <header className="page-header">{header}</header>
      <div className="page-body">
        {sidebar && <aside className="page-sidebar">{sidebar}</aside>}
        <main className="page-content">{children}</main>
      </div>
      {footer && <footer className="page-footer">{footer}</footer>}
    </div>
  )
}

// Uso:
<Page
  header={<NavBar />}
  sidebar={<FilterPanel />}
  footer={<Copyright />}
>
  <ProductList />
</Page>
```

---

## State Machine Pattern

Para flujos con estados definidos (wizard, checkout, flujo de auth):

```tsx
type WizardStep = 'info' | 'address' | 'payment' | 'confirmation'

const transitions: Record<WizardStep, { next?: WizardStep; prev?: WizardStep }> = {
  info:         { next: 'address' },
  address:      { next: 'payment', prev: 'info' },
  payment:      { next: 'confirmation', prev: 'address' },
  confirmation: { prev: 'payment' },
}

function useWizard(initial: WizardStep = 'info') {
  const [step, setStep] = useState<WizardStep>(initial)

  return {
    step,
    next: () => {
      const nextStep = transitions[step].next
      if (nextStep) setStep(nextStep)
    },
    prev: () => {
      const prevStep = transitions[step].prev
      if (prevStep) setStep(prevStep)
    },
    canGoNext: !!transitions[step].next,
    canGoPrev: !!transitions[step].prev,
    goTo: (s: WizardStep) => setStep(s),
  }
}
```

---

## Resumen

| Patrón | Propósito | Uso moderno |
|---|---|---|
| **Compound Components** | Componentes relacionados con estado implícito | ✅ Muy usado |
| **Render Props** | Delegar rendering al consumidor | 🟡 Custom hooks prefieren |
| **HOC** | Añadir funcionalidad a componentes | 🟡 Legacy, pero existe |
| **Provider Pattern** | Estado global compartido | ✅ Context + Reducer |
| **Slot Pattern** | Múltiples áreas de contenido | ✅ Props con ReactNode |
| **State Machine** | Flujos con pasos definidos | ✅ Con xstate o manual |
| **Headless Hook + UI** | Separar lógica de presentación | ✅ Patrón principal |
