# Testing en Angular

El testing es una habilidad crítica en entornos de trabajo profesional. Esta lección cubre desde pruebas unitarias con Jasmine/TestBed hasta pruebas E2E con Cypress.

---

## Tipos de prueba en Angular

| Tipo | Herramienta | Velocidad | Propósito |
|---|---|---|---|
| **Unitaria** | Jasmine + Karma | ⚡ Rápida | Lógica de servicios, pipes, funciones puras |
| **Componente** | Jasmine + TestBed | 🔶 Media | Renderizado, inputs, outputs, templates |
| **Integración** | TestBed + HttpClientTesting | 🔶 Media | Componentes + servicios juntos |
| **E2E** | Cypress / Playwright | 🐢 Lenta | Flujos completos del usuario |

---

## Jasmine — Estructura básica

```typescript
// saludo.service.spec.ts
import { SaludoService } from './saludo.service';

describe('SaludoService', () => {     // Agrupa pruebas relacionadas
  let servicio: SaludoService;

  beforeEach(() => {                  // Se ejecuta antes de cada 'it'
    servicio = new SaludoService();
  });

  it('debe devolver un saludo', () => {
    const resultado = servicio.saludar('Angular');
    expect(resultado).toBe('Hola, Angular!');
  });

  it('debe lanzar error si el nombre está vacío', () => {
    expect(() => servicio.saludar('')).toThrowError('Nombre requerido');
  });
});
```

### Matchers más usados de Jasmine

```typescript
expect(valor).toBe(3);                    // Igualdad estricta (===)
expect(valor).toEqual({ id: 1 });         // Igualdad profunda (deep equal)
expect(valor).toBeTruthy();               // Truthy
expect(valor).toBeFalsy();                // Falsy
expect(valor).toBeNull();
expect(valor).toBeUndefined();
expect(valor).toContain('Angular');       // Substring o elemento de array
expect(valor).toHaveBeenCalled();         // Spy fue llamado
expect(valor).toHaveBeenCalledWith('x');  // Spy fue llamado con 'x'
expect(fn).toThrowError('mensaje');       // Función lanza error
expect(valor).toBeGreaterThan(5);
```

---

## TestBed — Módulo de pruebas de Angular

TestBed crea un módulo Angular ligero para las pruebas:

```typescript
// componente.spec.ts
import { TestBed, ComponentFixture } from '@angular/core/testing';
import { TarjetaComponent } from './tarjeta.component';
import { ProductosService } from '../servicios/productos.service';

describe('TarjetaComponent', () => {
  let fixture: ComponentFixture<TarjetaComponent>;
  let component: TarjetaComponent;

  beforeEach(async () => {
    // Configura el módulo de prueba
    await TestBed.configureTestingModule({
      imports: [TarjetaComponent],           // Standalone component
      providers: [
        // Puedes proveer servicios reales o mocks aquí
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(TarjetaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();  // Dispara ngOnInit y la detección inicial
  });

  it('debe crearse', () => {
    expect(component).toBeTruthy();
  });
});
```

---

## Testing de Componentes

```typescript
// contador.component.ts
@Component({
  selector: 'app-contador',
  standalone: true,
  template: `
    <p>Valor: {{ contador }}</p>
    <button id="btn-mas" (click)="incrementar()">+</button>
    <button id="btn-menos" (click)="decrementar()">-</button>
  `
})
export class ContadorComponent {
  contador = 0;

  incrementar(): void { this.contador++; }
  decrementar(): void { this.contador--; }
}
```

