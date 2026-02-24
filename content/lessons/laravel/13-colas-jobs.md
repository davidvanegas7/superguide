---
title: "Colas y Jobs"
slug: "laravel-colas-jobs"
description: "Aprende a procesar tareas en segundo plano con colas y jobs en Laravel para mejorar el rendimiento de tu aplicación."
---

# Colas y Jobs

Cuando una aplicación necesita realizar tareas pesadas como enviar correos, procesar imágenes o generar reportes, hacerlo de forma síncrona bloquea la respuesta al usuario. Las **colas** (queues) permiten diferir estas tareas para que se ejecuten en segundo plano. Laravel proporciona una API unificada para trabajar con diferentes drivers de colas como Redis, bases de datos, Amazon SQS y más.

## ¿Qué es un Job?

Un **Job** es una clase que encapsula una tarea ejecutable. Cuando se despacha a una cola, un proceso **worker** separado lo toma y lo ejecuta de forma asíncrona, liberando al usuario de esperar.

### Crear un Job

```bash
php artisan make:job ProcessPodcast
```

```php
// app/Jobs/ProcessPodcast.php
namespace App\Jobs;

use App\Models\Podcast;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número máximo de intentos.
     */
    public int $tries = 3;

    /**
     * Tiempo máximo de ejecución en segundos.
     */
    public int $timeout = 120;

    /**
     * Crear una nueva instancia del job.
     */
    public function __construct(
        public Podcast $podcast
    ) {}

    /**
     * Ejecutar el job.
     */
    public function handle(): void
    {
        // Lógica pesada aquí
        // Ejemplo: convertir audio, generar transcripción
        $this->podcast->update([
            'processed'    => true,
            'processed_at' => now(),
        ]);
    }
}
```

La interfaz `ShouldQueue` le indica a Laravel que este job debe procesarse de forma asíncrona en la cola. Sin ella, el job se ejecutaría de forma síncrona.

## Despachando Jobs

### Dispatch Básico

```php
use App\Jobs\ProcessPodcast;

// Despachar a la cola por defecto
ProcessPodcast::dispatch($podcast);

// Despachar con retraso (ejecutar en 10 minutos)
ProcessPodcast::dispatch($podcast)->delay(now()->addMinutes(10));

// Despachar a una cola específica
ProcessPodcast::dispatch($podcast)->onQueue('processing');

// Despachar a una conexión específica
ProcessPodcast::dispatch($podcast)->onConnection('redis');

// Despachar síncronamente (ignorar la cola)
ProcessPodcast::dispatchSync($podcast);

// Despachar después de la respuesta HTTP
ProcessPodcast::dispatchAfterResponse($podcast);
```

### Dispatch Condicional

```php
// Solo despachar si se cumple una condición
ProcessPodcast::dispatchIf($podcast->needs_processing, $podcast);

// No despachar si se cumple una condición
ProcessPodcast::dispatchUnless($podcast->already_processed, $podcast);
```

## Queue Drivers

Laravel soporta múltiples drivers de cola. Configúralos en `.env`:

```env
# Sync: ejecuta inmediatamente (útil en desarrollo)
QUEUE_CONNECTION=sync

# Database: usa una tabla en la base de datos
QUEUE_CONNECTION=database

# Redis: rápido y recomendado para producción
QUEUE_CONNECTION=redis

# Amazon SQS
QUEUE_CONNECTION=sqs
```

### Configurar el Driver Database

```bash
# Crear la tabla de jobs
php artisan queue:table
php artisan migrate
```

### Configurar Redis

```bash
# Instalar el paquete predis
composer require predis/predis
```

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## Ejecutando el Worker

El worker es el proceso que escucha la cola y ejecuta los jobs pendientes:

```bash
# Iniciar el worker
php artisan queue:work

# Procesar solo la cola "emails"
php artisan queue:work --queue=emails

# Procesar un solo job y detenerse
php artisan queue:work --once

# Límite de memoria (MB)
php artisan queue:work --memory=256

# Tiempo de espera por job (segundos)
php artisan queue:work --timeout=60

# Reintentar jobs fallidos hasta 3 veces
php artisan queue:work --tries=3

# Pausa entre procesamiento de jobs
php artisan queue:work --sleep=3
```

**Importante:** Después de cambiar código, debes reiniciar los workers:

```bash
php artisan queue:restart
```

## Reintentos y Backoff

Configura cómo se reintenta un job que falla:

