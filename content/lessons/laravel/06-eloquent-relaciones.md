---
title: "Eloquent: Relaciones"
slug: "laravel-eloquent-relaciones"
description: "Aprende a definir y usar relaciones entre modelos Eloquent: hasOne, hasMany, belongsTo, belongsToMany, polimórficas, eager loading y más."
---

# Eloquent: Relaciones

Las relaciones son una de las características más poderosas de Eloquent. Permiten definir cómo se conectan tus modelos entre sí, reflejando las relaciones de tu base de datos de forma elegante y expresiva. En esta lección explorarás todos los tipos de relaciones y las mejores prácticas para consultarlas eficientemente.

## Tipos de Relaciones

Laravel soporta los siguientes tipos de relación:

| Relación | Descripción | Ejemplo |
|----------|------------|---------|
| `hasOne` | Uno a uno | Un Usuario tiene un Perfil |
| `hasMany` | Uno a muchos | Un Usuario tiene muchos Posts |
| `belongsTo` | Inversa de hasOne/hasMany | Un Post pertenece a un Usuario |
| `belongsToMany` | Muchos a muchos | Posts tienen muchas Etiquetas |
| `hasManyThrough` | A través de otro modelo | Un País tiene Posts a través de Usuarios |
| Polimórficas | Relación genérica | Un Comentario puede ser de un Post o Video |

## Uno a Uno: `hasOne` / `belongsTo`

Un usuario tiene un perfil; un perfil pertenece a un usuario.

```php
// Migración de perfiles
Schema::create('perfiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('biografia')->nullable();
    $table->string('avatar')->nullable();
    $table->date('fecha_nacimiento')->nullable();
    $table->timestamps();
});
```

```php
// app/Models/User.php
class User extends Authenticatable
{
    // Un usuario tiene un perfil
    public function perfil()
    {
        return $this->hasOne(Perfil::class);
        // Laravel busca user_id en la tabla perfiles
    }
}

// app/Models/Perfil.php
class Perfil extends Model
{
    // Un perfil pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

```php
// Uso
$usuario = User::find(1);
$perfil = $usuario->perfil;             // Acceso como propiedad (lazy loading)
$bio = $usuario->perfil->biografia;

// Crear el perfil asociado
$usuario->perfil()->create([
    'biografia' => 'Desarrollador full-stack',
    'avatar' => 'avatar.jpg',
]);

// Desde el perfil acceder al usuario
$perfil = Perfil::find(1);
$nombre = $perfil->usuario->name;
```

## Uno a Muchos: `hasMany` / `belongsTo`

Un usuario tiene muchos posts; cada post pertenece a un usuario.

```php
// app/Models/User.php
class User extends Authenticatable
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

// app/Models/Post.php
class Post extends Model
{
    public function autor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }
}
```

```php
// Obtener todos los posts de un usuario
$usuario = User::find(1);
$posts = $usuario->posts;                  // Collection de Post

// Filtrar la relación dinámicamente
$publicados = $usuario->posts()            // Nota: () devuelve el query builder
    ->where('publicado', true)
    ->orderBy('created_at', 'desc')
    ->get();

// Crear un post asociado al usuario
$usuario->posts()->create([
    'titulo' => 'Mi primer artículo',
    'contenido' => 'Contenido del artículo...',
]);

// Contar posts
$totalPosts = $usuario->posts()->count();

// Verificar si tiene posts
if ($usuario->posts()->exists()) {
    // El usuario tiene al menos un post
}
```

## Muchos a Muchos: `belongsToMany`

Los posts tienen muchas etiquetas y las etiquetas tienen muchos posts. Requiere una tabla pivote.

```php
// Migración de la tabla pivote
Schema::create('post_tag', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('tag_id')->constrained()->onDelete('cascade');
    $table->timestamps();

    // Índice único para evitar duplicados
    $table->unique(['post_id', 'tag_id']);
});
```

```php
// app/Models/Post.php
class Post extends Model
{
    public function etiquetas()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
        // Tabla pivote: post_tag
        // FK de este modelo: post_id
        // FK del modelo relacionado: tag_id
    }
}

