# Componentes en Angular

Los **componentes** son el bloque fundamental de Angular. Toda la interfaz de usuario de una aplicación Angular está construida con componentes. Un componente encapsula:

- **Template (Vista)** — El HTML que se muestra al usuario
- **Clase** — La lógica y los datos del componente (TypeScript)
- **Estilos** — CSS propio del componente (aislado)

---

## Anatomía de un componente

Un componente se define con el decorador `@Component`:

```typescript
import { Component } from '@angular/core';

@Component({
  selector: 'app-saludo',            // ← Nombre de la etiqueta HTML
  standalone: true,                   // ← Componente independiente (Angular 14+)
  template: `<h1>¡Hola, {{ nombre }}!</h1>`,  // ← Template inline
  styles: [`h1 { color: coral; }`]   // ← Estilos inline
})
export class SaludoComponent {
  nombre = 'Angular';   // ← Propiedad accesible desde el template
}
```

### Con archivos separados (lo más común)

```typescript
// saludo.component.ts
import { Component } from '@angular/core';

@Component({
  selector: 'app-saludo',
  standalone: true,
  templateUrl: './saludo.component.html',   // ← Archivo HTML
  styleUrl: './saludo.component.css'        // ← Archivo CSS
})
export class SaludoComponent {
  nombre = 'Angular';
  version = 17;
}
```

```html
<!-- saludo.component.html -->
<div class="saludo">
  <h1>¡Hola, {{ nombre }}!</h1>
  <p>Versión: {{ version }}</p>
</div>
```

---

## Generar componentes con la CLI

```bash
# Genera todos los archivos del componente
ng generate component components/saludo
ng g c components/saludo   # forma corta

# En la carpeta: src/app/components/saludo/
# ├── saludo.component.ts
# ├── saludo.component.html
# ├── saludo.component.css
# └── saludo.component.spec.ts
```

---

## Propiedades del decorador `@Component`

```typescript
@Component({
  // Selector CSS que identifica el componente en el HTML
  selector: 'app-tarjeta',

  // Puede ser 'component' o 'element' (custom element)
  // Por defecto es 'component'

  // Template: inline o en archivo separado (usa uno de los dos)
  template: `<p>Inline</p>`,
  templateUrl: './tarjeta.component.html',

  // Estilos: inline o en archivo (puedes tener varios)
  styles: [`p { margin: 0 }`],
  styleUrl: './tarjeta.component.css',
  styleUrls: ['./tarjeta.component.css', './tarjeta-extra.css'],

  // Encapsulación de estilos
  encapsulation: ViewEncapsulation.Emulated,   // Por defecto: estilos aislados
  // encapsulation: ViewEncapsulation.None,    // Sin aislamiento (estilos globales)
  // encapsulation: ViewEncapsulation.ShadowDom, // Web Components Shadow DOM

  // Estrategia de detección de cambios
  changeDetection: ChangeDetectionStrategy.OnPush,  // Más eficiente

  // Componentes, Directivas y Pipes que usa este componente
  imports: [CommonModule, RouterModule]
})
```

---

## Interpolación — Mostrando datos

La **interpolación** `{{ expresión }}` muestra valores en el template:

```typescript
@Component({
  selector: 'app-perfil',
  standalone: true,
  template: `
    <h2>{{ titulo }}</h2>
    <p>Nombre: {{ usuario.nombre }}</p>
    <p>Email: {{ usuario.email }}</p>
    <p>Edad: {{ usuario.edad }}</p>
    <p>Mayúsculas: {{ usuario.nombre.toUpperCase() }}</p>
    <p>Año actual: {{ obtenerAnio() }}</p>
    <p>Suma: {{ 2 + 3 }}</p>
    <img [src]="usuario.avatar" [alt]="usuario.nombre">
  `
})
export class PerfilComponent {
  titulo = 'Mi Perfil';

  usuario = {
    nombre: 'Ana García',
    email: 'ana@ejemplo.com',
    edad: 28,
    avatar: 'https://i.pravatar.cc/150?img=1'
  };

  obtenerAnio(): number {
    return new Date().getFullYear();
  }
}
```

