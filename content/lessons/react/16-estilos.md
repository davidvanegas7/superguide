# Estilos en React

React no impone un sistema de estilos — tienes múltiples opciones. Esta lección cubre las más populares: **CSS Modules**, **Tailwind CSS**, **Styled Components** y **CSS-in-JS**.

---

## Comparativa rápida

| Método | Scoping | Bundle | Runtime | TypeScript | Popularidad |
|---|---|---|---|---|---|
| CSS global | ❌ Manual | CSS separado | ❌ | ❌ | Baja (legacy) |
| CSS Modules | ✅ Auto | CSS separado | ❌ | Medio | Alta |
| Tailwind CSS | ✅ Utility | CSS optimizado | ❌ | ✅ | Muy alta |
| Styled Components | ✅ Auto | JS | ✅ Runtime | ✅ | Alta |
| Emotion | ✅ Auto | JS | ✅ Runtime | ✅ | Alta |
| Panda CSS / Vanilla Extract | ✅ Auto | CSS separado | ❌ Zero-runtime | ✅ | Creciente |

---

## CSS Modules

Los CSS Modules generan **nombres de clase únicos** automáticamente, evitando colisiones:

```css
/* Button.module.css */
.button {
  padding: 8px 16px;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  font-weight: 600;
}

.primary {
  background-color: #3b82f6;
  color: white;
}

.secondary {
  background-color: #e5e7eb;
  color: #374151;
}

.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
```

```tsx
// Button.tsx
import styles from './Button.module.css'

interface ButtonProps {
  variant?: 'primary' | 'secondary'
  disabled?: boolean
  children: React.ReactNode
  onClick?: () => void
}

function Button({ variant = 'primary', disabled, children, onClick }: ButtonProps) {
  const className = [
    styles.button,
    styles[variant],
    disabled ? styles.disabled : '',
  ].filter(Boolean).join(' ')

  return (
    <button className={className} disabled={disabled} onClick={onClick}>
      {children}
    </button>
  )
}
```

> Las clases se compilan a algo como `Button_button_x3k2a`, garantizando que no colisionan con otras.

### clsx / classnames (helper para clases condicionales)

```tsx
import clsx from 'clsx'

<div className={clsx(
  styles.card,
  isActive && styles.active,
  size === 'large' && styles.large,
)} />
```

---

## Tailwind CSS

**Tailwind** es un framework de utility-first CSS. En vez de escribir CSS, aplicas clases utilitarias directamente en el JSX:

### Instalación

```bash
npm install -D tailwindcss @tailwindcss/vite
```

```typescript
// vite.config.ts
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [react(), tailwindcss()],
})
```

```css
/* src/index.css */
@import "tailwindcss";
```

### Uso básico

```tsx
function ProductCard({ product }: { product: Product }) {
  return (
    <article className="rounded-lg shadow-md overflow-hidden bg-white hover:shadow-lg transition-shadow">
      <img
        src={product.image}
        alt={product.name}
        className="w-full h-48 object-cover"
      />
      <div className="p-4">
        <h3 className="text-lg font-semibold text-gray-900">{product.name}</h3>
        <p className="mt-1 text-sm text-gray-500">{product.description}</p>
        <div className="mt-4 flex items-center justify-between">
          <span className="text-xl font-bold text-blue-600">
            ${product.price}
          </span>
          <button className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 active:bg-blue-800 disabled:opacity-50 transition-colors">
            Comprar
          </button>
        </div>
      </div>
    </article>
  )
}
```

### Clases condicionales con Tailwind

```tsx
import clsx from 'clsx'

function Badge({ variant, children }: { variant: 'success' | 'error' | 'warning'; children: React.ReactNode }) {
  return (
    <span className={clsx(
      'px-2 py-1 rounded-full text-xs font-medium',
      {
        'bg-green-100 text-green-800': variant === 'success',
        'bg-red-100 text-red-800': variant === 'error',
        'bg-yellow-100 text-yellow-800': variant === 'warning',
      }
    )}>
      {children}
    </span>
  )
}
```

