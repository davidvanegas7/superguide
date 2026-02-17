# Performance y Optimización en Angular

El rendimiento es un diferenciador clave a nivel senior. Esta lección cubre las estrategias de optimización más importantes en Angular: desde el control de la detección de cambios hasta el Server-Side Rendering.

---

## Change Detection: Default vs OnPush

Angular detecta cambios revisando el árbol de componentes completo después de cada evento. La estrategia **OnPush** le dice a Angular que solo revise el componente cuando:

1. Cambia un `@Input` (por referencia)
2. El componente despacha un evento
3. Un Observable al que está suscrito emite un valor
4. Se llama explícitamente a `ChangeDetectorRef.markForCheck()`

```typescript
import { Component, Input, ChangeDetectionStrategy, ChangeDetectorRef, inject } from '@angular/core';

// Sin OnPush (defecto) — Angular revisa este componente en cada ciclo
@Component({ selector: 'app-lento', template: '...' })
export class ComponenteLentoComponent {
  @Input() datos: any[] = [];
}

// Con OnPush — mucho más eficiente
@Component({
  selector: 'app-rapido',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,  // ← La clave
  template: `
    <ul>
      @for (item of datos; track item.id) {
        <li>{{ item.nombre }}</li>
      }
    </ul>
    <p>Contador interno: {{ contador }}</p>
    <button (click)="incrementar()">+1</button>
  `
})
export class ComponenteRapidoComponent {
  @Input() datos: any[] = [];
  contador = 0;
  private cdr = inject(ChangeDetectorRef);

  incrementar(): void {
    this.contador++;
    // Con OnPush, los eventos del propio componente ya disparan la detección
    // No necesitas markForCheck() aquí
  }

  // Si modificas el estado desde fuera (ej. callback, setTimeout):
  actualizarDesdeAfuera(): void {
    this.contador = 100;
    this.cdr.markForCheck();  // ← Decirle a Angular que revise este componente
  }
}
```

### ⚠️ Trampa común con OnPush

```typescript
// ❌ Mal — Angular NO detecta la mutación del array (misma referencia)
this.datos.push(nuevoItem);

// ✅ Bien — nueva referencia → Angular detecta el cambio
this.datos = [...this.datos, nuevoItem];

// ❌ Mal — mutación del objeto
this.usuario.nombre = 'Ana';

// ✅ Bien — nuevo objeto
this.usuario = { ...this.usuario, nombre: 'Ana' };
```

---

## trackBy en `@for` — Optimizar listas

Sin `track`, Angular destruye y recrea todos los elementos DOM al actualizar la lista. Con `track`, solo actualiza los elementos que cambiaron:

```typescript
@Component({
  template: `
    <!-- ❌ Sin track — recrea todo el DOM en cada cambio -->
    @for (item of items) {
      <app-item [data]="item" />
    }

    <!-- ✅ Con track — reutiliza elementos existentes -->
    @for (item of items; track item.id) {
      <app-item [data]="item" />
    }

    <!-- Para listas de primitivos -->
    @for (nombre of nombres; track nombre) {
      <li>{{ nombre }}</li>
    }

    <!-- Track por índice (último recurso si no hay ID único) -->
    @for (item of items; track $index) {
      <li>{{ item }}</li>
    }
  `
})
export class ListaComponent {
  items = [
    { id: 1, nombre: 'Angular' },
    { id: 2, nombre: 'React' }
  ];
  nombres = ['Ana', 'Carlos', 'María'];
}
```

---

## Virtual Scrolling con CDK

Para listas de miles de elementos, el Virtual Scrolling renderiza **solo los elementos visibles** en el viewport:

```bash
npm install @angular/cdk
```

```typescript
import { Component } from '@angular/core';
import { ScrollingModule } from '@angular/cdk/scrolling';

@Component({
  selector: 'app-lista-virtual',
  standalone: true,
  imports: [ScrollingModule],
  template: `
    <!-- Altura fija del contenedor de scroll -->
    <cdk-virtual-scroll-viewport itemSize="50" style="height: 400px;">
      <!-- *cdkVirtualFor reemplaza a @for en este contexto -->
      <div *cdkVirtualFor="let item of items; trackBy: trackById"
           class="item"
           style="height: 50px;">
        {{ item.id }}. {{ item.nombre }}
      </div>
    </cdk-virtual-scroll-viewport>
  `
})
export class ListaVirtualComponent {
  // Lista de 10.000 elementos — sin virtual scroll sería muy lento
  items = Array.from({ length: 10_000 }, (_, i) => ({
    id: i + 1,
    nombre: `Producto ${i + 1}`
  }));

  trackById(index: number, item: { id: number }): number {
    return item.id;
  }
}
```

---

## Preloading Strategies — Lazy Loading inteligente

El lazy loading carga los módulos bajo demanda. Las estrategias de precarga los cargan **en segundo plano** después del load inicial:

```typescript
// app.config.ts
import {
  provideRouter,
  withPreloading,
  PreloadAllModules,    // Precarga todos los módulos lazy en background
  NoPreloading          // No precarga nada (por defecto)
} from '@angular/router';

export const appConfig = {
  providers: [
    provideRouter(routes, withPreloading(PreloadAllModules))
  ]
};
```

### Estrategia de precarga personalizada

