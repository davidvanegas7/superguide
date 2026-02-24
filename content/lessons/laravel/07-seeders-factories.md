---
title: "Seeders y Factories"
slug: "laravel-seeders-factories"
description: "Aprende a poblar tu base de datos con datos de prueba usando Seeders y Factories, generación de datos falsos con Faker y estados."
---

# Seeders y Factories

Cuando desarrollas una aplicación, necesitas datos de prueba para verificar que todo funciona correctamente. Laravel proporciona dos herramientas complementarias: **Seeders** para ejecutar la lógica de inserción y **Factories** para definir cómo se generan los datos falsos. Juntos, te permiten poblar tu base de datos en segundos.

## Seeders: Poblar la Base de Datos

Un seeder es una clase cuyo método `run()` inserta datos en la base de datos.

### Crear un seeder

```bash
php artisan make:seeder CategoriaSeeder
```

```php
<?php
// database/seeders/CategoriaSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        // Insertar datos manualmente
        $categorias = [
            ['nombre' => 'Tecnología', 'slug' => 'tecnologia'],
            ['nombre' => 'Ciencia', 'slug' => 'ciencia'],
            ['nombre' => 'Deportes', 'slug' => 'deportes'],
            ['nombre' => 'Cultura', 'slug' => 'cultura'],
            ['nombre' => 'Economía', 'slug' => 'economia'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}
```

### El seeder principal: `DatabaseSeeder`

El archivo `DatabaseSeeder` es el punto de entrada que orquesta todos los demás seeders:

```php
<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ejecutar seeders en orden (respetando dependencias)
        $this->call([
            CategoriaSeeder::class,
            UsuarioSeeder::class,
            PostSeeder::class,
            ComentarioSeeder::class,
        ]);
    }
}
```

### Ejecutar seeders

```bash
# Ejecutar el DatabaseSeeder (ejecuta todos los seeders registrados)
php artisan db:seed

# Ejecutar un seeder específico
php artisan db:seed --class=CategoriaSeeder

# Migrar y sembrar en un solo comando
php artisan migrate:fresh --seed

# Confirmar en producción (requiere --force)
php artisan db:seed --force
```

## Factories: Generadores de Datos Falsos

Las factories definen plantillas para generar modelos con datos realistas usando la librería **Faker**.

### Crear una factory

```bash
php artisan make:factory PostFactory
# o al crear el modelo
php artisan make:model Post -f
```

```php
<?php
// database/factories/PostFactory.php

namespace Database\Factories;

use App\Models\User;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    /**
     * Definir el estado por defecto del modelo.
     */
    public function definition(): array
    {
        $titulo = fake()->sentence(6);

        return [
            'user_id' => User::factory(),       // Crea un usuario automáticamente
            'categoria_id' => Categoria::factory(),
            'titulo' => $titulo,
            'slug' => Str::slug($titulo),
            'extracto' => fake()->paragraph(2),
            'contenido' => fake()->paragraphs(5, true), // 5 párrafos como string
            'imagen' => fake()->imageUrl(800, 400),
            'publicado' => fake()->boolean(80),  // 80% de probabilidad de true
            'publicado_en' => fake()->dateTimeBetween('-1 year', 'now'),
            'visitas' => fake()->numberBetween(0, 10000),
        ];
    }
}
```

### Datos de Faker disponibles

Faker proporciona generadores para todo tipo de datos:

