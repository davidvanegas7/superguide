---
title: "Rutas y Controladores"
slug: "laravel-rutas-y-controladores"
description: "Domina el sistema de enrutamiento de Laravel, crea controladores con Artisan y aprende a organizar la lógica de tu aplicación."
---

# Rutas y Controladores

El sistema de enrutamiento es el punto de entrada de todas las peticiones HTTP en Laravel. En esta lección aprenderás a definir rutas, pasar parámetros, agrupar rutas y crear controladores para mantener tu código organizado.

## Definición Básica de Rutas

Las rutas se definen en el archivo `routes/web.php`. Cada ruta asocia una URL con una acción:

```php
use Illuminate\Support\Facades\Route;

// Ruta básica que devuelve una vista
Route::get('/', function () {
    return view('welcome');
});

// Ruta que devuelve texto plano
Route::get('/hola', function () {
    return '¡Hola, mundo!';
});

// Ruta que devuelve JSON
Route::get('/api/estado', function () {
    return response()->json([
        'estado' => 'activo',
        'version' => '1.0.0'
    ]);
});
```

## Métodos HTTP Disponibles

Laravel soporta todos los verbos HTTP estándar:

```php
// GET: Obtener un recurso
Route::get('/productos', function () {
    return 'Lista de productos';
});

// POST: Crear un recurso
Route::post('/productos', function () {
    return 'Producto creado';
});

// PUT/PATCH: Actualizar un recurso
Route::put('/productos/{id}', function ($id) {
    return "Producto $id actualizado";
});

// DELETE: Eliminar un recurso
Route::delete('/productos/{id}', function ($id) {
    return "Producto $id eliminado";
});

// Responder a múltiples verbos
Route::match(['get', 'post'], '/formulario', function () {
    return 'GET o POST';
});

// Responder a cualquier verbo
Route::any('/comodin', function () {
    return 'Cualquier método HTTP';
});
```

## Parámetros de Ruta

### Parámetros obligatorios

```php
// Parámetro simple
Route::get('/usuarios/{id}', function (string $id) {
    return "Usuario con ID: $id";
});

// Múltiples parámetros
Route::get('/posts/{post}/comentarios/{comentario}', function ($post, $comentario) {
    return "Post $post, Comentario $comentario";
});
```

### Parámetros opcionales

```php
// El parámetro tiene un valor por defecto
Route::get('/idioma/{locale?}', function (string $locale = 'es') {
    return "Idioma seleccionado: $locale";
});
```

### Restricciones con expresiones regulares

```php
// Solo permite dígitos numéricos
Route::get('/usuarios/{id}', function (string $id) {
    return "Usuario: $id";
})->where('id', '[0-9]+');

// Solo permite letras
Route::get('/categorias/{nombre}', function (string $nombre) {
    return "Categoría: $nombre";
})->where('nombre', '[A-Za-z]+');

// Métodos auxiliares para restricciones comunes
Route::get('/productos/{id}', function ($id) {
    // ...
})->whereNumber('id');

Route::get('/usuarios/{nombre}', function ($nombre) {
    // ...
})->whereAlpha('nombre');
```

## Rutas con Nombre

Asignar nombres a las rutas permite referenciarlas sin depender de la URL exacta:

```php
// Definir una ruta con nombre
Route::get('/panel/configuracion', function () {
    return view('configuracion');
})->name('configuracion');

// Generar la URL desde cualquier parte de la aplicación
$url = route('configuracion');
// => /panel/configuracion

// Redirigir a una ruta nombrada
return redirect()->route('configuracion');

// En una vista Blade
<a href="{{ route('configuracion') }}">Configuración</a>
```

## Grupos de Rutas

Los grupos permiten compartir atributos entre varias rutas para evitar repetición:

### Prefijo de URL

```php
// Todas las rutas dentro tendrán el prefijo /admin
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return 'Panel de administración';
    }); // => /admin/dashboard

    Route::get('/usuarios', function () {
        return 'Gestión de usuarios';
    }); // => /admin/usuarios
});
```

### Middleware

```php
// Todas las rutas requieren autenticación
Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', function () {
        return view('perfil');
    });

    Route::get('/mis-pedidos', function () {
        return view('pedidos');
    });
});
```

### Prefijo de nombre

```php
Route::name('admin.')->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        // ...
    })->name('dashboard'); // Nombre completo: admin.dashboard

    Route::get('/usuarios', function () {
        // ...
    })->name('usuarios');  // Nombre completo: admin.usuarios
});
```

## Controladores

Los controladores son clases que agrupan la lógica relacionada con un recurso. En lugar de escribir closures en las rutas, delegamos la lógica a métodos del controlador.

### Crear un controlador

```bash
# Crear un controlador básico
php artisan make:controller ProductoController

# Crear un controlador de recurso (con métodos CRUD predefinidos)
php artisan make:controller ProductoController --resource

# Crear un controlador con modelo inyectado
php artisan make:controller ProductoController --resource --model=Producto
```

