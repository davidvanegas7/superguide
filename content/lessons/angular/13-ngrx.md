# NgRx — Gestión de Estado Global

**NgRx** es la librería de gestión de estado más usada en aplicaciones Angular empresariales. Implementa el patrón **Redux** con RxJS: un flujo de datos unidireccional predecible que facilita el debugging y el testing en apps grandes.

---

## El patrón Redux en Angular

```
Componente → dispatch(Action) → Reducer → Store (estado)
                                              ↓
                                    Selector ← Componente (Observable/Signal)
                                              ↓
                                          Effect → API/Servicios → dispatch(Action)
```

Los 5 bloques de NgRx:

| Bloque | Rol |
|---|---|
| **Store** | Fuente única de verdad (estado global) |
| **Action** | Evento que describe qué pasó |
| **Reducer** | Función pura que calcula el nuevo estado |
| **Selector** | Lee y deriva datos del estado |
| **Effect** | Maneja efectos secundarios (HTTP, localStorage) |

---

## Instalación

```bash
ng add @ngrx/store
ng add @ngrx/effects
ng add @ngrx/store-devtools   # DevTools para debugging en el browser
```

---

## Ejemplo completo: Gestión de productos

### 1. Definir el estado

```typescript
// store/productos/productos.state.ts
import { Producto } from '../../models/producto.model';

export interface ProductosState {
  items: Producto[];
  seleccionado: Producto | null;
  cargando: boolean;
  error: string | null;
}

export const productosEstadoInicial: ProductosState = {
  items: [],
  seleccionado: null,
  cargando: false,
  error: null
};

// Estado raíz de la app
export interface AppState {
  productos: ProductosState;
  auth: AuthState;
  // ...otros slices
}
```

### 2. Definir las Actions

```typescript
// store/productos/productos.actions.ts
import { createAction, props } from '@ngrx/store';
import { Producto } from '../../models/producto.model';

// ─── Cargar productos ───
export const cargarProductos = createAction('[Productos] Cargar Productos');

export const cargarProductosExito = createAction(
  '[Productos API] Cargar Productos Éxito',
  props<{ productos: Producto[] }>()
);

export const cargarProductosError = createAction(
  '[Productos API] Cargar Productos Error',
  props<{ error: string }>()
);

// ─── Agregar producto ───
export const agregarProducto = createAction(
  '[Productos] Agregar Producto',
  props<{ producto: Omit<Producto, 'id'> }>()
);

export const agregarProductoExito = createAction(
  '[Productos API] Agregar Producto Éxito',
  props<{ producto: Producto }>()
);

// ─── Actualizar producto ───
export const actualizarProducto = createAction(
  '[Productos] Actualizar Producto',
  props<{ id: number; cambios: Partial<Producto> }>()
);

export const actualizarProductoExito = createAction(
  '[Productos API] Actualizar Producto Éxito',
  props<{ producto: Producto }>()
);

// ─── Eliminar producto ───
export const eliminarProducto = createAction(
  '[Productos] Eliminar Producto',
  props<{ id: number }>()
);

export const eliminarProductoExito = createAction(
  '[Productos API] Eliminar Producto Éxito',
  props<{ id: number }>()
);

// ─── Selección ───
export const seleccionarProducto = createAction(
  '[Productos] Seleccionar Producto',
  props<{ producto: Producto | null }>()
);
```

### 3. Crear el Reducer

```typescript
// store/productos/productos.reducer.ts
import { createReducer, on } from '@ngrx/store';
import { productosEstadoInicial } from './productos.state';
import * as ProductosActions from './productos.actions';

export const productosReducer = createReducer(
  productosEstadoInicial,

  // ─── Cargar ───
  on(ProductosActions.cargarProductos, state => ({
    ...state,
    cargando: true,
    error: null
  })),

  on(ProductosActions.cargarProductosExito, (state, { productos }) => ({
    ...state,
    items: productos,
    cargando: false,
    error: null
  })),

  on(ProductosActions.cargarProductosError, (state, { error }) => ({
    ...state,
    cargando: false,
    error
  })),

  // ─── Agregar ───
  on(ProductosActions.agregarProductoExito, (state, { producto }) => ({
    ...state,
    items: [...state.items, producto]
  })),

  // ─── Actualizar ───
  on(ProductosActions.actualizarProductoExito, (state, { producto }) => ({
    ...state,
    items: state.items.map(p => p.id === producto.id ? producto : p)
  })),

  // ─── Eliminar ───
  on(ProductosActions.eliminarProductoExito, (state, { id }) => ({
    ...state,
    items: state.items.filter(p => p.id !== id)
  })),

  // ─── Selección ───
  on(ProductosActions.seleccionarProducto, (state, { producto }) => ({
    ...state,
    seleccionado: producto
  }))
);
```

