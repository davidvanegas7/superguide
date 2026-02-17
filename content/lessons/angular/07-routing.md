# Routing en Angular

El **Router de Angular** gestiona la navegación entre vistas en aplicaciones Single Page Application (SPA). Permite que diferentes URLs muestren diferentes componentes, sin recargar la página.

---

## Configuración inicial

### Definir las rutas

```typescript
// app.routes.ts
import { Routes } from '@angular/router';
import { HomeComponent } from './pages/home/home.component';
import { SobreNosotrosComponent } from './pages/sobre-nosotros/sobre-nosotros.component';
import { ProductosComponent } from './pages/productos/productos.component';
import { ProductoDetalleComponent } from './pages/producto-detalle/producto-detalle.component';
import { NotFoundComponent } from './pages/not-found/not-found.component';

export const routes: Routes = [
  // Ruta raíz
  { path: '', component: HomeComponent },

  // Rutas simples
  { path: 'sobre-nosotros', component: SobreNosotrosComponent },
  { path: 'productos', component: ProductosComponent },

  // Ruta con parámetro
  { path: 'productos/:id', component: ProductoDetalleComponent },

  // Ruta con múltiples parámetros
  { path: 'cursos/:categoriaId/lecciones/:leccionId', component: LeccionComponent },

  // Redirección
  { path: 'inicio', redirectTo: '', pathMatch: 'full' },

  // Ruta comodín — debe ir SIEMPRE al final
  { path: '**', component: NotFoundComponent }
];
```

### Registrar el router en la app

```typescript
// app.config.ts
import { ApplicationConfig } from '@angular/core';
import { provideRouter, withRouterConfig } from '@angular/router';
import { routes } from './app.routes';

export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(
      routes,
      withRouterConfig({ onSameUrlNavigation: 'reload' })
    )
  ]
};
```

### Agregar el outlet en el template

```typescript
// app.component.ts
import { Component } from '@angular/core';
import { RouterOutlet, RouterLink, RouterLinkActive } from '@angular/router';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, RouterLink, RouterLinkActive],
  template: `
    <nav>
      <a routerLink="/" routerLinkActive="activo" [routerLinkActiveOptions]="{exact:true}">
        Inicio
      </a>
      <a routerLink="/productos" routerLinkActive="activo">Productos</a>
      <a routerLink="/sobre-nosotros" routerLinkActive="activo">Nosotros</a>
    </nav>

    <!-- Aquí se renderizan los componentes de cada ruta -->
    <router-outlet></router-outlet>
  `
})
export class AppComponent {}
```

---

## Parámetros de ruta

### Leer parámetros con `ActivatedRoute`

```typescript
import { Component, OnInit, inject } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { ProductosService } from '../services/productos.service';

@Component({
  selector: 'app-producto-detalle',
  standalone: true,
  template: `
    @if (producto) {
      <h1>{{ producto.nombre }}</h1>
      <p>{{ producto.descripcion }}</p>
    } @else if (cargando) {
      <p>Cargando...</p>
    } @else {
      <p>Producto no encontrado</p>
    }
  `
})
export class ProductoDetalleComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private productosService = inject(ProductosService);

  producto: any = null;
  cargando = true;

  ngOnInit(): void {
    // Forma 1: snapshot (solo el valor inicial, no reactivo)
    const id = Number(this.route.snapshot.paramMap.get('id'));
    this.cargarProducto(id);

    // Forma 2: Observable (reactivo — detecta cambios en la URL)
    this.route.paramMap.subscribe(params => {
      const id = Number(params.get('id'));
      this.cargarProducto(id);
    });
  }

  private cargarProducto(id: number): void {
    this.cargando = true;
    this.producto = this.productosService.obtenerPorId(id);
    this.cargando = false;
  }
}
```

### Query Parameters

```typescript
// URL: /productos?categoria=electronica&orden=precio
ngOnInit(): void {
  // Snapshot
  const categoria = this.route.snapshot.queryParamMap.get('categoria');

  // Observable
  this.route.queryParamMap.subscribe(params => {
    const categoria = params.get('categoria');
    const orden = params.get('orden');
    const pagina = Number(params.get('pagina')) || 1;
  });
}
```

