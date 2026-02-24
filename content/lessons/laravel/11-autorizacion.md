---
title: "Autorización: Gates y Policies"
slug: "laravel-autorizacion-gates-policies"
description: "Aprende a implementar autorización en Laravel usando Gates y Policies para controlar el acceso a recursos y acciones."
---

# Autorización: Gates y Policies

La **autorización** determina si un usuario autenticado tiene permiso para realizar una acción específica. Laravel ofrece dos mecanismos principales: **Gates** (puertas) para autorizaciones simples basadas en closures, y **Policies** (políticas) para lógica de autorización agrupada por modelo. Ambos trabajan en conjunto con el sistema de autenticación para proteger tu aplicación de forma elegante y mantenible.

## Gates: Autorizaciones Simples

Los Gates son closures que determinan si un usuario puede realizar una acción. Se definen típicamente en `AppServiceProvider` o en un provider dedicado.

### Definiendo Gates

```php
// app/Providers/AppServiceProvider.php
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    // Gate simple: ¿puede el usuario actualizar un post?
    Gate::define('update-post', function (User $user, Post $post) {
        return $user->id === $post->user_id;
    });

    // Gate sin modelo: ¿es el usuario administrador?
    Gate::define('access-admin', function (User $user) {
        return $user->role === 'admin';
    });

    // Gate con múltiples condiciones
    Gate::define('delete-post', function (User $user, Post $post) {
        return $user->id === $post->user_id || $user->role === 'admin';
    });
}
```

### Usando Gates

```php
use Illuminate\Support\Facades\Gate;

// Verificar si el usuario puede realizar la acción
if (Gate::allows('update-post', $post)) {
    // El usuario puede actualizar el post
}

if (Gate::denies('update-post', $post)) {
    // El usuario NO puede actualizar el post
}

// Lanzar excepción 403 si no está autorizado
Gate::authorize('update-post', $post);

// Verificar para un usuario específico (no el autenticado)
Gate::forUser($otroUsuario)->allows('update-post', $post);

// Verificar múltiples habilidades
if (Gate::any(['update-post', 'delete-post'], $post)) {
    // Puede hacer al menos una de las dos acciones
}

if (Gate::none(['update-post', 'delete-post'], $post)) {
    // No puede hacer ninguna de las dos
}
```

### Gate de Super Admin

Puedes definir un "before" que otorgue todos los permisos a ciertos usuarios:

```php
Gate::before(function (User $user, string $ability) {
    if ($user->role === 'superadmin') {
        return true; // Permitir todo para superadmins
    }
    // Retornar null para que se evalúen los gates normales
});

Gate::after(function (User $user, string $ability, bool|null $result) {
    // Se ejecuta después de cada verificación
    // Útil para logging de intentos de autorización
});
```

## Policies: Autorización por Modelo

Las Policies agrupan la lógica de autorización alrededor de un modelo específico. Son la forma recomendada cuando tienes múltiples acciones sobre un recurso.

### Crear una Policy

```bash
# Crear una policy vacía
php artisan make:policy PostPolicy

# Crear con métodos CRUD pre-generados vinculada a un modelo
php artisan make:policy PostPolicy --model=Post
```

### Estructura de una Policy

```php
// app/Policies/PostPolicy.php
namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * ¿Puede el usuario ver la lista de posts?
     */
    public function viewAny(User $user): bool
    {
        return true; // Cualquier usuario autenticado puede ver posts
    }

    /**
     * ¿Puede ver un post específico?
     */
    public function view(User $user, Post $post): bool
    {
        // Posts publicados son visibles para todos,
        // borradores solo para el autor
        return $post->published || $user->id === $post->user_id;
    }

    /**
     * ¿Puede crear posts?
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'editor', 'author']);
    }

    /**
     * ¿Puede actualizar un post?
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * ¿Puede eliminar un post?
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->role === 'admin';
    }

    /**
     * ¿Puede restaurar un post eliminado (soft delete)?
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->role === 'admin';
    }

    /**
     * ¿Puede eliminar permanentemente?
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->role === 'superadmin';
    }
}
```

### Policy Auto-Discovery

