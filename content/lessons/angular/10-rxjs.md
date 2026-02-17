# RxJS y Programación Reactiva

**RxJS** (Reactive Extensions for JavaScript) es la librería de programación reactiva que Angular usa internamente en todas partes: HttpClient, Router, Formularios Reactivos, y más. Entender RxJS es esencial para ser un desarrollador Angular eficiente.

---

## Conceptos fundamentales

### Observable

Un **Observable** es como un flujo de datos en el tiempo. Puede emitir:
- **Cero o más valores** en cualquier momento
- Un **error** (y se termina)
- Una **señal de completado**

```
Timeline: ----1----2----3----4---|  (completado)
Timeline: ----a----b----X       (error en X)
```

### Observer

Un **Observer** es el que consume los valores del Observable:

```typescript
import { Observable, Observer } from 'rxjs';

const observable$ = new Observable<number>((subscriber) => {
  subscriber.next(1);         // Emite valor
  subscriber.next(2);
  subscriber.next(3);
  subscriber.complete();      // Señal de completado
  // subscriber.error(new Error('algo falló'));  // O un error
});

// Observar con un observer completo
const observer: Observer<number> = {
  next: (valor) => console.log('Valor:', valor),
  error: (err) => console.error('Error:', err),
  complete: () => console.log('¡Completado!')
};

const suscripcion = observable$.subscribe(observer);

// Cancelar la suscripción (importante para evitar memory leaks)
suscripcion.unsubscribe();
```

### Subscription

```typescript
import { interval } from 'rxjs';

// Emite 0, 1, 2, 3... cada segundo
const contador$ = interval(1000);

const sub = contador$.subscribe(n => console.log(n));

// Cancelar después de 5 segundos
setTimeout(() => {
  sub.unsubscribe();
  console.log('Cancelado');
}, 5000);
```

---

## Creación de Observables

```typescript
import {
  of, from, interval, timer, fromEvent,
  Subject, BehaviorSubject, ReplaySubject, EMPTY, NEVER
} from 'rxjs';

// of — Valores estáticos
const numeros$ = of(1, 2, 3);          // Emite 1, 2, 3 y completa

// from — Promesas, arrays, iterables
const array$ = from([10, 20, 30]);
const promesa$ = from(fetch('/api/datos').then(r => r.json()));

// interval — Temporizador periódico
const cada2segundos$ = interval(2000);  // 0, 1, 2... cada 2s

// timer — Retardo inicial + intervalo
const timer$ = timer(1000, 500);        // Empieza en 1s, luego cada 0.5s
const retardo$ = timer(3000);           // Emite 0 una vez después de 3s

// fromEvent — Eventos del DOM
const clicks$ = fromEvent(document, 'click');
const teclado$ = fromEvent<KeyboardEvent>(document, 'keydown');

// EMPTY — Completa inmediatamente sin emitir
EMPTY.subscribe({ complete: () => console.log('Vacío, completado') });

// NEVER — Nunca emite ni completa
NEVER.subscribe();  // infinito sin hacer nada
```

---

## Subjects — Observables multidifusión

Los **Subjects** son Observables que también pueden emitir valores manualmente:

```typescript
import { Subject, BehaviorSubject, ReplaySubject } from 'rxjs';

// Subject básico — solo reciben valores futuros
const eventos$ = new Subject<string>();

eventos$.subscribe(e => console.log('Suscriptor 1:', e));
eventos$.next('primer evento');    // Suscriptor 1: primer evento

eventos$.subscribe(e => console.log('Suscriptor 2:', e));
eventos$.next('segundo evento');   // Suscriptor 1 Y 2 reciben este
eventos$.complete();

// BehaviorSubject — Guarda el último valor (ideal para estado)
const usuario$ = new BehaviorSubject<string | null>(null);

// Un nuevo suscriptor recibe inmediatamente el valor actual
usuario$.subscribe(u => console.log('Usuario:', u));  // Usuario: null

usuario$.next('Ana');  // Usuario: Ana

// El valor actual siempre está disponible de forma sincrónica
console.log(usuario$.getValue());  // 'Ana'

// ReplaySubject — Guarda los N últimos valores
const historial$ = new ReplaySubject<string>(3);  // Guarda últimos 3

historial$.next('primero');
historial$.next('segundo');
historial$.next('tercero');
historial$.next('cuarto');

// Un nuevo suscriptor recibe los 3 últimos: 'segundo', 'tercero', 'cuarto'
historial$.subscribe(v => console.log(v));
```

---

## Operadores pipe

Los operadores transforman, filtran y combinan Observables:

### Transformación

