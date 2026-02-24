---
title: "Eloquent ORM: Fundamentos"
slug: "laravel-eloquent-basico"
description: "Domina los fundamentos de Eloquent, el ORM de Laravel: modelos, operaciones CRUD, mass assignment, scopes, accessors, mutators y casting."
---

# Eloquent ORM: Fundamentos

Eloquent es el ORM (Object-Relational Mapper) integrado en Laravel. Cada tabla de la base de datos tiene un modelo correspondiente que permite interactuar con los datos de forma expresiva y orientada a objetos. En esta lección aprenderás a realizar todas las operaciones fundamentales con Eloquent.

## Crear un Modelo

```bash
# Crear un modelo básico
php artisan make:model Producto

# Crear modelo con migración, factory y seeder
php artisan make:model Producto -mfs

# Crear modelo con todo: migración, factory, seeder, controlador de recurso y form request
php artisan make:model Producto --all
```

## Convenciones de Nombres

Eloquent sigue convenciones que simplifican la configuración:

| Concepto | Convención | Ejemplo |
|----------|-----------|---------|
| Nombre del modelo | Singular, PascalCase | `Producto` |
| Nombre de la tabla | Plural, snake_case | `productos` |
| Clave primaria | `id` | `productos.id` |
| Clave foránea | modelo_singular + `_id` | `producto_id` |
| Timestamps | `created_at`, `updated_at` | automáticos |

Si necesitas personalizar estas convenciones:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    // Nombre personalizado de tabla
    protected $table = 'catalogo_productos';

    // Clave primaria personalizada
    protected $primaryKey = 'producto_id';

    // Si la clave primaria no es auto-incremental
    public $incrementing = false;

    // Tipo de la clave primaria (si no es integer)
    protected $keyType = 'string';

    // Desactivar timestamps automáticos
    public $timestamps = false;

    // Formato de fecha personalizado
    protected $dateFormat = 'U'; // Unix timestamp
}
```

## Operaciones CRUD

### CREATE: Crear registros

```php
// Método 1: Crear instancia, asignar propiedades y guardar
$producto = new Producto();
$producto->nombre = 'Laptop Dell XPS';
$producto->precio = 1299.99;
$producto->stock = 25;
$producto->save();

// Método 2: Usar create() con mass assignment (requiere $fillable)
$producto = Producto::create([
    'nombre' => 'Monitor LG 27"',
    'precio' => 349.99,
    'stock' => 50,
]);

// Método 3: Crear o encontrar por atributos
$producto = Producto::firstOrCreate(
    ['sku' => 'LAP-001'],           // Buscar por estos atributos
    ['nombre' => 'Laptop', 'precio' => 999.99] // Crear con estos si no existe
);

// Método 4: Crear o actualizar
$producto = Producto::updateOrCreate(
    ['sku' => 'LAP-001'],
    ['precio' => 1099.99, 'stock' => 30]
);
```

### READ: Consultar registros

```php
// Obtener todos los registros
$productos = Producto::all();

// Encontrar por ID
$producto = Producto::find(1);

// Encontrar por ID o lanzar excepción 404
$producto = Producto::findOrFail(1);

// Encontrar múltiples por ID
$productos = Producto::find([1, 2, 3]);

// Primer registro que cumpla una condición
$producto = Producto::where('activo', true)->first();

// Primer registro o excepción 404
$producto = Producto::where('sku', 'LAP-001')->firstOrFail();

// Filtros con where
$productos = Producto::where('precio', '>', 100)
    ->where('stock', '>', 0)
    ->orderBy('precio', 'asc')
    ->limit(10)
    ->get();

// Obtener solo ciertas columnas
$nombres = Producto::select('nombre', 'precio')->get();

// Contar registros
$total = Producto::where('activo', true)->count();

// Agregaciones
$precioPromedio = Producto::avg('precio');
$precioMaximo = Producto::max('precio');
$sumaTotalStock = Producto::sum('stock');

// Obtener un valor de una sola columna
$nombres = Producto::pluck('nombre');

// Pluck con clave personalizada
$precios = Producto::pluck('precio', 'nombre');
// => ['Laptop' => 1299.99, 'Monitor' => 349.99]

// Paginación
$productos = Producto::paginate(15);
$productos = Producto::simplePaginate(15);

// Chunking para procesar grandes cantidades
Producto::chunk(100, function ($productos) {
    foreach ($productos as $producto) {
        // Procesar cada producto
    }
});
```

### UPDATE: Actualizar registros

```php
// Método 1: Buscar, modificar y guardar
$producto = Producto::find(1);
$producto->precio = 1199.99;
$producto->save();

// Método 2: Actualización masiva con update()
Producto::where('categoria_id', 5)
    ->update(['activo' => false]);

// Método 3: Incrementar/decrementar valores
$producto->increment('stock', 10);  // Sumar 10 al stock
$producto->decrement('stock', 3);   // Restar 3 al stock

// Incrementar con actualización adicional
Producto::where('id', 1)->increment('visitas', 1, [
    'ultima_visita' => now()
]);
```

### DELETE: Eliminar registros

```php
// Método 1: Buscar y eliminar
$producto = Producto::find(1);
$producto->delete();

// Método 2: Eliminar por ID directamente
Producto::destroy(1);
Producto::destroy([1, 2, 3]);

// Método 3: Eliminar por condición
Producto::where('activo', false)->delete();

// Soft Deletes: borrado suave (mantiene el registro en la BD)
// Requiere: use SoftDeletes en el modelo y columna deleted_at
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;
}

// Ahora delete() marca deleted_at en vez de eliminar
$producto->delete();      // Soft delete
$producto->restore();     // Restaurar un registro eliminado suavemente
$producto->forceDelete(); // Eliminar permanentemente

