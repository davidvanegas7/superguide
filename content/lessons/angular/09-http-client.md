# HTTP Client y APIs REST

Angular incluye `HttpClient`, un servicio potente para comunicarse con APIs REST. Retorna **Observables** de RxJS, lo que permite composición, cancelación y manejo de errores avanzado.

---

## Configuración inicial

```typescript
// app.config.ts
import { ApplicationConfig } from '@angular/core';
import { provideHttpClient, withInterceptors } from '@angular/common/http';

export const appConfig: ApplicationConfig = {
  providers: [
    provideHttpClient(
      withInterceptors([authInterceptor, errorInterceptor])  // Interceptores
    )
  ]
};
```

---

## Uso básico

```typescript
import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Post {
  id: number;
  userId: number;
  title: string;
  body: string;
}

export interface NuevoPost {
  userId: number;
  title: string;
  body: string;
}

@Injectable({ providedIn: 'root' })
export class PostsService {
  private http = inject(HttpClient);
  private apiUrl = 'https://jsonplaceholder.typicode.com';

  // GET — Obtener todos
  obtenerTodos(): Observable<Post[]> {
    return this.http.get<Post[]>(`${this.apiUrl}/posts`);
  }

  // GET — Obtener uno por ID
  obtenerPorId(id: number): Observable<Post> {
    return this.http.get<Post>(`${this.apiUrl}/posts/${id}`);
  }

  // GET — Con query params
  buscar(userId: number, pagina: number = 1): Observable<Post[]> {
    const params = new HttpParams()
      .set('userId', userId)
      .set('_page', pagina)
      .set('_limit', 10);

    return this.http.get<Post[]>(`${this.apiUrl}/posts`, { params });
  }

  // POST — Crear
  crear(datos: NuevoPost): Observable<Post> {
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('token')}`
    });

    return this.http.post<Post>(`${this.apiUrl}/posts`, datos, { headers });
  }

  // PUT — Actualizar completo
  actualizar(id: number, datos: Post): Observable<Post> {
    return this.http.put<Post>(`${this.apiUrl}/posts/${id}`, datos);
  }

  // PATCH — Actualizar parcial
  actualizarParcial(id: number, cambios: Partial<Post>): Observable<Post> {
    return this.http.patch<Post>(`${this.apiUrl}/posts/${id}`, cambios);
  }

  // DELETE — Eliminar
  eliminar(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/posts/${id}`);
  }
}
```

---

## Consumir el servicio en un componente

```typescript
import { Component, OnInit, inject } from '@angular/core';
import { PostsService, Post } from '../services/posts.service';
import { AsyncPipe } from '@angular/common';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-posts',
  standalone: true,
  imports: [AsyncPipe],
  template: `
    @if (posts$ | async; as posts) {
      @for (post of posts; track post.id) {
        <article>
          <h3>{{ post.title }}</h3>
          <p>{{ post.body }}</p>
          <button (click)="eliminar(post.id)">Eliminar</button>
        </article>
      }
    } @else {
      <p>Cargando posts...</p>
    }

    @if (error) {
      <p class="error">{{ error }}</p>
    }
  `
})
export class PostsComponent implements OnInit {
  private postsService = inject(PostsService);

  posts$!: Observable<Post[]>;
  error = '';

  ngOnInit(): void {
    this.cargarPosts();
  }

  cargarPosts(): void {
    this.posts$ = this.postsService.obtenerTodos();
  }

  eliminar(id: number): void {
    this.postsService.eliminar(id).subscribe({
      next: () => {
        console.log('Post eliminado');
        this.cargarPosts();  // Recargar la lista
      },
      error: (err) => {
        this.error = 'No se pudo eliminar el post';
        console.error(err);
      }
    });
  }
}
```

---

## Manejo de errores con `catchError`

```typescript
import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, retry, map } from 'rxjs/operators';

@Injectable({ providedIn: 'root' })
export class ApiService {
  private http = inject(HttpClient);

  obtenerDatos<T>(url: string): Observable<T> {
    return this.http.get<T>(url).pipe(
      retry(2),              // Reintentar 2 veces ante fallos
      catchError(this.manejarError)
    );
  }

  private manejarError(error: HttpErrorResponse): Observable<never> {
    let mensajeError = '';

    if (error.status === 0) {
      // Error de red o del cliente
      mensajeError = `Error de red: ${error.message}`;
    } else {
      // El servidor retornó un código de error
      switch (error.status) {
        case 400: mensajeError = 'Solicitud inválida'; break;
        case 401: mensajeError = 'No autorizado. Inicia sesión'; break;
        case 403: mensajeError = 'No tienes permiso para esta acción'; break;
        case 404: mensajeError = 'Recurso no encontrado'; break;
        case 422: mensajeError = 'Datos de formulario inválidos'; break;
        case 500: mensajeError = 'Error interno del servidor'; break;
        default:  mensajeError = `Error ${error.status}: ${error.message}`; break;
      }
    }

    console.error('Error HTTP:', error);
    return throwError(() => new Error(mensajeError));
  }
}
```

---

## Interceptores

Los interceptores permiten modificar **todas** las peticiones HTTP de forma centralizada:

### Interceptor de autenticación

```typescript
// auth.interceptor.ts
import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const auth = inject(AuthService);
  const token = auth.getToken();

  if (token) {
    // Clonar la request y agregar el header de autorización
    const reqConToken = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
    return next(reqConToken);
  }

  return next(req);
};
```

### Interceptor de errores globales

```typescript
// error.interceptor.ts
import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';

export const errorInterceptor: HttpInterceptorFn = (req, next) => {
  const router = inject(Router);

  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      if (error.status === 401) {
        // Token expirado — redirigir al login
        localStorage.removeItem('token');
        router.navigate(['/login']);
      }
      if (error.status === 503) {
        router.navigate(['/mantenimiento']);
      }
      return throwError(() => error);
    })
  );
};
```

### Interceptor de loading

```typescript
// loading.interceptor.ts
import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { LoadingService } from '../services/loading.service';
import { finalize } from 'rxjs/operators';

export const loadingInterceptor: HttpInterceptorFn = (req, next) => {
  const loading = inject(LoadingService);
  loading.mostrar();

  return next(req).pipe(
    finalize(() => loading.ocultar())
  );
};
```

---

## Obtener headers y metadata de la respuesta

```typescript
// Por defecto, HttpClient solo retorna el body
this.http.get<Post[]>('/api/posts')  // retorna Post[]

// Para obtener la respuesta completa
this.http.get<Post[]>('/api/posts', { observe: 'response' }).subscribe(resp => {
  console.log(resp.status);     // 200
  console.log(resp.headers.get('X-Total-Count'));  // Total de registros (para paginación)
  console.log(resp.body);       // Post[]
});

// Solo los headers
this.http.get('/api/posts', { observe: 'events' }).subscribe(event => {
  // HttpSentEvent, HttpHeaderResponse, HttpResponse, HttpProgressEvent...
});
```

---

## Subir archivos

```typescript
subirArchivo(archivo: File): Observable<any> {
  const formData = new FormData();
  formData.append('archivo', archivo, archivo.name);
  formData.append('tipo', 'imagen');

  return this.http.post('/api/upload', formData, {
    reportProgress: true,
    observe: 'events'
  }).pipe(
    map(event => {
      if (event.type === HttpEventType.UploadProgress) {
        const progreso = Math.round(100 * event.loaded / (event.total ?? 1));
        return { tipo: 'progreso', porcentaje: progreso };
      }
      if (event.type === HttpEventType.Response) {
        return { tipo: 'completado', body: event.body };
      }
      return { tipo: 'otro' };
    })
  );
}
```

```typescript
// En el componente
@Component({
  template: `
    <input type="file" (change)="onFileChange($event)">
    @if (progreso > 0 && progreso < 100) {
      <progress [value]="progreso" max="100"></progress>
    }
    @if (archivoSubido) {
      <p>✅ Archivo subido correctamente</p>
    }
  `
})
export class SubidaComponent {
  progreso = 0;
  archivoSubido = false;

  private apiService = inject(ApiService);

  onFileChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;

    const archivo = input.files[0];
    this.apiService.subirArchivo(archivo).subscribe(resultado => {
      if (resultado.tipo === 'progreso') {
        this.progreso = resultado.porcentaje;
      }
      if (resultado.tipo === 'completado') {
        this.archivoSubido = true;
        this.progreso = 100;
      }
    });
  }
}
```

---

## Caché simple de peticiones

```typescript
@Injectable({ providedIn: 'root' })
export class CacheService<T> {
  private cache = new Map<string, { datos: T; timestamp: number }>();
  private TTL = 5 * 60 * 1000;  // 5 minutos en ms

  guardar(clave: string, datos: T): void {
    this.cache.set(clave, { datos, timestamp: Date.now() });
  }

  obtener(clave: string): T | null {
    const entrada = this.cache.get(clave);
    if (!entrada) return null;

    const expirado = Date.now() - entrada.timestamp > this.TTL;
    if (expirado) {
      this.cache.delete(clave);
      return null;
    }

    return entrada.datos;
  }

  limpiar(clave?: string): void {
    if (clave) this.cache.delete(clave);
    else this.cache.clear();
  }
}

// Servicio con caché
@Injectable({ providedIn: 'root' })
export class ProductosService {
  private http = inject(HttpClient);
  private cache = inject(CacheService<Producto[]>);

  obtenerTodos(): Observable<Producto[]> {
    const cacheKey = 'productos-todos';
    const cached = this.cache.obtener(cacheKey);

    if (cached) {
      return of(cached);  // Retorna desde caché
    }

    return this.http.get<Producto[]>('/api/productos').pipe(
      tap(datos => this.cache.guardar(cacheKey, datos))
    );
  }
}
```

---

## Resumen

| Método HTTP | Uso | Observable retornado |
|---|---|---|
| `get<T>(url)` | Obtener datos | `Observable<T>` |
| `post<T>(url, body)` | Crear recurso | `Observable<T>` |
| `put<T>(url, body)` | Actualizar completo | `Observable<T>` |
| `patch<T>(url, body)` | Actualizar parcial | `Observable<T>` |
| `delete<T>(url)` | Eliminar | `Observable<T>` |
| `head(url)` | Solo headers | `Observable<HttpResponse>` |

| Opción | Descripción |
|---|---|
| `{ params }` | Query parameters |
| `{ headers }` | Headers HTTP |
| `{ observe: 'response' }` | Respuesta completa |
| `{ reportProgress: true }` | Progreso de carga |
| `{ responseType: 'blob' }` | Descarga de archivos |