// app/Models/Tag.php
class Tag extends Model
{
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }
}
```

### Trabajar con la tabla pivote

```php
$post = Post::find(1);

// Asignar etiquetas (reemplaza todas las existentes)
$post->etiquetas()->sync([1, 2, 3]);

// Asignar sin eliminar las existentes
$post->etiquetas()->syncWithoutDetaching([4, 5]);

// Agregar una etiqueta
$post->etiquetas()->attach($tagId);

// Agregar con datos extra en la tabla pivote
$post->etiquetas()->attach($tagId, ['orden' => 1]);

// Quitar una etiqueta
$post->etiquetas()->detach($tagId);

// Quitar todas las etiquetas
$post->etiquetas()->detach();

// Toggle: agrega si no existe, quita si existe
$post->etiquetas()->toggle([1, 2, 3]);

// Acceder a datos de la tabla pivote
foreach ($post->etiquetas as $etiqueta) {
    echo $etiqueta->pivot->created_at;
}
```

### Columnas extra en la tabla pivote

```php
// Definir qué columnas de la pivote se cargan
public function etiquetas()
{
    return $this->belongsToMany(Tag::class, 'post_tag')
        ->withPivot('orden', 'destacado')
        ->withTimestamps();   // Incluir created_at y updated_at de la pivote
}
```

## Has Many Through

Permite acceder a relaciones lejanas a través de una intermedia. Ejemplo: un País tiene muchos Posts a través de Usuarios.

```php
// app/Models/Pais.php
class Pais extends Model
{
    // Un país tiene muchos posts a través de sus usuarios
    public function posts()
    {
        return $this->hasManyThrough(
            Post::class,   // Modelo final
            User::class,   // Modelo intermedio
            'pais_id',     // FK en users que apunta a este modelo
            'user_id',     // FK en posts que apunta a users
        );
    }
}

// Uso
$pais = Pais::find(1);
$posts = $pais->posts; // Todos los posts de usuarios de este país
```

## Relaciones Polimórficas

Las relaciones polimórficas permiten que un modelo pertenezca a más de un tipo de modelo con una sola relación.

### Uno a Muchos Polimórfica

Un Comentario puede pertenecer a un Post o a un Video:

```php
// Migración de comentarios
Schema::create('comentarios', function (Blueprint $table) {
    $table->id();
    $table->text('cuerpo');
    $table->morphs('comentable'); // Crea comentable_type y comentable_id
    $table->timestamps();
});
```

```php
// app/Models/Comentario.php
class Comentario extends Model
{
    public function comentable()
    {
        return $this->morphTo(); // Relación polimórfica
    }
}

// app/Models/Post.php
class Post extends Model
{
    public function comentarios()
    {
        return $this->morphMany(Comentario::class, 'comentable');
    }
}

// app/Models/Video.php
class Video extends Model
{
    public function comentarios()
    {
        return $this->morphMany(Comentario::class, 'comentable');
    }
}
```

```php
// Uso
$post = Post::find(1);
$post->comentarios()->create(['cuerpo' => 'Gran artículo!']);

$video = Video::find(1);
$video->comentarios()->create(['cuerpo' => 'Excelente video!']);

// Desde el comentario, acceder al modelo padre
$comentario = Comentario::find(1);
$padre = $comentario->comentable; // Puede ser Post o Video
```

## Eager Loading: Carga Anticipada

El **problema N+1** ocurre cuando cada acceso a una relación genera una consulta adicional:

```php
// ❌ Problema N+1: 1 consulta + N consultas adicionales
$posts = Post::all(); // 1 consulta
foreach ($posts as $post) {
    echo $post->autor->name; // 1 consulta por cada post (N consultas)
}
// Si hay 100 posts = 101 consultas!
```

### Solución con `with()` (Eager Loading)

```php
// ✅ Solo 2 consultas, sin importar cuántos posts haya
$posts = Post::with('autor')->get();
// Consulta 1: SELECT * FROM posts
// Consulta 2: SELECT * FROM users WHERE id IN (1, 2, 3, ...)

