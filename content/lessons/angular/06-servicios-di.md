# Servicios e Inyección de Dependencias

Los **servicios** son clases que encapsulan la lógica de negocio, el acceso a datos y las funcionalidades compartidas entre componentes. La **Inyección de Dependencias (DI)** es el mecanismo que Angular usa para proporcionar estas clases a los componentes que las necesitan.

---

## ¿Por qué servicios?

Sin servicios, los componentes tendrían que:
- Duplicar lógica en múltiples componentes
- Gestionar el estado de forma inconsistente
- Hacer llamadas HTTP directamente (difícil de testear)

Con servicios:
- **Separación de responsabilidades**: el componente solo maneja la vista
- **Reutilización**: un servicio puede ser usado por muchos componentes
- **Testeabilidad**: fácil de hacer mock en tests

---

## Crear un servicio

```bash
ng generate service services/usuarios
ng g s services/usuarios   # forma corta
```

Esto genera `src/app/services/usuarios.service.ts`:

```typescript
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'   // ← disponible en toda la app (singleton)
})
export class UsuariosService {
  // Lógica aquí
}
```

---

## `@Injectable` y `providedIn`

El decorador `@Injectable` marca la clase como inyectable. La opción `providedIn` controla dónde se registra:

```typescript
@Injectable({
  providedIn: 'root'       // Singleton global — una sola instancia en toda la app
})

@Injectable({
  providedIn: 'platform'   // Compartido entre múltiples apps Angular en la misma página
})

// Sin providedIn — hay que registrarlo manualmente en un componente o módulo
@Injectable()
export class ServicioLocal {}
```

### Registrar servicio localmente (instancia por componente)

```typescript
@Component({
  selector: 'app-mi-comp',
  standalone: true,
  providers: [ServicioLocal],   // ← Nueva instancia solo para este componente y sus hijos
  template: '...'
})
export class MiComponente {
  constructor(private servicio: ServicioLocal) {}
}
```

---

## Servicio básico con datos en memoria

```typescript
import { Injectable } from '@angular/core';

export interface Tarea {
  id: number;
  titulo: string;
  completada: boolean;
  prioridad: 'alta' | 'media' | 'baja';
  createdAt: Date;
}

@Injectable({ providedIn: 'root' })
export class TareasService {
  private tareas: Tarea[] = [
    { id: 1, titulo: 'Aprender Angular', completada: false, prioridad: 'alta', createdAt: new Date() },
    { id: 2, titulo: 'Crear un proyecto', completada: false, prioridad: 'media', createdAt: new Date() }
  ];

  private proximoId = 3;

  // Obtener todas
  obtenerTodas(): Tarea[] {
    return [...this.tareas];  // copia para evitar mutaciones externas
  }

  // Obtener por id
  obtenerPorId(id: number): Tarea | undefined {
    return this.tareas.find(t => t.id === id);
  }

  // Obtener filtradas
  obtenerPorPrioridad(prioridad: Tarea['prioridad']): Tarea[] {
    return this.tareas.filter(t => t.prioridad === prioridad);
  }

  // Agregar
  agregar(titulo: string, prioridad: Tarea['prioridad'] = 'media'): Tarea {
    const nueva: Tarea = {
      id: this.proximoId++,
      titulo,
      completada: false,
      prioridad,
      createdAt: new Date()
    };
    this.tareas.push(nueva);
    return nueva;
  }

  // Actualizar
  actualizar(id: number, cambios: Partial<Tarea>): boolean {
    const indice = this.tareas.findIndex(t => t.id === id);
    if (indice === -1) return false;

    this.tareas[indice] = { ...this.tareas[indice], ...cambios };
    return true;
  }

  // Toggle completada
  toggleCompletada(id: number): void {
    const tarea = this.tareas.find(t => t.id === id);
    if (tarea) tarea.completada = !tarea.completada;
  }

  // Eliminar
  eliminar(id: number): boolean {
    const indice = this.tareas.findIndex(t => t.id === id);
    if (indice === -1) return false;

    this.tareas.splice(indice, 1);
    return true;
  }

  // Estadísticas
  obtenerEstadisticas() {
    return {
      total: this.tareas.length,
      completadas: this.tareas.filter(t => t.completada).length,
      pendientes: this.tareas.filter(t => !t.completada).length
    };
  }
}
```

