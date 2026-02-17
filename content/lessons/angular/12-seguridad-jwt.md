# Seguridad: XSS, DomSanitizer y JWT con Laravel

La seguridad en aplicaciones Angular tiene tres frentes principales: **protección contra XSS** (Cross-Site Scripting), **sanitización del DOM** con `DomSanitizer`, y la integración con APIs autenticadas mediante **tokens JWT**, incluyendo el patrón de **refresh token automático**.

---

## XSS y el sistema de sanitización de Angular

Angular **sanitiza automáticamente** el contenido antes de insertarlo en el DOM. Esto previene la mayoría de ataques XSS sin que tengas que hacer nada extra.

```typescript
@Component({
  template: `
    <!-- Angular escapa automáticamente — SEGURO -->
    <p>{{ textoUsuario }}</p>

    <!-- Angular sanitiza el HTML — SEGURO (elimina scripts) -->
    <div [innerHTML]="htmlUsuario"></div>
  `
})
export class EjemploComponent {
  textoUsuario = '<script>alert("xss")</script>';  // Se muestra como texto plano
  htmlUsuario = '<b>Negrita</b><script>alert("xss")</script>';
  // Angular elimina el <script> y renderiza solo <b>Negrita</b>
}
```

Angular sanitiza 5 contextos de seguridad:

| Contexto | Binding | Qué sanitiza |
|---|---|---|
| **HTML** | `[innerHTML]` | Tags y atributos peligrosos |
| **Style** | `[style]` | CSS con expresiones maliciosas |
| **URL** | `href`, `src` | URLs `javascript:` |
| **Resource URL** | `<script src>`, `<iframe src>` | Solo permite URLs de confianza |
| **Script** | No aplica — Angular nunca inserta scripts dinámicos | — |

---

## DomSanitizer — Confiar en contenido de fuentes conocidas

A veces necesitas insertar HTML o URLs que Angular bloquea por defecto. `DomSanitizer` te permite marcarlos explícitamente como seguros:

```typescript
import { Component, inject } from '@angular/core';
import { DomSanitizer, SafeHtml, SafeUrl, SafeResourceUrl } from '@angular/platform-browser';

@Component({
  selector: 'app-contenido-rico',
  standalone: true,
  template: `
    <!-- HTML de CMS externo sanitizado manualmente -->
    <div [innerHTML]="contenidoSeguro"></div>

    <!-- URL de video embed -->
    <iframe [src]="videoUrl" width="560" height="315"></iframe>

    <!-- Enlace a PDF -->
    <a [href]="enlacePdf" target="_blank">Descargar PDF</a>
  `
})
export class ContenidoRicoComponent {
  private sanitizer = inject(DomSanitizer);

  // SafeHtml — para [innerHTML] con HTML de confianza
  contenidoSeguro: SafeHtml;

  // SafeResourceUrl — para src de iframes
  videoUrl: SafeResourceUrl;

  // SafeUrl — para href de enlaces
  enlacePdf: SafeUrl;

  constructor() {
    const htmlCms = '<h2>Título</h2><p>Párrafo con <strong>negrita</strong></p>';
    this.contenidoSeguro = this.sanitizer.bypassSecurityTrustHtml(htmlCms);

    this.videoUrl = this.sanitizer.bypassSecurityTrustResourceUrl(
      'https://www.youtube.com/embed/dQw4w9WgXcQ'
    );

    this.enlacePdf = this.sanitizer.bypassSecurityTrustUrl(
      'https://mi-sitio.com/docs/manual.pdf'
    );
  }
}
```

> ⚠️ **NUNCA** uses `bypassSecurityTrust*` con contenido que venga del usuario. Solo úsalo con contenido que **tú controlas** (tu CMS, tus URLs, tus embeds).

### Pipe personalizado para sanitizar HTML de CMS

```typescript
import { Pipe, PipeTransform, inject } from '@angular/core';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';

@Pipe({ name: 'safeHtml', standalone: true })
export class SafeHtmlPipe implements PipeTransform {
  private sanitizer = inject(DomSanitizer);

  transform(valor: string): SafeHtml {
    return this.sanitizer.bypassSecurityTrustHtml(valor);
  }
}
```

```html
<!-- Uso en template -->
<div [innerHTML]="articulo.contenidoHtml | safeHtml"></div>
```

---