foreach ($posts as $post) {
    echo $post->autor->name; // No genera consultas adicionales
}
```

### Variantes de Eager Loading

```php
// Cargar múltiples relaciones
$posts = Post::with(['autor', 'etiquetas', 'comentarios'])->get();

// Cargar relaciones anidadas
$posts = Post::with('autor.perfil')->get();

// Eager loading con restricciones
$posts = Post::with(['comentarios' => function ($query) {
    $query->where('aprobado', true)
          ->orderBy('created_at', 'desc')
          ->limit(5);
}])->get();

// Cargar solo ciertas columnas
$posts = Post::with('autor:id,name,email')->get();

// Lazy Eager Loading (cargar relaciones después de obtener los modelos)
$posts = Post::all();
$posts->load('autor', 'etiquetas');
```

### Prevenir Lazy Loading

En desarrollo, puedes hacer que Laravel lance una excepción cuando ocurra lazy loading:

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    // Solo en desarrollo: lanza excepción en lazy loading
    Model::preventLazyLoading(! app()->isProduction());
}
```

## Consultas con `withCount`

Puedes contar registros relacionados sin cargarlos:

```php
// Contar comentarios de cada post
$posts = Post::withCount('comentarios')->get();

foreach ($posts as $post) {
    echo $post->comentarios_count; // Accesible como propiedad _count
}

// Contar con condiciones
$posts = Post::withCount([
    'comentarios',
    'comentarios as comentarios_aprobados_count' => function ($query) {
        $query->where('aprobado', true);
    },
])->get();

// Filtrar por conteo
$populares = Post::withCount('comentarios')
    ->having('comentarios_count', '>', 10)
    ->get();

// Otras agregaciones
$posts = Post::withSum('pedidos', 'total')
    ->withAvg('valoraciones', 'puntuacion')
    ->withMin('comentarios', 'created_at')
    ->withMax('comentarios', 'created_at')
    ->get();
```

## Ejercicio Práctico

Diseña un sistema de relaciones para una plataforma educativa:

1. **Crea los modelos**: `Curso`, `Leccion`, `Estudiante`, `Inscripcion`.
2. **Define las relaciones**:
   - Un `Curso` tiene muchas `Lecciones` (`hasMany`).
   - Una `Leccion` pertenece a un `Curso` (`belongsTo`).
   - Un `Curso` tiene muchos `Estudiantes` a través de `Inscripciones` (`belongsToMany`).
   - La tabla pivote `inscripciones` tiene columnas extra: `progreso`, `completado`, `fecha_inscripcion`.
3. **Implementa eager loading**: carga cursos con lecciones y conteo de estudiantes.
4. **Previene lazy loading** en el entorno de desarrollo.
5. **Prueba en Tinker**: crea un curso con 5 lecciones y 3 estudiantes inscritos.

```php
// Solución parcial
class Curso extends Model
{
    public function lecciones()
    {
        return $this->hasMany(Leccion::class);
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'inscripciones')
            ->withPivot('progreso', 'completado')
            ->withTimestamps();
    }
}

// Consulta optimizada
$cursos = Curso::with('lecciones')
    ->withCount('estudiantes')
    ->get();
```

## Resumen

- **`hasOne`** y **`hasMany`** definen relaciones "tiene un/muchos"; **`belongsTo`** es la inversa.
- **`belongsToMany`** gestiona relaciones muchos a muchos con tabla pivote; usa `sync()`, `attach()` y `detach()`.
- **`hasManyThrough`** accede a modelos lejanos a través de un modelo intermedio.
- Las **relaciones polimórficas** permiten que un modelo se relacione con múltiples tipos de modelos.
- **Eager loading** (`with()`) resuelve el problema N+1 cargando relaciones en pocas consultas.
- **`preventLazyLoading()`** ayuda a detectar problemas de rendimiento en desarrollo.
- **`withCount()`** y sus variantes (`withSum`, `withAvg`) agregan conteos sin cargar los modelos completos.