```php
class ProcessPodcast implements ShouldQueue
{
    // Máximo 5 intentos
    public int $tries = 5;

    // Backoff exponencial: esperar 10s, 30s, 60s entre reintentos
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    // Alternativa: intentar durante un máximo de 5 minutos
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(5);
    }

    // Lógica cuando el job falla definitivamente
    public function failed(\Throwable $exception): void
    {
        // Notificar al admin, registrar el error, etc.
        \Log::error("Job falló: {$exception->getMessage()}");
    }
}
```

## Job Batching

Ejecuta un grupo de jobs y realiza acciones cuando todos terminen:

```php
use App\Jobs\ProcessPodcast;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

$podcasts = Podcast::where('processed', false)->get();

// Crear un batch de jobs
$batch = Bus::batch(
    $podcasts->map(fn ($podcast) => new ProcessPodcast($podcast))->toArray()
)
->then(function (Batch $batch) {
    // Todos los jobs del batch completados exitosamente
    \Log::info("Batch {$batch->id} completado");
})
->catch(function (Batch $batch, \Throwable $e) {
    // Se detectó el primer fallo en el batch
    \Log::error("Batch falló: {$e->getMessage()}");
})
->finally(function (Batch $batch) {
    // El batch terminó (sin importar si hubo fallos)
})
->name('Procesar Podcasts')
->allowFailures()   // Continuar aunque algunos jobs fallen
->dispatch();

// Verificar el progreso del batch
$batch = Bus::findBatch($batchId);
echo $batch->progress(); // Porcentaje completado (0-100)
echo $batch->totalJobs;
echo $batch->failedJobs;
```

Para usar batching, necesitas la tabla de batches:

```bash
php artisan queue:batches-table
php artisan migrate
```

## Job Chaining

Ejecuta jobs secuencialmente, uno tras otro. Si uno falla, los siguientes no se ejecutan:

```php
use Illuminate\Support\Facades\Bus;

Bus::chain([
    new DownloadPodcast($podcast),
    new ConvertAudio($podcast),
    new GenerateTranscript($podcast),
    new NotifyUser($podcast->user),
])->dispatch();

// Con manejo de errores en la cadena
Bus::chain([
    new DownloadPodcast($podcast),
    new ConvertAudio($podcast),
])->catch(function (\Throwable $e) {
    \Log::error("Cadena falló: {$e->getMessage()}");
})->dispatch();
```

## Jobs Únicos

Evita que el mismo job se despache múltiples veces:

```php
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ProcessPodcast implements ShouldQueue, ShouldBeUnique
{
    // El lock se mantiene por 60 segundos
    public int $uniqueFor = 60;

    // Identificador único (por defecto usa el ID del modelo)
    public function uniqueId(): string
    {
        return $this->podcast->id;
    }
}
```

## Jobs Fallidos

Cuando un job excede sus reintentos, se mueve a la tabla `failed_jobs`:

```bash
# Crear la tabla de jobs fallidos
php artisan queue:failed-table
php artisan migrate

# Ver jobs fallidos
php artisan queue:failed

# Reintentar un job fallido específico
php artisan queue:retry 5

# Reintentar todos los jobs fallidos
php artisan queue:retry all

# Eliminar un job fallido
php artisan queue:forget 5

# Eliminar todos los jobs fallidos
php artisan queue:flush
```

## Supervisor en Producción

En producción, usa **Supervisor** para mantener los workers activos:

```ini
; /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/app/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## Ejercicio Práctico

Implementa un sistema de procesamiento de órdenes por colas:

1. Crea un job `ProcessOrder` que reciba un modelo `Order`:
   - Simula el procesamiento con `sleep(5)`.
   - Actualiza el estado de la orden a `completed`.
   - Configura 3 reintentos con backoff de [5, 15, 30] segundos.
2. Crea un job `SendOrderConfirmation` que envíe una notificación.
3. Encadena ambos jobs: primero procesar, luego notificar.
4. En un controller, despacha la cadena cuando se crea una orden.
5. Configura el driver `database` y ejecuta el worker para probar.

```bash
php artisan queue:table && php artisan migrate
php artisan queue:work --tries=3
```

## Resumen

- Los **Jobs** encapsulan tareas que se ejecutan en segundo plano implementando `ShouldQueue`.
- `dispatch()` envía un job a la cola; `dispatchSync()` lo ejecuta inmediatamente.
- Los drivers principales son `sync` (desarrollo), `database` y `redis` (producción).
- `queue:work` inicia el worker que procesa los jobs pendientes.
- Configura `tries`, `backoff` y `timeout` para controlar reintentos y tiempos límite.
- **Job batching** ejecuta múltiples jobs en paralelo y reacciona cuando todos terminan.
- **Job chaining** ejecuta jobs en secuencia, deteniéndose si alguno falla.
- En producción, usa **Supervisor** para mantener los workers corriendo continuamente.
