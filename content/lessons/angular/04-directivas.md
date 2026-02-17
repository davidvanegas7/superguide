# Directivas en Angular

Las **directivas** son instrucciones que modifican el comportamiento o la apariencia del DOM. Angular incluye directivas integradas muy potentes, y también puedes crear las tuyas propias.

Hay tres tipos de directivas:

| Tipo | Descripción | Ejemplo |
|---|---|---|
| **Componentes** | Directivas con template (ya las vimos) | `@Component` |
| **Estructurales** | Modifican la estructura del DOM | `@if`, `@for`, `*ngIf`, `*ngFor` |
| **De atributo** | Modifican la apariencia o comportamiento | `[ngClass]`, `[ngStyle]` |

---

## Control Flow moderno (Angular 17+)

Angular 17 introdujo la **nueva sintaxis de control flow** directamente en los templates. Es más legible y eficiente que las directivas antiguas:

### `@if` — Condicionales

```html
<!-- Angular 17+ -->
@if (usuario.estaLogueado) {
  <p>Bienvenido, {{ usuario.nombre }}</p>
} @else if (usuario.estaCargando) {
  <p>Cargando...</p>
} @else {
  <a href="/login">Inicia sesión</a>
}
```

### `@for` — Listas

```html
<!-- Angular 17+ -->
@for (producto of productos; track producto.id) {
  <div class="producto">
    <h3>{{ producto.nombre }}</h3>
    <p>{{ producto.precio | currency }}</p>
  </div>
} @empty {
  <p>No hay productos disponibles.</p>
}
```

> El `track` es obligatorio en `@for`. Indica a Angular qué propiedad identifica de forma única cada elemento. Esto optimiza el renderizado al actualizar la lista.

### `@switch` — Múltiples casos

```html
@switch (usuario.rol) {
  @case ('admin') {
    <app-panel-admin />
  }
  @case ('editor') {
    <app-panel-editor />
  }
  @case ('viewer') {
    <p>Solo puedes ver el contenido.</p>
  }
  @default {
    <p>Rol desconocido</p>
  }
}
```

---

## `@defer` — Carga diferida (Angular 17+)

Permite cargar partes del template de forma perezosa (lazy), mejorando el rendimiento:

```html
<!-- Se carga cuando el bloque entra en el viewport -->
@defer (on viewport) {
  <app-grafico-complejo />
} @placeholder {
  <div class="skeleton">Cargando gráfico...</div>
} @loading (minimum 500ms) {
  <p>⏳ Procesando...</p>
} @error {
  <p>❌ No se pudo cargar el gráfico.</p>
}

<!-- Otros activadores de defer -->
@defer (on idle) { ... }          <!-- Cuando el navegador está libre -->
@defer (on immediate) { ... }    <!-- Inmediatamente después del render inicial -->
@defer (on timer(2s)) { ... }    <!-- Después de 2 segundos -->
@defer (on interaction) { ... }  <!-- Cuando el usuario interactúa -->
@defer (when condicion) { ... }  <!-- Cuando la condición es verdadera -->
```

---

## Directivas estructurales antiguas (aún vigentes)

Si trabajas en proyectos con Angular < 17 o código legado, verás estas directivas:

### `*ngIf`

```html
<!-- Requiere importar NgIf o CommonModule -->
<p *ngIf="mostrar">Visible</p>
<p *ngIf="!mostrar">Oculto</p>

<!-- Con else -->
<p *ngIf="estaLogueado; else sinSesion">Bienvenido</p>
<ng-template #sinSesion>
  <p>Por favor inicia sesión</p>
</ng-template>

<!-- Con then y else -->
<ng-container *ngIf="cargando; then spinner; else contenido"></ng-container>
<ng-template #spinner><p>Cargando...</p></ng-template>
<ng-template #contenido><p>Datos cargados</p></ng-template>
```

### `*ngFor`

```typescript
import { Component } from '@angular/core';
import { NgFor } from '@angular/common';

@Component({
  selector: 'app-lista',
  standalone: true,
  imports: [NgFor],
  template: `
    <ul>
      <li *ngFor="let item of items; let i = index; trackBy: trackById">
        {{ i + 1 }}. {{ item.nombre }}
        <span *ngIf="i === 0">(primero)</span>
        <span *ngIf="last">(último)</span>
      </li>
    </ul>

    <!-- Variables disponibles en ngFor -->
    <!-- index: número de iteración (0-based) -->
    <!-- first: boolean, ¿es el primero? -->
    <!-- last: boolean, ¿es el último? -->
    <!-- even: boolean, ¿índice par? -->
    <!-- odd: boolean, ¿índice impar? -->
  `
})
export class ListaComponent {
  items = [
    { id: 1, nombre: 'Angular' },
    { id: 2, nombre: 'React' },
    { id: 3, nombre: 'Vue' }
  ];

  trackById(index: number, item: { id: number }): number {
    return item.id;   // ← Angular identifica elementos por id
  }
}
```

---

## Directivas de atributo

### `[ngClass]` — Clases CSS dinámicas

