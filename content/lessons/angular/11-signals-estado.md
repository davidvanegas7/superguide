# Signals — Gestión de Estado Reactivo

**Signals** es el nuevo sistema de reactividad de Angular (introducido en v16, estable en v17). Proporciona una forma más simple, predecible y eficiente de gestionar el estado comparado con ZoneJS y RxJS para casos de uso locales.

---

## ¿Qué es un Signal?

Un Signal es un **contenedor de valor reactivo**. Cuando su valor cambia, Angular sabe exactamente qué partes del template necesitan actualizarse, sin necesidad de verificar todo el árbol de componentes.

```
Signal: [valor actual] ← puede leerse ← y modificarse
                ↓
           Notifica automáticamente a quien dependa de él
```

---

## Signals básicos

```typescript
import { Component, signal, computed, effect } from '@angular/core';

@Component({
  selector: 'app-carrito',
  standalone: true,
  template: `
    <h2>Carrito de compras</h2>
    <p>Productos: {{ totalProductos() }}</p>
    <p>Total: {{ precioTotal() | currency }}</p>
    <p>IVA (19%): {{ iva() | currency }}</p>
    <p>Total con IVA: {{ totalConIva() | currency }}</p>
    <button (click)="agregarProducto()">+ Agregar producto</button>
    <button (click)="limpiar()">🗑️ Limpiar carrito</button>
  `
})
export class CarritoComponent {
  // Signal mutable — valor inicial
  private items = signal<{ nombre: string; precio: number }[]>([]);

  // Signals computed — derivados de otros signals
  totalProductos = computed(() => this.items().length);
  precioTotal = computed(() => this.items().reduce((sum, item) => sum + item.precio, 0));
  iva = computed(() => this.precioTotal() * 0.19);
  totalConIva = computed(() => this.precioTotal() + this.iva());

  constructor() {
    // Effect — se ejecuta cuando cambia alguna dependencia
    effect(() => {
      const total = this.totalConIva();
      if (total > 0) {
        localStorage.setItem('carrito-total', String(total));
      }
    });
  }

  agregarProducto(): void {
    // update — modifica basado en el valor anterior
    this.items.update(items => [
      ...items,
      { nombre: `Producto ${items.length + 1}`, precio: Math.random() * 100 }
    ]);
  }

  limpiar(): void {
    // set — establece un nuevo valor directamente
    this.items.set([]);
  }
}
```

---

## Métodos de un Signal

```typescript
import { signal } from '@angular/core';

const contador = signal(0);

// Leer el valor (siempre con paréntesis)
console.log(contador());   // 0

// set — establecer un nuevo valor
contador.set(10);

// update — calcular nuevo valor basado en el anterior
contador.update(n => n + 1);  // 11

// Para objetos: mutate está disponible en signals de objeto
const usuario = signal({ nombre: 'Ana', edad: 28 });

// Actualizar un campo del objeto
usuario.update(u => ({ ...u, edad: 29 }));

// Señales de solo lectura (para exponer desde servicios)
const contadorPublico = contador.asReadonly();
// contadorPublico.set(5);  // ❌ Error: es de solo lectura
// contadorPublico();       // ✅ Solo se puede leer
```

---

## Computed Signals

Los Computed Signals son **derivados** de otros signals. Se recalculan automáticamente cuando sus dependencias cambian:

```typescript
import { signal, computed } from '@angular/core';

const productos = signal([
  { id: 1, nombre: 'Laptop', precio: 999, cantidad: 2 },
  { id: 2, nombre: 'Mouse', precio: 25, cantidad: 1 }
]);
const descuento = signal(0.10);  // 10%

// Se recalcula cuando cambie 'productos' o 'descuento'
const subtotal = computed(() =>
  productos().reduce((sum, p) => sum + p.precio * p.cantidad, 0)
);

const totalConDescuento = computed(() =>
  subtotal() * (1 - descuento())
);

// Los computed son de solo lectura
// totalConDescuento.set(100);  // ❌ Error
console.log(totalConDescuento());  // 2.002 * 0.9 = 1.801.80
```