## Autenticación JWT con Laravel

### Flujo completo

```
1. Login → POST /api/auth/login  →  { access_token, refresh_token, expires_in }
2. Cada request → Authorization: Bearer <access_token>
3. Token expira → POST /api/auth/refresh  →  nuevo access_token
4. Logout → POST /api/auth/logout  →  invalida el token en el servidor
```

### Servicio de autenticación

```typescript
// services/auth.service.ts
import { Injectable, inject, signal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { tap, catchError } from 'rxjs/operators';
import { Observable, throwError } from 'rxjs';
import { environment } from '../environments/environment';

export interface LoginResponse {
  access_token: string;
  refresh_token: string;
  token_type: string;
  expires_in: number;  // segundos hasta expiración
}

export interface Usuario {
  id: number;
  nombre: string;
  email: string;
  rol: string;
}

@Injectable({ providedIn: 'root' })
export class AuthService {
  private http = inject(HttpClient);
  private router = inject(Router);
  private apiUrl = environment.apiUrl;

  // Estado con Signals
  private _usuario = signal<Usuario | null>(null);
  readonly usuario = this._usuario.asReadonly();
  readonly estaLogueado = computed(() => !!this.obtenerToken());

  constructor() {
    this.cargarUsuarioDesdeStorage();
  }

  // ─── Login ───
  login(email: string, password: string): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.apiUrl}/auth/login`, { email, password }).pipe(
      tap(resp => this.guardarTokens(resp)),
      tap(() => this.cargarPerfil())
    );
  }

  // ─── Logout ───
  logout(): void {
    const refreshToken = this.obtenerRefreshToken();
    // Invalidar en el servidor (best effort — no esperamos la respuesta)
    if (refreshToken) {
      this.http.post(`${this.apiUrl}/auth/logout`, { refresh_token: refreshToken })
        .subscribe({ error: () => {} });
    }
    this.limpiarStorage();
    this._usuario.set(null);
    this.router.navigate(['/login']);
  }

  // ─── Refresh del token ───
  refrescarToken(): Observable<LoginResponse> {
    const refreshToken = this.obtenerRefreshToken();
    if (!refreshToken) {
      return throwError(() => new Error('No hay refresh token'));
    }
    return this.http.post<LoginResponse>(`${this.apiUrl}/auth/refresh`, {
      refresh_token: refreshToken
    }).pipe(
      tap(resp => this.guardarTokens(resp)),
      catchError(err => {
        this.limpiarStorage();
        this.router.navigate(['/login']);
        return throwError(() => err);
      })
    );
  }

  // ─── Helpers de tokens ───
  obtenerToken(): string | null {
    return localStorage.getItem('access_token');
  }

  private obtenerRefreshToken(): string | null {
    return localStorage.getItem('refresh_token');
  }

  private guardarTokens(resp: LoginResponse): void {
    localStorage.setItem('access_token', resp.access_token);
    localStorage.setItem('refresh_token', resp.refresh_token);
    // Guardar timestamp de expiración
    const expira = Date.now() + resp.expires_in * 1000;
    localStorage.setItem('token_expira', String(expira));
  }

  tokenEstaExpirado(): boolean {
    const expira = localStorage.getItem('token_expira');
    if (!expira) return true;
    return Date.now() >= Number(expira) - 30_000;  // 30s de margen
  }

  private limpiarStorage(): void {
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    localStorage.removeItem('token_expira');
    localStorage.removeItem('usuario');
  }

  private cargarPerfil(): void {
    this.http.get<Usuario>(`${this.apiUrl}/auth/me`).subscribe(
      usuario => {
        this._usuario.set(usuario);
        localStorage.setItem('usuario', JSON.stringify(usuario));
      }
    );
  }

  private cargarUsuarioDesdeStorage(): void {
    const datos = localStorage.getItem('usuario');
    if (datos) {
      try { this._usuario.set(JSON.parse(datos)); }
      catch { localStorage.removeItem('usuario'); }
    }
  }
}
```

---

## Interceptor con refresh token automático

Este es el patrón más importante para integración JWT. Cuando el servidor retorna `401`, el interceptor refresca el token **automáticamente** y reintenta la petición original:

```typescript
// interceptors/auth.interceptor.ts
import { HttpInterceptorFn, HttpRequest, HttpHandlerFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { catchError, switchMap, throwError, BehaviorSubject, filter, take } from 'rxjs';
import { AuthService } from '../services/auth.service';

// Previene múltiples refreshes simultáneos
let refrescando = false;
const tokenRefrescado$ = new BehaviorSubject<string | null>(null);

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const auth = inject(AuthService);
  const reqConToken = agregarToken(req, auth.obtenerToken());

  return next(reqConToken).pipe(
    catchError((error: HttpErrorResponse) => {
      // Solo manejamos 401 (no autorizado)
      if (error.status !== 401) {
        return throwError(() => error);
      }

      // Si ya estamos refrescando, esperamos al nuevo token
      if (refrescando) {
        return tokenRefrescado$.pipe(
          filter(token => token !== null),
          take(1),
          switchMap(token => next(agregarToken(req, token!)))
        );
      }

      // Iniciar el proceso de refresh
      refrescando = true;
      tokenRefrescado$.next(null);

      return auth.refrescarToken().pipe(
        switchMap(resp => {
          refrescando = false;
          tokenRefrescado$.next(resp.access_token);
          // Reintentar la petición original con el nuevo token
          return next(agregarToken(req, resp.access_token));
        }),
        catchError(err => {
          refrescando = false;
          // El refresh falló — el servicio ya redirige al login
          return throwError(() => err);
        })
      );
    })
  );
};