---

## Navegación programática

```typescript
import { Component, inject } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  standalone: true,
  template: `
    <button (click)="irAlInicio()">Ir al inicio</button>
    <button (click)="irAProducto(42)">Ver producto 42</button>
    <button (click)="irConQueryParams()">Buscar</button>
  `
})
export class LoginComponent {
  private router = inject(Router);

  irAlInicio(): void {
    this.router.navigate(['/']);
  }

  irAProducto(id: number): void {
    this.router.navigate(['/productos', id]);
    // equivale a: /productos/42
  }

  irConQueryParams(): void {
    this.router.navigate(['/productos'], {
      queryParams: { categoria: 'electronica', pagina: 1 }
    });
    // equivale a: /productos?categoria=electronica&pagina=1
  }

  // Navegación relativa
  irARelativo(): void {
    this.router.navigate(['../otra-ruta'], { relativeTo: this.route });
  }

  // Reemplazar en el historial (sin crear nueva entrada)
  irSinHistorial(): void {
    this.router.navigate(['/home'], { replaceUrl: true });
  }
}
```

---

## Rutas anidadas (Child Routes)

```typescript
// app.routes.ts
export const routes: Routes = [
  {
    path: 'admin',
    component: AdminLayoutComponent,
    children: [
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
      { path: 'dashboard', component: AdminDashboardComponent },
      { path: 'usuarios', component: AdminUsuariosComponent },
      { path: 'usuarios/:id/editar', component: EditarUsuarioComponent },
      { path: 'productos', component: AdminProductosComponent }
    ]
  }
];
```

```typescript
// admin-layout.component.ts — debe tener su propio <router-outlet>
@Component({
  selector: 'app-admin-layout',
  standalone: true,
  imports: [RouterOutlet, RouterLink, RouterLinkActive],
  template: `
    <aside>
      <nav>
        <a routerLink="dashboard" routerLinkActive="activo">Dashboard</a>
        <a routerLink="usuarios" routerLinkActive="activo">Usuarios</a>
        <a routerLink="productos" routerLinkActive="activo">Productos</a>
      </nav>
    </aside>
    <main>
      <router-outlet></router-outlet>  <!-- Rutas hijas aquí -->
    </main>
  `
})
export class AdminLayoutComponent {}
```

---

## Lazy Loading — Carga perezosa

El lazy loading carga el código de una ruta **solo cuando se accede a ella**, reduciendo el bundle inicial:

```typescript
// app.routes.ts
export const routes: Routes = [
  { path: '', component: HomeComponent },

  // Lazy loading de un componente standalone
  {
    path: 'admin',
    loadComponent: () =>
      import('./pages/admin/admin.component').then(m => m.AdminComponent)
  },

  // Lazy loading de un grupo de rutas (más común)
  {
    path: 'tienda',
    loadChildren: () =>
      import('./pages/tienda/tienda.routes').then(m => m.tiendaRoutes)
  }
];
```

```typescript
// tienda/tienda.routes.ts — rutas del módulo tienda
import { Routes } from '@angular/router';

export const tiendaRoutes: Routes = [
  { path: '', component: TiendaHomeComponent },
  { path: 'catalogo', component: CatalogoComponent },
  { path: 'carrito', component: CarritoComponent },
  { path: 'checkout', component: CheckoutComponent }
];
```

---

## Guards — Rutas protegidas

Los Guards deciden si se puede acceder a una ruta:

### `canActivate` — Proteger acceso

```typescript
// auth.guard.ts
import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const authGuard: CanActivateFn = (route, state) => {
  const auth = inject(AuthService);
  const router = inject(Router);

  if (auth.estaLogueado()) {
    return true;
  }

  // Guardar la URL a la que intentaba acceder
  router.navigate(['/login'], {
    queryParams: { returnUrl: state.url }
  });
  return false;
};

// Guard para verificar rol
export const adminGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  const router = inject(Router);

  if (auth.esAdmin()) {
    return true;
  }

  router.navigate(['/sin-permiso']);
  return false;
};
```