---

## Effects

Los effects ejecutan código como **efecto secundario** cuando cambian las señales que leen:

```typescript
import { Component, signal, effect, EffectCleanupRegisterFn } from '@angular/core';

@Component({ selector: 'app-tema', standalone: true, template: '' })
export class TemaComponent {
  tema = signal<'claro' | 'oscuro'>('claro');

  constructor() {
    // Se ejecuta inmediatamente y cada vez que 'tema' cambia
    effect(() => {
      const temaActual = this.tema();
      document.body.classList.toggle('tema-oscuro', temaActual === 'oscuro');
      localStorage.setItem('tema', temaActual);
    });

    // Effect con cleanup
    effect((onCleanup: EffectCleanupRegisterFn) => {
      const intervalo = setInterval(() => {
        console.log('Tick:', this.tema());
      }, 1000);

      // Se ejecuta antes de la siguiente ejecución del effect
      onCleanup(() => clearInterval(intervalo));
    });
  }

  toggleTema(): void {
    this.tema.update(t => t === 'claro' ? 'oscuro' : 'claro');
  }
}
```

> **Importante:** No modifiques signals dentro de un effect. Puede causar ciclos infinitos. Los effects son para **sincronizar con el mundo externo** (DOM, localStorage, logging, etc.).

---

## Signals en Servicios — Gestión de Estado Global

```typescript
// estado/auth.store.ts
import { Injectable, signal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { inject } from '@angular/core';

export interface Usuario {
  id: number;
  nombre: string;
  email: string;
  rol: 'admin' | 'editor' | 'viewer';
}

@Injectable({ providedIn: 'root' })
export class AuthStore {
  private http = inject(HttpClient);

  // Estado privado
  private _usuario = signal<Usuario | null>(null);
  private _cargando = signal(false);
  private _error = signal<string | null>(null);

  // Estado público (solo lectura)
  readonly usuario = this._usuario.asReadonly();
  readonly cargando = this._cargando.asReadonly();
  readonly error = this._error.asReadonly();

  // Derivados
  readonly estaLogueado = computed(() => this._usuario() !== null);
  readonly esAdmin = computed(() => this._usuario()?.rol === 'admin');
  readonly nombreUsuario = computed(() => this._usuario()?.nombre ?? 'Invitado');

  // Acciones
  async login(email: string, password: string): Promise<void> {
    this._cargando.set(true);
    this._error.set(null);

    try {
      const usuario = await this.http
        .post<Usuario>('/api/auth/login', { email, password })
        .toPromise();

      this._usuario.set(usuario ?? null);
    } catch (err: any) {
      this._error.set(err.message ?? 'Error al iniciar sesión');
    } finally {
      this._cargando.set(false);
    }
  }

  logout(): void {
    this._usuario.set(null);
    this._error.set(null);
  }
}
```

```typescript
// Uso en cualquier componente
@Component({
  selector: 'app-navbar',
  standalone: true,
  template: `
    @if (auth.estaLogueado()) {
      <span>Hola, {{ auth.nombreUsuario() }}</span>
      <button (click)="auth.logout()">Cerrar sesión</button>
      @if (auth.esAdmin()) {
        <a routerLink="/admin">Admin</a>
      }
    } @else {
      <a routerLink="/login">Iniciar sesión</a>
    }
  `
})
export class NavbarComponent {
  auth = inject(AuthStore);  // El estado es compartido — singleton
}
```

---

## Store de productos con Signals