> **Nota:** La interpolación permite expresiones simples pero no sentencias (no puedes usar `if`, `for`, `let`, `=`, etc. dentro de `{{ }}`).

---

## Property Binding — Enlazar propiedades

Los corchetes `[propiedad]` enlazan propiedades HTML/DOM con valores del componente:

```typescript
@Component({
  selector: 'app-demo-binding',
  standalone: true,
  template: `
    <!-- Propiedad del elemento HTML -->
    <img [src]="imagenUrl" [alt]="imagenAlt" [width]="200">

    <!-- Propiedad booleana -->
    <button [disabled]="estaDeshabilitado">Enviar</button>
    <input [readonly]="soloLectura" value="texto">

    <!-- Clase CSS dinámica -->
    <div [class]="claseActual">contenido</div>
    <p [class.texto-rojo]="esError">Mensaje</p>

    <!-- Estilo inline dinámico -->
    <div [style.color]="colorTexto">Hola</div>
    <div [style.font-size.px]="tamanoFuente">Texto</div>

    <!-- Propiedad de componente hijo -->
    <app-tarjeta [titulo]="'Mi título'" [activo]="true">
    </app-tarjeta>
  `
})
export class DemoBindingComponent {
  imagenUrl = 'https://angular.io/assets/images/logos/angular/angular.png';
  imagenAlt = 'Logo de Angular';
  estaDeshabilitado = false;
  soloLectura = true;
  claseActual = 'card destacada';
  esError = true;
  colorTexto = 'steelblue';
  tamanoFuente = 18;
}
```

---

## Event Binding — Escuchando eventos

Los paréntesis `(evento)` escuchan eventos del DOM:

```typescript
@Component({
  selector: 'app-demo-eventos',
  standalone: true,
  template: `
    <!-- Click básico -->
    <button (click)="incrementar()">+1</button>
    <span>{{ contador }}</span>
    <button (click)="decrementar()">-1</button>

    <!-- Acceder al evento con $event -->
    <input (input)="onInput($event)" placeholder="Escribe algo">
    <p>Escribiste: {{ texto }}</p>

    <!-- Eventos de teclado -->
    <input (keyup.enter)="buscar()" [(ngModel)]="busqueda">
    <button (click)="buscar()">Buscar</button>

    <!-- Evento con datos inline -->
    <button (click)="mostrarMensaje('¡Hola!')">Saludar</button>

    <!-- Eventos del ratón -->
    <div (mouseenter)="onHover(true)"
         (mouseleave)="onHover(false)"
         [class.resaltado]="estaHover">
      Pasa el ratón por aquí
    </div>
  `
})
export class DemoEventosComponent {
  contador = 0;
  texto = '';
  busqueda = '';
  estaHover = false;

  incrementar(): void {
    this.contador++;
  }

  decrementar(): void {
    if (this.contador > 0) this.contador--;
  }

  onInput(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.texto = input.value;
  }

  buscar(): void {
    console.log(`Buscando: ${this.busqueda}`);
    // aquí llamarías a un servicio
  }

  mostrarMensaje(msg: string): void {
    alert(msg);
  }

  onHover(estado: boolean): void {
    this.estaHover = estado;
  }
}
```

---

## Two-Way Binding — Doble enlace

`[(ngModel)]` combina property binding y event binding. Requiere `FormsModule`:

```typescript
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-formulario-simple',
  standalone: true,
  imports: [FormsModule],   // ← Necesario para ngModel
  template: `
    <input [(ngModel)]="nombre" placeholder="Tu nombre">
    <p>¡Hola, {{ nombre }}!</p>

    <!-- Equivalente manual (sin ngModel) -->
    <input [value]="apellido" (input)="apellido = $any($event.target).value">
    <p>Apellido: {{ apellido }}</p>
  `
})
export class FormularioSimpleComponent {
  nombre = '';
  apellido = '';
}
```

---

## @Input y @Output — Comunicación entre componentes

