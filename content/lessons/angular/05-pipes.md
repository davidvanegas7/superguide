# Pipes en Angular

Los **Pipes** transforman datos directamente en los templates. Son como filtros que toman un valor de entrada, lo procesan y devuelven un nuevo valor formateado. Son perfectos para formatear fechas, números, monedas, textos y más.

---

## Sintaxis básica

```html
{{ valor | nombrePipe }}
{{ valor | nombrePipe : argumento1 : argumento2 }}
{{ valor | pipe1 | pipe2 | pipe3 }}   <!-- Encadenamiento -->
```

---

## Pipes integrados de Angular

### DatePipe — Fechas

```typescript
import { Component } from '@angular/core';
import { DatePipe } from '@angular/common';

@Component({
  selector: 'app-demo-date',
  standalone: true,
  imports: [DatePipe],
  template: `
    <p>{{ hoy | date }}</p>
    <!-- Feb 17, 2026 -->

    <p>{{ hoy | date:'short' }}</p>
    <!-- 2/17/26, 3:00 PM -->

    <p>{{ hoy | date:'medium' }}</p>
    <!-- Feb 17, 2026, 3:00:00 PM -->

    <p>{{ hoy | date:'long' }}</p>
    <!-- February 17, 2026 at 3:00:00 PM GMT+0 -->

    <p>{{ hoy | date:'fullDate' }}</p>
    <!-- Monday, February 17, 2026 -->

    <!-- Formato personalizado -->
    <p>{{ hoy | date:'dd/MM/yyyy' }}</p>
    <!-- 17/02/2026 -->

    <p>{{ hoy | date:'HH:mm:ss' }}</p>
    <!-- 15:00:00 -->

    <p>{{ hoy | date:'EEEE, d de MMMM yyyy' : '' : 'es' }}</p>
    <!-- Lunes, 17 de Febrero 2026 (en español) -->
  `
})
export class DemoDateComponent {
  hoy = new Date();
}
```

### CurrencyPipe — Monedas

```html
<p>{{ 1234.56 | currency }}</p>
<!-- $1,234.56 (USD por defecto) -->

<p>{{ 1234.56 | currency:'EUR' }}</p>
<!-- €1,234.56 -->

<p>{{ 1234.56 | currency:'USD':'symbol':'1.0-0' }}</p>
<!-- $1,235 (sin decimales) -->

<p>{{ 1234.56 | currency:'COP':'symbol':'1.0-0' }}</p>
<!-- $1,235 COP -->

<!-- Formato: minimumIntegerDigits.minimumFractionDigits-maximumFractionDigits -->
<p>{{ 3.14159 | currency:'USD':'symbol':'1.2-4' }}</p>
<!-- $3.1416 -->
```

### DecimalPipe — Números

```html
<p>{{ 3.14159 | number }}</p>
<!-- 3.142 -->

<p>{{ 3.14159 | number:'1.2-4' }}</p>
<!-- 3.1416 -->

<p>{{ 1000000 | number }}</p>
<!-- 1,000,000 -->

<p>{{ 0.75 | percent }}</p>
<!-- 75% -->

<p>{{ 0.756 | percent:'1.1-2' }}</p>
<!-- 75.60% -->
```

### UpperCase, LowerCase, TitleCase

```html
<p>{{ 'angular es genial' | uppercase }}</p>
<!-- ANGULAR ES GENIAL -->

<p>{{ 'ANGULAR ES GENIAL' | lowercase }}</p>
<!-- angular es genial -->

<p>{{ 'angular es genial' | titlecase }}</p>
<!-- Angular Es Genial -->
```

### SlicePipe — Subarreglos y subcadenas