```typescript
// estado/productos.store.ts
import { Injectable, signal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { inject } from '@angular/core';
import { tap } from 'rxjs/operators';

export interface Producto {
  id: number;
  nombre: string;
  precio: number;
  categoria: string;
  stock: number;
}

@Injectable({ providedIn: 'root' })
export class ProductosStore {
  private http = inject(HttpClient);

  // Estado
  private _productos = signal<Producto[]>([]);
  private _filtro = signal('');
  private _categoriaSeleccionada = signal('todas');
  private _cargando = signal(false);

  // Público solo lectura
  readonly cargando = this._cargando.asReadonly();

  // Derivados con múltiples filtros
  readonly productosFiltrados = computed(() => {
    const termino = this._filtro().toLowerCase();
    const categoria = this._categoriaSeleccionada();

    return this._productos().filter(p => {
      const coincideNombre = p.nombre.toLowerCase().includes(termino);
      const coincideCategoria = categoria === 'todas' || p.categoria === categoria;
      return coincideNombre && coincideCategoria;
    });
  });

  readonly categorias = computed(() => {
    const cats = new Set(this._productos().map(p => p.categoria));
    return ['todas', ...cats];
  });

  readonly totalProductos = computed(() => this.productosFiltrados().length);

  readonly hayResultados = computed(() => this.totalProductos() > 0);

  // Acciones
  cargarProductos(): void {
    this._cargando.set(true);

    this.http.get<Producto[]>('/api/productos').pipe(
      tap({
        next: (productos) => {
          this._productos.set(productos);
          this._cargando.set(false);
        },
        error: () => this._cargando.set(false)
      })
    ).subscribe();
  }

  filtrar(termino: string): void {
    this._filtro.set(termino);
  }

  seleccionarCategoria(categoria: string): void {
    this._categoriaSeleccionada.set(categoria);
  }

  actualizarStock(id: number, cantidad: number): void {
    this._productos.update(productos =>
      productos.map(p => p.id === id ? { ...p, stock: p.stock + cantidad } : p)
    );
  }

  eliminar(id: number): void {
    this._productos.update(productos => productos.filter(p => p.id !== id));
  }
}
```

---

## `input()` y `output()` — El nuevo API de componentes

Angular 17.1+ introduce `input()` y `output()` como alternativa a `@Input()` y `@Output()`:

```typescript
import { Component, input, output, model } from '@angular/core';

@Component({
  selector: 'app-calificacion',
  standalone: true,
  template: `
    @for (estrella of estrellas; track estrella) {
      <span
        (click)="seleccionar(estrella)"
        [class.activa]="estrella <= valorActual()">
        ⭐
      </span>
    }
    <p>Calificación: {{ valorActual() }}/{{ max() }}</p>
  `
})
export class CalificacionComponent {
  // input() — equivale a @Input()
  max = input(5);                    // Opcional con valor por defecto
  etiqueta = input.required<string>(); // Requerido

  // model() — equivale a @Input() + @Output() (two-way binding)
  valorActual = model(0);            // [(valorActual)]="miValor" desde el padre

  // output() — equivale a @Output() EventEmitter
  calificacionCambiada = output<number>();

  estrellas = Array.from({ length: this.max() }, (_, i) => i + 1);

  seleccionar(estrella: number): void {
    this.valorActual.set(estrella);           // model se puede setear
    this.calificacionCambiada.emit(estrella); // output emite
  }
}
```

```html
<!-- Uso desde el padre -->
<app-calificacion
  [max]="10"
  etiqueta="Califica este curso"
  [(valorActual)]="miCalificacion"
  (calificacionCambiada)="onCalificacion($event)">
</app-calificacion>
```

---

## Resumen: Cuándo usar Signals vs RxJS

| Caso de uso | Recomendación |
|---|---|
| Estado local del componente | ✅ Signals |
| Estado global (auth, carrito) | ✅ Signals en servicios |
| Llamadas HTTP | ✅ RxJS + AsyncPipe |
| Búsqueda en tiempo real | ✅ RxJS (debounce, switchMap) |
| Múltiples fuentes de datos combinadas | ✅ RxJS (combineLatest, forkJoin) |
| Eventos del DOM complejos | ✅ RxJS (fromEvent) |
| Interoperabilidad | ✅ toSignal() / toObservable() |

En proyectos modernos se usan **ambos**: Signals para el estado y RxJS para flujos de datos asíncronos complejos.