### Usar el servicio en un componente

```typescript
import { Component, OnInit } from '@angular/core';
import { TareasService, Tarea } from '../services/tareas.service';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-tareas',
  standalone: true,
  imports: [FormsModule],
  template: `
    <h2>Mis Tareas</h2>

    <!-- Formulario para agregar -->
    <div>
      <input [(ngModel)]="nuevaTitulo" placeholder="Nueva tarea" (keyup.enter)="agregar()">
      <select [(ngModel)]="nuevaPrioridad">
        <option value="alta">Alta</option>
        <option value="media">Media</option>
        <option value="baja">Baja</option>
      </select>
      <button (click)="agregar()">+ Agregar</button>
    </div>

    <!-- Estadísticas -->
    <p>{{ stats.completadas }}/{{ stats.total }} completadas</p>

    <!-- Lista -->
    @for (tarea of tareas; track tarea.id) {
      <div [class.completada]="tarea.completada">
        <input type="checkbox"
               [checked]="tarea.completada"
               (change)="toggleCompletada(tarea.id)">
        <span>{{ tarea.titulo }}</span>
        <span class="badge">{{ tarea.prioridad }}</span>
        <button (click)="eliminar(tarea.id)">🗑️</button>
      </div>
    }
  `
})
export class TareasComponent implements OnInit {
  tareas: Tarea[] = [];
  stats = { total: 0, completadas: 0, pendientes: 0 };
  nuevaTitulo = '';
  nuevaPrioridad: Tarea['prioridad'] = 'media';

  // Angular inyecta el servicio automáticamente
  constructor(private tareasService: TareasService) {}

  ngOnInit(): void {
    this.cargarTareas();
  }

  cargarTareas(): void {
    this.tareas = this.tareasService.obtenerTodas();
    this.stats = this.tareasService.obtenerEstadisticas();
  }

  agregar(): void {
    if (!this.nuevaTitulo.trim()) return;
    this.tareasService.agregar(this.nuevaTitulo, this.nuevaPrioridad);
    this.nuevaTitulo = '';
    this.cargarTareas();
  }

  toggleCompletada(id: number): void {
    this.tareasService.toggleCompletada(id);
    this.cargarTareas();
  }

  eliminar(id: number): void {
    this.tareasService.eliminar(id);
    this.cargarTareas();
  }
}
```

---

## Inyección moderna con `inject()`

Angular 14+ permite inyectar dependencias con la función `inject()`, sin necesidad del constructor:

```typescript
import { Component, inject, OnInit } from '@angular/core';
import { TareasService } from '../services/tareas.service';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  template: '...'
})
export class DashboardComponent implements OnInit {
  // Forma moderna — sin constructor
  private tareasService = inject(TareasService);
  private router = inject(Router);
  private auth = inject(AuthService);

  ngOnInit(): void {
    if (!this.auth.estaLogueado()) {
      this.router.navigate(['/login']);
    }
  }
}
```

> La función `inject()` solo puede usarse durante la **fase de construcción** del componente (en propiedades de clase o en el constructor). No dentro de métodos.

---

## Servicio de autenticación — ejemplo real

```typescript
import { Injectable, signal, computed } from '@angular/core';
import { Router } from '@angular/router';

export interface Usuario {
  id: number;
  nombre: string;
  email: string;
  rol: 'admin' | 'editor' | 'viewer';
  token: string;
}

@Injectable({ providedIn: 'root' })
export class AuthService {
  // Usando Signals para estado reactivo
  private usuarioActual = signal<Usuario | null>(null);

  // Computed signals — se recalculan automáticamente
  readonly estaLogueado = computed(() => this.usuarioActual() !== null);
  readonly usuario = computed(() => this.usuarioActual());
  readonly esAdmin = computed(() => this.usuarioActual()?.rol === 'admin');

  constructor(private router: Router) {
    // Recuperar sesión del localStorage al iniciar
    this.recuperarSesion();
  }

  async login(email: string, password: string): Promise<boolean> {
    try {
      // Simulación de llamada HTTP (en producción usarías HttpClient)
      await this.delay(800);

      if (email === 'admin@test.com' && password === '123456') {
        const usuario: Usuario = {
          id: 1,
          nombre: 'Admin',
          email,
          rol: 'admin',
          token: 'token-simulado-123'
        };
        this.establecerUsuario(usuario);
        return true;
      }

      return false;
    } catch {
      return false;
    }
  }

  logout(): void {
    this.usuarioActual.set(null);
    localStorage.removeItem('usuario');
    this.router.navigate(['/login']);
  }

  private establecerUsuario(usuario: Usuario): void {
    this.usuarioActual.set(usuario);
    localStorage.setItem('usuario', JSON.stringify(usuario));
  }

  private recuperarSesion(): void {
    const datos = localStorage.getItem('usuario');
    if (datos) {
      try {
        this.usuarioActual.set(JSON.parse(datos));
      } catch {
        localStorage.removeItem('usuario');
      }
    }
  }

  private delay(ms: number): Promise<void> {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}
```