### 4. Crear los Selectors

```typescript
// store/productos/productos.selectors.ts
import { createFeatureSelector, createSelector } from '@ngrx/store';
import { ProductosState } from './productos.state';

// Selector base — apunta al slice 'productos' del estado
const selectProductosState = createFeatureSelector<ProductosState>('productos');

// Selectors derivados
export const selectTodosProductos = createSelector(
  selectProductosState,
  state => state.items
);

export const selectProductosCargando = createSelector(
  selectProductosState,
  state => state.cargando
);

export const selectProductosError = createSelector(
  selectProductosState,
  state => state.error
);

export const selectProductoSeleccionado = createSelector(
  selectProductosState,
  state => state.seleccionado
);

// Selector con parámetro (memoizado)
export const selectProductoPorId = (id: number) => createSelector(
  selectTodosProductos,
  productos => productos.find(p => p.id === id) ?? null
);

// Selector compuesto — derivado de múltiples selectores
export const selectResumenProductos = createSelector(
  selectTodosProductos,
  productos => ({
    total: productos.length,
    disponibles: productos.filter(p => p.stock > 0).length,
    agotados: productos.filter(p => p.stock === 0).length,
    valorTotal: productos.reduce((sum, p) => sum + p.precio * p.stock, 0)
  })
);
```

### 5. Crear los Effects

```typescript
// store/productos/productos.effects.ts
import { Injectable, inject } from '@angular/core';
import { Actions, createEffect, ofType } from '@ngrx/effects';
import { ProductosService } from '../../services/productos.service';
import * as ProductosActions from './productos.actions';
import { switchMap, map, catchError, of } from 'rxjs';

@Injectable()
export class ProductosEffects {
  private actions$ = inject(Actions);
  private productosService = inject(ProductosService);

  // Effect: cargar productos desde la API
  cargarProductos$ = createEffect(() =>
    this.actions$.pipe(
      ofType(ProductosActions.cargarProductos),
      switchMap(() =>
        this.productosService.obtenerTodos().pipe(
          map(productos => ProductosActions.cargarProductosExito({ productos })),
          catchError(error => of(ProductosActions.cargarProductosError({
            error: error.message ?? 'Error al cargar productos'
          })))
        )
      )
    )
  );

  // Effect: agregar producto
  agregarProducto$ = createEffect(() =>
    this.actions$.pipe(
      ofType(ProductosActions.agregarProducto),
      switchMap(({ producto }) =>
        this.productosService.crear(producto).pipe(
          map(productoCreado => ProductosActions.agregarProductoExito({ producto: productoCreado })),
          catchError(error => of(ProductosActions.cargarProductosError({ error: error.message })))
        )
      )
    )
  );

  // Effect: eliminar producto
  eliminarProducto$ = createEffect(() =>
    this.actions$.pipe(
      ofType(ProductosActions.eliminarProducto),
      switchMap(({ id }) =>
        this.productosService.eliminar(id).pipe(
          map(() => ProductosActions.eliminarProductoExito({ id })),
          catchError(error => of(ProductosActions.cargarProductosError({ error: error.message })))
        )
      )
    )
  );

  // Effect sin acción de retorno (solo efectos secundarios)
  guardarEnStorage$ = createEffect(() =>
    this.actions$.pipe(
      ofType(ProductosActions.cargarProductosExito),
      map(({ productos }) => {
        localStorage.setItem('productos-cache', JSON.stringify(productos));
      })
    ),
    { dispatch: false }  // ← No despacha ninguna acción
  );
}
```

### 6. Registrar NgRx en la app

```typescript
// app.config.ts
import { ApplicationConfig } from '@angular/core';
import { provideStore } from '@ngrx/store';
import { provideEffects } from '@ngrx/effects';
import { provideStoreDevtools } from '@ngrx/store-devtools';
import { productosReducer } from './store/productos/productos.reducer';
import { ProductosEffects } from './store/productos/productos.effects';

export const appConfig: ApplicationConfig = {
  providers: [
    provideStore({
      productos: productosReducer
      // auth: authReducer,
    }),
    provideEffects(ProductosEffects),
    provideStoreDevtools({
      maxAge: 25,               // Guarda los últimos 25 estados
      logOnly: false,           // false en desarrollo para usar DevTools completo
      autoPause: true
    })
  ]
};
```

---

## Usar NgRx en los componentes