// Consultar incluyendo soft-deleted
Producto::withTrashed()->get();

// Solo registros eliminados
Producto::onlyTrashed()->get();
```

## Mass Assignment: `$fillable` y `$guarded`

Laravel protege contra la asignación masiva no deseada. Debes declarar qué campos son asignables:

```php
class Producto extends Model
{
    // Opción 1: Lista blanca — solo estos campos son asignables masivamente
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'categoria_id',
    ];

    // Opción 2: Lista negra — todos excepto estos son asignables
    // protected $guarded = ['id', 'created_at', 'updated_at'];

    // Permitir todo (¡NO recomendado en producción!)
    // protected $guarded = [];
}
```

> **Regla de oro**: Usa `$fillable` para ser explícito sobre qué campos acepta el modelo. Nunca uses `$guarded = []` en producción.

## Scopes: Consultas Reutilizables

### Scopes locales

Los scopes locales encapsulan condiciones de consulta reutilizables:

```php
class Producto extends Model
{
    // Definir un scope local (el método empieza con "scope")
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopeBarato($query, float $precioMaximo = 50.0)
    {
        return $query->where('precio', '<=', $precioMaximo);
    }

    public function scopeEnStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeRecientes($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}

// Uso: se encadenan como métodos (sin el prefijo "scope")
$productos = Producto::activo()
    ->enStock()
    ->barato(100)
    ->recientes()
    ->get();
```

### Scopes globales

Los scopes globales se aplican automáticamente a todas las consultas del modelo:

```php
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

// Definir el scope como clase
class ActivoScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('activo', true);
    }
}

// Aplicar en el modelo
class Producto extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new ActivoScope);
    }
}

// Todas las consultas filtrarán por activo = true automáticamente
Producto::all(); // Solo activos

// Ignorar el scope global temporalmente
Producto::withoutGlobalScope(ActivoScope::class)->get();
```

## Accessors y Mutators

Los accessors y mutators transforman valores al leerlos o escribirlos:

```php
use Illuminate\Database\Eloquent\Casts\Attribute;

class Producto extends Model
{
    // Accessor: transforma el valor al leerlo
    protected function nombre(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords($value),
        );
    }

    // Mutator: transforma el valor al escribirlo
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower(str_replace(' ', '-', $value)),
        );
    }

    // Accessor + Mutator combinados
    protected function precio(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => $value / 100,      // Almacenado en centavos
            set: fn (float $value) => (int) ($value * 100), // Guardar en centavos
        );
    }

    // Accessor para atributo virtual (no existe en la BD)
    protected function precioFormateado(): Attribute
    {
        return Attribute::make(
            get: fn () => '$' . number_format($this->precio, 2),
        );
    }
}

// Uso
$producto = Producto::find(1);
echo $producto->nombre;            // "Laptop Dell Xps" (capitalizado)
echo $producto->precio_formateado; // "$1,299.99"
```

## Attribute Casting

El casting convierte automáticamente tipos de datos al leer y escribir:

```php
class Producto extends Model
{
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'activo' => 'boolean',
            'metadatos' => 'array',       // JSON -> array PHP
            'opciones' => 'collection',   // JSON -> Collection
            'publicado_en' => 'datetime',
            'fecha_expiracion' => 'date',
            'configuracion' => 'object',  // JSON -> stdClass
            'etiquetas' => AsStringable::class,
        ];
    }
}

// Los valores se convierten automáticamente
$producto = Producto::find(1);
$producto->activo;        // true (boolean, no 1)
$producto->metadatos;     // ['color' => 'rojo'] (array, no JSON string)
$producto->publicado_en;  // Instancia de Carbon
```

## Ejercicio Práctico

Crea un modelo `Articulo` completo para un blog:

1. **Genera** el modelo con migración: `php artisan make:model Articulo -m`.
2. **Define `$fillable`** con: `titulo`, `slug`, `contenido`, `extracto`, `publicado`, `publicado_en`, `autor_id`.
3. **Crea dos scopes locales**: `publicado()` y `recientes()`.
4. **Añade un accessor** para `titulo` que lo capitalice.
5. **Añade un mutator** para `slug` que lo convierta a minúsculas y reemplace espacios por guiones.
6. **Define casts** para `publicado` (boolean), `publicado_en` (datetime) y `metadatos` (array).
7. **Prueba en Tinker**: crea 3 artículos, consulta con los scopes y verifica los accessors.

```php
// Solución: app/Models/Articulo.php
class Articulo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'titulo', 'slug', 'contenido', 'extracto',
        'publicado', 'publicado_en', 'autor_id',
    ];

    protected function casts(): array
    {
        return [
            'publicado' => 'boolean',
            'publicado_en' => 'datetime',
        ];
    }

    public function scopePublicado($query)
    {
        return $query->where('publicado', true)
                     ->where('publicado_en', '<=', now());
    }

    public function scopeRecientes($query)
    {
        return $query->orderBy('publicado_en', 'desc');
    }

    protected function titulo(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
        );
    }
}
```

## Resumen

- Cada **tabla** tiene un **modelo Eloquent** correspondiente que sigue convenciones de nombres.
- Las operaciones **CRUD** se realizan con métodos como `create()`, `find()`, `update()` y `delete()`.
- **Mass assignment** se controla con `$fillable` (lista blanca) o `$guarded` (lista negra).
- Los **scopes locales** encapsulan consultas reutilizables; los **scopes globales** se aplican automáticamente.
- Los **accessors** transforman valores al leerlos; los **mutators** al escribirlos.
- El **casting** convierte automáticamente tipos de datos entre PHP y la base de datos.
