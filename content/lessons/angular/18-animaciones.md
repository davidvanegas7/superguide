# Animaciones en Angular

## ¿Por qué usar animaciones?

Las animaciones bien usadas:
- **Guían la atención** del usuario hacia cambios importantes
- **Reducen la percepción de latencia** (un spinner animado se siente más rápido)
- **Mejoran la experiencia** al comunicar transiciones de estado (abierto/cerrado, activo/inactivo)

Angular incluye un módulo de animaciones basado en la **Web Animations API**, que abstrae las diferencias entre navegadores y se integra con el sistema de detección de cambios de Angular.

---

## Configuración inicial

```bash
# Ya está incluido en @angular/core pero necesita el módulo de plataforma:
npm install @angular/animations
```

En `app.config.ts`:

```typescript
import { ApplicationConfig } from '@angular/core';
import { provideRouter } from '@angular/router';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async'; // ← async = lazy

export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(routes),
    provideAnimationsAsync()  // carga animaciones solo cuando se necesitan
  ]
};
```

> **`provideAnimationsAsync`** (Angular 17+) es la forma moderna. Si usas módulos: `BrowserAnimationsModule` en `AppModule`.

---

## Conceptos fundamentales

| Función | Qué hace |
|---|---|
| `trigger(name, [...])` | Define una animación con nombre |
| `state(name, style)` | Define el estilo CSS de un estado |
| `transition('a => b', ...)` | Define qué ocurre al cambiar de estado `a` a `b` |
| `animate(timing, style?)` | La animación en sí: duración, easing, estilos finales |
| `keyframes([...])` | Animación por fotogramas clave |
| `group([...])` | Varias animaciones en paralelo |
| `sequence([...])` | Varias animaciones en secuencia |
| `query(selector, ...)` | Anima elementos hijos |
| `stagger(time, ...)` | Retraso escalonado entre elementos de una lista |

---

## Tu primera animación: fade in/out

```typescript
import { Component } from '@angular/core';
import {
  trigger,
  state,
  style,
  transition,
  animate
} from '@angular/animations';

@Component({
  selector: 'app-mensaje',
  standalone: true,
  template: `
    <button (click)="visible = !visible">Toggle</button>
    <div [@fadeInOut]="visible ? 'visible' : 'hidden'" class="caja">
      Hola mundo
    </div>
  `,
  styles: [`.caja { background: #4f46e5; color: white; padding: 1rem; margin-top: 1rem; }`],
  animations: [
    trigger('fadeInOut', [
      state('visible', style({ opacity: 1, transform: 'translateY(0)' })),
      state('hidden',  style({ opacity: 0, transform: 'translateY(-10px)' })),
      transition('hidden => visible', animate('300ms ease-out')),
      transition('visible => hidden', animate('200ms ease-in')),
    ])
  ]
})
export class MensajeComponent {
  visible = true;
}
```

**Explicación:**
- `[@fadeInOut]="estado"` — vincula la animación al estado del componente
- `state()` — define los estilos CSS cuando el elemento está en ese estado
- `transition('A => B', animate(...))` — define la transición entre estados
- `'300ms ease-out'` — duración + función de easing (acepta también `'0.3s 100ms ease-out'` para delay)

---

## Atajos `:enter` y `:leave`

Los alias `:enter` y `:leave` se usan para animar elementos cuando Angular los **añade o elimina del DOM** (con `*ngIf` / `@if`):

```typescript
@Component({
  selector: 'app-modal',
  standalone: true,
  imports: [NgIf],
  template: `
    <button (click)="mostrar = !mostrar">Abrir modal</button>
    <div *ngIf="mostrar" [@slideDown] class="modal">
      Contenido del modal
    </div>
  `,
  animations: [
    trigger('slideDown', [
      transition(':enter', [
        style({ opacity: 0, transform: 'translateY(-20px)' }),
        animate('250ms ease-out', style({ opacity: 1, transform: 'translateY(0)' }))
      ]),
      transition(':leave', [
        animate('200ms ease-in', style({ opacity: 0, transform: 'translateY(-20px)' }))
      ])
    ])
  ]
})
export class ModalComponent {
  mostrar = false;
}
```

> `:enter` = `void => *` (el elemento pasa de "no existir" a cualquier estado)
> `:leave` = `* => void` (el elemento pasa de cualquier estado a "no existir")

---

## Animaciones en listas con `stagger`

Para animar elementos de una lista uno tras otro:

```typescript
import { trigger, transition, query, stagger, animate, style } from '@angular/animations';

