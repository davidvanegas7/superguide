# Introducción a React

React es una **biblioteca de JavaScript** creada y mantenida por Meta (Facebook), diseñada para construir **interfaces de usuario** modernas y reactivas. Es la librería frontend más popular del mundo y la base de aplicaciones como Facebook, Instagram, WhatsApp Web, Netflix y Airbnb.

---

## ¿Por qué React?

| Característica | Descripción |
|---|---|
| **Basado en componentes** | UI dividida en piezas reutilizables e independientes |
| **Virtual DOM** | Actualizaciones eficientes comparando árboles virtuales antes de tocar el DOM real |
| **Unidirectional data flow** | Los datos fluyen de padre a hijo, haciendo la app predecible |
| **Ecosistema masivo** | React Router, Redux, Next.js, React Query y miles de librerías |
| **Mantenido por Meta** | Actualizaciones regulares, React 19+ con Server Components |
| **Gran comunidad** | La mayor comunidad frontend, abundante documentación y empleo |

---

## Historia y versiones clave

- **React 0.3 (2013)**: Lanzamiento open source por Facebook.
- **React 16 (2017)**: Fiber — nuevo motor de reconciliación, error boundaries, portals.
- **React 16.8 (2019)**: **Hooks** — revolución que reemplazó las clases en la mayoría de casos.
- **React 17 (2020)**: Sin nuevas features visibles; mejoras internas para upgrades graduales.
- **React 18 (2022)**: Concurrent rendering, `useTransition`, `useDeferredValue`, Suspense mejorado.
- **React 19 (2024)**: Server Components, Actions, `use()` hook, compilador optimizador.

> La versión actual de React usa **funciones con hooks** como modelo principal. Los ejemplos con clases (`class MyComponent extends React.Component`) pertenecen al estilo legacy.

---

## Arquitectura general

Una aplicación React se estructura así:

```
App
├── Componentes (funciones que retornan JSX)
│   ├── Props         ← Datos que recibe del padre
│   ├── State         ← Datos internos del componente
│   ├── Hooks         ← Lógica reutilizable (useState, useEffect, custom hooks)
│   └── Eventos       ← Interacción del usuario
├── Context / Estado global  ← Datos compartidos entre componentes lejanos
└── Router                   ← Navegación entre vistas (React Router, Next.js)
```

### Flujo de datos unidireccional

```
Props (padre → hijo)
     ↓
Componente → Renderiza JSX → DOM virtual
     ↑                            ↓
  Estado (useState)         Reconciliación → DOM real
     ↑
  Eventos del usuario
```

---

## Requisitos previos

Antes de aprender React, deberías conocer:

- ✅ **HTML y CSS** — Estructura y estilo web
- ✅ **JavaScript ES6+** — Arrow functions, destructuring, módulos, spread operator, promesas
- ✅ **TypeScript básico** — Tipos, interfaces, generics (recomendado)
- ✅ **Node.js y npm** — Para el tooling de desarrollo

---

## Instalación

### 1. Instalar Node.js

Descarga Node.js desde [nodejs.org](https://nodejs.org). React requiere Node.js 18 o superior.

```bash
node --version   # v18.x.x o superior
npm --version    # 9.x.x o superior
```

### 2. Crear un proyecto React con Vite

**Vite** es el bundler recomendado para proyectos React modernos (reemplaza a Create React App):

```bash
npm create vite@latest mi-app-react -- --template react-ts
```

> Usamos `react-ts` para incluir TypeScript desde el inicio.

```bash
cd mi-app-react
npm install
npm run dev
```

Abre el navegador en `http://localhost:5173` y verás la pantalla de bienvenida de Vite + React.

---

## Estructura del proyecto

```
mi-app-react/
├── src/
│   ├── App.tsx           ← Componente raíz
│   ├── App.css           ← Estilos del componente
│   ├── main.tsx          ← Punto de entrada
│   ├── index.css         ← Estilos globales
│   └── vite-env.d.ts     ← Tipos de Vite
├── public/               ← Archivos estáticos (favicon, imágenes)
├── index.html            ← HTML principal
├── package.json          ← Dependencias
├── tsconfig.json         ← Configuración TypeScript
└── vite.config.ts        ← Configuración de Vite
```

### `index.html` — El HTML real
```html
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mi App React</title>
  </head>
  <body>
    <div id="root"></div>         <!-- React monta la app aquí -->
    <script type="module" src="/src/main.tsx"></script>
  </body>
</html>
```

### `main.tsx` — Punto de entrada

```tsx
import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App'
import './index.css'

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
)
```

> **StrictMode** ejecuta los componentes dos veces en desarrollo para detectar efectos secundarios accidentales. No afecta producción.

### `App.tsx` — Componente raíz

```tsx
function App() {
  return (
    <div>
      <h1>¡Hola React!</h1>
      <p>Mi primera aplicación con React y TypeScript.</p>
    </div>
  )
}

export default App
```

---

## React vs otros frameworks

| Aspecto | React | Angular | Vue |
|---|---|---|---|
| **Tipo** | Biblioteca (UI) | Framework completo | Framework progresivo |
| **Lenguaje** | JSX/TSX | TypeScript + HTML | SFC (HTML/JS/CSS) |
| **Estado** | Hooks + librerías | Servicios + RxJS | Composition API |
| **Curva de aprendizaje** | Baja-Media | Alta | Baja |
| **Renderizado** | Virtual DOM | Incremental DOM | Virtual DOM + Proxy |
| **Ecosistema** | Por librerías | Todo incluido | Intermedio |
| **Empleo** | Mayor demanda | Demanda alta | Demanda creciente |

---

## Comandos más usados

```bash
# Crear proyecto con Vite
npm create vite@latest nombre -- --template react-ts

# Iniciar servidor de desarrollo (hot reload)
npm run dev

# Compilar para producción
npm run build

# Previsualizar el build de producción
npm run preview

# Instalar una dependencia
npm install nombre-paquete

# Instalar dependencia de desarrollo
npm install -D nombre-paquete
```

---

## ¿Qué aprenderás en este curso?

Este curso cubre React de forma progresiva:

1. 🧱 **JSX y Rendering** — La sintaxis de React
2. 📦 **Componentes y Props** — Crear piezas reutilizables
3. 🔄 **Estado y Eventos** — Interactividad
4. 🪝 **Hooks fundamentales** — useState, useEffect
5. 🧩 **Hooks avanzados** — useReducer, useContext, useRef, useMemo
6. 📝 **Formularios** — Controlados, validación
7. 🗺️ **React Router** — Navegación SPA
8. 🌐 **Fetching de datos** — fetch, React Query, Suspense
9. 📦 **Context API** — Estado global sin librerías
10. 🏪 **Redux Toolkit** — Estado complejo a gran escala
11. 🧪 **Testing** — Vitest, React Testing Library
12. ⚡ **Performance** — memo, useMemo, useCallback, lazy loading
13. 🪝 **Custom Hooks** — Patrones reutilizables
14. 🔷 **TypeScript con React** — Tipado avanzado de componentes
15. 🎨 **Estilos en React** — CSS Modules, Tailwind, Styled Components
16. 🖥️ **SSR con Next.js** — Server-Side Rendering y App Router
17. 🏗️ **Patrones avanzados** — Compound components, render props, HOCs
18. ❓ **Preguntas de entrevista** — Lo que te van a preguntar
19. 🌍 **i18n** — Internacionalización con react-intl / next-intl

¡Vamos a empezar!