```typescript
// Aplicar los guards en las rutas
export const routes: Routes = [
  { path: 'login', component: LoginComponent },
  {
    path: 'perfil',
    component: PerfilComponent,
    canActivate: [authGuard]   // ← Solo accesible si está logueado
  },
  {
    path: 'admin',
    component: AdminComponent,
    canActivate: [authGuard, adminGuard]  // ← Debe estar logueado Y ser admin
  }
];
```

### `canDeactivate` — Prevenir salida

```typescript
// unsaved-changes.guard.ts
import { CanDeactivateFn } from '@angular/router';

export interface PuedeSalir {
  tieneCambiosSinGuardar(): boolean;
}

export const unsavedChangesGuard: CanDeactivateFn<PuedeSalir> = (componente) => {
  if (componente.tieneCambiosSinGuardar()) {
    return confirm('Tienes cambios sin guardar. ¿Deseas salir?');
  }
  return true;
};
```

```typescript
// Componente de formulario
@Component({ selector: 'app-editar-perfil', ... })
export class EditarPerfilComponent implements PuedeSalir {
  formularioModificado = false;

  tieneCambiosSinGuardar(): boolean {
    return this.formularioModificado;
  }
}

// En las rutas
{ path: 'editar-perfil', component: EditarPerfilComponent, canDeactivate: [unsavedChangesGuard] }
```

---

## Resolvers — Precargar datos antes de entrar a una ruta

```typescript
// producto.resolver.ts
import { ResolveFn } from '@angular/router';
import { inject } from '@angular/core';
import { ProductosService } from '../services/productos.service';

export const productoResolver: ResolveFn<Producto> = (route) => {
  const id = Number(route.paramMap.get('id'));
  return inject(ProductosService).obtenerPorId(id);
  // También puede retornar un Observable o Promise
};
```

```typescript
// En las rutas
{ path: 'productos/:id', component: ProductoDetalleComponent, resolve: { producto: productoResolver } }

// En el componente — los datos ya están disponibles
ngOnInit(): void {
  this.producto = this.route.snapshot.data['producto'];
}
```

---

## RouterLink avanzado

```html
<!-- Link simple -->
<a routerLink="/productos">Productos</a>

<!-- Link con parámetros -->
<a [routerLink]="['/productos', producto.id]">Ver producto</a>

<!-- Link con query params -->
<a [routerLink]="['/buscar']" [queryParams]="{q: 'angular', pagina: 1}">
  Buscar Angular
</a>

<!-- Link con fragment (#seccion) -->
<a [routerLink]="['/docs']" fragment="instalacion">
  Ir a instalación
</a>

<!-- Preservar query params existentes -->
<a [routerLink]="['/otra-pagina']" queryParamsHandling="preserve">
  Otra página
</a>

<!-- Clase activa personalizada -->
<a routerLink="/inicio" routerLinkActive="mi-clase-activa">Inicio</a>

<!-- Solo activo en ruta exacta (no en sub-rutas) -->
<a routerLink="/" routerLinkActive="activo"
   [routerLinkActiveOptions]="{exact: true}">
  Inicio
</a>
```

---

## Configuración del router

```typescript
// app.config.ts
import { provideRouter, withHashLocation, withViewTransitions, withPreloading, PreloadAllModules } from '@angular/router';

export const appConfig = {
  providers: [
    provideRouter(
      routes,
      withHashLocation(),           // URLs con # (para hosting sin configuración)
      withViewTransitions(),         // Transiciones animadas entre vistas (Chrome)
      withPreloading(PreloadAllModules)  // Precarga todos los módulos lazy en segundo plano
    )
  ]
};
```

---

## Resumen

| Concepto | Función |
|---|---|
| `Routes` | Array de configuración de rutas |
| `RouterOutlet` | Donde se renderizan los componentes |
| `RouterLink` | Navegación declarativa en templates |
| `RouterLinkActive` | Clase CSS para link activo |
| `ActivatedRoute` | Leer parámetros de la ruta actual |
| `Router` | Navegación programática |
| `canActivate` | Guard para proteger acceso |
| `canDeactivate` | Guard para prevenir salida |
| `resolve` | Precargar datos antes de la ruta |
| `loadComponent` / `loadChildren` | Lazy loading |
