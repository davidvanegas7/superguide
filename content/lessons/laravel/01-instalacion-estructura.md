---
title: "Instalación y Estructura de un Proyecto Laravel"
slug: "laravel-instalacion-estructura"
description: "Aprende a instalar Laravel con Composer, comprende la estructura de carpetas del framework y domina los comandos básicos de Artisan CLI."
---

# Instalación y Estructura de un Proyecto Laravel

Laravel es el framework PHP más popular del mundo, diseñado para facilitar el desarrollo de aplicaciones web robustas y elegantes. En esta primera lección aprenderás a crear un proyecto desde cero, entender cada carpeta y archivo clave, y utilizar la herramienta de línea de comandos **Artisan**.

## Requisitos Previos

Antes de instalar Laravel, asegúrate de tener instalados los siguientes componentes:

- **PHP 8.2** o superior
- **Composer** (gestor de dependencias de PHP)
- **Node.js y npm** (para compilar assets del frontend)
- Un servidor de base de datos como **MySQL**, **PostgreSQL** o **SQLite**

Puedes verificar las versiones ejecutando:

```bash
# Verificar versión de PHP
php -v

# Verificar versión de Composer
composer --version

# Verificar versión de Node.js
node -v
```

## Instalación con Composer

Existen dos formas principales de crear un proyecto Laravel:

### Opción 1: Usando `composer create-project`

```bash
# Crear un nuevo proyecto Laravel en la carpeta "mi-aplicacion"
composer create-project laravel/laravel mi-aplicacion

# Acceder al directorio del proyecto
cd mi-aplicacion
```

### Opción 2: Usando el instalador global de Laravel

```bash
# Instalar el instalador de Laravel globalmente
composer global require laravel/installer

# Crear un nuevo proyecto
laravel new mi-aplicacion
```

Ambas opciones generan exactamente la misma estructura. La primera es más directa y no requiere instalar nada adicional.

## Estructura de Carpetas

Una vez creado el proyecto, encontrarás la siguiente estructura. Es fundamental entender el propósito de cada directorio:

```
mi-aplicacion/
├── app/                # Código principal de la aplicación
│   ├── Http/
│   │   ├── Controllers/  # Controladores (lógica de peticiones)
│   │   └── Middleware/    # Filtros de peticiones HTTP
│   ├── Models/            # Modelos Eloquent (representan tablas)
│   └── Providers/         # Proveedores de servicios
├── bootstrap/           # Archivos de arranque del framework
├── config/              # Archivos de configuración
├── database/            # Migraciones, seeders y factories
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/              # Punto de entrada web (index.php, assets)
├── resources/           # Vistas, CSS y JavaScript sin compilar
│   ├── css/
│   ├── js/
│   └── views/
├── routes/              # Definición de rutas
│   ├── web.php          # Rutas web (con sesión y CSRF)
│   └── console.php      # Comandos de consola personalizados
├── storage/             # Archivos generados (logs, cache, uploads)
├── tests/               # Tests automatizados
├── vendor/              # Dependencias de Composer (NO editar)
├── .env                 # Variables de entorno (configuración local)
├── .env.example         # Plantilla de variables de entorno
├── artisan              # CLI de Laravel
├── composer.json        # Dependencias PHP del proyecto
├── package.json         # Dependencias Node.js del proyecto
└── vite.config.js       # Configuración de Vite (bundler de assets)
```

### Carpetas clave en detalle

**`app/`** — Aquí vive el corazón de tu aplicación. Los controladores manejan las peticiones HTTP, los modelos representan las tablas de la base de datos y los proveedores de servicios registran las dependencias del framework.

**`routes/`** — Define todas las URLs de tu aplicación. El archivo `web.php` se usa para rutas que necesitan sesión, cookies y protección CSRF. Para APIs, puedes crear un archivo `api.php`.

**`config/`** — Cada archivo contiene un arreglo de configuración para un aspecto diferente: `database.php` para conexiones a base de datos, `mail.php` para correo electrónico, `app.php` para configuración general, etc.

**`resources/views/`** — Contiene las plantillas Blade (el motor de vistas de Laravel) con extensión `.blade.php`.

**`database/`** — Organiza las migraciones (esquema de la base de datos), los seeders (datos de prueba) y las factories (generadores de datos falsos).

**`public/`** — Es el único directorio accesible desde el navegador. Aquí se encuentra `index.php`, que es el punto de entrada de todas las peticiones.

## El Archivo `.env`

