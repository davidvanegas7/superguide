# Introducción a Angular

Angular es un **framework de desarrollo web** creado y mantenido por Google, basado en **TypeScript**. Es una plataforma completa para construir aplicaciones web modernas, especialmente **Single Page Applications (SPA)**: aplicaciones que cargan una sola vez y actualizan el contenido dinámicamente sin recargar la página.

---

## ¿Por qué Angular?

| Característica | Descripción |
|---|---|
| **Framework completo** | Incluye todo: routing, formularios, HTTP, testing |
| **TypeScript nativo** | Tipado estático, autocompletado, detección de errores en tiempo de desarrollo |
| **Arquitectura sólida** | Módulos, componentes, servicios e inyección de dependencias |
| **Mantenido por Google** | Actualizaciones regulares, soporte a largo plazo (LTS) |
| **CLI potente** | Angular CLI automatiza la creación de proyectos, componentes y más |

---

## Historia y versiones

- **AngularJS (2010)**: La versión original, basada en JavaScript. Ya está deprecada.
- **Angular 2+ (2016)**: Reescritura total en TypeScript. Es lo que hoy conocemos como "Angular".
- **Angular Ivy (2019)**: Nuevo motor de compilación, más rápido y eficiente.
- **Angular Signals (v16+)**: Sistema de reactividad moderno inspirado en SolidJS.
- **Angular 17+ (2023)**: Control flow nativo (`@if`, `@for`), vistas diferidas, zoneless experimental.

> La versión actual se llama simplemente **Angular** (sin número al inicio). Si alguien dice "AngularJS", se refiere a la versión antigua e incompatible.

---

## Arquitectura general

Una aplicación Angular se estructura en capas bien definidas:

```
App
├── Módulos (NgModule o Standalone)
│   ├── Componentes  ← Vista + Lógica de presentación
│   ├── Directivas   ← Modifican el DOM
│   ├── Pipes        ← Transforman datos en la plantilla
│   └── Servicios    ← Lógica de negocio, HTTP, estado
└── Routing          ← Navegación entre vistas
```

### Flujo de datos básico

```
Usuario → Evento → Componente → Servicio → HTTP API
                       ↑                       ↓
                   Template ← Componente ← Datos
```

---

## Requisitos previos

Antes de aprender Angular, deberías conocer:

- ✅ **HTML y CSS** — Estructura y estilo web
- ✅ **JavaScript ES6+** — Arrow functions, destructuring, módulos, promesas
- ✅ **TypeScript básico** — Tipos, interfaces, clases, decoradores
- ✅ **Node.js y npm** — Para instalar herramientas y dependencias

---

## Instalación

### 1. Instalar Node.js

Descarga Node.js desde [nodejs.org](https://nodejs.org). Angular requiere Node.js 18 o superior.

```bash
node --version   # v18.x.x o superior
npm --version    # 9.x.x o superior
```

### 2. Instalar Angular CLI

La CLI de Angular es la herramienta principal de desarrollo:

```bash
npm install -g @angular/cli
```

Verifica la instalación:

```bash
ng version
```

Verás algo como:

```
Angular CLI: 17.x.x
Node: 20.x.x
Package Manager: npm
OS: linux x64
```

---

## Crear tu primer proyecto

```bash
ng new mi-primera-app
```

La CLI te hará algunas preguntas:

```
? Which stylesheet format would you like to use? CSS
? Do you want to enable Server-Side Rendering (SSR)? No
```

> **¿Qué es SSR?** *Server-Side Rendering* significa que Angular renderiza la aplicación en el servidor antes de enviarla al navegador. Esto mejora el SEO y el tiempo de carga inicial, pero añade complejidad de configuración (servidor Node.js, manejo especial de `window`/`document`, etc.). Para **aprender Angular** responde **No** — lo cubriremos en detalle en la lección dedicada [SSR con Angular Universal](/angular/angular-completo/ssr-angular-universal).

Luego entra al proyecto e inícialo:

```bash
cd mi-primera-app
ng serve
```

Abre el navegador en `http://localhost:4200` y verás la pantalla de bienvenida de Angular.

---

## Estructura del proyecto

```
mi-primera-app/
├── src/
│   ├── app/
│   │   ├── app.component.ts       ← Componente raíz
│   │   ├── app.component.html     ← Plantilla HTML
│   │   ├── app.component.css      ← Estilos del componente
│   │   ├── app.component.spec.ts  ← Tests unitarios
│   │   └── app.config.ts          ← Configuración de la app
│   ├── index.html                 ← HTML principal (solo tiene <app-root>)
│   ├── main.ts                    ← Punto de entrada de la aplicación
│   └── styles.css                 ← Estilos globales
├── angular.json                   ← Configuración de Angular CLI
├── package.json                   ← Dependencias del proyecto
└── tsconfig.json                  ← Configuración de TypeScript
```

### `index.html` — El HTML real

```html
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>MiPrimeraApp</title>
  <base href="/">
</head>
<body>
  <app-root></app-root>  <!-- ← Aquí Angular monta la app -->
</body>
</html>
```

### `main.ts` — Punto de entrada

```typescript
import { bootstrapApplication } from '@angular/platform-browser';
import { AppComponent } from './app/app.component';
import { appConfig } from './app/app.config';

bootstrapApplication(AppComponent, appConfig)
  .catch((err) => console.error(err));
```

### `app.component.ts` — Componente raíz

```typescript
import { Component } from '@angular/core';

@Component({
  selector: 'app-root',       // ← La etiqueta HTML <app-root>
  standalone: true,            // ← Componente standalone (moderno)
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  title = 'mi-primera-app';   // ← Propiedad accesible en el template
}
```

---

## Comandos Angular CLI más usados

```bash
# Crear un nuevo proyecto
ng new nombre-proyecto

# Iniciar el servidor de desarrollo (con hot reload)
ng serve

# Generar un componente
ng generate component nombre
ng g c nombre   # forma corta

# Generar un servicio
ng generate service nombre
ng g s nombre

# Compilar para producción
ng build --configuration=production

# Ejecutar tests
ng test

# Ver ayuda
ng help
```

---

## ¿Qué aprenderás en este curso?

Este curso cubre Angular de forma progresiva:

1. 🧱 **Componentes** — Creación, inputs, outputs, ciclo de vida
2. 📄 **Templates** — Interpolación, binding, directivas, pipes
3. 💉 **Servicios e Inyección de Dependencias**
4. 🗺️ **Routing** — Navegación, rutas protegidas, lazy loading
5. 📝 **Formularios** — Template-driven y Reactive Forms
6. 🌐 **HTTP Client** — Conectarse a APIs REST
7. 🔄 **RxJS** — Programación reactiva con Observables
8. 📦 **Estado** — Señales (Signals) y gestión de estado

¡Vamos a empezar!
