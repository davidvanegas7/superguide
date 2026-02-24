---
title: "Deploy y Producción"
slug: "laravel-deploy-produccion"
description: "Prepara y despliega tu aplicación Laravel en producción con las mejores prácticas de rendimiento, seguridad y monitoreo."
---

# Deploy y Producción

Llevar una aplicación Laravel de desarrollo a producción requiere configurar optimizaciones, seguridad, procesos en segundo plano y monitoreo. En esta lección cubriremos todo lo necesario para un deploy profesional: desde la configuración del entorno hasta el uso de herramientas como Forge, Vapor y Supervisor para mantener tu aplicación corriendo de forma confiable.

## Configuración del Entorno de Producción

### Archivo `.env` para Producción

```env
APP_NAME="Mi Aplicación"
APP_ENV=production
APP_KEY=base64:TU_CLAVE_GENERADA
APP_DEBUG=false
APP_URL=https://miapp.com

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=miapp_produccion
DB_USERNAME=miapp_user
DB_PASSWORD=contraseña_segura_aqui

# Cache y sesiones
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

**Reglas fundamentales:**
- `APP_DEBUG=false` — **Nunca** dejar en `true` en producción (expone información sensible).
- `APP_ENV=production` — Activa comportamientos de producción.
- Usar contraseñas fuertes y únicas para BD y servicios.
- Nunca versionar el archivo `.env` en Git.

## Comandos de Optimización

Laravel proporciona comandos para cachear configuraciones y mejorar el rendimiento:

### Cache de Configuración

```bash
# Cachear toda la configuración en un solo archivo
php artisan config:cache

# Limpiar la cache de configuración
php artisan config:clear
```

Esto combina todos los archivos de `config/` en un solo archivo cacheado, eliminando la necesidad de leer múltiples archivos en cada request.

### Cache de Rutas

```bash
# Cachear las rutas (mucho más rápido)
php artisan route:cache

# Limpiar
php artisan route:clear
```

**Nota:** El cache de rutas no funciona con closures en rutas. Asegúrate de que todas las rutas apunten a controladores.

### Cache de Vistas

```bash
# Precompilar todas las vistas Blade
php artisan view:cache

# Limpiar
php artisan view:clear
```

### Cache de Eventos

```bash
# Cachear el mapeo de eventos y listeners
php artisan event:cache
php artisan event:clear
```

### Comando Optimize

El comando `optimize` ejecuta múltiples optimizaciones de una vez:

```bash
# Cachear config, rutas, vistas y eventos
php artisan optimize

# Limpiar todas las caches
php artisan optimize:clear
```

### Autoloader de Composer

```bash
# Optimizar el autoloader de Composer
composer install --optimize-autoloader --no-dev

# --no-dev excluye paquetes de desarrollo (PHPUnit, Faker, etc.)
# --optimize-autoloader genera un class map optimizado
```

## Script de Deploy

Un script típico de deploy automatiza los pasos necesarios:

```bash
#!/bin/bash
# deploy.sh — Script de deploy para Laravel

set -e  # Detener en caso de error

echo "🔄 Iniciando deploy..."

# 1. Activar modo de mantenimiento
php artisan down --retry=60 --secret="deploy-bypass-token"

# 2. Obtener código más reciente
git pull origin main

# 3. Instalar dependencias (sin dev)
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Ejecutar migraciones
php artisan migrate --force

# 5. Optimizar la aplicación
php artisan optimize

# 6. Compilar assets frontend
npm ci && npm run build

# 7. Reiniciar queue workers
php artisan queue:restart

# 8. Desactivar modo de mantenimiento
php artisan up

echo "✅ Deploy completado exitosamente"
```

### Modo de Mantenimiento

```bash
# Activar mantenimiento con página personalizada
php artisan down --retry=60

# Permitir acceso con token secreto
php artisan down --secret="mi-token-secreto"
# Acceder vía: https://miapp.com/mi-token-secreto

# Permitir IP específicas
php artisan down --allow=192.168.1.100

# Renderizar vista personalizada durante mantenimiento
php artisan down --render="errors.503"

# Desactivar mantenimiento
php artisan up
```

## Laravel Forge

**[Laravel Forge](https://forge.laravel.com)** es un servicio de provisión de servidores creado por Taylor Otwell (creador de Laravel). Automatiza la configuración de servidores en DigitalOcean, AWS, Linode, etc.

### Características Principales

- Provisión automática de servidores con Nginx, PHP, MySQL, Redis.
- Deploy automático con Git (push to deploy).
- Certificados SSL con Let's Encrypt.
- Gestión de workers de colas y tareas programadas.
- Monitoreo básico del servidor.

```bash
# Forge configura automáticamente:
# - Nginx con configuración optimizada
# - PHP-FPM con opciones de producción
# - MySQL/PostgreSQL
# - Redis
# - Supervisor para queue workers
# - Cron para el scheduler
# - SSL/TLS certificates
```

## Laravel Vapor

**[Laravel Vapor](https://vapor.laravel.com)** es una plataforma serverless para Laravel sobre AWS Lambda. Tu app escala automáticamente sin gestionar servidores.

```bash
# Instalar Vapor CLI
composer require laravel/vapor-cli --dev

