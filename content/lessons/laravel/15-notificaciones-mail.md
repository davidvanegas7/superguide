---
title: "Notificaciones y Mail"
slug: "laravel-notificaciones-mail"
description: "Envía notificaciones por múltiples canales y correos electrónicos elegantes con el sistema de notificaciones y mail de Laravel."
---

# Notificaciones y Mail

Laravel ofrece un sistema potente para comunicarse con los usuarios a través de múltiples canales: correo electrónico, base de datos, SMS, Slack y más. Las **notificaciones** proporcionan una interfaz unificada para enviar mensajes por distintos canales desde una sola clase, mientras que las **Mailables** permiten crear correos HTML elegantes y reutilizables.

## Sistema de Notificaciones

Las notificaciones representan mensajes que se envían a los usuarios. Cada notificación define cómo se entrega por cada canal soportado.

### Crear una Notificación

```bash
php artisan make:notification InvoicePaid
```

```php
// app/Notifications/InvoicePaid.php
namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invoice $invoice
    ) {}

    /**
     * Canales por los que se enviará la notificación.
     */
    public function via(object $notifiable): array
    {
        // Puedes decidir los canales dinámicamente
        $channels = ['database'];

        if ($notifiable->email_notifications) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Representación como correo electrónico.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Factura Pagada #' . $this->invoice->number)
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Tu factura ha sido pagada exitosamente.')
            ->line('Monto: $' . number_format($this->invoice->amount, 2))
            ->action('Ver Factura', url('/invoices/' . $this->invoice->id))
            ->line('¡Gracias por tu pago!');
    }

    /**
     * Representación para la base de datos.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'amount'     => $this->invoice->amount,
            'message'    => "Factura #{$this->invoice->number} pagada",
        ];
    }
}
```

### Enviar Notificaciones

```php
use App\Notifications\InvoicePaid;

// Opción 1: Notificar al modelo directamente (trait Notifiable)
$user->notify(new InvoicePaid($invoice));

// Opción 2: Usar la fachada Notification
use Illuminate\Support\Facades\Notification;

// Notificar a múltiples usuarios
$users = User::where('role', 'admin')->get();
Notification::send($users, new InvoicePaid($invoice));

// Opción 3: Notificación on-demand (sin modelo)
Notification::route('mail', 'cliente@example.com')
    ->route('slack', '#ventas')
    ->notify(new InvoicePaid($invoice));
```

## Canal de Base de Datos

Para almacenar notificaciones en la base de datos y mostrarlas en la UI (como un panel de notificaciones):

```bash
# Crear la tabla de notificaciones
php artisan notifications:table
php artisan migrate
```

### Acceder a las Notificaciones

```php
// Obtener notificaciones no leídas
$unread = $user->unreadNotifications;

foreach ($unread as $notification) {
    echo $notification->data['message'];
    echo $notification->created_at->diffForHumans();
}

// Marcar como leídas
$user->unreadNotifications->markAsRead();

// Marcar una específica como leída
$notification->markAsRead();

// Obtener todas las notificaciones
$all = $user->notifications()->paginate(10);

// Contar no leídas (útil para badges)
$count = $user->unreadNotifications->count();

// Eliminar notificaciones antiguas
$user->notifications()->where('created_at', '<', now()->subMonth())->delete();
```

### Mostrar en Blade

```blade
{{-- Ícono de campana con badge --}}
<div class="notification-bell">
    <i class="fa fa-bell"></i>
    @if(auth()->user()->unreadNotifications->count())
        <span class="badge">
            {{ auth()->user()->unreadNotifications->count() }}
        </span>
    @endif
</div>

{{-- Lista de notificaciones --}}
<ul class="notification-list">
    @forelse(auth()->user()->notifications()->take(10)->get() as $notification)
        <li class="{{ $notification->read_at ? '' : 'unread' }}">
            <p>{{ $notification->data['message'] }}</p>
            <small>{{ $notification->created_at->diffForHumans() }}</small>
        </li>
    @empty
        <li>No tienes notificaciones</li>
    @endforelse
</ul>
```

## Canal Broadcast

Para notificaciones en tiempo real con WebSockets:

```php
use Illuminate\Notifications\Messages\BroadcastMessage;

class InvoicePaid extends Notification
{
    public function via(object $notifiable): array
    {
        return ['broadcast', 'database'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'invoice_id' => $this->invoice->id,
            'message'    => "Factura #{$this->invoice->number} pagada",
            'amount'     => $this->invoice->amount,
        ]);
    }
}
```

## Personalizar el Canal Mail

