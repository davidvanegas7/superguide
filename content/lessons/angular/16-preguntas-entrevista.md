# Preguntas de Entrevista: Angular

Estas son las preguntas más frecuentes en entrevistas técnicas para posiciones de Angular en empresas de Estados Unidos (senior, mid-level y tech leads). Están organizadas por categoría, con respuestas concisas y directas.

---

## Fundamentos de Angular

**¿Qué es Angular y en qué se diferencia de AngularJS?**

Angular (v2+) es un framework SPA basado en TypeScript, con arquitectura de componentes, DI jerárquica y compilación Ahead-of-Time. AngularJS (v1) usaba JavaScript, el patrón MVC con $scope y two-way data binding global. Son proyectos distintos: no hay compatibilidad directa.

---

**¿Qué es un componente en Angular? ¿Cuáles son sus partes esenciales?**

Un componente es la unidad básica de la UI. Consta de:
- **Clase TypeScript** — lógica y estado
- **Template HTML** — vista, puede ser inline o en archivo separado
- **Estilos** — CSS/SCSS encapsulados por ViewEncapsulation
- **Decorador `@Component`** — metadata: `selector`, `templateUrl`, `styleUrl`, `standalone`, `imports`, `changeDetection`

---

**¿Qué diferencia hay entre `@Component`, `@Directive` y `@Pipe`?**

| Decorador | Propósito | Tiene template |
|---|---|---|
| `@Component` | Elemento visual con UI | ✅ Sí |
| `@Directive` | Comportamiento sin UI propia | ❌ No |
| `@Pipe` | Transformación de datos en templates | ❌ No |

---

**¿Qué son los decoradores `@Input` y `@Output`?**

- **`@Input()`**: permite que el componente padre pase datos al hijo (property binding)
- **`@Output()`**: permite que el hijo emita eventos hacia el padre (con `EventEmitter`)

En Angular 17+ se puede usar la función `input()` y `output()` de Signals como alternativa más moderna.

---

**¿Qué es el módulo `NgModule` y para qué sirve? ¿Sigue siendo necesario?**

`NgModule` es el sistema de módulos clásico de Angular: agrupa componentes, pipes, directivas y configura providers. Con **Standalone Components** (Angular 14+, por defecto en Angular 17+) ya no es necesario para la mayoría de los casos. Los componentes standalone declaran sus propias dependencias en `imports[]` directamente.

---

## Inyección de Dependencias

**¿Cómo funciona el sistema de DI de Angular?**

Angular mantiene un árbol de injectors. Cuando un componente solicita un servicio, Angular busca hacia arriba en el árbol: primero en el injector del componente, luego en el padre, hasta llegar al Root Injector. Si ninguno puede satisfacerlo, lanza un error.

Los tres scopes principales:
- **`providedIn: 'root'`** — singleton global
- **`providers` en `@Component`** — instancia por componente
- **`providers` en rutas lazy** — instancia por módulo lazy

---

**¿Qué diferencia hay entre `providedIn: 'root'` y declarar el servicio en `providers: []` de un componente?**

- `'root'`: una sola instancia compartida en toda la app (singleton)
- En `providers[]` de un componente: nueva instancia para ese componente y sus hijos (se destruye con el componente)

---

**¿Cuándo usarías `useFactory` vs `useValue` vs `useClass` en un provider?**

- **`useValue`**: para valores estáticos (tokens de configuración, constantes)
- **`useClass`**: para sustituir una clase por otra (ej. mock en tests)
- **`useFactory`**: cuando la instancia depende de lógica de creación o de otros servicios

---

## Change Detection y Rendimiento

**¿Qué es Change Detection y cómo funciona?**

Angular usa Zone.js para interceptar eventos asíncronos (clicks, timers, HTTP) y disparar un ciclo de detección de cambios. En ese ciclo recorre el árbol de componentes de arriba hacia abajo comparando los valores actuales del template con los anteriores.

---

**¿Qué es `ChangeDetectionStrategy.OnPush` y cuándo lo usarías?**

Con `OnPush`, Angular solo revisa el componente cuando:
1. Cambia un `@Input` (por referencia)
2. El componente emite un evento
3. Un Observable ligado con `async` pipe emite
4. Se llama explícitamente a `markForCheck()` o `detectChanges()`

Se usa en componentes presentacionales que reciben datos por `@Input` y no mutan estado interno. Mejora significativamente el rendimiento en apps grandes.

---

**¿Qué es `trackBy` y por qué importa en listas?**

Sin `track`, Angular destruye y recrea todos los nodos DOM cuando cambia el array. Con `track item.id` (o `trackBy` en la sintaxis antigua), Angular identifica cada elemento y solo actualiza los que realmente cambiaron, reduciendo operaciones DOM.