```typescript
// contador.component.spec.ts
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { ContadorComponent } from './contador.component';

describe('ContadorComponent', () => {
  let fixture: ComponentFixture<ContadorComponent>;
  let component: ContadorComponent;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ContadorComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(ContadorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('debe empezar en 0', () => {
    expect(component.contador).toBe(0);
  });

  it('debe incrementar al hacer click en "+"', () => {
    // Buscar el botón en el DOM
    const boton = fixture.debugElement.query(By.css('#btn-mas'));
    boton.triggerEventHandler('click', null);

    fixture.detectChanges();
    expect(component.contador).toBe(1);

    // Verificar el DOM también
    const parrafo = fixture.debugElement.query(By.css('p'));
    expect(parrafo.nativeElement.textContent).toContain('Valor: 1');
  });

  it('debe decrementar al hacer click en "-"', () => {
    component.contador = 5;  // Estado inicial para la prueba
    const boton = fixture.debugElement.query(By.css('#btn-menos'));
    boton.triggerEventHandler('click', null);
    fixture.detectChanges();
    expect(component.contador).toBe(4);
  });
});
```

---

## Mocking de Servicios

### Con jasmine.createSpyObj

```typescript
// productos.service.ts
@Injectable({ providedIn: 'root' })
export class ProductosService {
  private http = inject(HttpClient);

  obtenerProductos(): Observable<Producto[]> {
    return this.http.get<Producto[]>('/api/productos');
  }
}
```

```typescript
// lista-productos.component.spec.ts
import { of } from 'rxjs';

describe('ListaProductosComponent', () => {
  let servicioMock: jasmine.SpyObj<ProductosService>;

  beforeEach(async () => {
    // Crear el mock del servicio
    servicioMock = jasmine.createSpyObj('ProductosService', ['obtenerProductos']);
    servicioMock.obtenerProductos.and.returnValue(of([
      { id: 1, nombre: 'Laptop', precio: 1200 },
      { id: 2, nombre: 'Mouse', precio: 25 }
    ]));

    await TestBed.configureTestingModule({
      imports: [ListaProductosComponent],
      providers: [
        { provide: ProductosService, useValue: servicioMock }  // ← Inyectar mock
      ]
    }).compileComponents();
  });

  it('debe mostrar 2 productos', () => {
    const fixture = TestBed.createComponent(ListaProductosComponent);
    fixture.detectChanges();

    const items = fixture.debugElement.queryAll(By.css('.producto-item'));
    expect(items.length).toBe(2);
    expect(servicioMock.obtenerProductos).toHaveBeenCalled();
  });
});
```

---

## HttpClientTestingModule — Testing de HTTP

```typescript
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';

describe('ProductosService', () => {
  let servicio: ProductosService;
  let httpMock: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],  // ← Intercepta llamadas HTTP
      providers: [ProductosService]
    });

    servicio = TestBed.inject(ProductosService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpMock.verify();  // Verifica que no quedan requests sin manejar
  });

  it('debe hacer GET /api/productos y devolver lista', () => {
    const productosMock = [{ id: 1, nombre: 'Laptop', precio: 1200 }];

    servicio.obtenerProductos().subscribe(productos => {
      expect(productos.length).toBe(1);
      expect(productos[0].nombre).toBe('Laptop');
    });

    // Interceptar la request y responder con datos de prueba
    const req = httpMock.expectOne('/api/productos');
    expect(req.request.method).toBe('GET');
    req.flush(productosMock);  // Simular la respuesta del servidor
  });

  it('debe manejar error 404', () => {
    servicio.obtenerProductos().subscribe({
      next: () => fail('Debería haber fallado'),
      error: (error) => {
        expect(error.status).toBe(404);
      }
    });

    const req = httpMock.expectOne('/api/productos');
    req.flush('Not Found', { status: 404, statusText: 'Not Found' });
  });
});
```

---

## Testing de Observables y código asíncrono

### Con fakeAsync y tick

```typescript
import { fakeAsync, tick } from '@angular/core/testing';

it('debe cargar datos después del debounce', fakeAsync(() => {
  component.buscar('Angular');

  tick(300);  // Avanzar el tiempo simulado 300ms (el debounce time)
  fixture.detectChanges();

  expect(component.resultados.length).toBeGreaterThan(0);
}));
```

### Con async / await