```typescript
@Component({
  template: `
    <!-- Con objeto: clave=clase, valor=condición -->
    <div [ngClass]="{
      'activo': estaActivo,
      'deshabilitado': estaDeshabilitado,
      'destacado': esImportante
    }">Elemento</div>

    <!-- Con array de clases -->
    <p [ngClass]="['texto-grande', colorClase]">Párrafo</p>

    <!-- Con string -->
    <span [ngClass]="claseString">Texto</span>

    <!-- Forma simplificada (sin ngClass) -->
    <div [class.activo]="estaActivo"
         [class.error]="tieneError">
      Simple
    </div>
  `
})
export class DemoNgClassComponent {
  estaActivo = true;
  estaDeshabilitado = false;
  esImportante = true;
  colorClase = 'azul';
  claseString = 'grande negrita';
  tieneError = false;
}
```

### `[ngStyle]` — Estilos inline dinámicos

```typescript
@Component({
  template: `
    <!-- Con objeto -->
    <div [ngStyle]="{
      'color': colorTexto,
      'font-size': tamano + 'px',
      'background-color': fondoColor,
      'display': visible ? 'block' : 'none'
    }">
      Texto estilizado
    </div>

    <!-- Forma simplificada (sin ngStyle) -->
    <p [style.color]="colorTexto"
       [style.font-size.px]="tamano">
      Más simple
    </p>
  `
})
export class DemoNgStyleComponent {
  colorTexto = 'steelblue';
  tamano = 20;
  fondoColor = '#f0f0f0';
  visible = true;
}
```

---

## Crear directivas personalizadas

### Directiva de atributo

```typescript
import { Directive, ElementRef, HostListener, Input, OnInit } from '@angular/core';

@Directive({
  selector: '[appResaltar]',   // Se aplica como atributo: <p appResaltar>
  standalone: true
})
export class ResaltarDirective implements OnInit {
  @Input() appResaltar = 'yellow';   // El color viene del atributo
  @Input() colorHover = 'orange';

  private colorOriginal = '';

  constructor(private el: ElementRef) {}

  ngOnInit(): void {
    this.colorOriginal = this.el.nativeElement.style.backgroundColor;
  }

  @HostListener('mouseenter')
  onMouseEnter(): void {
    this.el.nativeElement.style.backgroundColor = this.appResaltar;
  }

  @HostListener('mouseleave')
  onMouseLeave(): void {
    this.el.nativeElement.style.backgroundColor = this.colorOriginal;
  }

  @HostListener('click')
  onClick(): void {
    this.el.nativeElement.style.backgroundColor = this.colorHover;
  }
}
```

```html
<!-- Uso de la directiva -->
<p appResaltar>Resalta en amarillo (por defecto)</p>
<p [appResaltar]="'lightblue'" [colorHover]="'darkblue'">Resalta en azul</p>
```

### Directiva con HostBinding

```typescript
import { Directive, HostBinding, Input } from '@angular/core';

@Directive({
  selector: '[appBadge]',
  standalone: true
})
export class BadgeDirective {
  @Input() tipo: 'success' | 'danger' | 'warning' = 'success';

  @HostBinding('class')
  get clases(): string {
    return `badge badge-${this.tipo}`;
  }

  @HostBinding('attr.role')
  role = 'status';  // Agrega atributo HTML role="status"
}
```

```html
<span appBadge [tipo]="'success'">Activo</span>
<span appBadge [tipo]="'danger'">Error</span>
```

### Directiva estructural personalizada

```typescript
import { Directive, Input, TemplateRef, ViewContainerRef } from '@angular/core';

@Directive({
  selector: '[appSiRol]',
  standalone: true
})
export class SiRolDirective {
  private rolActual = 'viewer';   // vendría de un servicio

  @Input() set appSiRol(rolesPermitidos: string[]) {
    if (rolesPermitidos.includes(this.rolActual)) {
      // Mostrar el contenido
      this.viewContainer.createEmbeddedView(this.templateRef);
    } else {
      // Ocultar el contenido
      this.viewContainer.clear();
    }
  }

  constructor(
    private templateRef: TemplateRef<any>,
    private viewContainer: ViewContainerRef
  ) {}
}
```

```html
<!-- Solo se muestra si el usuario tiene ese rol -->
<button *appSiRol="['admin', 'editor']">Editar</button>
<button *appSiRol="['admin']">Eliminar</button>
```

---

## `ng-container` y `ng-template`

Son elementos auxiliares que no generan HTML en el DOM:

```html
<!-- ng-container: agrupa sin agregar elemento al DOM -->
<ng-container *ngIf="usuarios.length > 0">
  <h2>Lista de usuarios</h2>
  <ul>
    @for (u of usuarios; track u.id) {
      <li>{{ u.nombre }}</li>
    }
  </ul>
</ng-container>

<!-- ng-template: define un bloque de template reutilizable -->
<ng-template #cargando>
  <div class="spinner">⏳ Cargando...</div>
</ng-template>

<!-- Se puede mostrar programáticamente con ngTemplateOutlet -->
<ng-container *ngTemplateOutlet="cargando"></ng-container>

<!-- O con @if -->
@if (!datos) {
  <ng-container *ngTemplateOutlet="cargando"></ng-container>
}
```

---

## Resumen de directivas

| Directiva | Uso |
|---|---|
| `@if` / `*ngIf` | Mostrar/ocultar basado en condición |
| `@for` / `*ngFor` | Renderizar listas |
| `@switch` / `*ngSwitch` | Múltiples condiciones |
| `@defer` | Carga lazy de contenido |
| `[ngClass]` | Clases CSS dinámicas |
| `[ngStyle]` | Estilos inline dinámicos |
| `ng-container` | Agrupador sin HTML extra |
| `ng-template` | Bloques de template reutilizables |
