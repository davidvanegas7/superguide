---
title: "Eventos y Listeners"
slug: "laravel-eventos-listeners"
description: "Implementa el patrón Observer en Laravel con eventos y listeners para desacoplar la lógica de tu aplicación."
---

# Eventos y Listeners

El patrón **Observer** (observador) permite que diferentes partes de tu aplicación reaccionen ante acciones específicas sin acoplarse directamente. En Laravel, los **eventos** representan algo que sucedió (un usuario se registró, un pedido fue creado), y los **listeners** contienen la lógica que debe ejecutarse en respuesta. Este patrón promueve código limpio, desacoplado y fácil de extender.

## Conceptos Fundamentales

Imagina que cuando un usuario se registra, necesitas: enviar un email de bienvenida, crear un perfil por defecto, y registrar la acción en un log. Sin eventos, todo ese código estaría en el controlador. Con eventos, el controlador solo dispara `UserRegistered` y cada listener se encarga de una tarea específica.

```
Controller → dispara UserRegistered
                ├── SendWelcomeEmail (listener)
                ├── CreateDefaultProfile (listener)
                └── LogRegistration (listener)
```

## Crear Eventos y Listeners

```bash
# Crear un evento
php artisan make:event OrderShipped

# Crear un listener
php artisan make:listener SendShipmentNotification --event=OrderShipped
```

### Estructura de un Evento

```php
// app/Events/OrderShipped.php
namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderShipped
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * El evento recibe los datos relevantes.
     */
    public function __construct(
        public Order $order
    ) {}
}
```

Los eventos son simples contenedores de datos. No contienen lógica de negocio, solo transportan la información necesaria para que los listeners trabajen.

### Estructura de un Listener

```php
// app/Listeners/SendShipmentNotification.php
namespace App\Listeners;

use App\Events\OrderShipped;
use App\Notifications\OrderShippedNotification;

class SendShipmentNotification
{
    /**
     * Manejar el evento.
     */
    public function handle(OrderShipped $event): void
    {
        // Acceder a los datos del evento
        $order = $event->order;

        // Enviar notificación al cliente
        $order->user->notify(new OrderShippedNotification($order));
    }
}
```

## Registrar Eventos y Listeners

### Registro Manual en AppServiceProvider

```php
// app/Providers/AppServiceProvider.php
use App\Events\OrderShipped;
use App\Listeners\SendShipmentNotification;
use App\Listeners\UpdateInventory;
use App\Listeners\LogOrderShipment;
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    Event::listen(OrderShipped::class, SendShipmentNotification::class);
    Event::listen(OrderShipped::class, UpdateInventory::class);
    Event::listen(OrderShipped::class, LogOrderShipment::class);

    // También puedes usar closures para listeners simples
    Event::listen(OrderShipped::class, function (OrderShipped $event) {
        \Log::info("Orden #{$event->order->id} enviada");
    });
}
```

### Event Discovery (Auto-descubrimiento)

Laravel puede descubrir automáticamente listeners si siguen la convención. Dentro de la carpeta `app/Listeners`, Laravel examina los métodos `handle` y los vincula al evento que reciben:

```php
// app/Listeners/SendShipmentNotification.php
// Laravel detecta automáticamente que escucha OrderShipped
// porque el método handle tiene type-hint de OrderShipped

class SendShipmentNotification
{
    public function handle(OrderShipped $event): void
    {
        // ...
    }
}
```

Para verificar los eventos y listeners registrados:

```bash
php artisan event:list
```

## Disparar Eventos

```php
// En un controller o servicio
use App\Events\OrderShipped;

class OrderController extends Controller
{
    public function ship(Order $order)
    {
        // Actualizar estado de la orden
        $order->update(['status' => 'shipped', 'shipped_at' => now()]);

        // Disparar el evento
        OrderShipped::dispatch($order);

        // Alternativa usando el helper event()
        event(new OrderShipped($order));

        return redirect()->route('orders.show', $order)
            ->with('success', 'Orden enviada correctamente');
    }
}
```

## Listeners Asíncronos (Queue)

Los listeners pueden procesarse en segundo plano implementando `ShouldQueue`:

```php
// app/Listeners/SendShipmentNotification.php
namespace App\Listeners;

use App\Events\OrderShipped;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendShipmentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Cola a usar para este listener.
     */
    public string $queue = 'notifications';

    /**
     * Tiempo de retraso antes de procesar.
     */
    public int $delay = 10;

    /**
     * Número máximo de intentos.
     */
    public int $tries = 3;

    public function handle(OrderShipped $event): void
    {
        $event->order->user->notify(
            new OrderShippedNotification($event->order)
        );
    }

    /**
     * Determinar si el listener debe ser encolado.
     */
    public function shouldQueue(OrderShipped $event): bool
    {
        // Solo encolar si el usuario tiene email verificado
        return $event->order->user->hasVerifiedEmail();
    }

    /**
     * Manejar la falla del listener.
     */
    public function failed(OrderShipped $event, \Throwable $exception): void
    {
        \Log::error("Fallo al notificar envío: {$exception->getMessage()}");
    }
}
```

## Event Subscribers

Un subscriber es una clase que agrupa múltiples listeners relacionados en un solo lugar:

```php
// app/Listeners/OrderEventSubscriber.php
namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderShipped;
use App\Events\OrderCancelled;
use Illuminate\Events\Dispatcher;

class OrderEventSubscriber
{
    /**
     * Manejar cuando se crea una orden.
     */
    public function handleOrderCreated(OrderCreated $event): void
    {
        \Log::info("Orden #{$event->order->id} creada");
        // Reservar inventario, enviar confirmación, etc.
    }

    /**
     * Manejar cuando se envía una orden.
     */
    public function handleOrderShipped(OrderShipped $event): void
    {
        \Log::info("Orden #{$event->order->id} enviada");
    }

    /**
     * Manejar cuando se cancela una orden.
     */
    public function handleOrderCancelled(OrderCancelled $event): void
    {
        \Log::info("Orden #{$event->order->id} cancelada");
        // Devolver inventario, procesar reembolso, etc.
    }

    /**
     * Registrar los listeners del subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            OrderCreated::class   => 'handleOrderCreated',
            OrderShipped::class   => 'handleOrderShipped',
            OrderCancelled::class => 'handleOrderCancelled',
        ];
    }
}
```

Registrar el subscriber:

```php
// app/Providers/AppServiceProvider.php
use App\Listeners\OrderEventSubscriber;

public function boot(): void
{
    Event::subscribe(OrderEventSubscriber::class);
}
```

## Model Events y Observers

Eloquent dispara eventos automáticamente durante el ciclo de vida de un modelo: `creating`, `created`, `updating`, `updated`, `deleting`, `deleted`, `saving`, `saved`, `restoring`, `restored`.

### Listeners Directos en el Modelo

```php
// app/Models/User.php
class User extends Authenticatable
{
    protected static function booted(): void
    {
        // Se ejecuta al crear un usuario
        static::created(function (User $user) {
            $user->profile()->create([
                'bio' => 'Nuevo usuario',
            ]);
        });

        // Se ejecuta antes de eliminar
        static::deleting(function (User $user) {
            $user->posts()->delete();
        });
    }
}
```

### Observers

Para modelos con mucha lógica de eventos, usa un Observer dedicado:

```bash
php artisan make:observer UserObserver --model=User
```

```php
// app/Observers/UserObserver.php
namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Se ejecuta al crear un usuario.
     */
    public function created(User $user): void
    {
        // Crear perfil por defecto
        $user->profile()->create(['bio' => 'Nuevo usuario']);

        // Enviar email de bienvenida
        $user->notify(new WelcomeNotification());
    }

    /**
     * Se ejecuta al actualizar un usuario.
     */
    public function updated(User $user): void
    {
        // Si cambió el email, re-verificar
        if ($user->isDirty('email')) {
            $user->update(['email_verified_at' => null]);
        }
    }

    /**
     * Se ejecuta antes de eliminar.
     */
    public function deleting(User $user): void
    {
        // Eliminar datos relacionados
        $user->posts()->delete();
        $user->comments()->delete();
    }
}
```

Registrar el Observer:

```php
// app/Providers/AppServiceProvider.php
use App\Models\User;
use App\Observers\UserObserver;

public function boot(): void
{
    User::observe(UserObserver::class);
}
```

### Silenciar Eventos Temporalmente

```php
// Ejecutar sin disparar eventos del modelo
User::withoutEvents(function () {
    $user = User::find(1);
    $user->update(['name' => 'Nuevo nombre']);
    // No se disparará el evento "updated"
});

// También para un modelo específico
$user->saveQuietly(); // Guardar sin disparar eventos
$user->deleteQuietly();
$user->updateQuietly(['name' => 'Test']);
```

## Ejercicio Práctico

Implementa un sistema de eventos para una tienda en línea:

1. Crea el evento `ProductPurchased` que reciba un `Product` y un `User`.
2. Crea tres listeners:
   - `SendPurchaseConfirmation` (asíncrono, cola `notifications`).
   - `UpdateProductStock` (síncrono, reduce stock en 1).
   - `LogPurchase` (síncrono, registra en logs).
3. Crea un Observer `ProductObserver` para el modelo `Product`:
   - Cuando `stock` llegue a 0 al actualizar, marca `available` como `false`.
   - Cuando se elimine un producto, registra un log.
4. Registra todo en `AppServiceProvider`.
5. Dispara `ProductPurchased` desde un controller cuando se realice una compra.

```php
// Ejemplo del dispatch en el controller
public function purchase(Product $product)
{
    ProductPurchased::dispatch($product, auth()->user());
    return redirect()->back()->with('success', '¡Compra realizada!');
}
```

## Resumen

- Los **eventos** representan acciones que ocurren en tu aplicación; los **listeners** reaccionan ante ellos.
- Usa `make:event` y `make:listener` para generar las clases necesarias.
- Dispara eventos con `Event::dispatch()` o el helper `event()`.
- Los listeners asíncronos implementan `ShouldQueue` para procesarse en segundo plano.
- Los **subscribers** agrupan múltiples listeners relacionados en una sola clase.
- Los **model observers** reaccionan al ciclo de vida de modelos Eloquent (created, updated, deleted).
- El event discovery de Laravel vincula automáticamente listeners a eventos por el type-hint del método `handle`.
- Usa `withoutEvents()` o `saveQuietly()` cuando necesites operar sin disparar eventos.