### `@Input` — El padre pasa datos al hijo

```typescript
// Componente hijo
import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-tarjeta-usuario',
  standalone: true,
  template: `
    <div class="tarjeta">
      <h3>{{ usuario.nombre }}</h3>
      <p>{{ usuario.email }}</p>
      <span [class]="'badge-' + rol">{{ rol }}</span>
    </div>
  `
})
export class TarjetaUsuarioComponent {
  @Input() usuario!: { nombre: string; email: string };
  @Input() rol: string = 'viewer';              // con valor por defecto
  @Input('etiqueta') claseCSS: string = '';    // alias: en HTML se usa [etiqueta]
}
```

```typescript
// Componente padre
@Component({
  selector: 'app-lista-usuarios',
  standalone: true,
  imports: [TarjetaUsuarioComponent],
  template: `
    <app-tarjeta-usuario
      [usuario]="usuarioActual"
      [rol]="'admin'"
      [etiqueta]="'destacado'">
    </app-tarjeta-usuario>
  `
})
export class ListaUsuariosComponent {
  usuarioActual = { nombre: 'Carlos', email: 'carlos@test.com' };
}
```

### `@Output` — El hijo notifica al padre

```typescript
// Componente hijo
import { Component, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-boton-like',
  standalone: true,
  template: `
    <button (click)="darLike()">
      ❤️ {{ likes }} likes
    </button>
  `
})
export class BotonLikeComponent {
  likes = 0;

  // EventEmitter que emite el número actualizado
  @Output() likeAdded = new EventEmitter<number>();

  darLike(): void {
    this.likes++;
    this.likeAdded.emit(this.likes);   // ← Notifica al padre
  }
}
```

```typescript
// Componente padre
@Component({
  selector: 'app-articulo',
  standalone: true,
  imports: [BotonLikeComponent],
  template: `
    <h2>{{ articulo.titulo }}</h2>
    <p>{{ articulo.contenido }}</p>
    <app-boton-like (likeAdded)="onLike($event)">
    </app-boton-like>
    <p>Total de likes recibidos: {{ totalLikes }}</p>
  `
})
export class ArticuloComponent {
  totalLikes = 0;
  articulo = { titulo: 'Aprendiendo Angular', contenido: 'Es genial...' };

  onLike(nuevoTotal: number): void {
    this.totalLikes = nuevoTotal;
    console.log(`El artículo tiene ${nuevoTotal} likes`);
  }
}
```

---

## Ciclo de vida de un componente

Angular llama a estos métodos en orden durante la vida del componente:

```typescript
import {
  Component, OnInit, OnChanges, DoCheck,
  AfterContentInit, AfterContentChecked,
  AfterViewInit, AfterViewChecked, OnDestroy,
  Input, SimpleChanges
} from '@angular/core';

@Component({ selector: 'app-ciclo', standalone: true, template: '' })
export class CicloDeVidaComponent implements OnInit, OnChanges, OnDestroy {

  @Input() dato = '';

  // 1. El constructor carga primero (inyección de dependencias aquí)
  constructor() {
    console.log('1. constructor');
  }

  // 2. Se dispara cuando cambia un @Input
  ngOnChanges(changes: SimpleChanges): void {
    console.log('2. ngOnChanges', changes);
    // changes.dato.currentValue  — valor nuevo
    // changes.dato.previousValue — valor anterior
    // changes.dato.firstChange   — ¿es el primer cambio?
  }

  // 3. Angular ha inicializado las propiedades del componente
  ngOnInit(): void {
    console.log('3. ngOnInit — aquí vas a pedir datos a la API');
    // Llamadas HTTP, suscripciones a Observables, etc.
  }

  // 4. Cada vez que Angular verifica cambios (se llama muchas veces)
  ngDoCheck(): void {
    console.log('4. ngDoCheck');
  }

  // 5. Contenido proyectado con <ng-content> fue inicializado
  ngAfterContentInit(): void {
    console.log('5. ngAfterContentInit');
  }

  // 6. Se verifica el contenido proyectado
  ngAfterContentChecked(): void {
    console.log('6. ngAfterContentChecked');
  }

  // 7. La vista del componente fue inicializada
  ngAfterViewInit(): void {
    console.log('7. ngAfterViewInit — puedes acceder al DOM aquí');
    // @ViewChild está disponible aquí
  }

  // 8. Se verifica la vista del componente
  ngAfterViewChecked(): void {
    console.log('8. ngAfterViewChecked');
  }

  // 9. El componente está siendo destruido
  ngOnDestroy(): void {
    console.log('9. ngOnDestroy — cancela suscripciones aquí');
    // Aquí debes cancelar suscripciones y limpiar recursos
  }
}
```

