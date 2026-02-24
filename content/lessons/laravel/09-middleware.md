---
title: "Middleware"
slug: "laravel-middleware"
description: "Aprende a crear y usar middleware en Laravel para filtrar peticiones HTTP, proteger rutas, limitar tasas de acceso y ejecutar lógica antes y después de las respuestas."
---

# Middleware

El middleware proporciona un mecanismo para inspeccionar y filtrar las peticiones HTTP que entran a tu aplicación. Puedes imaginar el middleware como una serie de capas que cada petición debe atravesar antes de llegar al controlador. Cada capa puede examinar la petición, modificarla, rechazarla o incluso modificar la respuesta de salida.

## ¿Qué es un Middleware?

Un middleware es una clase con un método `handle()` que recibe la petición y un closure que representa la siguiente capa:

```
Petición HTTP → Middleware 1 → Middleware 2 → ... → Controlador
                                                         ↓
Respuesta HTTP ← Middleware 1 ← Middleware 2 ← ... ← Respuesta
```

Ejemplos comunes de middleware incluidos en Laravel:

- **`auth`**: Verifica que el usuario esté autenticado.
- **`throttle`**: Limita la tasa de peticiones (rate limiting).
- **`verified`**: Verifica que el email del usuario esté confirmado.
- **`csrf`**: Protege contra ataques CSRF (Cross-Site Request Forgery).

## Crear un Middleware

```bash
php artisan make:middleware VerificarEdad
```

```php
<?php
// app/Http/Middleware/VerificarEdad.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarEdad
{
    /**
     * Manejar la petición entrante.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario sea mayor de edad
        if ($request->user() && $request->user()->edad < 18) {
            // Opción 1: Redirigir
            return redirect()->route('acceso-denegado');

            // Opción 2: Abortar con error 403
            // abort(403, 'Debes ser mayor de 18 años.');

            // Opción 3: Devolver JSON (para APIs)
            // return response()->json(['error' => 'Acceso denegado'], 403);
        }

        // Continuar con la siguiente capa
        return $next($request);
    }
}
```

## Before vs After Middleware

### Before Middleware (antes del controlador)

Ejecuta lógica **antes** de que la petición llegue al controlador:

```php
class RegistrarIdioma
{
    public function handle(Request $request, Closure $next): Response
    {
        // Establecer el idioma ANTES de procesar la petición
        $locale = $request->header('Accept-Language', 'es');
        app()->setLocale(substr($locale, 0, 2));

        return $next($request);
    }
}
```

### After Middleware (después del controlador)

Ejecuta lógica **después** de que el controlador genera la respuesta:

```php
class AgregarCabecerasSeguridad
{
    public function handle(Request $request, Closure $next): Response
    {
        // Primero obtener la respuesta
        $response = $next($request);

        // Modificar la respuesta DESPUÉS del controlador
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
```

### Middleware combinado (before + after)

```php
class MedirTiempoRespuesta
{
    public function handle(Request $request, Closure $next): Response
    {
        // ANTES: Registrar el tiempo de inicio
        $inicio = microtime(true);

        // Procesar la petición
        $response = $next($request);

        // DESPUÉS: Calcular el tiempo transcurrido
        $duracion = round((microtime(true) - $inicio) * 1000, 2);

        // Añadir el tiempo como cabecera de respuesta
        $response->headers->set('X-Response-Time', $duracion . 'ms');

        // Registrar en el log si es lento
        if ($duracion > 500) {
            \Log::warning("Petición lenta: {$request->url()} ({$duracion}ms)");
        }

        return $response;
    }
}
```

## Registrar Middleware

### Middleware global

Se ejecuta en **todas** las peticiones HTTP. Se registra en `bootstrap/app.php`:

```php
// bootstrap/app.php
use App\Http\Middleware\MedirTiempoRespuesta;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        // Añadir middleware global
        $middleware->append(MedirTiempoRespuesta::class);
    })
    ->create();
```

### Middleware de ruta (alias)

Se asigna a rutas específicas y necesita un alias:

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'edad' => \App\Http\Middleware\VerificarEdad::class,
        'idioma' => \App\Http\Middleware\RegistrarIdioma::class,
        'rol' => \App\Http\Middleware\VerificarRol::class,
    ]);
})
```

```php
// routes/web.php
// Usar el middleware en rutas individuales
Route::get('/contenido-adulto', function () {
    return view('contenido-adulto');
})->middleware('edad');

// Usar en un grupo de rutas
Route::middleware(['auth', 'edad'])->group(function () {
    Route::get('/casino', [CasinoController::class, 'index']);
    Route::get('/apuestas', [ApuestasController::class, 'index']);
});

// Usar en un controlador de recurso
Route::resource('productos', ProductoController::class)
    ->middleware('auth');
```

### Grupos de middleware

Agrupan varios middleware bajo un solo nombre:

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->appendToGroup('admin', [
        \App\Http\Middleware\VerificarAdmin::class,
        \App\Http\Middleware\RegistrarAccionAdmin::class,
        \App\Http\Middleware\ForzarDosFactor::class,
    ]);
})
```