```typescript
import { of, from } from 'rxjs';
import { map, flatMap, switchMap, concatMap, mergeMap, scan, reduce } from 'rxjs/operators';

// map — Transforma cada valor
of(1, 2, 3).pipe(
  map(n => n * 2)
).subscribe(console.log);  // 2, 4, 6

// Transformar objetos
from(usuarios).pipe(
  map(u => ({ ...u, nombreCompleto: `${u.nombre} ${u.apellido}` }))
);

// switchMap — Cancela el Observable anterior (ideal para búsqueda en tiempo real)
busqueda$.pipe(
  debounceTime(300),
  distinctUntilChanged(),
  switchMap(termino => this.apiService.buscar(termino))
).subscribe(resultados => this.resultados = resultados);

// mergeMap — Ejecuta todos en paralelo
ids$.pipe(
  mergeMap(id => this.http.get(`/api/items/${id}`))
).subscribe(item => this.items.push(item));

// concatMap — Ejecuta en secuencia (espera a que termine el anterior)
solicitudes$.pipe(
  concatMap(solicitud => this.http.post('/api/procesar', solicitud))
).subscribe(console.log);

// exhaustMap — Ignora nuevas emisiones mientras el Observable interno está activo
// Ideal para prevenir doble envío de formularios o clicks repetidos
botonEnviar$.pipe(
  exhaustMap(() => this.http.post('/api/formulario', datos))
).subscribe(respuesta => console.log('Enviado:', respuesta));
// Si el usuario hace click varias veces mientras procesa, los clicks extra se IGNORAN

// Comparativa rápida:
// switchMap   → Cancela el anterior, emite el nuevo (búsqueda en tiempo real)
// mergeMap    → Paralelo, todos corren a la vez (cargar múltiples IDs)
// concatMap   → Secuencial, espera al anterior (procesar cola en orden)
// exhaustMap  → Ignora nuevos mientras hay uno activo (submit de formulario)

// scan — Acumulador (como reduce pero emite en cada paso)
of(1, 2, 3, 4, 5).pipe(
  scan((acum, valor) => acum + valor, 0)
).subscribe(console.log);  // 1, 3, 6, 10, 15
```

### Filtrado

```typescript
import { debounceTime, distinctUntilChanged, filter, take, takeUntil, skip, first } from 'rxjs/operators';

// filter — Solo pasa los valores que cumplen la condición
of(1, 2, 3, 4, 5, 6).pipe(
  filter(n => n % 2 === 0)
).subscribe(console.log);  // 2, 4, 6

// debounceTime — Espera X ms sin nuevos valores antes de emitir
input$.pipe(debounceTime(400));  // Espera 400ms después de la última tecla

// distinctUntilChanged — Solo emite si el valor cambió
of('a', 'a', 'b', 'b', 'c').pipe(
  distinctUntilChanged()
).subscribe(console.log);  // a, b, c

// take — Solo toma N valores y completa
interval(1000).pipe(take(5)).subscribe(console.log);  // 0, 1, 2, 3, 4

// takeUntil — Toma valores hasta que otro Observable emite
const parar$ = new Subject<void>();
interval(500).pipe(
  takeUntil(parar$)
).subscribe(console.log);

setTimeout(() => parar$.next(), 2000);  // Para a los 2 segundos

// first — Solo el primer valor (o el primero que cumple condición)
clicks$.pipe(first()).subscribe(console.log);  // Solo el primer click
clicks$.pipe(first(e => e.button === 2)).subscribe(console.log);  // Primer click derecho
```

### Combinación

```typescript
import { combineLatest, forkJoin, merge, zip, withLatestFrom } from 'rxjs';

// forkJoin — Como Promise.all: espera a que todos completen
forkJoin([
  this.http.get('/api/usuarios'),
  this.http.get('/api/productos'),
  this.http.get('/api/categorias')
]).subscribe(([usuarios, productos, categorias]) => {
  // Los tres han cargado
  this.inicializar(usuarios, productos, categorias);
});

// combineLatest — Emite cuando cualquiera emite (con el último valor de todos)
combineLatest([precio$, cantidad$]).pipe(
  map(([precio, cantidad]) => precio * cantidad)
).subscribe(total => this.total = total);

// merge — Une varios Observables, emite cuando cualquiera emite
merge(click$, teclado$).subscribe(evento => manejar(evento));

// zip — Emite cuando todos han emitido (pares 1-1)
zip(nombres$, edades$).pipe(
  map(([nombre, edad]) => ({ nombre, edad }))
);

// withLatestFrom — Combina con el último valor de otro Observable
boton$.pipe(
  withLatestFrom(formulario$)
).subscribe(([_click, datos]) => enviar(datos));
```

### Manejo de errores

```typescript
import { catchError, retry, retryWhen, throwError, EMPTY } from 'rxjs';
import { delay } from 'rxjs/operators';

// catchError — Captura el error y puede retornar un valor por defecto
this.http.get('/api/datos').pipe(
  catchError(error => {
    console.error('Error:', error);
    return of([]);  // Retorna array vacío en caso de error
    // return throwError(() => error);  // Re-lanza el error
    // return EMPTY;  // Completa sin emitir nada
  })
);

// retry — Reintenta N veces
this.http.get('/api/datos').pipe(
  retry(3)  // Reintenta hasta 3 veces antes de propagar el error
);

// retryWhen — Reintento con lógica personalizada
this.http.get('/api/datos').pipe(
  retryWhen(errors =>
    errors.pipe(
      delayWhen((_, intento) => timer(Math.pow(2, intento) * 1000))  // Backoff exponencial
    )
  )
);
```