---

**¿Qué es `@defer` en Angular 17+?**

`@defer` carga un bloque del template de forma lazy, solo cuando se cumple una condición (`on viewport`, `on idle`, `when condicion()`). Reduce el bundle inicial y mejora el Time to Interactive.

---

## RxJS y Reactividad

**¿Cuál es la diferencia entre `switchMap`, `mergeMap`, `concatMap` y `exhaustMap`?**

| Operador | Comportamiento cuando llega nueva emisión |
|---|---|
| `switchMap` | Cancela el Observable anterior, suscribe al nuevo |
| `mergeMap` | Mantiene todos los Observables activos en paralelo |
| `concatMap` | Espera a que termine el actual antes de suscribir al siguiente |
| `exhaustMap` | Ignora nuevas emisiones mientras el Observable actual sigue activo |

Casos de uso: `switchMap` → búsquedas; `mergeMap` → descargas paralelas; `concatMap` → operaciones secuenciales; `exhaustMap` → prevenir doble submit.

---

**¿Qué es un Subject en RxJS? ¿Y un BehaviorSubject?**

- **Subject**: Observable y Observer a la vez. Los suscriptores solo reciben emisiones futuras.
- **BehaviorSubject**: igual que Subject pero guarda el último valor y lo emite inmediatamente a cualquier nuevo suscriptor. Ideal para estado compartido.
- **ReplaySubject(n)**: guarda los últimos `n` valores y los emite a nuevos suscriptores.

---

**¿Qué diferencia hay entre `Observable` y `Promise`?**

| | Observable | Promise |
|---|---|---|
| Valores | Puede emitir múltiples | Un solo valor |
| Lazy | ✅ Sí (no ejecuta hasta subscribe) | ❌ No (ejecuta inmediatamente) |
| Cancelable | ✅ Sí (unsubscribe) | ❌ No |
| Operadores | Sí (pipe, map, filter...) | No (solo .then/.catch) |

---

**¿Cómo evitas memory leaks con Observables en componentes Angular?**

Opciones:
1. **`takeUntilDestroyed()`** (Angular 16+) — el operador más moderno, se cancela automáticamente al destruirse el componente
2. **`AsyncPipe`** — gestiona la suscripción automáticamente
3. **`toSignal()`** — convierte Observable a Signal, gestiona la suscripción
4. **Patrón `Subject` + `takeUntil`** — antiguo pero aún válido

---

## Signals

**¿Qué son los Signals en Angular y por qué se introdujeron?**

Los Signals son primitivos reactivos (Angular 16+) que permiten rastrear dependencias de forma síncrona sin Zone.js. Un Signal sabe exactamente qué componentes o `computed` dependen de él, permitiendo actualizaciones más eficientes que el ciclo de Change Detection clásico.

Ventajas sobre Observables para estado de componente: son síncronos, no requieren `subscribe`/`unsubscribe`, y el template se actualiza automáticamente cuando cambia el valor.

---

**¿Cuándo usarías Signals vs RxJS?**

- **Signals**: estado local del componente, valores derivados, comunicación simple padre-hijo
- **RxJS**: streams de eventos asíncronos, operaciones HTTP complejas, composición de múltiples fuentes de datos, timing (debounce, throttle, retry)

Se pueden combinar: `toSignal(observable$)` y `toObservable(signal)`.

---

## Routing y Guards

**¿Qué tipos de Guards existen en Angular y para qué sirve cada uno?**

| Guard | Cuándo se ejecuta |
|---|---|
| `canActivate` | Antes de activar una ruta |
| `canActivateChild` | Antes de activar rutas hijas |
| `canDeactivate` | Al salir de una ruta (ej. formulario sin guardar) |
| `canMatch` | Determina si una ruta debe considerarse |
| `resolve` | Precarga datos antes de activar la ruta |

En Angular 14+ se recomiendan guards como funciones en lugar de clases.

---

**¿Qué es el Lazy Loading y cuáles son sus ventajas?**

Lazy loading carga los módulos o componentes solo cuando el usuario navega a esa ruta, reduciendo el bundle inicial. Se implementa con `loadComponent` (standalone) o `loadChildren` (módulos):

```typescript
{ path: 'admin', loadComponent: () => import('./admin.component').then(m => m.AdminComponent) }
```

---

**¿Qué son los Resolvers?**

Un Resolver precarga datos antes de activar una ruta. El componente recibe los datos ya listos en `ActivatedRoute.data`, evitando el patrón "pantalla vacía → spinner → datos".

---

## Formularios

**¿Cuál es la diferencia entre Template-Driven Forms y Reactive Forms?**

