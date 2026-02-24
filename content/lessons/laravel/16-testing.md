---
title: "Testing en Laravel"
slug: "laravel-testing"
description: "Escribe tests automatizados en Laravel con PHPUnit y Pest para garantizar la calidad y confiabilidad de tu aplicación."
---

# Testing en Laravel

El testing automatizado es fundamental para construir aplicaciones confiables. Laravel viene preparado para pruebas desde el inicio, con soporte para **PHPUnit** y **Pest**, helpers para probar HTTP, bases de datos, colas, correos y mucho más. Un buen conjunto de tests te permite refactorizar con confianza, detectar errores antes de producción y documentar el comportamiento esperado de tu aplicación.

## Configuración Inicial

Laravel incluye un archivo `phpunit.xml` preconfigurado y una base de datos SQLite en memoria para tests:

```xml
<!-- phpunit.xml (fragmento relevante) -->
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="QUEUE_CONNECTION" value="sync"/>
<env name="MAIL_MAILER" value="array"/>
```

### Ejecutar Tests

```bash
# Ejecutar todos los tests
php artisan test

# Con PHPUnit directamente
./vendor/bin/phpunit

# Ejecutar un archivo específico
php artisan test --filter=ProductTest

# Ejecutar un método específico
php artisan test --filter=test_user_can_create_product

# Tests en paralelo (más rápido)
php artisan test --parallel

# Con cobertura de código
php artisan test --coverage
```

## Feature Tests vs Unit Tests

### Unit Tests

Prueban una clase o método de forma aislada, sin dependencias externas:

```bash
php artisan make:test Services/PriceCalculatorTest --unit
```

```php
// tests/Unit/Services/PriceCalculatorTest.php
namespace Tests\Unit\Services;

use App\Services\PriceCalculator;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    public function test_calcula_precio_con_descuento(): void
    {
        $calculator = new PriceCalculator();

        $result = $calculator->applyDiscount(100, 20); // 20% descuento

        $this->assertEquals(80, $result);
    }

    public function test_descuento_no_puede_ser_negativo(): void
    {
        $calculator = new PriceCalculator();

        $this->expectException(\InvalidArgumentException::class);

        $calculator->applyDiscount(100, -10);
    }

    public function test_precio_minimo_es_cero(): void
    {
        $calculator = new PriceCalculator();

        $result = $calculator->applyDiscount(100, 150);

        $this->assertEquals(0, $result);
    }
}
```

### Feature Tests

Prueban la aplicación completa, incluyendo HTTP, base de datos, etc.:

```bash
php artisan make:test ProductTest
```

```php
// tests/Feature/ProductTest.php
namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase; // Resetea la BD entre tests

    public function test_listado_de_productos(): void
    {
        // Arrange: preparar datos
        Product::factory()->count(5)->create();

        // Act: realizar la acción
        $response = $this->get('/products');

        // Assert: verificar el resultado
        $response->assertStatus(200);
        $response->assertViewHas('products');
    }

    public function test_usuario_puede_crear_producto(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/products', [
            'name'  => 'Laptop Pro',
            'price' => 999.99,
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', [
            'name'  => 'Laptop Pro',
            'price' => 999.99,
        ]);
    }

    public function test_invitado_no_puede_crear_producto(): void
    {
        $response = $this->post('/products', [
            'name'  => 'Test',
            'price' => 10,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_validacion_al_crear_producto(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/products', [
            'name'  => '', // vacío — debe fallar
            'price' => 'no-es-numero',
        ]);

        $response->assertSessionHasErrors(['name', 'price']);
    }
}
```

## HTTP Tests

Laravel proporciona una API fluida para simular peticiones HTTP y verificar respuestas:

```php
class ApiProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_retorna_productos_en_json(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data')
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'price', 'created_at']
                     ]
                 ]);
    }

    public function test_api_crea_producto(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/products', [
                'name'  => 'Mouse Gamer',
                'price' => 49.99,
            ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'data' => [
                         'name'  => 'Mouse Gamer',
                         'price' => 49.99,
                     ]
                 ]);
    }

    public function test_api_elimina_producto(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_api_retorna_404_para_producto_inexistente(): void
    {
        $response = $this->getJson('/api/products/999');

        $response->assertStatus(404);
    }
}
```

## Database Testing

### RefreshDatabase

El trait `RefreshDatabase` migra la base de datos al inicio y usa transacciones para revertir cambios entre tests:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_crear_usuario(): void
    {
        $user = User::factory()->create([
            'name'  => 'Ana García',
            'email' => 'ana@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ana@example.com',
        ]);

        $this->assertDatabaseCount('users', 1);
    }
}
```

### Factories en Tests

Las factories son esenciales para generar datos de prueba realistas:

```php
public function test_usuario_con_posts(): void
{
    // Crear usuario con 5 posts
    $user = User::factory()
        ->has(Post::factory()->count(5))
        ->create();

    $this->assertCount(5, $user->posts);

    // Crear post con estado específico
    $draft = Post::factory()
        ->draft()
        ->for($user)
        ->create();

    $this->assertFalse($draft->published);
}

