---
title: "Migraciones y Schema Builder"
slug: "laravel-migraciones-schema"
description: "Aprende a versionar tu base de datos con migraciones, definir tablas con el Schema Builder y gestionar claves foráneas e índices."
---

# Migraciones y Schema Builder

Las migraciones son el sistema de control de versiones para tu base de datos. Permiten definir y modificar el esquema de forma programática, compartir esos cambios con tu equipo y revertirlos cuando sea necesario. En esta lección dominarás la creación de migraciones y el uso del Schema Builder de Laravel.

## ¿Qué son las Migraciones?

Una migración es una clase PHP que contiene dos métodos:

- **`up()`**: define los cambios que se aplicarán (crear tabla, añadir columna, etc.).
- **`down()`**: revierte los cambios definidos en `up()`.

Las migraciones se almacenan en `database/migrations/` y se ejecutan en orden cronológico gracias a su marca temporal en el nombre del archivo.

## Crear una Migración

```bash
# Crear una migración para una nueva tabla
php artisan make:migration create_productos_table

# Crear una migración para modificar una tabla existente
php artisan make:migration add_precio_to_productos_table

# Crear modelo con migración incluida
php artisan make:model Producto -m
```

Laravel genera automáticamente un archivo con la marca temporal:

```
2026_02_23_120000_create_productos_table.php
```