```php
// routes/web.php
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::resource('usuarios', AdminUsuarioController::class);
});
```

## Middleware con Parámetros

Puedes pasar parámetros al middleware desde la definición de la ruta:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Los parámetros se reciben después de $next.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (!$usuario || !in_array($usuario->rol, $roles)) {
            abort(403, 'No tienes el rol necesario para acceder a este recurso.');
        }

        return $next($request);
    }
}
```

```php
// routes/web.php
// Pasar un parámetro
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('rol:admin');

// Pasar múltiples parámetros
Route::get('/gestion', [GestionController::class, 'index'])
    ->middleware('rol:admin,editor,moderador');
```

### Ejemplo: Middleware de verificación de suscripción

```php
class VerificarSuscripcion
{
    public function handle(Request $request, Closure $next, string $plan = 'basico'): Response
    {
        $usuario = $request->user();

        if (!$usuario || !$usuario->tieneSuscripcion($plan)) {
            return redirect()->route('planes')
                ->with('error', "Necesitas el plan '$plan' para acceder a este contenido.");
        }

        return $next($request);
    }
}

// Uso
Route::get('/cursos-premium', [CursoController::class, 'premium'])
    ->middleware('suscripcion:premium');

Route::get('/cursos-pro', [CursoController::class, 'pro'])
    ->middleware('suscripcion:pro');
```

## Método `terminate()`

El método `terminate()` se ejecuta **después de que la respuesta se envía al navegador**. Es útil para tareas que no deben retrasar la respuesta:

```php
class RegistrarAnalytics
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Se ejecuta DESPUÉS de enviar la respuesta al cliente.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Esto no afecta el tiempo de respuesta del usuario
        \DB::table('analytics')->insert([
            'url' => $request->url(),
            'metodo' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'status_code' => $response->getStatusCode(),
            'tiempo_respuesta' => defined('LARAVEL_START')
                ? round((microtime(true) - LARAVEL_START) * 1000)
                : null,
            'created_at' => now(),
        ]);
    }
}
```

## Rate Limiting (Limitación de Tasa)

Laravel incluye un sistema flexible de rate limiting integrado:

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    // Límite global para la API: 60 peticiones por minuto
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });

    // Límite para login: 5 intentos por minuto
    RateLimiter::for('login', function (Request $request) {
        return Limit::perMinute(5)
            ->by($request->input('email') . '|' . $request->ip())
            ->response(function () {
                return response('Demasiados intentos. Espera un minuto.', 429);
            });
    });

    // Límites diferentes según el usuario
    RateLimiter::for('uploads', function (Request $request) {
        return $request->user()?->esPremium()
            ? Limit::perMinute(100)     // Premium: 100/minuto
            : Limit::perMinute(10);     // Gratis: 10/minuto
    });

    // Múltiples límites combinados
    RateLimiter::for('exportar', function (Request $request) {
        return [
            Limit::perMinute(10),       // Máximo 10 por minuto
            Limit::perHour(50),         // Y máximo 50 por hora
            Limit::perDay(200),         // Y máximo 200 por día
        ];
    });
}
```

```php
// routes/web.php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

Route::middleware('throttle:api')->group(function () {
    Route::get('/api/productos', [ApiProductoController::class, 'index']);
});

Route::post('/exportar', [ExportController::class, 'export'])
    ->middleware('throttle:exportar');
```

## Excluir Rutas de un Middleware

```php
// Excluir rutas específicas del middleware CSRF
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'webhook/*',          // Excluir todos los webhooks
        'api/pagos/notificacion',
    ]);
})
```

## Ejercicio Práctico

Crea un sistema de middleware para una aplicación multi-tenant con las siguientes capas:

1. **`VerificarMantenimiento`**: Si la app está en mantenimiento, muestra un mensaje 503 excepto para administradores.
2. **`RegistrarVisita`**: Guarda la URL, IP y timestamp de cada visita (usa `terminate()` para no afectar el rendimiento).
3. **`VerificarRol`**: Recibe parámetros de roles permitidos (`middleware('rol:admin,editor')`) y verifica el rol del usuario.
4. **`LimitarPeticiones`**: Configura rate limiting de 30 peticiones/minuto para usuarios normales y 100 para premium.
5. **Registra** todos los middleware en `bootstrap/app.php` y crea un grupo llamado `'panel'` que incluya `auth`, `VerificarRol` y `RegistrarVisita`.

```bash
php artisan make:middleware VerificarMantenimiento
php artisan make:middleware RegistrarVisita
php artisan make:middleware VerificarRol
```

## Resumen

- El **middleware** filtra las peticiones HTTP antes y/o después de llegar al controlador.
- Se crea con `php artisan make:middleware` e implementa el método `handle()`.
- Los middleware **before** ejecutan lógica antes del controlador; los **after** modifican la respuesta.
- Se registran como **globales** (todas las peticiones), **de ruta** (con alias) o en **grupos**.
- Los middleware pueden recibir **parámetros** desde la definición de la ruta.
- El método **`terminate()`** ejecuta código después de enviar la respuesta al navegador.
- El **rate limiting** se configura con `RateLimiter::for()` y se aplica con el middleware `throttle`.