public function test_relaciones_complejas(): void
{
    $user = User::factory()
        ->has(
            Order::factory()
                ->count(3)
                ->has(OrderItem::factory()->count(2))
        )
        ->create();

    $this->assertCount(3, $user->orders);
    $this->assertCount(2, $user->orders->first()->items);
}
```

## Mocking y Faking

Laravel facilita simular servicios externos y comportamientos que no quieres ejecutar realmente en tests.

### Mail::fake()

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

public function test_envio_de_email_al_comprar(): void
{
    Mail::fake(); // Interceptar todos los correos

    // Ejecutar la acción que envía email
    $this->actingAs($user)->post('/orders', $orderData);

    // Verificar que se envió el correo
    Mail::assertSent(OrderConfirmation::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    // Verificar cantidad de envíos
    Mail::assertSent(OrderConfirmation::class, 1);

    // Verificar que NO se envió otro correo
    Mail::assertNotSent(WelcomeEmail::class);
}
```

### Queue::fake()

```php
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessOrder;

public function test_job_se_despacha_al_crear_orden(): void
{
    Queue::fake();

    $this->actingAs($user)->post('/orders', $orderData);

    Queue::assertPushed(ProcessOrder::class, function ($job) {
        return $job->order->total === 150.00;
    });

    Queue::assertPushedOn('processing', ProcessOrder::class);
}
```

### Event::fake()

```php
use Illuminate\Support\Facades\Event;
use App\Events\OrderCreated;

public function test_evento_se_dispara_al_crear_orden(): void
{
    Event::fake([OrderCreated::class]); // Solo fake este evento

    $this->actingAs($user)->post('/orders', $orderData);

    Event::assertDispatched(OrderCreated::class, function ($event) {
        return $event->order->user_id === $this->user->id;
    });

    Event::assertDispatchedTimes(OrderCreated::class, 1);
}
```

### Notification::fake()

```php
use Illuminate\Support\Facades\Notification;
use App\Notifications\InvoicePaid;

public function test_notificacion_enviada_al_pagar(): void
{
    Notification::fake();

    $user = User::factory()->create();
    $invoice = Invoice::factory()->create(['user_id' => $user->id]);

    // Acción que envía notificación
    $invoice->markAsPaid();

    Notification::assertSentTo($user, InvoicePaid::class);
    Notification::assertNotSentTo($otherUser, InvoicePaid::class);
}
```

### Storage::fake()

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

public function test_subida_de_avatar(): void
{
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    $response = $this->actingAs($user)->post('/profile/avatar', [
        'avatar' => $file,
    ]);

    // Verificar que el archivo existe en el disco fake
    Storage::disk('public')->assertExists('avatars/' . $file->hashName());
}
```

## Testing con Pest

Pest ofrece una sintaxis más expresiva y concisa:

```php
// tests/Feature/ProductTest.php
use App\Models\Product;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('muestra la lista de productos', function () {
    Product::factory()->count(5)->create();

    $this->get('/products')
         ->assertStatus(200)
         ->assertViewHas('products');
});

it('permite crear un producto al usuario autenticado', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
         ->post('/products', ['name' => 'Laptop', 'price' => 999])
         ->assertRedirect('/products');

    expect(Product::count())->toBe(1);
    expect(Product::first()->name)->toBe('Laptop');
});

it('rechaza datos inválidos', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
         ->post('/products', ['name' => '', 'price' => -5])
         ->assertSessionHasErrors(['name', 'price']);
});

// Datasets para probar múltiples escenarios
it('valida precios correctamente', function (float $price, bool $valid) {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/products', ['name' => 'Test', 'price' => $price]);

    if ($valid) {
        $response->assertSessionDoesntHaveErrors('price');
    } else {
        $response->assertSessionHasErrors('price');
    }
})->with([
    [10.00, true],
    [0, false],
    [-5, false],
    [9999.99, true],
]);
```

## Ejercicio Práctico

Escribe un conjunto completo de tests para un CRUD de artículos de blog:

1. **Feature test** para `ArticleController`:
   - `test_index_muestra_articulos` — Verifica status 200 y que la vista tiene artículos.
   - `test_store_crea_articulo` — Verifica que se crea en la BD y redirige.
   - `test_store_valida_campos_requeridos` — Verifica errores de validación.
   - `test_update_modifica_articulo` — Verifica actualización en BD.
   - `test_destroy_elimina_articulo` — Verifica que desaparece de la BD.
   - `test_invitado_no_puede_crear` — Verifica redirección a login.
2. **Faking**: verifica que al crear un artículo se dispara un evento `ArticleCreated` con `Event::fake()`.
3. **Unit test** para un servicio `SlugGenerator` que genera slugs únicos a partir del título.
4. Usa `RefreshDatabase` y factories en todos los tests.

```bash
php artisan make:test ArticleTest
php artisan test --filter=ArticleTest
```

## Resumen

- Laravel soporta **PHPUnit** y **Pest** para testing, con helpers que simplifican las pruebas.
- Los **Unit Tests** prueban clases aisladas; los **Feature Tests** prueban flujos completos.
- Usa `RefreshDatabase` para una base de datos limpia entre tests.
- Los HTTP tests (`get`, `post`, `putJson`, etc.) simulan peticiones y verifican respuestas con aserciones fluidas.
- **Fakes** (`Mail::fake()`, `Queue::fake()`, `Event::fake()`) interceptan side effects para verificar sin ejecutar.
- `assertDatabaseHas`, `assertDatabaseMissing`, `assertDatabaseCount` verifican el estado de la BD.
- Las factories generan datos de prueba realistas, incluyendo relaciones complejas.
- Ejecuta `php artisan test --parallel` para correr tests en paralelo y acelerar la suite.