```html
<!-- Strings -->
<p>{{ 'Hola Mundo' | slice:0:4 }}</p>
<!-- Hola -->

<p>{{ 'Hola Mundo' | slice:5 }}</p>
<!-- Mundo -->

<!-- Arrays -->
<p>{{ [1,2,3,4,5] | slice:1:3 }}</p>
<!-- [2, 3] -->

@for (item of items | slice:0:3; track item) {
  <p>{{ item }}</p>
}
<!-- Solo muestra los primeros 3 elementos -->
```

### JsonPipe — Debug

```html
<!-- Muy útil para depuración -->
<pre>{{ miObjeto | json }}</pre>
<!-- Muestra el objeto formateado como JSON -->
```

### KeyValuePipe — Iterar objetos

```typescript
@Component({
  template: `
    @for (entry of config | keyvalue; track entry.key) {
      <p>{{ entry.key }}: {{ entry.value }}</p>
    }
  `
})
export class ConfigComponent {
  config = {
    tema: 'oscuro',
    idioma: 'español',
    notificaciones: true
  };
}
```

### AsyncPipe — Observables y Promesas

El `AsyncPipe` es uno de los más importantes en Angular. Se suscribe automáticamente a un Observable o Promise y se desuscribe cuando el componente se destruye:

```typescript
import { Component } from '@angular/core';
import { AsyncPipe } from '@angular/common';
import { Observable, of } from 'rxjs';
import { delay } from 'rxjs/operators';

@Component({
  selector: 'app-async-demo',
  standalone: true,
  imports: [AsyncPipe],
  template: `
    <!-- Con Observable -->
    @if (usuarios$ | async; as usuarios) {
      @for (u of usuarios; track u.id) {
        <p>{{ u.nombre }}</p>
      }
    } @else {
      <p>Cargando usuarios...</p>
    }

    <!-- Con Promise -->
    <p>Tiempo servidor: {{ horaServidor | async }}</p>
  `
})
export class AsyncDemoComponent {
  usuarios$ = this.obtenerUsuarios();   // Observable
  horaServidor = this.obtenerHora();    // Promise

  private obtenerUsuarios(): Observable<{id: number; nombre: string}[]> {
    return of([
      { id: 1, nombre: 'Ana' },
      { id: 2, nombre: 'Carlos' }
    ]).pipe(delay(1000));  // simula un delay de 1 segundo
  }

  private obtenerHora(): Promise<string> {
    return new Promise(resolve =>
      setTimeout(() => resolve(new Date().toLocaleTimeString()), 500)
    );
  }
}
```

> **Ventaja del AsyncPipe**: Se desuscribe automáticamente cuando el componente se destruye, evitando memory leaks. ¡Úsalo siempre que puedas en lugar de suscribirte manualmente!

---

## Crear Pipes personalizados

### Pipe puro (por defecto)

```typescript
import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'truncar',
  standalone: true
})
export class TruncarPipe implements PipeTransform {
  transform(valor: string, limite: number = 100, sufijo: string = '...'): string {
    if (!valor) return '';
    if (valor.length <= limite) return valor;
    return valor.substring(0, limite) + sufijo;
  }
}
```

```html
<p>{{ textoLargo | truncar:50 }}</p>
<p>{{ textoLargo | truncar:100:' [ver más]' }}</p>
```

### Pipe de filtro para listas

```typescript
@Pipe({
  name: 'filtrar',
  standalone: true
})
export class FiltrarPipe implements PipeTransform {
  transform<T extends Record<string, any>>(
    lista: T[],
    campo: keyof T,
    busqueda: string
  ): T[] {
    if (!busqueda?.trim()) return lista;

    const termino = busqueda.toLowerCase();
    return lista.filter(item =>
      String(item[campo]).toLowerCase().includes(termino)
    );
  }
}
```