### Los más utilizados

| Hook | Cuándo usarlo |
|---|---|
| `ngOnInit` | Pedir datos a la API, inicializar el estado |
| `ngOnChanges` | Reaccionar a cambios en `@Input` |
| `ngOnDestroy` | Cancelar suscripciones a Observables, limpiar timers |
| `ngAfterViewInit` | Acceder a elementos del DOM con `@ViewChild` |

---

## ViewChild — Acceder a elementos del DOM

```typescript
import { Component, ViewChild, ElementRef, AfterViewInit } from '@angular/core';

@Component({
  selector: 'app-demo-viewchild',
  standalone: true,
  template: `
    <input #campoNombre placeholder="Tu nombre" />
    <button (click)="enfocar()">Enfocar</button>
  `
})
export class DemoViewChildComponent implements AfterViewInit {
  @ViewChild('campoNombre') campoInput!: ElementRef<HTMLInputElement>;

  ngAfterViewInit(): void {
    // Ya está disponible el elemento del DOM
    console.log(this.campoInput.nativeElement.value);
  }

  enfocar(): void {
    this.campoInput.nativeElement.focus();
  }
}
```

---

## Content Projection — `<ng-content>`

Permite pasar contenido HTML desde el padre al hijo (similar a `slots` en Vue o `children` en React):

```typescript
// Componente reutilizable con ng-content
@Component({
  selector: 'app-modal',
  standalone: true,
  template: `
    <div class="modal-overlay">
      <div class="modal">
        <header>
          <ng-content select="[slot=header]"></ng-content>  <!-- Contenido del header -->
        </header>
        <main>
          <ng-content></ng-content>  <!-- Contenido principal -->
        </main>
        <footer>
          <ng-content select="[slot=footer]"></ng-content>
        </footer>
      </div>
    </div>
  `
})
export class ModalComponent {}
```

```html
<!-- Uso del componente -->
<app-modal>
  <h2 slot="header">Confirmar acción</h2>

  <p>¿Estás seguro de que quieres eliminar este elemento?</p>

  <div slot="footer">
    <button (click)="cancelar()">Cancelar</button>
    <button (click)="confirmar()">Confirmar</button>
  </div>
</app-modal>
```

---

## Ejemplo completo: Componente de tarjeta de producto

```typescript
// producto-card.component.ts
import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CurrencyPipe } from '@angular/common';

interface Producto {
  id: number;
  nombre: string;
  precio: number;
  imagen: string;
  disponible: boolean;
}

@Component({
  selector: 'app-producto-card',
  standalone: true,
  imports: [CurrencyPipe],
  template: `
    <div class="card" [class.agotado]="!producto.disponible">
      <img [src]="producto.imagen" [alt]="producto.nombre">
      <div class="card-body">
        <h3>{{ producto.nombre }}</h3>
        <p class="precio">{{ producto.precio | currency:'USD' }}</p>
        <p *ngIf="!producto.disponible" class="badge-rojo">Agotado</p>
        <button
          [disabled]="!producto.disponible"
          (click)="agregar()">
          🛒 Agregar al carrito
        </button>
      </div>
    </div>
  `
})
export class ProductoCardComponent {
  @Input() producto!: Producto;
  @Output() agregadoAlCarrito = new EventEmitter<Producto>();

  agregar(): void {
    this.agregadoAlCarrito.emit(this.producto);
  }
}
```