function agregarToken(req: HttpRequest<unknown>, token: string | null): HttpRequest<unknown> {
  if (!token) return req;
  return req.clone({
    setHeaders: { Authorization: `Bearer ${token}` }
  });
}
```

### Registrar el interceptor

```typescript
// app.config.ts
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { authInterceptor } from './interceptors/auth.interceptor';

export const appConfig = {
  providers: [
    provideHttpClient(withInterceptors([authInterceptor]))
  ]
};
```

---

## API en Laravel (referencia)

Para la parte del backend, Laravel con `tymon/jwt-auth` o Laravel Sanctum:

```php
// routes/api.php
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
});

Route::middleware('auth:api')->group(function () {
    Route::apiResource('productos', ProductoController::class);
});
```

```php
// app/Http/Controllers/AuthController.php
class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function logout(): JsonResponse
    {
        auth()->logout();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    private function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token'  => $token,
            'refresh_token' => auth()->refresh(),
            'token_type'    => 'bearer',
            'expires_in'    => auth()->factory()->getTTL() * 60,
        ]);
    }
}
```

---

## Headers de seguridad en Laravel (CORS)

```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://tu-app-angular.com'],  // No '*' en producción
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

---

## Guard de ruta basado en JWT

```typescript
// guards/auth.guard.ts
import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const authGuard: CanActivateFn = (route, state) => {
  const auth = inject(AuthService);
  const router = inject(Router);

  if (auth.estaLogueado() && !auth.tokenEstaExpirado()) {
    return true;
  }

  router.navigate(['/login'], { queryParams: { returnUrl: state.url } });
  return false;
};

// Guard por roles
export const rolGuard = (rolesPermitidos: string[]): CanActivateFn => () => {
  const auth = inject(AuthService);
  const router = inject(Router);

  const rol = auth.usuario()?.rol;
  if (rol && rolesPermitidos.includes(rol)) {
    return true;
  }

  router.navigate(['/sin-permiso']);
  return false;
};
```

```typescript
// Uso en rutas
export const routes: Routes = [
  { path: 'admin', component: AdminComponent, canActivate: [authGuard, rolGuard(['admin'])] },
  { path: 'editor', component: EditorComponent, canActivate: [authGuard, rolGuard(['admin', 'editor'])] }
];
```

---

## Resumen de buenas prácticas de seguridad

| Práctica | Por qué |
|---|---|
| Nunca construir HTML concatenando strings | Usar templates de Angular |
| Usar `bypassSecurityTrust*` solo con contenido propio | Confiar solo en fuentes controladas |
| Almacenar tokens en `localStorage` con cuidado | Considerar `httpOnly cookies` para mayor seguridad |
| Validar en el servidor siempre | El cliente siempre puede ser manipulado |
| Configurar CORS restrictivo en Laravel | No usar `*` en producción |
| Refresh token automático con interceptor | Mejor UX sin re-login constante |
| Expiración corta del access token (15-60 min) | Minimiza ventana de ataque |