```typescript
// Componente de login que usa el servicio
@Component({
  selector: 'app-login',
  standalone: true,
  imports: [FormsModule],
  template: `
    <form (ngSubmit)="onLogin()">
      <input [(ngModel)]="email" name="email" type="email" required>
      <input [(ngModel)]="password" name="password" type="password" required>
      <button type="submit" [disabled]="cargando">
        {{ cargando ? 'Entrando...' : 'Iniciar sesión' }}
      </button>
      @if (error) {
        <p class="error">{{ error }}</p>
      }
    </form>
  `
})
export class LoginComponent {
  email = '';
  password = '';
  error = '';
  cargando = false;

  private auth = inject(AuthService);
  private router = inject(Router);

  async onLogin(): Promise<void> {
    this.error = '';
    this.cargando = true;

    const exito = await this.auth.login(this.email, this.password);

    this.cargando = false;

    if (exito) {
      this.router.navigate(['/dashboard']);
    } else {
      this.error = 'Credenciales incorrectas';
    }
  }
}
```

---

## Inyección de tokens — InjectionToken

Para inyectar valores primitivos (strings, números, objetos de configuración):

```typescript
import { InjectionToken, inject } from '@angular/core';

// Definir el token
export const API_URL = new InjectionToken<string>('api.url');
export const APP_CONFIG = new InjectionToken<{ debug: boolean; version: string }>('app.config');

// Registrar en la configuración de la app (app.config.ts)
export const appConfig = {
  providers: [
    { provide: API_URL, useValue: 'https://api.miapp.com' },
    {
      provide: APP_CONFIG,
      useValue: { debug: false, version: '1.0.0' }
    }
  ]
};

// Usar en cualquier componente o servicio
@Injectable({ providedIn: 'root' })
export class ApiService {
  private apiUrl = inject(API_URL);

  obtenerUrl(ruta: string): string {
    return `${this.apiUrl}/${ruta}`;
  }
}
```

---

## Servicios con fábrica

```typescript
// Diferentes implementaciones del mismo servicio
abstract class LoggerService {
  abstract log(mensaje: string): void;
  abstract error(mensaje: string): void;
}

class ConsoleLogger extends LoggerService {
  log(mensaje: string): void { console.log(`[LOG] ${mensaje}`); }
  error(mensaje: string): void { console.error(`[ERROR] ${mensaje}`); }
}

class RemoteLogger extends LoggerService {
  log(mensaje: string): void {
    fetch('/api/logs', { method: 'POST', body: JSON.stringify({ nivel: 'info', mensaje }) });
  }
  error(mensaje: string): void {
    fetch('/api/logs', { method: 'POST', body: JSON.stringify({ nivel: 'error', mensaje }) });
  }
}

// En app.config.ts — decide qué implementación usar según el entorno
const entorno = { produccion: true };

export const appConfig = {
  providers: [
    {
      provide: LoggerService,
      useFactory: () => entorno.produccion ? new RemoteLogger() : new ConsoleLogger()
    }
  ]
};
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| `@Injectable({ providedIn: 'root' })` | Singleton global |
| `@Injectable()` + `providers: []` | Instancia por componente |
| `constructor(private servicio: Servicio)` | Inyección tradicional |
| `inject(Servicio)` | Inyección moderna (Angular 14+) |
| `InjectionToken` | Inyectar valores primitivos/objetos |
| `useFactory` | Instancias condicionales con fábrica |