@Component({
  selector: 'app-lista',
  standalone: true,
  template: `
    <button (click)="cargar()">Cargar items</button>
    <ul [@listaAnimada]="items.length">
      <li *ngFor="let item of items">{{ item }}</li>
    </ul>
  `,
  animations: [
    trigger('listaAnimada', [
      transition('* => *', [
        query(':enter', [
          style({ opacity: 0, transform: 'translateX(-20px)' }),
          stagger(80, [
            animate('300ms ease-out', style({ opacity: 1, transform: 'translateX(0)' }))
          ])
        ], { optional: true })
      ])
    ])
  ]
})
export class ListaComponent {
  items: string[] = [];

  cargar() {
    this.items = ['Angular', 'React', 'Vue', 'Svelte', 'Solid'];
  }
}
```

`stagger(80, ...)` introduce un retraso de 80ms entre cada elemento, creando el efecto "cascada".

---

## Animaciones de ruta (Route Transitions)

Para animar la entrada/salida de páginas al navegar:

```typescript
// animations/route-animations.ts
import { trigger, transition, style, query, animate } from '@angular/animations';

export const routeAnimations = trigger('routeAnimations', [
  transition('* <=> *', [
    query(':enter, :leave', style({ position: 'fixed', width: '100%' }), { optional: true }),
    query(':enter', style({ transform: 'translateX(100%)' }), { optional: true }),
    query(':leave', animate('300ms ease-out', style({ transform: 'translateX(-100%)' })), { optional: true }),
    query(':enter', animate('300ms ease-out', style({ transform: 'translateX(0%)' })), { optional: true }),
  ])
]);
```

En `app.component.ts`:

```typescript
import { RouterOutlet } from '@angular/router';
import { routeAnimations } from './animations/route-animations';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet],
  template: `
    <main [@routeAnimations]="getRouteState(outlet)">
      <router-outlet #outlet="outlet"></router-outlet>
    </main>
  `,
  animations: [routeAnimations]
})
export class AppComponent {
  getRouteState(outlet: RouterOutlet) {
    return outlet?.activatedRouteData?.['animation'] ?? '';
  }
}
```

En las rutas, define el dato de animación:

```typescript
// app.routes.ts
export const routes: Routes = [
  { path: 'home',    component: HomeComponent,    data: { animation: 'home' } },
  { path: 'about',   component: AboutComponent,   data: { animation: 'about' } },
];
```

---

## `AnimationBuilder` — animaciones programáticas

Cuando necesitas lanzar una animación desde código (sin bindear al template):

```typescript
import { Component, ElementRef, ViewChild, inject } from '@angular/core';
import { AnimationBuilder, animate, style, keyframes } from '@angular/animations';

@Component({
  selector: 'app-shake',
  standalone: true,
  template: `
    <input #inputRef type="text" [(ngModel)]="valor" placeholder="Escribe algo">
    <button (click)="validar()">Validar</button>
  `
})
export class ShakeComponent {
  @ViewChild('inputRef') inputRef!: ElementRef;
  valor = '';
  private builder = inject(AnimationBuilder);

  validar() {
    if (!this.valor.trim()) {
      const factory = this.builder.build([
        animate('500ms', keyframes([
          style({ transform: 'translateX(0)',    offset: 0 }),
          style({ transform: 'translateX(-8px)', offset: 0.25 }),
          style({ transform: 'translateX(8px)',  offset: 0.5 }),
          style({ transform: 'translateX(-8px)', offset: 0.75 }),
          style({ transform: 'translateX(0)',    offset: 1 }),
        ]))
      ]);
      const player = factory.create(this.inputRef.nativeElement);
      player.play();
    }
  }
}
```

---

## Deshabilitar animaciones en tests

Las animaciones pueden ralentizar los tests. Para deshabilitarlas:

```typescript
// en el archivo de test:
import { NoopAnimationsModule } from '@angular/platform-browser/animations';

TestBed.configureTestingModule({
  imports: [NoopAnimationsModule]  // reemplaza todas las animaciones con no-ops
});
```

---

## Consejos de rendimiento

1. **Usa `transform` y `opacity`** — son las propiedades más baratas (se procesan en la GPU). Evita animar `width`, `height`, `top`, `left` ya que fuerzan *reflow*.
2. **Duración < 400ms** para la mayoría de transiciones de UI. El usuario percibe > 400ms como lento.
3. **`provideAnimationsAsync()`** carga animaciones de forma lazy; úsalo en lugar de `provideAnimations()`.
4. **`{ optional: true }` en `query()`** evita errores si el selector no encuentra elementos.

---

## Resumen

| Necesitas | Usa |
|---|---|
| Mostrar/ocultar con `*ngIf` | `transition(':enter / :leave', ...)` |
| Cambio de estado binario | `state()` + `transition('a => b', ...)` |
| Animar lista al cargar | `query(':enter', stagger(...))` |
| Animación de ruta | `trigger` en `<router-outlet>` |
| Animación desde código TS | `AnimationBuilder.build(...)` |
| Fotogramas intermedios | `keyframes([style({offset: 0.5}), ...])` |
