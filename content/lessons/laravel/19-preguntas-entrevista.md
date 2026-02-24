---
title: "Preguntas Frecuentes de Entrevista Laravel"
slug: "laravel-preguntas-entrevista"
description: "Prepárate para entrevistas técnicas de Laravel con respuestas detalladas a las preguntas más comunes sobre el framework."
---

# Preguntas Frecuentes de Entrevista Laravel

Esta lección recopila las preguntas más frecuentes en entrevistas técnicas para desarrolladores Laravel. Cada respuesta incluye explicaciones claras y ejemplos de código que demuestran comprensión profunda del framework. Estudiar estos temas te preparará tanto para entrevistas como para escribir mejor código en tu día a día.

## 1. ¿Qué es el Service Container?

El **Service Container** (contenedor de servicios) es el corazón de Laravel. Es un contenedor de inyección de dependencias que gestiona la creación y resolución de clases y sus dependencias.

```php
// Registrar un servicio en el contenedor
app()->bind(PaymentGateway::class, function ($app) {
    return new StripePaymentGateway(config('services.stripe.key'));
});

// Singleton: misma instancia en toda la aplicación
app()->singleton(CartService::class, function ($app) {
    return new CartService($app->make(SessionManager::class));
});

// Resolución automática: Laravel inyecta las dependencias automáticamente
class OrderController extends Controller
{
    // Laravel resuelve PaymentGateway del contenedor
    public function store(Request $request, PaymentGateway $gateway)
    {
        $gateway->charge($request->amount);
    }
}
```

**Respuesta clave:** El Service Container resuelve dependencias automáticamente mediante reflexión (type-hints). Esto permite código desacoplado y testeable, ya que puedes intercambiar implementaciones sin modificar los consumidores.

## 2. ¿Qué son los Service Providers?

Los **Service Providers** son el lugar central para configurar y registrar servicios en la aplicación. Todo en Laravel se inicializa a través de providers.

```php
// app/Providers/PaymentServiceProvider.php
class PaymentServiceProvider extends ServiceProvider
{
    // register(): registrar bindings en el contenedor
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function ($app) {
            return new StripeGateway(config('services.stripe.key'));
        });
    }

    // boot(): ejecutar lógica después de que todos los providers se registren
    public function boot(): void
    {
        // Publicar configuración, registrar rutas, vistas, etc.
    }
}
```

**Respuesta clave:** Los providers tienen dos métodos: `register()` para vincular clases al contenedor, y `boot()` para ejecutar lógica de inicialización. Se registran en `bootstrap/providers.php`.

## 3. ¿Facades vs Inyección de Dependencias?

**Facades** son atajos estáticos que proporcionan acceso a servicios del contenedor. La **inyección de dependencias (DI)** recibe las dependencias explícitamente.

```php
// Facade: acceso estático (más conciso)
use Illuminate\Support\Facades\Cache;
Cache::put('key', 'value', 3600);

// Inyección de dependencias: explícita (más testeable)
class ProductController extends Controller
{
    public function __construct(
        private CacheManager $cache
    ) {}

    public function index()
    {
        $this->cache->put('key', 'value', 3600);
    }
}
```

**Respuesta clave:** Las Facades son proxies estáticos a servicios del contenedor. Su ventaja es la brevedad; su desventaja es que ocultan dependencias. DI es preferible en clases complejas porque hace las dependencias explícitas y facilita el testing con mocks.

## 4. ¿Cuál es el ciclo de vida de una petición en Laravel?

1. **Entry point:** `public/index.php` recibe la petición.
2. **Bootstrap:** Se crea la instancia de la aplicación (`bootstrap/app.php`).
3. **Service Providers:** Se registran y arrancan todos los providers.
4. **Kernel HTTP:** La petición pasa por el kernel HTTP.
5. **Middleware global:** Se ejecutan los middleware globales (CORS, mantenimiento, etc.).
6. **Router:** El router encuentra la ruta que coincide con la URL.
7. **Middleware de ruta:** Se ejecutan los middleware asignados a la ruta.
8. **Controller/Action:** Se ejecuta el controlador o closure de la ruta.
9. **Response:** La respuesta viaja de regreso por los middleware.
10. **Termination:** Se ejecutan los middleware `terminate()` y se envía la respuesta al navegador.

```
Petición → index.php → Kernel → Middleware Global → Router
  → Middleware de Ruta → Controller → Response → Middleware → Cliente
```

## 5. ¿Qué es el problema N+1 y cómo se resuelve?

El problema **N+1** ocurre cuando se ejecuta una consulta adicional por cada elemento de una colección. Son N consultas extras más la consulta inicial.

```php
// ❌ Problema N+1: 1 consulta para posts + N consultas para autores
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // Consulta extra por cada post
}

// ✅ Solución: Eager Loading con with()
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name; // Sin consultas adicionales
}

// ✅ Eager loading anidado
$posts = Post::with(['author', 'comments.user', 'tags'])->get();

// ✅ Prevenir N+1 automáticamente en desarrollo
// app/Providers/AppServiceProvider.php
use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    Model::preventLazyLoading(! app()->isProduction());
}
```