## Anatomía de una Migración

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar la migración: crear la tabla productos.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();                          // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('nombre');              // VARCHAR(255)
            $table->text('descripcion')->nullable(); // TEXT, permite NULL
            $table->decimal('precio', 10, 2);      // DECIMAL(10,2)
            $table->integer('stock')->default(0);  // INT con valor por defecto 0
            $table->boolean('activo')->default(true);
            $table->timestamps();                  // created_at y updated_at
        });
    }

    /**
     * Revertir la migración: eliminar la tabla.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
```

## Tipos de Columna Disponibles

El Schema Builder ofrece métodos para todos los tipos de columna comunes:

### Números

```php
$table->id();                       // Alias de bigIncrements('id')
$table->bigIncrements('id');        // BIGINT UNSIGNED AUTO_INCREMENT
$table->integer('cantidad');        // INT
$table->tinyInteger('nivel');       // TINYINT
$table->smallInteger('orden');      // SMALLINT
$table->mediumInteger('puntos');    // MEDIUMINT
$table->bigInteger('visitas');      // BIGINT
$table->unsignedInteger('edad');    // INT UNSIGNED
$table->decimal('precio', 8, 2);   // DECIMAL(8,2)
$table->float('promedio', 8, 2);   // FLOAT(8,2)
$table->double('coordenada');       // DOUBLE
```

### Texto

```php
$table->char('codigo', 5);         // CHAR(5)
$table->string('nombre');           // VARCHAR(255)
$table->string('slug', 100);       // VARCHAR(100)
$table->text('descripcion');        // TEXT
$table->mediumText('contenido');    // MEDIUMTEXT
$table->longText('cuerpo');         // LONGTEXT
```

### Fecha y Hora

```php
$table->date('fecha_nacimiento');       // DATE
$table->dateTime('publicado_en');       // DATETIME
$table->time('hora_inicio');            // TIME
$table->timestamp('verificado_en');     // TIMESTAMP
$table->timestamps();                   // created_at y updated_at (TIMESTAMP)
$table->softDeletes();                  // deleted_at (TIMESTAMP, para borrado suave)
$table->year('anio_graduacion');        // YEAR
```

### Otros tipos

```php
$table->boolean('activo');              // BOOLEAN (TINYINT(1))
$table->enum('estado', ['borrador', 'publicado', 'archivado']);
$table->json('metadatos');              // JSON
$table->uuid('uuid');                   // CHAR(36)
$table->ulid('ulid');                   // CHAR(26)
$table->ipAddress('ip_visitante');      // VARCHAR(45)
$table->macAddress('mac');              // VARCHAR(17)
$table->binary('datos');                // BLOB
$table->rememberToken();               // VARCHAR(100), para "Recordarme"
```

## Modificadores de Columna

```php
$table->string('email')->nullable();           // Permite NULL
$table->integer('stock')->default(0);          // Valor por defecto
$table->string('nombre')->comment('Nombre completo del usuario');
$table->string('codigo')->unique();            // Restricción UNIQUE
$table->text('notas')->nullable()->after('descripcion'); // Ordenar posición
$table->integer('orden')->unsigned();          // Sin signo
$table->string('temporal')->virtualAs('CONCAT(nombre, " ", apellido)'); // Columna virtual
```

## Índices

Los índices mejoran el rendimiento de las consultas:

```php
// Índice primario (ya incluido con id())
$table->primary('id');

// Índice único
$table->string('email')->unique();
// o
$table->unique('email');

// Índice regular (para búsquedas frecuentes)
$table->index('nombre');

// Índice compuesto
$table->index(['apellido', 'nombre']);

// Índice de texto completo
$table->fullText('contenido');

// Nombre personalizado para el índice
$table->index('email', 'idx_usuarios_email');
```

## Claves Foráneas (Foreign Keys)

Las claves foráneas garantizan la integridad referencial entre tablas:

```php
// Forma moderna y concisa (Laravel 7+)
Schema::create('pedidos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('producto_id')->constrained()->onDelete('cascade');
    // constrained() sin argumento busca la tabla 'productos' (plural del prefijo)
    $table->integer('cantidad');
    $table->decimal('total', 10, 2);
    $table->timestamps();
});

// Forma explícita (más control)
Schema::create('comentarios', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('post_id');
    $table->text('cuerpo');
    $table->timestamps();

    $table->foreign('post_id')
          ->references('id')
          ->on('posts')
          ->onUpdate('cascade')
          ->onDelete('cascade');
});

// Eliminar una clave foránea
Schema::table('pedidos', function (Blueprint $table) {
    $table->dropForeign(['usuario_id']);
});
```

### Acciones en cascada

```php
->onDelete('cascade')    // Eliminar registros hijos al eliminar el padre
->onDelete('set null')   // Poner NULL en los hijos (columna debe ser nullable)
->onDelete('restrict')   // Impedir la eliminación si hay hijos
->onUpdate('cascade')    // Actualizar la FK en hijos si cambia el padre
```

## Modificar Tablas Existentes

```php
// Añadir columnas a una tabla existente
Schema::table('productos', function (Blueprint $table) {
    $table->string('sku')->unique()->after('nombre');
    $table->foreignId('categoria_id')->nullable()->constrained();
});

// Renombrar una columna
Schema::table('productos', function (Blueprint $table) {
    $table->renameColumn('nombre', 'titulo');
});

// Cambiar el tipo de una columna
Schema::table('productos', function (Blueprint $table) {
    $table->text('descripcion')->nullable()->change();
});

// Eliminar columnas
Schema::table('productos', function (Blueprint $table) {
    $table->dropColumn(['sku', 'temporal']);
});

// Renombrar una tabla
Schema::rename('productos', 'articulos');

// Eliminar una tabla
Schema::dropIfExists('productos');
```

## Comandos de Migración

```bash
# Ejecutar todas las migraciones pendientes
php artisan migrate

# Ver el estado de las migraciones
php artisan migrate:status

# Revertir la última migración (último batch)
php artisan migrate:rollback

# Revertir las últimas N migraciones
php artisan migrate:rollback --step=3

# Revertir TODAS las migraciones
php artisan migrate:reset

# Revertir todo y volver a migrar (refresh)
php artisan migrate:refresh

# Refresh con seeders
php artisan migrate:refresh --seed

# Eliminar todas las tablas y volver a migrar (fresh)
php artisan migrate:fresh

# Fresh con seeders
php artisan migrate:fresh --seed

# Comprimir migraciones en un archivo SQL (squash)
php artisan schema:dump

# Squash y eliminar migraciones antiguas
php artisan schema:dump --prune
```

### Diferencia entre `refresh` y `fresh`

- **`migrate:refresh`** ejecuta el método `down()` de cada migración en orden inverso y luego `up()` de todas.
- **`migrate:fresh`** elimina directamente todas las tablas (DROP) y ejecuta todas las migraciones desde cero. Es más rápido pero no prueba los métodos `down()`.

## Squash de Migraciones

Cuando acumulas muchas migraciones, puedes comprimirlas en un solo archivo SQL:

```bash
# Genera database/schema/mysql-schema.sql
php artisan schema:dump

# Genera el dump y elimina los archivos de migración antiguos
php artisan schema:dump --prune
```

Al ejecutar `migrate`, Laravel primero carga el esquema SQL y luego ejecuta las migraciones restantes.

## Ejercicio Práctico

Diseña el esquema de base de datos para una tienda online:

1. **Crea la migración** `create_categorias_table` con campos: `id`, `nombre`, `slug` (único), `descripcion` (nullable), `timestamps`.
2. **Crea la migración** `create_productos_table` con: `id`, `categoria_id` (FK), `nombre`, `slug`, `descripcion`, `precio` (decimal), `stock` (entero, default 0), `activo` (boolean, default true), `timestamps`, `softDeletes`.
3. **Crea la migración** `create_pedidos_table` con: `id`, `usuario_id` (FK a users), `total`, `estado` (enum), `timestamps`.
4. **Crea la migración** `create_pedido_producto_table` (tabla pivote) con: `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`.
5. **Ejecuta** `php artisan migrate` y verifica con `migrate:status`.

## Resumen

- Las **migraciones** permiten versionar el esquema de la base de datos de forma programática y colaborativa.
- Se crean con `php artisan make:migration` y contienen métodos `up()` y `down()`.
- El **Schema Builder** ofrece métodos para todos los tipos de columna: `string`, `integer`, `decimal`, `boolean`, `json`, `timestamp`, etc.
- Los **modificadores** como `nullable()`, `default()` y `unique()` ajustan el comportamiento de las columnas.
- Las **claves foráneas** se definen con `foreignId()->constrained()` y soportan acciones en cascada.
- Los comandos `migrate`, `rollback`, `refresh`, `fresh` y `schema:dump` gestionan el ciclo de vida de las migraciones.