```php
// Texto
fake()->name();                    // "María García López"
fake()->firstName();               // "Carlos"
fake()->lastName();                // "Rodríguez"
fake()->sentence();                // "Lorem ipsum dolor sit amet."
fake()->paragraph();               // Un párrafo completo
fake()->text(200);                 // Texto de hasta 200 caracteres
fake()->word();                    // "voluptas"
fake()->words(3, true);           // "aperiam est aut"

// Números
fake()->numberBetween(1, 100);    // 42
fake()->randomFloat(2, 10, 1000); // 523.67
fake()->boolean(70);              // true (70% probabilidad)
fake()->randomElement(['a', 'b']); // 'a' o 'b'

// Internet
fake()->email();                   // "juan@ejemplo.com"
fake()->safeEmail();              // "juan@example.org"
fake()->url();                     // "https://www.ejemplo.com"
fake()->userName();                // "juan_garcia"
fake()->password();                // "k5@#Jd8sL"
fake()->ipv4();                    // "192.168.1.45"

// Fechas
fake()->date();                    // "2025-03-15"
fake()->dateTime();                // DateTime object
fake()->dateTimeBetween('-1 year', 'now');
fake()->time();                    // "14:30:00"

// Direcciones
fake()->address();                 // Dirección completa
fake()->city();                    // "Madrid"
fake()->country();                 // "España"
fake()->postcode();                // "28001"
fake()->latitude();                // 40.4168
fake()->longitude();               // -3.7038

// Teléfono
fake()->phoneNumber();             // "+34 612 345 678"

// Imágenes
fake()->imageUrl(640, 480);        // URL de imagen placeholder

// Colores
fake()->hexColor();                // "#fa3cc2"
fake()->rgbColor();                // "0,255,122"

// Únicos (no repite valores)
fake()->unique()->email();
fake()->unique()->numberBetween(1, 100);
```

### Usar Faker en español

Para datos en español, configura el locale en la configuración de la aplicación:

```php
// config/app.php
'faker_locale' => 'es_ES',
```

Ahora `fake()->name()` generará nombres como "José Antonio Martínez Ruiz".

## Usar Factories

### Crear modelos

```php
use App\Models\Post;

// Crear un solo modelo (guardado en BD)
$post = Post::factory()->create();

// Crear un modelo sin guardarlo en BD (solo instancia)
$post = Post::factory()->make();

// Crear múltiples modelos
$posts = Post::factory()->count(50)->create();
// o equivalente:
$posts = Post::factory(50)->create();

// Crear con atributos específicos (sobrescriben la factory)
$post = Post::factory()->create([
    'titulo' => 'Mi Post Personalizado',
    'publicado' => true,
]);
```

### Estados (States)

Los estados permiten definir variaciones del modelo:

```php
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(),
            'contenido' => fake()->paragraphs(3, true),
            'publicado' => false,
            'publicado_en' => null,
        ];
    }

    // Estado: post publicado
    public function publicado(): static
    {
        return $this->state(fn (array $attributes) => [
            'publicado' => true,
            'publicado_en' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    // Estado: post destacado
    public function destacado(): static
    {
        return $this->state(fn (array $attributes) => [
            'destacado' => true,
            'visitas' => fake()->numberBetween(5000, 50000),
        ]);
    }

    // Estado: post borrador
    public function borrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'publicado' => false,
            'publicado_en' => null,
        ]);
    }

    // Estado: post programado para el futuro
    public function programado(): static
    {
        return $this->state(fn (array $attributes) => [
            'publicado' => true,
            'publicado_en' => fake()->dateTimeBetween('+1 day', '+3 months'),
        ]);
    }
}
```

```php
// Uso de estados
Post::factory()->publicado()->create();
Post::factory()->destacado()->publicado()->create();
Post::factory()->borrador()->count(10)->create();
Post::factory()->programado()->count(5)->create();
```

## Relaciones en Factories

### Pertenece a (belongsTo)

```php
// La factory crea automáticamente el usuario relacionado
$post = Post::factory()->create();
// user_id => User::factory() crea un User automáticamente

// Asignar a un usuario existente
$usuario = User::factory()->create();
$post = Post::factory()->create(['user_id' => $usuario->id]);

// O usando el método for()
$post = Post::factory()
    ->for($usuario)
    ->create();
```

### Tiene muchos (hasMany)

```php
// Crear un usuario con 5 posts
$usuario = User::factory()
    ->has(Post::factory()->count(5))
    ->create();

// Sintaxis mágica: método con nombre de la relación
$usuario = User::factory()
    ->hasPosts(5)              // Crea 5 posts asociados
    ->create();

// Posts con estado específico
$usuario = User::factory()
    ->has(Post::factory()->count(3)->publicado())
    ->create();

// Sintaxis mágica con estado
$usuario = User::factory()
    ->hasPosts(3, ['publicado' => true])
    ->create();
```

### Muchos a muchos (belongsToMany)