```typescript
import { Injectable } from '@angular/core';
import { PreloadingStrategy, Route } from '@angular/router';
import { Observable, of, timer } from 'rxjs';
import { switchMap } from 'rxjs/operators';

@Injectable({ providedIn: 'root' })
export class PrecargaSelectiva implements PreloadingStrategy {
  preload(route: Route, cargar: () => Observable<any>): Observable<any> {
    // Solo precarga rutas marcadas con data.preload = true
    if (route.data?.['preload']) {
      // Espera 2 segundos antes de precargar (no compite con el load inicial)
      return timer(2000).pipe(switchMap(() => cargar()));
    }
    return of(null);  // No precargar
  }
}

// En las rutas
export const routes: Routes = [
  { path: '', component: HomeComponent },
  {
    path: 'dashboard',
    loadComponent: () => import('./pages/dashboard.component').then(m => m.DashboardComponent),
    data: { preload: true }   // ← Se precarga en background
  },
  {
    path: 'reportes',
    loadComponent: () => import('./pages/reportes.component').then(m => m.ReportesComponent),
    data: { preload: false }  // ← No se precarga
  }
];

// Registrar la estrategia
provideRouter(routes, withPreloading(PrecargaSelectiva))
```

---

## Análisis del Bundle

### Verificar el tamaño del bundle

```bash
# Build de producción con estadísticas
ng build --stats-json

# Analizar con webpack-bundle-analyzer
npx webpack-bundle-analyzer dist/mi-app/browser/stats.json
```

### Buenas prácticas para reducir el bundle

```typescript
// ❌ Importar toda la librería
import * as _ from 'lodash';
import { format } from 'date-fns';

// ✅ Importar solo lo que usas
import debounce from 'lodash/debounce';
import { format } from 'date-fns';

// ❌ CommonModule incluye muchas cosas que quizás no usas
import { CommonModule } from '@angular/common';

// ✅ Importar solo lo que necesitas
import { AsyncPipe, DatePipe, CurrencyPipe } from '@angular/common';
import { NgIf, NgFor } from '@angular/common';  // Si usas directivas antiguas

// Verificar qué importa tu componente
@Component({
  imports: [AsyncPipe, DatePipe],  // Solo estas dos pipes
  ...
})
```

### Análisis con source-map-explorer

```bash
npm install -g source-map-explorer
ng build --source-map
source-map-explorer dist/mi-app/browser/*.js
```

---

## Server-Side Rendering (SSR) con Angular Universal

SSR renderiza la app en el servidor y envía HTML listo al navegador, mejorando:
- **SEO** (los motores de búsqueda ven el contenido)
- **First Contentful Paint (FCP)** (el usuario ve contenido más rápido)

```bash
# Agregar SSR al proyecto existente
ng add @angular/ssr
```

Esto genera:
- `server.ts` — servidor Express con SSR
- `app.config.server.ts` — configuración para el servidor

```typescript
// app.config.server.ts
import { mergeApplicationConfig } from '@angular/core';
import { provideServerRendering } from '@angular/platform-server';
import { appConfig } from './app.config';

const serverConfig = {
  providers: [provideServerRendering()]
};

export const config = mergeApplicationConfig(appConfig, serverConfig);
```

### Cuidados con SSR

```typescript
import { Component, inject, PLATFORM_ID } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

@Component({ ... })
export class AppComponent {
  private platformId = inject(PLATFORM_ID);

  ngOnInit(): void {
    // ❌ Esto falla en SSR (no hay window/document en Node.js)
    // window.localStorage.getItem('token');

    // ✅ Verificar si estamos en el browser antes de usar APIs del navegador
    if (isPlatformBrowser(this.platformId)) {
      const token = localStorage.getItem('token');
    }
  }
}
```

---

## Memoización con `pure` pipes

Los pipes puros (por defecto) solo recalculan cuando cambia la referencia de entrada:

```typescript
// Este pipe es puro — solo recalcula si 'lista' cambia de referencia
@Pipe({ name: 'ordenar', standalone: true, pure: true })
export class OrdenarPipe implements PipeTransform {
  transform(lista: any[], campo: string): any[] {
    // Solo se ejecuta cuando 'lista' es una nueva referencia
    return [...lista].sort((a, b) => a[campo] > b[campo] ? 1 : -1);
  }
}
```

---

## Defer — Carga lazy de contenido pesado

```html
<!-- Carga el componente solo cuando entra en el viewport -->
@defer (on viewport) {
  <app-grafico-d3 [datos]="datos" />          <!-- Pesado -->
  <app-mapa-interactivo [config]="config" />   <!-- Pesado -->
} @placeholder {
  <div class="skeleton" style="height:300px">Cargando visualización...</div>
}

<!-- Carga cuando el usuario hace idle (no está interactuando) -->
@defer (on idle) {
  <app-chat-widget />
}

<!-- Carga solo si se cumple una condición -->
@defer (when usuarioPremium()) {
  <app-funciones-premium />
} @placeholder {
  <p>Actualiza a Premium para ver esto</p>
}
```

---

## Lighthouse y métricas de rendimiento

Las métricas que importan (Core Web Vitals):

| Métrica | Objetivo | Descripción |
|---|---|---|
| **LCP** | < 2.5s | Largest Contentful Paint |
| **FID/INP** | < 100ms | First Input Delay / Interaction to Next Paint |
| **CLS** | < 0.1 | Cumulative Layout Shift |
| **FCP** | < 1.8s | First Contentful Paint |
| **TTFB** | < 600ms | Time To First Byte |

```bash
# Medir con Chrome DevTools → Lighthouse
# O con la CLI:
npx lighthouse https://tu-app.com --output html --output-path reporte.html
```

---

## Checklist de optimización

```
✅ OnPush en todos los componentes presentacionales
✅ track en todos los @for
✅ AsyncPipe en lugar de suscripciones manuales (o toSignal)
✅ Lazy loading en rutas no críticas
✅ @defer para contenido pesado bajo el fold
✅ Virtual scrolling para listas > 100 elementos
✅ Imágenes con loading="lazy" y tamaños definidos
✅ SSR si el SEO es importante
✅ Bundle analyzer para detectar dependencias pesadas
✅ Preloading strategy configurada
✅ Compilación de producción con --configuration=production
```