**Respuesta clave:** Usa `with()` (eager loading) para cargar relaciones en una sola consulta. Activa `preventLazyLoading()` en desarrollo para detectar N+1 automáticamente.

## 6. ¿Qué es Mass Assignment Protection?

Laravel protege contra la asignación masiva, que ocurre cuando un usuario envía campos no esperados que modifican datos sensibles.

```php
// ❌ Peligroso: sin protección
User::create($request->all());
// Un usuario podría enviar: { "role": "admin", "name": "Hacker" }

// ✅ Opción 1: $fillable (whitelist)
class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    // Solo estos campos se pueden asignar masivamente
}

// ✅ Opción 2: $guarded (blacklist)
class User extends Model
{
    protected $guarded = ['id', 'role', 'is_admin'];
    // Estos campos NO se pueden asignar masivamente
}

// ✅ Opción 3: validated data (más seguro)
User::create($request->validated());
// Solo los campos que pasaron la validación
```

**Respuesta clave:** Siempre define `$fillable` o `$guarded` en tus modelos. La mejor práctica es usar `$request->validated()` en lugar de `$request->all()`.

## 7. ¿Cómo funciona la protección CSRF?

**CSRF** (Cross-Site Request Forgery) es un ataque donde un sitio malicioso envía peticiones en nombre del usuario autenticado. Laravel protege con tokens.

```php
// Laravel incluye el middleware VerifyCsrfToken automáticamente

// En formularios Blade, incluir el token:
<form method="POST" action="/posts">
    @csrf  {{-- Genera un campo oculto con el token --}}
    <input type="text" name="title">
    <button type="submit">Crear</button>
</form>

// En peticiones AJAX, el token se envía como header
// Laravel lo configura automáticamente con Axios:
// X-CSRF-TOKEN: {{ csrf_token() }}

// Excluir rutas de la verificación CSRF (webhooks, etc.)
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'webhooks/*',
        'api/payments/callback',
    ]);
})
```

## 8. ¿Qué es el Middleware Pipeline?

Los middleware son capas que filtran las peticiones HTTP antes y después de llegar al controlador.

```php
// Middleware que se ejecuta ANTES del controlador
class CheckAge
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->age < 18) {
            return redirect('home');
        }

        return $next($request); // Continuar al siguiente middleware
    }
}

// Middleware que se ejecuta DESPUÉS del controlador
class AddHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request); // Primero ejecuta el controlador

        $response->header('X-Custom-Header', 'Valor');

        return $response;
    }
}

// Pipeline: las peticiones atraviesan capas como una cebolla
// Request → Auth → CORS → Rate Limit → Controller
// Response ← Auth ← CORS ← Rate Limit ← Controller
```

## 9. ¿Eloquent ORM vs Query Builder?

```php
// Query Builder: consultas directas, más cercano a SQL
$users = DB::table('users')
    ->where('active', true)
    ->orderBy('name')
    ->get(); // Retorna colección de stdClass

// Eloquent ORM: trabaja con modelos, relaciones y eventos
$users = User::where('active', true)
    ->orderBy('name')
    ->get(); // Retorna colección de modelos User

// Eloquent ventajas: relaciones, mutators, casts, eventos, scopes
$user->posts()->where('published', true)->get();
$user->fullName; // Accessor
$user->delete(); // Dispara evento 'deleting' y 'deleted'

// Query Builder ventajas: rendimiento, consultas complejas, aggregates
DB::table('orders')
    ->join('users', 'orders.user_id', '=', 'users.id')
    ->select(DB::raw('users.name, SUM(orders.total) as revenue'))
    ->groupBy('users.name')
    ->get();
```

**Respuesta clave:** Usa Eloquent para operaciones CRUD estándar y relaciones. Usa Query Builder para consultas complejas, reportes masivos o cuando el rendimiento es crítico.

## 10. ¿Qué comandos Artisan son los más importantes?

```bash
# Generación
php artisan make:model Product -mfcs  # Modelo + Migration + Factory + Controller + Seeder
php artisan make:middleware CheckRole
php artisan make:request StoreProductRequest

# Base de datos
php artisan migrate              # Ejecutar migraciones
php artisan migrate:rollback     # Revertir última migración
php artisan db:seed              # Ejecutar seeders
php artisan migrate:fresh --seed # Recrear BD desde cero

# Cache y optimización
php artisan optimize             # Cachear todo para producción
php artisan config:cache         # Cachear configuración
php artisan route:list           # Ver todas las rutas

# Debugging
php artisan tinker               # REPL interactivo
php artisan queue:work           # Procesar jobs de cola

# Crear comandos personalizados
php artisan make:command SendWeeklyReport
```

## 11. ¿Cuáles son las estrategias de caching en Laravel?