### Responsive design

```tsx
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  {products.map(p => <ProductCard key={p.id} product={p} />)}
</div>

{/* Mobile: 1 columna, Tablet: 2 columnas, Desktop: 3 columnas */}
```

### Dark mode

```tsx
<div className="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
  <h1 className="text-2xl font-bold">Título</h1>
</div>
```

---

## Styled Components

**Styled Components** permite escribir CSS dentro de JavaScript usando tagged template literals:

### Instalación

```bash
npm install styled-components
npm install -D @types/styled-components
```

### Uso básico

```tsx
import styled from 'styled-components'

const Card = styled.div`
  background: white;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);

  &:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  }
`

const Title = styled.h2`
  font-size: 1.5rem;
  color: #1a1a1a;
  margin-bottom: 8px;
`

const Price = styled.span<{ $discounted?: boolean }>`
  font-size: 1.25rem;
  font-weight: 700;
  color: ${props => props.$discounted ? '#dc2626' : '#3b82f6'};
  text-decoration: ${props => props.$discounted ? 'line-through' : 'none'};
`

function ProductCard({ product }: { product: Product }) {
  return (
    <Card>
      <Title>{product.name}</Title>
      <Price $discounted={product.onSale}>${product.price}</Price>
    </Card>
  )
}
```

> **Convención**: las props transitorias (solo para estilos) empiezan con `$` para evitar que se pasen al DOM.

### Extender estilos

```tsx
const Button = styled.button`
  padding: 8px 16px;
  border-radius: 4px;
  border: none;
  cursor: pointer;
`

const PrimaryButton = styled(Button)`
  background: #3b82f6;
  color: white;

  &:hover {
    background: #2563eb;
  }
`

const DangerButton = styled(Button)`
  background: #dc2626;
  color: white;
`
```

### Temas con ThemeProvider

```tsx
import { ThemeProvider } from 'styled-components'

const lightTheme = {
  colors: {
    bg: '#ffffff',
    text: '#1a1a1a',
    primary: '#3b82f6',
  },
}

const darkTheme = {
  colors: {
    bg: '#1a1a1a',
    text: '#f5f5f5',
    primary: '#60a5fa',
  },
}

const Container = styled.div`
  background: ${props => props.theme.colors.bg};
  color: ${props => props.theme.colors.text};
`

function App() {
  const [isDark, setIsDark] = useState(false)

  return (
    <ThemeProvider theme={isDark ? darkTheme : lightTheme}>
      <Container>
        <button onClick={() => setIsDark(!isDark)}>Toggle theme</button>
      </Container>
    </ThemeProvider>
  )
}
```

---

## ¿Cuál elegir?

| Criterio | CSS Modules | Tailwind | Styled Components |
|---|---|---|---|
| **Curva de aprendizaje** | Baja (es CSS normal) | Media (clases utility) | Media (CSS-in-JS) |
| **Performance** | ✅ Zero runtime | ✅ Zero runtime | 🟡 Runtime overhead |
| **Bundle size** | Pequeño | Muy pequeño (purge) | Más grande |
| **Colocación** | Archivo separado | En el JSX | En el JS |
| **Diseño consistente** | Manual | ✅ Sistema de diseño built-in | Manual |
| **Server Components** | ✅ | ✅ | ❌ No compatible |

### Recomendación

- **Nuevo proyecto**: Tailwind CSS (productividad, consistencia, zero runtime)
- **Proyecto corporativo**: CSS Modules (familiar, zero runtime, sin opiniones)
- **Design system library**: Styled Components o Vanilla Extract

---

## Resumen

| Método | Scoping | Runtime | Mejor para |
|---|---|---|---|
| CSS Modules | ✅ Automático | ❌ | Equipos que prefieren CSS puro |
| Tailwind | ✅ Utility | ❌ | Velocidad, diseño consistente |
| Styled Components | ✅ Automático | ✅ | CSS dinámico, temas |
| Panda/Vanilla Extract | ✅ Automático | ❌ | Zero-runtime type-safe |
