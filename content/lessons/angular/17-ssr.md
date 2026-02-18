# SSR con Angular Universal

## ¿Qué es Server-Side Rendering?

Por defecto Angular es una **SPA (Single Page Application)**: el servidor envía un HTML casi vacío y es el navegador quien construye toda la UI ejecutando JavaScript. Esto funciona muy bien para dashboards y apps autenticadas, pero tiene dos desventajas claras:

- **SEO deficiente** — los crawlers de Google (y sobre todo Bing, LinkedIn, Twitter…) ven la página vacía.
- **FCP lento** (*First Contentful Paint*) — el usuario ve una pantalla en blanco mientras se descarga y ejecuta el bundle JS.

**SSR** resuelve esto: Angular ejecuta la app en un servidor **Node.js**, genera el HTML completo y lo envía al navegador. El navegador lo muestra de inmediato (tiempo de carga rápido) y luego Angular "hidrata" la página (la hace interactiva) sin volver a renderizarla.

```
Petición  →  Servidor Node.js (Angular)
                  │
                  ▼
             HTML completo
                  │
                  ▼
          Navegador lo muestra al instante
                  │
                  ▼  (Angular se "hidrata")
          App totalmente interactiva
```

---

## Cuándo usar SSR (y cuándo no)

| Úsalo si… | Evítalo si… |
|---|---|
| Landing pages y marketing | Dashboard autenticado |
| Blog / documentación | Herramienta muy interactiva |
| E-commerce (producto/listado) | App interna sin necesidad de SEO |
| Necesitas Open Graph tags dinámicos | Quieres simplicidad de despliegue |

---

## Agregar SSR a un proyecto existente

```bash
ng add @angular/ssr
```

Este comando modifica automáticamente tu proyecto:

```
src/
├── app/
│   ├── app.config.ts            ← añade provideClientHydration()
│   └── app.config.server.ts     ← config específica del servidor (NUEVO)
├── server.ts                    ← punto de entrada Node.js (NUEVO)
angular.json                     ← añade el builder "server"
```

---

## Archivos clave generados

### `server.ts` — el servidor Node.js

```typescript
import { APP_BASE_HREF } from '@angular/common';
import { CommonEngine } from '@angular/ssr';
import express from 'express';
import { fileURLToPath } from 'node:url';
import { dirname, join, resolve } from 'node:path';
import bootstrap from './src/main.server';

export function app(): express.Express {
  const server = express();
  const serverDistFolder = dirname(fileURLToPath(import.meta.url));
  const browserDistFolder = resolve(serverDistFolder, '../browser');
  const indexHtml = join(serverDistFolder, 'index.server.html');

  const commonEngine = new CommonEngine();

  server.set('view engine', 'html');
  server.set('views', browserDistFolder);

  // Sirve archivos estáticos del build del browser
  server.get('**', express.static(browserDistFolder, {
    maxAge: '1y',
    index: 'index.html',
  }));

  // Renderiza todas las rutas con Angular
  server.get('**', (req, res, next) => {
    const { protocol, originalUrl, baseUrl, headers } = req;

    commonEngine
      .render({
        bootstrap,
        documentFilePath: indexHtml,
        url: `${protocol}://${headers.host}${originalUrl}`,
        publicPath: browserDistFolder,
        providers: [{ provide: APP_BASE_HREF, useValue: baseUrl }],
      })
      .then((html) => res.send(html))
      .catch((err) => next(err));
  });

  return server;
}
```

### `app.config.server.ts` — configuración del servidor

```typescript
import { mergeApplicationConfig, ApplicationConfig } from '@angular/core';
import { provideServerRendering } from '@angular/platform-server';
import { appConfig } from './app.config';

const serverConfig: ApplicationConfig = {
  providers: [
    provideServerRendering()
  ]
};

export const config = mergeApplicationConfig(appConfig, serverConfig);
```

### `app.config.ts` — hidratación en el cliente

```typescript
import { ApplicationConfig, provideZoneChangeDetection } from '@angular/core';
import { provideRouter } from '@angular/router';
import { provideClientHydration } from '@angular/platform-browser';  // ← añadido
import { routes } from './app.routes';