# Inicializar proyecto
php artisan vapor:install

# Deploy
php artisan vapor:deploy production
```

```yaml
# vapor.yml
id: 12345
name: mi-app
environments:
  production:
    memory: 1024
    cli-memory: 512
    runtime: php-8.3
    build:
      - 'composer install --no-dev'
      - 'npm ci && npm run build'
    queues:
      - default
    database: mi-base-de-datos
    cache: mi-cache-redis
```

## Laravel Envoyer

**[Envoyer](https://envoyer.io)** gestiona deployments con zero-downtime (sin tiempo de inactividad):

- Deployments atómicos usando symlinks.
- Rollback instantáneo a versiones anteriores.
- Health checks post-deploy.
- Integración con Slack y otros servicios de notificación.
- Ejecución de hooks pre y post deploy.

## Queue Workers con Supervisor

En producción, los queue workers deben mantenerse activos permanentemente con **Supervisor**:

```ini
; /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/miapp/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/miapp/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Aplicar configuración
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

# Verificar estado
sudo supervisorctl status
```

### Scheduler con Cron

El scheduler de Laravel necesita un solo cron entry:

```bash
# Editar crontab
crontab -e

# Agregar esta línea
* * * * * cd /var/www/miapp && php artisan schedule:run >> /dev/null 2>&1
```

## Logging en Producción

Configura múltiples canales de log para producción:

```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver'   => 'stack',
        'channels' => ['daily', 'slack'],
        'ignore_exceptions' => false,
    ],

    'daily' => [
        'driver' => 'daily',
        'path'   => storage_path('logs/laravel.log'),
        'level'  => 'warning',   // Solo warning y errores
        'days'   => 14,           // Retener 14 días
    ],

    'slack' => [
        'driver'   => 'slack',
        'url'      => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'emoji'    => ':boom:',
        'level'    => 'critical', // Solo errores críticos a Slack
    ],
],
```

```php
// Uso en código
use Illuminate\Support\Facades\Log;

Log::info('Orden procesada', ['order_id' => $order->id]);
Log::warning('Stock bajo', ['product_id' => $product->id, 'stock' => 3]);
Log::critical('Fallo en pasarela de pago', ['error' => $e->getMessage()]);

// Escribir a un canal específico
Log::channel('slack')->critical('Servidor de BD no responde');
```

## Health Checks

Implementa un endpoint de salud para monitoreo:

```php
// routes/web.php
Route::get('/health', function () {
    $checks = [];

    // Verificar base de datos
    try {
        DB::connection()->getPdo();
        $checks['database'] = 'ok';
    } catch (\Exception $e) {
        $checks['database'] = 'error: ' . $e->getMessage();
    }

    // Verificar Redis
    try {
        Redis::ping();
        $checks['redis'] = 'ok';
    } catch (\Exception $e) {
        $checks['redis'] = 'error: ' . $e->getMessage();
    }

    // Verificar storage
    $checks['storage'] = is_writable(storage_path()) ? 'ok' : 'error: not writable';

    // Verificar cola
    $checks['queue'] = Cache::get('queue:health', 'unknown');

    $allOk = collect($checks)->every(fn ($v) => $v === 'ok');

    return response()->json([
        'status' => $allOk ? 'healthy' : 'unhealthy',
        'checks' => $checks,
        'timestamp' => now()->toISOString(),
    ], $allOk ? 200 : 503);
});
```

## Seguridad en Producción

```php
// Forzar HTTPS en producción
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }
}
```

```nginx
# Configuración Nginx recomendada
server {
    listen 80;
    server_name miapp.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name miapp.com;
    root /var/www/miapp/public;

    ssl_certificate /etc/ssl/certs/miapp.crt;
    ssl_certificate_key /etc/ssl/private/miapp.key;

    # Headers de seguridad
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Ejercicio Práctico

Prepara tu aplicación Laravel para producción:

1. Crea un script `deploy.sh` que incluya:
   - Modo de mantenimiento, git pull, composer install, migraciones, optimización y reinicio de workers.
2. Configura el archivo `.env.production` con variables apropiadas (debug off, cache Redis, log daily).
3. Implementa un endpoint `/health` que verifique BD, Redis y storage.
4. Configura Supervisor para ejecutar 2 workers de cola.
5. Asegúrate de que todas las rutas usen controllers (no closures) para poder cachearlas.
6. Ejecuta `php artisan optimize` y verifica que no haya errores.

## Resumen

- Configura `APP_DEBUG=false` y `APP_ENV=production` en producción — nunca expongas errores detallados.
- Usa `php artisan optimize` para cachear configuración, rutas, vistas y eventos de una sola vez.
- Ejecuta `composer install --no-dev --optimize-autoloader` para excluir dependencias de desarrollo.
- **Forge** provisiona servidores, **Vapor** es serverless sobre AWS, **Envoyer** gestiona deploys sin downtime.
- Usa **Supervisor** para mantener queue workers activos y **cron** para el scheduler.
- Configura logging con canales `daily` (archivos rotativos) y `slack` (alertas críticas).
- Implementa **health checks** para monitoreo automatizado del estado de la aplicación.
- El modo de mantenimiento (`php artisan down`) permite deploys controlados con bypass por token.