```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->from('ventas@miapp.com', 'Equipo de Ventas')
        ->subject('Tu factura ha sido procesada')
        ->greeting('¡Hola!')
        ->line('Tu factura ha sido procesada correctamente.')
        ->lines([
            "Número de factura: #{$this->invoice->number}",
            "Monto: \${$this->invoice->amount}",
            "Fecha: {$this->invoice->created_at->format('d/m/Y')}",
        ])
        ->action('Descargar PDF', url("/invoices/{$this->invoice->id}/pdf"))
        ->line('Gracias por confiar en nosotros.')
        ->salutation('Saludos, Equipo de soporte');
}
```

## Colas en Notificaciones

Para enviar notificaciones en segundo plano, implementa `ShouldQueue`:

```php
class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable;

    // Especificar cola y conexión
    public $queue = 'notifications';
    public $connection = 'redis';
    public $delay = 10; // segundos

    // Decidir si encolar según condiciones
    public function shouldSend(object $notifiable, string $channel): bool
    {
        return $notifiable->wants_notifications;
    }
}
```

## Sistema de Mail: Mailables

Para correos más complejos con vistas dedicadas, usa Mailables:

### Crear una Mailable

```bash
php artisan make:mail OrderConfirmation --markdown=emails.order-confirmation
```

```php
// app/Mail/OrderConfirmation.php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    /**
     * Sobre del correo (remitente, asunto, destinatarios).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('ventas@mitienda.com', 'Mi Tienda'),
            subject: 'Confirmación de Orden #' . $this->order->id,
            replyTo: [new Address('soporte@mitienda.com', 'Soporte')],
        );
    }

    /**
     * Contenido del correo.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-confirmation',
            with: [
                'orderUrl' => route('orders.show', $this->order),
                'items'    => $this->order->items,
                'total'    => $this->order->total,
            ],
        );
    }
}
```

### Vista Markdown del Correo

```blade
{{-- resources/views/emails/order-confirmation.blade.php --}}
<x-mail::message>
# Confirmación de Orden

¡Hola {{ $order->user->name }}! Tu orden ha sido confirmada.

**Número de orden:** #{{ $order->id }}
**Fecha:** {{ $order->created_at->format('d/m/Y H:i') }}

<x-mail::table>
| Producto | Cantidad | Precio |
|:---------|:--------:|-------:|
@foreach($items as $item)
| {{ $item->name }} | {{ $item->quantity }} | ${{ number_format($item->price, 2) }} |
@endforeach
| **Total** | | **${{ number_format($total, 2) }}** |
</x-mail::table>

<x-mail::button :url="$orderUrl" color="primary">
Ver Orden
</x-mail::button>

Gracias por tu compra,<br>
{{ config('app.name') }}
</x-mail::message>
```

### Enviar Correos

```php
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;

// Enviar un correo
Mail::to($user)->send(new OrderConfirmation($order));

// Con CC y BCC
Mail::to($user)
    ->cc('contabilidad@empresa.com')
    ->bcc('auditor@empresa.com')
    ->send(new OrderConfirmation($order));

// Enviar en cola (si implementa ShouldQueue, es automático)
Mail::to($user)->queue(new OrderConfirmation($order));

// Enviar con retraso
Mail::to($user)->later(now()->addMinutes(5), new OrderConfirmation($order));
```

### Configuración de Mail

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hola@miapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Ejercicio Práctico

Implementa un sistema completo de notificaciones para una plataforma de cursos:

1. Crea la notificación `CourseEnrollment` con canales `mail` y `database`:
   - El correo debe incluir el nombre del curso, instructor y un botón para acceder.
   - La versión de base de datos guarda el `course_id` y un mensaje.
2. Crea una Mailable `WeeklyReport` con plantilla Markdown que incluya:
   - Una tabla con los cursos completados durante la semana.
   - Estadísticas de progreso del estudiante.
3. Implementa un endpoint `/notifications` que muestre las notificaciones del usuario autenticado con paginación.
4. Agrega funcionalidad para marcar notificaciones como leídas vía AJAX.
5. Encola todas las notificaciones y correos usando `ShouldQueue`.

## Resumen

- Las **notificaciones** envían mensajes por múltiples canales (mail, database, broadcast, Slack) desde una sola clase.
- Cada canal tiene su método: `toMail()`, `toArray()`, `toBroadcast()`.
- El canal `database` almacena notificaciones para mostrarlas en la UI con `$user->notifications`.
- Las **Mailables** crean correos complejos con vistas Blade o Markdown templates.
- Los Markdown mailables usan componentes como `<x-mail::button>` y `<x-mail::table>`.
- Implementa `ShouldQueue` para enviar notificaciones y correos en segundo plano.
- Las notificaciones **on-demand** permiten enviar a destinatarios sin modelo (e.g., un email externo).
- Configura los drivers de mail en `.env` según tu proveedor (SMTP, Mailgun, SES, etc.).