---

## RxJS en componentes Angular

### Patrón: Gestionar suscripciones con `takeUntilDestroyed`

```typescript
import { Component, OnInit, inject } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';

@Component({ selector: 'app-demo', standalone: true, template: '' })
export class DemoComponent implements OnInit {
  private postsService = inject(PostsService);

  // takeUntilDestroyed cancela automáticamente cuando el componente se destruye
  ngOnInit(): void {
    this.postsService.obtenerTodos().pipe(
      takeUntilDestroyed()   // ← No necesitas ngOnDestroy
    ).subscribe(posts => this.posts = posts);
  }
}
```

### Patrón: Búsqueda en tiempo real

```typescript
import { Component, OnInit, inject } from '@angular/core';
import { FormControl, ReactiveFormsModule } from '@angular/forms';
import { debounceTime, distinctUntilChanged, switchMap, startWith } from 'rxjs/operators';
import { AsyncPipe } from '@angular/common';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-buscador',
  standalone: true,
  imports: [ReactiveFormsModule, AsyncPipe],
  template: `
    <input [formControl]="campoBusqueda" placeholder="Buscar...">

    @if (resultados$ | async; as resultados) {
      @for (r of resultados; track r.id) {
        <div>{{ r.titulo }}</div>
      }
    }
  `
})
export class BuscadorComponent implements OnInit {
  private apiService = inject(ApiService);
  campoBusqueda = new FormControl('');
  resultados$!: Observable<any[]>;

  ngOnInit(): void {
    this.resultados$ = this.campoBusqueda.valueChanges.pipe(
      startWith(''),                        // Emite al iniciar con ''
      debounceTime(350),                    // Espera 350ms sin teclear
      distinctUntilChanged(),               // Solo si el valor cambió
      switchMap(termino =>                  // Cancela búsquedas anteriores
        this.apiService.buscar(termino ?? '')
      )
    );
  }
}
```

---

## Signals vs RxJS

Angular 16+ introdujo **Signals** como alternativa más simple a RxJS para estado reactivo local:

```typescript
import { Component, signal, computed, effect } from '@angular/core';

@Component({
  selector: 'app-contador',
  standalone: true,
  template: `
    <p>Contador: {{ contador() }}</p>
    <p>Doble: {{ doble() }}</p>
    <button (click)="incrementar()">+1</button>
  `
})
export class ContadorComponent {
  // Signal — valor reactivo
  contador = signal(0);

  // Computed signal — se recalcula automáticamente
  doble = computed(() => this.contador() * 2);

  constructor() {
    // Effect — se ejecuta cuando cambia una dependencia
    effect(() => {
      console.log(`El contador cambió a: ${this.contador()}`);
    });
  }

  incrementar(): void {
    this.contador.update(n => n + 1);     // Basado en valor anterior
    // this.contador.set(10);              // Establece valor directamente
  }
}
```

### Interoperabilidad Signals ↔ RxJS

```typescript
import { toObservable, toSignal } from '@angular/core/rxjs-interop';

// Signal → Observable
const contador = signal(0);
const contador$ = toObservable(contador);

// Observable → Signal
const datos$ = this.http.get<Data[]>('/api/datos');
const datos = toSignal(datos$, { initialValue: [] });
// Ahora puedes usar datos() en el template en lugar de async pipe
```

---

## Resumen de operadores más usados

| Operador | Categoría | Descripción |
|---|---|---|
| `map` | Transformación | Transforma cada valor |
| `switchMap` | Transformación | Cancela el anterior, ideal para búsquedas |
| `mergeMap` | Transformación | Paralelo, todos a la vez |
| `concatMap` | Transformación | Secuencial, respeta el orden |
| `exhaustMap` | Transformación | Ignora nuevos mientras hay uno activo (submit) |
| `filter` | Filtrado | Solo pasa lo que cumple la condición |
| `debounceTime` | Filtrado | Espera silencio antes de emitir |
| `distinctUntilChanged` | Filtrado | Solo si cambia el valor |
| `take` / `takeUntil` | Filtrado | Limitar emisiones |
| `catchError` | Errores | Manejar errores |
| `retry` | Errores | Reintentar |
| `forkJoin` | Combinación | Esperar varios (Promise.all) |
| `combineLatest` | Combinación | Último de todos |
| `tap` | Utilidad | Efectos secundarios sin modificar |
| `startWith` | Utilidad | Emite un valor inicial |
| `shareReplay` | Multidifusión | Comparte y recuerda la última emisión |