| | Template-Driven | Reactive |
|---|---|---|
| Definición del form | En el template (HTML) | En la clase (TypeScript) |
| Binding | `ngModel` | `formControl`, `formGroup` |
| Validación | Directivas HTML | Funciones `Validators.*` |
| Testing | Difícil (depende del DOM) | Fácil (lógica en la clase) |
| Casos de uso | Forms simples | Forms complejos, dinámicos |

---

**¿Cómo implementarías un validador personalizado en Reactive Forms?**

```typescript
function emailEmpresarial(control: AbstractControl): ValidationErrors | null {
  const permitidos = ['@miempresa.com', '@partner.com'];
  const valido = permitidos.some(dominio => control.value?.endsWith(dominio));
  return valido ? null : { emailEmpresarial: true };
}
```

---

## NgRx y Estado

**¿Cuándo justificarías usar NgRx en lugar de Signals?**

NgRx se justifica cuando:
- El estado es compartido entre muchos componentes no relacionados
- Se necesita trazabilidad de cambios (DevTools, time-travel debugging)
- Los efectos secundarios (HTTP, localStorage) son complejos
- El equipo ya usa el patrón Redux

Para estado local o compartido entre pocos componentes, Signals o un servicio con Signals es suficiente.

---

**¿Qué son los Effects en NgRx?**

Los Effects son el lugar donde vive la lógica de efectos secundarios (llamadas HTTP, localStorage, analytics). Escuchan el stream de Actions, realizan la operación asíncrona y despachan nuevas Actions con el resultado (éxito o error). Mantienen el Reducer puro.

---

## Seguridad

**¿Cómo protege Angular contra XSS?**

Angular sanitiza automáticamente todos los valores interpolados en templates (`{{ valor }}`). Para HTML dinámico con `[innerHTML]`, Angular sanitiza el HTML eliminando scripts y atributos peligrosos. `DomSanitizer.bypassSecurityTrustHtml()` debe usarse con extrema precaución y solo con contenido de confianza.

---

**¿Qué es CSRF y cómo lo maneja Angular?**

CSRF (Cross-Site Request Forgery) es un ataque donde un sitio malicioso envía requests en nombre del usuario. `HttpClient` de Angular lee automáticamente la cookie `XSRF-TOKEN` y la incluye como header `X-XSRF-TOKEN` en cada request POST/PUT/DELETE. El backend (Laravel, etc.) valida ese token.

---

## Testing

**¿Qué es TestBed y para qué se usa?**

`TestBed` es el módulo de pruebas de Angular. Crea un entorno Angular ligero para las pruebas, permitiendo instanciar componentes, inyectar servicios reales o mocks, y probar el template. Es la base de las pruebas de integración de componentes en Angular.

---

**¿Cómo mockeas un servicio en una prueba de componente?**

```typescript
const mockServicio = jasmine.createSpyObj('MiServicio', ['obtenerDatos']);
mockServicio.obtenerDatos.and.returnValue(of([...]));

TestBed.configureTestingModule({
  providers: [{ provide: MiServicio, useValue: mockServicio }]
});
```

---

## Preguntas de Arquitectura (Senior)

**¿Cómo estructurarías una aplicación Angular de gran escala?**

Enfoque común (Feature-based / Domain-driven):
- `core/` — servicios singleton, interceptors, guards globales
- `shared/` — componentes, pipes y directivas reutilizables
- `features/` — módulos de dominio lazy-loaded (auth, dashboard, products...)
- Cada feature tiene sus propios componentes, servicios y store (si usa NgRx)

---

**¿Qué es una Micro Frontend architecture y cómo se aplica en Angular?**

Cada equipo/feature tiene su propia app Angular que se integra en un shell principal. Implementaciones comunes:
- **Module Federation** (Webpack) — comparte dependencias entre apps
- **Angular Elements** — exportar componentes Angular como Web Components

---

**¿Cómo optimizarías el bundle de producción de una app Angular?**

1. Lazy loading de rutas
2. `@defer` para contenido no crítico
3. `ChangeDetectionStrategy.OnPush` en todos los componentes
4. Importaciones tree-shakeable (evitar CommonModule completo)
5. Análisis con `webpack-bundle-analyzer` o `source-map-explorer`
6. SSR con `@angular/ssr` para mejor FCP/LCP

---

**¿Qué es Hydration en Angular SSR?**

Hydration es el proceso donde el HTML renderizado en el servidor se "activa" en el cliente sin re-renderizarlo. Angular 16+ incluye `provideClientHydration()` que preserva el DOM del servidor y solo añade los event listeners, mejorando dramáticamente el performance percibido.