```php
use Illuminate\Support\Facades\Cache;

// Guardar en cache
Cache::put('key', 'value', now()->addHours(1));

// Obtener o calcular (cache-aside pattern)
$users = Cache::remember('active_users', 3600, function () {
    return User::where('active', true)->get();
});

// Cache forever (sin expiración)
Cache::forever('settings', $settings);

// Invalidar cache
Cache::forget('active_users');
Cache::flush(); // Limpiar todo

// Cache tags (solo Redis/Memcached)
Cache::tags(['users', 'admins'])->put('admin_list', $admins, 3600);
Cache::tags('users')->flush(); // Invalidar solo cache de usuarios

// Model caching pattern
class Product extends Model
{
    public static function getCached()
    {
        return Cache::remember('products_all', 3600, function () {
            return static::with('category')->get();
        });
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('products_all'));
        static::deleted(fn () => Cache::forget('products_all'));
    }
}
```

## 12. ¿Qué patrones de diseño usa Laravel?

| Patrón | Uso en Laravel |
|---|---|
| **Service Container** | IoC Container para inyección de dependencias |
| **Facade** | Acceso estático a servicios (`Cache::get()`, `DB::table()`) |
| **Repository** | Eloquent encapsula el acceso a datos |
| **Observer** | Model Observers, Events & Listeners |
| **Strategy** | Drivers de cache, sesión, cola, log |
| **Factory** | Model Factories para datos de prueba |
| **Builder** | Query Builder, Mail Message Builder |
| **Middleware/Pipeline** | HTTP Middleware pipeline |
| **Singleton** | Instancias únicas en el container |
| **Template Method** | Form Requests (`authorize()`, `rules()`) |

```php
// Ejemplo del patrón Strategy: intercambiar drivers fácilmente
// config/cache.php define múltiples "strategies"
// El código consumidor no cambia:
Cache::put('key', 'value'); // Funciona con Redis, File, Memcached...

// Ejemplo del patrón Builder
$query = User::query()
    ->where('active', true)
    ->when($role, fn ($q) => $q->where('role', $role))
    ->orderBy('name')
    ->paginate(10);
```

## Preguntas de Concepto Rápido

**¿Cuál es la diferencia entre `$request->all()` y `$request->validated()`?**
`all()` retorna todos los datos enviados; `validated()` solo los campos que pasaron las reglas de validación. Siempre prefiere `validated()`.

**¿Qué es un Form Request?**
Una clase dedicada que encapsula validación y autorización, separándolas del controller para mantener código limpio.

**¿Cómo se protege contra SQL Injection en Laravel?**
Eloquent y Query Builder usan prepared statements automáticamente. Nunca concatenes variables directamente en `DB::raw()`.

**¿Qué hace `php artisan tinker`?**
Abre un REPL (Read-Eval-Print Loop) interactivo donde puedes ejecutar código PHP con acceso completo a tu aplicación Laravel.

**¿Cuándo usar `belongsTo` vs `hasOne`?**
`belongsTo` se define en el modelo que tiene la foreign key. `hasOne` se define en el modelo referenciado. Si `posts` tiene `user_id`, entonces `Post` tiene `belongsTo(User)` y `User` tiene `hasOne(Post)` o `hasMany(Post)`.

## Ejercicio Práctico

Prepárate para una entrevista respondiendo estas preguntas por escrito:

1. Explica con un diagrama cómo fluye una petición HTTP desde el navegador hasta la respuesta, mencionando cada capa de Laravel involucrada.
2. Escribe un ejemplo donde el problema N+1 cause 101 consultas y muestra cómo reducirlo a 2 consultas con eager loading.
3. Implementa un Service Provider que registre un servicio `ReportGenerator` como singleton, con inyección de dependencias en un controller.
4. Crea un middleware personalizado `CheckSubscription` que verifique si el usuario tiene una suscripción activa antes de acceder a ciertos recursos.
5. Demuestra la diferencia entre `Cache::remember` y `Cache::rememberForever` con un ejemplo práctico.

```php
// Esqueleto para la pregunta 3
class ReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReportGenerator::class, function ($app) {
            return new ReportGenerator(
                $app->make(DatabaseManager::class),
                config('reports.format')
            );
        });
    }
}
```

## Resumen

- El **Service Container** gestiona dependencias y es la base de la arquitectura de Laravel.
- Los **Service Providers** registran servicios y configuran la aplicación en `register()` y `boot()`.
- **Facades** ofrecen sintaxis estática concisa, pero DI es preferible para testabilidad.
- El **ciclo de vida** sigue: index.php → Kernel → Middleware → Router → Controller → Response.
- Resuelve el **problema N+1** con `with()` (eager loading) y `preventLazyLoading()`.
- **Mass Assignment** se protege con `$fillable`/`$guarded` y usando `$request->validated()`.
- **CSRF** se protege con tokens automáticos; middleware filtra peticiones en el pipeline.
- Usa **Eloquent** para CRUD y relaciones; **Query Builder** para consultas complejas.
- Domina las **estrategias de caching** (`remember`, tags, invalidación) para optimizar rendimiento.
- Laravel implementa múltiples **patrones de diseño**: Facade, Observer, Strategy, Builder, Singleton y más.