```typescript
// pages/productos/productos.component.ts
import { Component, OnInit, inject } from '@angular/core';
import { Store } from '@ngrx/store';
import { AsyncPipe } from '@angular/common';
import { AppState } from '../../store/app.state';
import {
  selectTodosProductos,
  selectProductosCargando,
  selectResumenProductos
} from '../../store/productos/productos.selectors';
import * as ProductosActions from '../../store/productos/productos.actions';

@Component({
  selector: 'app-productos',
  standalone: true,
  imports: [AsyncPipe],
  template: `
    @if (cargando$ | async) {
      <p>Cargando...</p>
    }

    @if (resumen$ | async; as resumen) {
      <div class="resumen">
        <span>Total: {{ resumen.total }}</span>
        <span>Disponibles: {{ resumen.disponibles }}</span>
        <span>Agotados: {{ resumen.agotados }}</span>
      </div>
    }

    @if (productos$ | async; as productos) {
      @for (p of productos; track p.id) {
        <div class="producto-card">
          <h3>{{ p.nombre }}</h3>
          <p>{{ p.precio | currency }}</p>
          <button (click)="seleccionar(p)">Ver detalle</button>
          <button (click)="eliminar(p.id)">Eliminar</button>
        </div>
      }
    }
  `
})
export class ProductosComponent implements OnInit {
  private store = inject(Store<AppState>);

  // Selectors retornan Observables
  productos$ = this.store.select(selectTodosProductos);
  cargando$ = this.store.select(selectProductosCargando);
  resumen$ = this.store.select(selectResumenProductos);

  ngOnInit(): void {
    // Despachar acción para cargar datos
    this.store.dispatch(ProductosActions.cargarProductos());
  }

  seleccionar(producto: any): void {
    this.store.dispatch(ProductosActions.seleccionarProducto({ producto }));
  }

  eliminar(id: number): void {
    this.store.dispatch(ProductosActions.eliminarProducto({ id }));
  }
}
```

### Usando signals en lugar de AsyncPipe (NgRx 17+)

```typescript
import { toSignal } from '@angular/core/rxjs-interop';

@Component({ ... })
export class ProductosComponent {
  private store = inject(Store);

  // Convierte selectores en signals — no necesitas async pipe
  productos = toSignal(this.store.select(selectTodosProductos), { initialValue: [] });
  cargando = toSignal(this.store.select(selectProductosCargando), { initialValue: false });
}
```

```html
<!-- Template más limpio sin async pipe -->
@if (cargando()) {
  <p>Cargando...</p>
}
@for (p of productos(); track p.id) {
  <p>{{ p.nombre }}</p>
}
```

---

## NgRx ComponentStore — Estado local de componentes

Para estado local complejo sin necesidad del store global:

```typescript
import { Injectable } from '@angular/core';
import { ComponentStore } from '@ngrx/component-store';
import { switchMap, tap } from 'rxjs/operators';
import { ProductosService } from '../../services/productos.service';

interface BuscadorState {
  termino: string;
  resultados: Producto[];
  cargando: boolean;
}

@Injectable()
export class BuscadorStore extends ComponentStore<BuscadorState> {
  private productosService = inject(ProductosService);

  constructor() {
    super({ termino: '', resultados: [], cargando: false });  // Estado inicial
  }

  // Selectors locales
  readonly termino$ = this.select(s => s.termino);
  readonly resultados$ = this.select(s => s.resultados);
  readonly cargando$ = this.select(s => s.cargando);

  // Updaters — modifican el estado
  readonly setTermino = this.updater((state, termino: string) => ({
    ...state, termino
  }));

  // Effects locales
  readonly buscar = this.effect((termino$: Observable<string>) =>
    termino$.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      tap(() => this.patchState({ cargando: true })),
      switchMap(termino =>
        this.productosService.buscar(termino).pipe(
          tap(resultados => this.patchState({ resultados, cargando: false })),
          catchError(() => {
            this.patchState({ cargando: false });
            return EMPTY;
          })
        )
      )
    )
  );
}
```

---

## DevTools — Debugging del estado

Con `@ngrx/store-devtools` instalado, en Chrome con la extensión [Redux DevTools](https://chrome.google.com/webstore/detail/redux-devtools/lmhkpmbekcpmknklioeibfkpmmfibljd):

- **Time Travel Debugging**: retrocede y avanza en el historial de acciones
- **Inspección del estado**: ve el estado completo en cada momento
- **Action log**: historial de todas las acciones despachadas
- **Import/Export**: guarda y restaura sesiones de debugging

---

## Cuándo usar NgRx vs Signals

| Situación | Recomendación |
|---|---|
| App pequeña/mediana | Signals en servicios (más simple) |
| App grande con múltiples equipos | NgRx (convenciones claras) |
| Estado que muchos componentes leen | NgRx Store |
| Estado local de un componente | ComponentStore o Signal local |
| Efectos HTTP complejos | NgRx Effects |
| Necesitas time-travel debugging | NgRx con DevTools |