```typescript
@Component({
  standalone: true,
  imports: [FiltrarPipe, FormsModule],
  template: `
    <input [(ngModel)]="busqueda" placeholder="Filtrar...">

    @for (p of productos | filtrar:'nombre':busqueda; track p.id) {
      <div>{{ p.nombre }} - {{ p.precio | currency }}</div>
    }
  `
})
export class ListaProductosComponent {
  busqueda = '';
  productos = [
    { id: 1, nombre: 'Laptop', precio: 999 },
    { id: 2, nombre: 'Monitor', precio: 299 },
    { id: 3, nombre: 'Teclado', precio: 79 },
    { id: 4, nombre: 'Mouse', precio: 35 }
  ];
}
```

### Pipe de formateo de teléfono

```typescript
@Pipe({ name: 'telefono', standalone: true })
export class TelefonoPipe implements PipeTransform {
  transform(valor: string | number, codigo: string = '+1'): string {
    const num = String(valor).replace(/\D/g, '');  // solo dígitos

    if (num.length === 10) {
      return `${codigo} (${num.slice(0,3)}) ${num.slice(3,6)}-${num.slice(6)}`;
    }
    return String(valor);  // si no tiene formato esperado, retorna tal cual
  }
}
```

```html
<p>{{ '3001234567' | telefono:'+57' }}</p>
<!-- +57 (300) 123-4567 -->
```

### Pipe impuro

Un pipe **puro** solo se ejecuta cuando el valor de entrada cambia. Un pipe **impuro** se ejecuta en cada ciclo de detección de cambios (más lento, pero detecta cambios internos de arrays/objetos):

```typescript
@Pipe({
  name: 'ordenar',
  standalone: true,
  pure: false   // ← pipe impuro
})
export class OrdenarPipe implements PipeTransform {
  transform<T>(lista: T[], campo: keyof T, direccion: 'asc' | 'desc' = 'asc'): T[] {
    if (!lista) return [];

    return [...lista].sort((a, b) => {
      const valA = a[campo];
      const valB = b[campo];

      if (valA < valB) return direccion === 'asc' ? -1 : 1;
      if (valA > valB) return direccion === 'asc' ? 1 : -1;
      return 0;
    });
  }
}
```

> **Cuidado con pipes impuros**: se ejecutan en cada change detection cycle, lo que puede afectar el rendimiento. Prefiere pipes puros siempre que sea posible.

---

## Pipes encadenados

```html
<p>{{ 'hola mundo' | titlecase | slice:0:5 }}</p>
<!-- Hola -->

<p>{{ fecha | date:'fullDate' | uppercase }}</p>
<!-- MONDAY, FEBRUARY 17, 2026 -->

<p>{{ precio | currency:'USD' | lowercase }}</p>
<!-- $1,234.56 (no tiene mucho sentido, pero es posible) -->
```

---

## Configurar el locale para español

Para que los pipes de fecha, número y moneda usen el formato en español:

```typescript
// main.ts
import { bootstrapApplication } from '@angular/platform-browser';
import { AppComponent } from './app/app.component';
import { LOCALE_ID, importProvidersFrom } from '@angular/core';
import { registerLocaleData } from '@angular/common';
import localeEs from '@angular/common/locales/es';

registerLocaleData(localeEs);

bootstrapApplication(AppComponent, {
  providers: [
    { provide: LOCALE_ID, useValue: 'es' }
  ]
});
```

```html
<!-- Ahora las fechas salen en español automáticamente -->
<p>{{ hoy | date:'fullDate' }}</p>
<!-- lunes, 17 de febrero de 2026 -->

<p>{{ 1234.56 | currency:'USD' }}</p>
<!-- 1.234,56 US$ (formato europeo/latinoamericano) -->
```

---

## Resumen

| Pipe | Uso |
|---|---|
| `date` | Formatear fechas |
| `currency` | Formatear monedas |
| `number` | Formatear números decimales |
| `percent` | Formatear porcentajes |
| `uppercase` / `lowercase` / `titlecase` | Transformar texto |
| `slice` | Subarreglos y subcadenas |
| `json` | Debug de objetos |
| `keyvalue` | Iterar propiedades de un objeto |
| `async` | Suscribirse a Observables y Promises |