Laravel descubre automáticamente las policies si siguen la convención de nombres. Para el modelo `App\Models\Post`, buscará `App\Policies\PostPolicy`. Si necesitas registrar manualmente:

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(Post::class, PostPolicy::class);
}
```

## Usando Policies en Controllers

### Método `authorize()`

```php
// app/Http/Controllers/PostController.php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        // Verifica PostPolicy@viewAny
        $this->authorize('viewAny', Post::class);

        return view('posts.index', ['posts' => Post::all()]);
    }

    public function show(Post $post)
    {
        // Verifica PostPolicy@view — lanza 403 si falla
        $this->authorize('view', $post);

        return view('posts.show', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->update($request->validated());

        return redirect()->route('posts.show', $post);
    }

    public function store(Request $request)
    {
        // Para acciones sin instancia, pasar la clase
        $this->authorize('create', Post::class);

        $post = Post::create($request->validated());

        return redirect()->route('posts.show', $post);
    }
}
```

### Middleware de Autorización

```php
// En routes/web.php
use App\Models\Post;

Route::put('/posts/{post}', [PostController::class, 'update'])
    ->middleware('can:update,post'); // 'post' coincide con el parámetro de ruta

Route::post('/posts', [PostController::class, 'store'])
    ->middleware('can:create,App\Models\Post');
```

### Form Request Authorization

```php
// app/Http/Requests/UpdatePostRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');
        return $this->user()->can('update', $post);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body'  => 'required|string',
        ];
    }
}
```

## Autorización en Blade

Laravel proporciona directivas convenientes para mostrar u ocultar contenido según permisos:

```blade
{{-- Verificar si el usuario puede actualizar --}}
@can('update', $post)
    <a href="{{ route('posts.edit', $post) }}">Editar Post</a>
@endcan

{{-- Verificar si NO puede --}}
@cannot('delete', $post)
    <p>No tienes permiso para eliminar este post.</p>
@endcannot

{{-- Condicional completo --}}
@can('update', $post)
    <button>Editar</button>
@elsecan('view', $post)
    <span>Solo lectura</span>
@else
    <span>Sin acceso</span>
@endcan

{{-- Para acciones sin modelo (pasar la clase) --}}
@can('create', App\Models\Post::class)
    <a href="{{ route('posts.create') }}">Nuevo Post</a>
@endcan

{{-- Usando el helper auth directamente --}}
@if(auth()->user()->can('access-admin'))
    <a href="/admin">Panel de Admin</a>
@endif
```

## Abilities vs Policies: ¿Cuándo Usar Cada Uno?

| Característica | Gates | Policies |
|---|---|---|
| Complejidad | Acciones simples | Lógica agrupada por modelo |
| Ubicación | `AppServiceProvider` | Clases dedicadas |
| Relación con modelo | Opcional | Siempre vinculada a un modelo |
| Ejemplo de uso | `access-admin` | CRUD completo de `Post` |
| Testabilidad | Moderada | Alta, son clases individuales |

**Regla general:** usa Gates para permisos globales (¿es admin?) y Policies para autorización sobre modelos específicos (CRUD de posts, comentarios, etc.).

## Ejercicio Práctico

Implementa un sistema de autorización para un blog con las siguientes reglas:

1. Crea un modelo `Article` con campos: `title`, `body`, `user_id`, `published`.
2. Genera una policy `ArticlePolicy` con estos permisos:
   - Cualquier usuario autenticado puede ver artículos publicados.
   - Solo el autor puede ver sus borradores.
   - Solo usuarios con rol `editor` o `admin` pueden crear artículos.
   - Solo el autor puede editar su artículo (a menos que sea admin).
   - Solo admin puede eliminar cualquier artículo.
3. Aplica la autorización en un `ArticleController` usando `$this->authorize()`.
4. En la vista Blade, muestra los botones "Editar" y "Eliminar" solo a usuarios autorizados usando `@can`.
5. Define un Gate `before` que permita todas las acciones a usuarios `superadmin`.

```php
// Esqueleto inicial del controlador
class ArticleController extends Controller
{
    public function edit(Article $article)
    {
        $this->authorize('update', $article);
        return view('articles.edit', compact('article'));
    }
}
```

## Resumen

- **Gates** son closures ideales para permisos simples y globales, definidos en providers.
- **Policies** agrupan lógica de autorización por modelo y se generan con `make:policy`.
- Laravel auto-descubre policies por convención de nombres (`PostPolicy` → `Post`).
- Usa `$this->authorize()` en controllers, `@can/@cannot` en Blade, y `Gate::allows/denies` en lógica general.
- El método `before` permite crear super-usuarios que saltan todas las verificaciones.
- Combina ambos mecanismos: Gates para permisos del sistema, Policies para permisos de recursos.