### Controlador básico

```php
<?php
// app/Http/Controllers/ProductoController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductoController extends Controller
{
    // Mostrar lista de productos
    public function index()
    {
        $productos = ['Laptop', 'Teclado', 'Monitor'];
        return view('productos.index', compact('productos'));
    }

    // Mostrar un producto específico
    public function show(string $id)
    {
        return view('productos.show', ['id' => $id]);
    }

    // Guardar un nuevo producto
    public function store(Request $request)
    {
        // Lógica para guardar el producto
        return redirect()->route('productos.index');
    }
}
```

### Registrar rutas hacia el controlador

```php
use App\Http\Controllers\ProductoController;

// Rutas individuales
Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('productos.show');
Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
```

## Controladores de Recurso

Un controlador de recurso implementa las 7 acciones CRUD estándar. Laravel puede registrar todas las rutas con una sola línea:

```php
// Registra automáticamente las 7 rutas RESTful
Route::resource('productos', ProductoController::class);
```

Esto genera las siguientes rutas:

| Verbo     | URI                       | Acción   | Nombre de ruta      |
|-----------|---------------------------|----------|----------------------|
| GET       | /productos                | index    | productos.index      |
| GET       | /productos/create         | create   | productos.create     |
| POST      | /productos                | store    | productos.store      |
| GET       | /productos/{producto}     | show     | productos.show       |
| GET       | /productos/{producto}/edit| edit     | productos.edit       |
| PUT/PATCH | /productos/{producto}     | update   | productos.update     |
| DELETE    | /productos/{producto}     | destroy  | productos.destroy    |

### Limitar las acciones de un recurso

```php
// Solo incluir ciertas acciones
Route::resource('productos', ProductoController::class)
    ->only(['index', 'show']);

// Excluir ciertas acciones
Route::resource('productos', ProductoController::class)
    ->except(['destroy']);
```

## Inyección de Dependencias en Controladores

Laravel resuelve automáticamente las dependencias declaradas en los constructores y métodos de los controladores:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductoController extends Controller
{
    // El objeto Request se inyecta automáticamente
    public function store(Request $request)
    {
        $nombre = $request->input('nombre');
        $precio = $request->input('precio');

        $producto = Producto::create([
            'nombre' => $nombre,
            'precio' => $precio,
        ]);

        return redirect()->route('productos.show', $producto);
    }

    // Route Model Binding: Laravel encuentra el modelo automáticamente
    public function show(Producto $producto)
    {
        // $producto ya es la instancia del modelo con ese ID
        return view('productos.show', compact('producto'));
    }

    // Combinación de Request y Model Binding
    public function update(Request $request, Producto $producto)
    {
        $producto->update($request->all());
        return redirect()->route('productos.show', $producto);
    }
}
```

## Ejercicio Práctico

Crea un sistema de rutas y controladores para gestionar un blog:

1. **Genera un controlador** de recurso llamado `ArticuloController` asociado al modelo `Articulo`.
2. **Registra las rutas** de recurso en `web.php`.
3. **Crea un grupo de rutas** de administración con prefijo `/admin`, middleware `auth` y prefijo de nombre `admin.`.
4. **Implementa los métodos** `index` y `show` con datos de ejemplo.
5. **Verifica las rutas** ejecutando `php artisan route:list`.

```bash
# Paso 1: Crear modelo y controlador
php artisan make:model Articulo -mf
php artisan make:controller ArticuloController --resource --model=Articulo
```

```php
// routes/web.php
use App\Http\Controllers\ArticuloController;

// Rutas públicas del blog
Route::get('/blog', [ArticuloController::class, 'index'])->name('blog.index');
Route::get('/blog/{articulo}', [ArticuloController::class, 'show'])->name('blog.show');

// Rutas de administración protegidas
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('articulos', ArticuloController::class);
});
```

```php
// app/Http/Controllers/ArticuloController.php
public function index()
{
    $articulos = Articulo::latest()->paginate(10);
    return view('blog.index', compact('articulos'));
}

public function show(Articulo $articulo)
{
    return view('blog.show', compact('articulo'));
}
```

## Resumen

- Las **rutas** se definen en `routes/web.php` usando métodos como `Route::get()`, `Route::post()`, etc.
- Los **parámetros de ruta** pueden ser obligatorios `{id}` u opcionales `{id?}`, y se pueden restringir con `where()`.
- Las **rutas con nombre** (`->name()`) permiten generar URLs independientes de la estructura de la URL.
- Los **grupos de rutas** comparten prefijos, middleware y nombres para evitar repetición.
- Los **controladores** organizan la lógica en clases; los controladores de recurso implementan las 7 acciones CRUD.
- **Route Model Binding** resuelve automáticamente modelos Eloquent desde los parámetros de ruta.
- La **inyección de dependencias** funciona tanto en constructores como en métodos de los controladores.