```typescript
it('debe resolver la promesa', async () => {
  const resultado = await servicio.operacionAsincrona();
  expect(resultado).toBe('éxito');
});
```

### Con done (callback pattern)

```typescript
it('debe emitir un valor', (done) => {
  servicio.obtenerDatos().subscribe(datos => {
    expect(datos).toBeTruthy();
    done();  // Indicar que la prueba asíncrona terminó
  });
});
```

---

## Testing de Signals

```typescript
// contador.component.ts (con Signals)
@Component({
  standalone: true,
  template: `<p>{{ contador() }}</p>`
})
export class ContadorSignalComponent {
  contador = signal(0);
  doble = computed(() => this.contador() * 2);

  incrementar() { this.contador.update(n => n + 1); }
}
```

```typescript
// contador.component.spec.ts
it('debe actualizar el signal y el computed', () => {
  component.incrementar();
  fixture.detectChanges();

  expect(component.contador()).toBe(1);
  expect(component.doble()).toBe(2);
});
```

---

## Ejecutar las pruebas

```bash
# Ejecutar todas las pruebas con Karma (browser)
ng test

# Con cobertura de código
ng test --code-coverage

# En modo headless (para CI/CD)
ng test --watch=false --browsers=ChromeHeadless

# Ver el reporte de cobertura
open coverage/index.html
```

### Configurar el reporte de cobertura en angular.json

```json
{
  "test": {
    "options": {
      "codeCoverageExclude": [
        "src/app/**/*.module.ts",
        "src/main.ts"
      ],
      "codeCoverage": true
    }
  }
}
```

---

## E2E con Cypress

Cypress es la herramienta más popular para pruebas end-to-end:

```bash
# Instalar Cypress
npm install --save-dev cypress

# Abrir Cypress (interfaz gráfica)
npx cypress open

# Ejecutar en modo headless (CI/CD)
npx cypress run
```

### Ejemplo de prueba E2E

```javascript
// cypress/e2e/login.cy.js
describe('Flujo de Login', () => {
  beforeEach(() => {
    cy.visit('/login');
  });

  it('debe iniciar sesión correctamente', () => {
    cy.get('[data-cy="email"]').type('admin@empresa.com');
    cy.get('[data-cy="password"]').type('secreto123');
    cy.get('[data-cy="btn-login"]').click();

    // Verificar redirección al dashboard
    cy.url().should('include', '/dashboard');
    cy.get('[data-cy="bienvenida"]').should('contain', 'Hola, Admin');
  });

  it('debe mostrar error con credenciales incorrectas', () => {
    cy.get('[data-cy="email"]').type('usuario@test.com');
    cy.get('[data-cy="password"]').type('contraseña-incorrecta');
    cy.get('[data-cy="btn-login"]').click();

    cy.get('[data-cy="error-mensaje"]').should('be.visible');
    cy.get('[data-cy="error-mensaje"]').should('contain', 'Credenciales inválidas');
  });
});
```

### Buena práctica: atributos `data-cy`

```html
<!-- En tu componente Angular — separar selectores de UI de selectores de test -->
<input
  formControlName="email"
  class="form-control"
  data-cy="email"
/>

<button
  type="submit"
  class="btn btn-primary"
  data-cy="btn-login"
>
  Iniciar sesión
</button>
```

---

## Resumen — Qué probar y qué no

| Probar ✅ | No probar ❌ |
|---|---|
| Lógica de negocio en servicios | Implementación interna del framework |
| Transformaciones de datos (pipes) | Código de terceros ya testeado |
| Interacciones del usuario (clicks) | Getters/setters triviales |
| Llamadas HTTP (con mock) | CSS y estilos |
| Manejo de errores | Configuración de módulos |
| Casos límite (lista vacía, null) | |

### Cobertura — objetivos razonables

| Nivel | Cobertura |
|---|---|
| Mínimo aceptable | 60% |
| Buen estándar | 80% |
| Óptimo | 90%+ |
| Perfeccionismo innecesario | 100% (raro que valga la pena) |