```php
// Crear un post con 3 etiquetas
$post = Post::factory()
    ->hasAttached(
        Tag::factory()->count(3),
        ['orden' => 1]  // Datos de la tabla pivote (opcional)
    )
    ->create();

// Crear post con etiquetas existentes
$etiquetas = Tag::factory(5)->create();
$post = Post::factory()
    ->hasAttached($etiquetas)
    ->create();
```

## Ejemplo Completo: Seeder con Factories

```php
<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Categoria;
use App\Models\Comentario;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear categorías fijas
        $categorias = collect(['Tecnología', 'Ciencia', 'Deportes', 'Cultura'])
            ->map(fn ($nombre) => Categoria::factory()->create(['nombre' => $nombre]));

        // 2. Crear etiquetas
        $etiquetas = Tag::factory(15)->create();

        // 3. Crear un usuario administrador
        $admin = User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
        ]);

        // 4. Crear usuarios regulares con posts
        User::factory(20)->create()->each(function ($usuario) use ($categorias, $etiquetas) {
            // Cada usuario tiene entre 1 y 5 posts
            $posts = Post::factory(rand(1, 5))
                ->publicado()
                ->create([
                    'user_id' => $usuario->id,
                    'categoria_id' => $categorias->random()->id,
                ]);

            // Cada post tiene entre 1 y 4 etiquetas
            $posts->each(function ($post) use ($etiquetas) {
                $post->etiquetas()->attach(
                    $etiquetas->random(rand(1, 4))->pluck('id')
                );
            });
        });

        // 5. Añadir comentarios a posts aleatorios
        Post::all()->random(30)->each(function ($post) {
            Comentario::factory(rand(1, 10))->create([
                'post_id' => $post->id,
            ]);
        });

        $this->command->info('¡Base de datos poblada exitosamente!');
        $this->command->info('Usuarios: ' . User::count());
        $this->command->info('Posts: ' . Post::count());
        $this->command->info('Comentarios: ' . Comentario::count());
    }
}
```

## Reconfigure: Evitar Duplicados

Para seeders que se ejecutan múltiples veces, usa `truncate()` o `updateOrCreate()`:

```php
public function run(): void
{
    // Opción 1: Vaciar la tabla antes de insertar
    Categoria::truncate();

    // Opción 2: Insertar o actualizar
    $categorias = ['Tecnología', 'Ciencia', 'Deportes'];

    foreach ($categorias as $nombre) {
        Categoria::updateOrCreate(
            ['slug' => Str::slug($nombre)],
            ['nombre' => $nombre]
        );
    }
}
```

## Ejercicio Práctico

Crea un sistema completo de seeders y factories para una tienda online:

1. **Crea la factory** `ProductoFactory` con: nombre, descripción, precio (entre 9.99 y 999.99), stock, SKU único e imagen.
2. **Define 3 estados**: `agotado()` (stock = 0), `oferta()` (precio reducido) y `nuevo()` (created_at reciente).
3. **Crea la factory** `PedidoFactory` que genere pedidos con usuario y total calculado.
4. **Crea el seeder** `TiendaSeeder` que genere: 10 categorías, 100 productos (repartidos entre las categorías), 50 usuarios con pedidos.
5. **Registra** todo en `DatabaseSeeder` y ejecuta `php artisan migrate:fresh --seed`.

```bash
# Comandos para generar los archivos
php artisan make:factory ProductoFactory
php artisan make:factory PedidoFactory
php artisan make:seeder TiendaSeeder
```

## Resumen

- Los **Seeders** insertan datos en la base de datos mediante el método `run()`.
- El **`DatabaseSeeder`** orquesta la ejecución de todos los seeders con `$this->call()`.
- Las **Factories** definen plantillas de datos usando **Faker** para generar datos realistas.
- Se crean modelos con `factory()->create()` (persiste en BD) o `factory()->make()` (solo instancia).
- Los **estados** (`state()`) definen variaciones como "publicado", "destacado" o "borrador".
- Las factories soportan **relaciones**: `has()`, `for()`, `hasAttached()` y métodos mágicos.
- Se ejecutan con `php artisan db:seed` o `php artisan migrate:fresh --seed`.