export const appConfig: ApplicationConfig = {
  providers: [
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(routes),
    provideClientHydration()  // ← permite que Angular reutilice el HTML del servidor
  ]
};
```

---

## El problema de `window` y `document`

En el servidor **no existe** el navegador, por lo que acceder directamente a `window`, `document`, `localStorage` o `navigator` provoca un error. La solución es `isPlatformBrowser`:

```typescript
import { Component, OnInit, inject, PLATFORM_ID } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

@Component({
  selector: 'app-analytics',
  template: `<p>Analytics cargado</p>`
})
export class AnalyticsComponent implements OnInit {
  private platformId = inject(PLATFORM_ID);

  ngOnInit() {
    if (isPlatformBrowser(this.platformId)) {
      // Este bloque SOLO se ejecuta en el navegador
      console.log('window.innerWidth:', window.innerWidth);
      localStorage.setItem('visited', 'true');
    }
  }
}
```

> **Regla de oro:** si usas `window`, `document`, `localStorage` o cualquier API del navegador, envuélvelo en `isPlatformBrowser(platformId)`.

---

## `TransferState` — evitar doble petición HTTP

Sin SSR el flujo es:
1. Servidor renderiza → hace petición HTTP a la API
2. Cliente hidrata → **vuelve a hacer** la misma petición HTTP

Con `TransferState` el servidor serializa la respuesta en el HTML y el cliente la reutiliza:

```typescript
import { Component, OnInit, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { TransferState, makeStateKey } from '@angular/core';

const POSTS_KEY = makeStateKey<any[]>('posts');

@Component({
  selector: 'app-blog',
  template: `<article *ngFor="let post of posts">{{ post.title }}</article>`
})
export class BlogComponent implements OnInit {
  private http = inject(HttpClient);
  private state = inject(TransferState);
  posts: any[] = [];

  ngOnInit() {
    const cached = this.state.get(POSTS_KEY, null);
    if (cached) {
      this.posts = cached;
      this.state.remove(POSTS_KEY);
    } else {
      this.http.get<any[]>('https://api.ejemplo.com/posts').subscribe(data => {
        this.posts = data;
        this.state.set(POSTS_KEY, data); // serializa para el cliente
      });
    }
  }
}
```

> **Angular 17+**: `withHttpTransferCache()` en `provideClientHydration` hace esto automáticamente para peticiones HTTP simples:
>
> ```typescript
> provideClientHydration(withHttpTransferCache())
> ```

---

## Construir y ejecutar

```bash
# Build completo (browser + server)
ng build

# Ejecutar el servidor SSR localmente
node dist/mi-app/server/server.mjs

# O con el script de npm que genera ng add @angular/ssr:
npm run serve:ssr:mi-app
```

La salida del build será:

```
dist/mi-app/
├── browser/   ← assets estáticos (CSS, JS, imágenes)
└── server/    ← bundle Node.js
    └── server.mjs
```

---

## Despliegue en producción

Para producción necesitas un servidor Node.js corriendo. Opciones comunes:

### Con PM2 (VPS / servidor propio)

```bash
npm install -g pm2
pm2 start dist/mi-app/server/server.mjs --name "mi-app-ssr"
pm2 save
pm2 startup
```

### Con Nginx de proxy inverso

```nginx
server {
    listen 443 ssl;
    server_name miapp.com;

    location / {
        proxy_pass http://localhost:4000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

### Plataformas que soportan SSR sin configuración

| Plataforma | Notas |
|---|---|
| **Vercel** | Detecta Angular SSR automáticamente |
| **Firebase Hosting** + Cloud Functions | `ng deploy` con `@angular/fire` |
| **Netlify** | Con el adaptador oficial |
| **Railway / Render** | Deployar la imagen Docker |

---

## Resumen

| Concepto | Qué hace |
|---|---|
| `ng add @angular/ssr` | Añade SSR a un proyecto existente |
| `server.ts` | Servidor Express que renderiza Angular |
| `provideClientHydration()` | Reutiliza el HTML del servidor (sin re-renderizar) |
| `isPlatformBrowser(platformId)` | Evita usar APIs del navegador en el servidor |
| `TransferState` / `withHttpTransferCache()` | Evita doble petición HTTP cliente/servidor |
| `ng build` + `node dist/.../server.mjs` | Compila y ejecuta la app SSR |