El archivo `.env` almacena la configuración específica de cada entorno (desarrollo, producción, testing). **Nunca debe subirse al repositorio** (ya está en `.gitignore`).

```env
# Nombre y entorno de la aplicación
APP_NAME="Mi Aplicación"
APP_ENV=local
APP_KEY=base64:clave-generada-automaticamente
APP_DEBUG=true
APP_URL=http://localhost:8000

# Configuración de base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mi_base_de_datos
DB_USERNAME=root
DB_PASSWORD=secret

# Configuración de correo
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

Para acceder a estas variables desde tu código, usa la función `env()` o, preferiblemente, la función `config()`:

```php
// Acceder directamente a la variable de entorno
$nombre = env('APP_NAME');

// Mejor práctica: acceder a través de config (usa caché)
$nombre = config('app.name');
```

## Artisan CLI: Tu Herramienta de Línea de Comandos

**Artisan** es la interfaz de línea de comandos incluida en Laravel. Te permite generar código, ejecutar migraciones, limpiar cachés y mucho más.

### Comandos esenciales

```bash
# Iniciar el servidor de desarrollo
php artisan serve
# => La aplicación estará disponible en http://localhost:8000

# Iniciar en un puerto específico
php artisan serve --port=9000

# Ver todos los comandos disponibles
php artisan list

# Obtener ayuda sobre un comando específico
php artisan help migrate

# Generar la clave de la aplicación (ya se hace automáticamente)
php artisan key:generate

# Limpiar todos los cachés
php artisan optimize:clear

# Ver las rutas registradas
php artisan route:list

# Abrir la consola interactiva (tinker)
php artisan tinker
```

### Generar código con Artisan

Artisan puede generar automáticamente controladores, modelos, migraciones y más:

```bash
# Crear un controlador
php artisan make:controller ProductoController

# Crear un modelo con migración y factory
php artisan make:model Producto -mf

# Crear un middleware
php artisan make:middleware VerificarEdad

# Crear un seeder
php artisan make:seeder ProductoSeeder
```

### Tinker: La consola interactiva

Tinker te permite interactuar con tu aplicación directamente desde la terminal:

```bash
php artisan tinker

# Dentro de tinker puedes ejecutar código PHP:
>>> $usuario = new App\Models\User();
>>> $usuario->name = 'Juan';
>>> $usuario->email = 'juan@ejemplo.com';
>>> $usuario->save();
>>> App\Models\User::count();
# => 1
```

## Iniciar el Servidor de Desarrollo

Para ver tu aplicación en acción:

```bash
# Desde la raíz del proyecto
php artisan serve
```

Abre tu navegador en `http://localhost:8000` y verás la página de bienvenida de Laravel.

Si también necesitas compilar los assets del frontend:

```bash
# Instalar dependencias de Node.js
npm install

# Compilar assets en modo desarrollo (con recarga automática)
npm run dev
```

## Ejercicio Práctico

Realiza los siguientes pasos para consolidar lo aprendido:

1. **Crea un nuevo proyecto** llamado `blog-personal` usando Composer.
2. **Configura el archivo `.env`** con una base de datos SQLite (cambia `DB_CONNECTION=sqlite` y elimina las demás variables `DB_*`).
3. **Ejecuta** `php artisan serve` y verifica que la página de bienvenida se muestra correctamente.
4. **Explora** la estructura de carpetas y abre al menos 3 archivos de configuración en `config/` para leer sus comentarios.
5. **Ejecuta** `php artisan route:list` para ver las rutas predefinidas.
6. **Abre Tinker** con `php artisan tinker` y ejecuta `config('app.name')` para verificar el nombre de tu aplicación.

```bash
# Solución paso a paso
composer create-project laravel/laravel blog-personal
cd blog-personal

# Crear archivo de base de datos SQLite
touch database/database.sqlite

# Ejecutar migraciones
php artisan migrate

# Iniciar servidor
php artisan serve
```

## Resumen

- **Laravel se instala con Composer** usando `composer create-project laravel/laravel nombre-proyecto`.
- La **estructura de carpetas** está organizada lógicamente: `app/` para código, `routes/` para URLs, `config/` para configuración, `resources/` para vistas y `database/` para esquemas.
- El archivo **`.env`** contiene la configuración específica del entorno y nunca debe compartirse públicamente.
- **Artisan** es la herramienta CLI que te permite generar código, ejecutar migraciones, limpiar cachés e interactuar con tu aplicación mediante Tinker.
- `php artisan serve` inicia un servidor de desarrollo en `http://localhost:8000`.
