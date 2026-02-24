# Namespaces y Autoloading en PHP

Los namespaces y el autoloading son fundamentales para organizar proyectos PHP modernos. Permiten evitar conflictos de nombres y cargar clases automáticamente sin necesidad de `require` manuales.

---

## ¿Qué son los Namespaces?

Un namespace es un contenedor lógico que agrupa clases, interfaces, funciones y constantes bajo un nombre único, evitando colisiones de nombres.

```php
// Sin namespaces: conflicto si dos librerías definen "Logger"
// Con namespaces: cada una vive en su propio espacio

namespace App\Servicios;

class Logger
{
    public function info(string $mensaje): void
    {
        echo "[INFO] {$mensaje}\n";
    }
}
```

```php
namespace Vendor\OtraLibreria;

class Logger
{
    public function info(string $mensaje): void
    {
        // Otra implementación completamente diferente
        file_put_contents('log.txt', $mensaje . PHP_EOL, FILE_APPEND);
    }
}
```

Ambas clases `Logger` coexisten sin conflicto porque están en namespaces diferentes.

---

## Declarar Namespaces

La declaración `namespace` debe ser la primera instrucción del archivo (después de `<?php` y `declare`):

```php
<?php

declare(strict_types=1);

namespace App\Modelos;

class Usuario
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly string $email
    ) {}
}
```

### Reglas importantes

- Solo puede haber **una** declaración `namespace` por archivo (buena práctica).
- El namespace debe ser la **primera instrucción** del archivo.
- Los namespaces son **sensibles a mayúsculas**, pero por convención se usa PascalCase.

---

## Use Statements

Para usar una clase de otro namespace, utiliza `use`:

```php
<?php

namespace App\Controladores;

use App\Modelos\Usuario;
use App\Servicios\Logger;
use App\Repositorios\RepositorioUsuario;

class ControladorUsuario
{
    public function __construct(
        private RepositorioUsuario $repositorio,
        private Logger $logger
    ) {}

    public function mostrar(int $id): ?Usuario
    {
        $this->logger->info("Buscando usuario {$id}");
        return $this->repositorio->buscarPorId($id);
    }
}
```

### Importar múltiples clases del mismo namespace

```php
use App\Modelos\{Usuario, Producto, Pedido};
use App\Servicios\{Logger, Mailer, Cache};
```

---

## Alias con `as`

Cuando dos clases tienen el mismo nombre, usa alias para diferenciarlas:

```php
use App\Servicios\Logger as AppLogger;
use Vendor\OtraLibreria\Logger as VendorLogger;

$miLogger = new AppLogger();
$otroLogger = new VendorLogger();
```

También puedes usar alias para nombres largos:

```php
use App\Infraestructura\Persistencia\Doctrine\RepositorioUsuarioOrm as RepoUsuario;

$repo = new RepoUsuario();
```

---

## Importar Funciones y Constantes

No solo clases: también puedes importar funciones y constantes:

```php
namespace App\Utilidades;

// Definir función en un namespace
function formatearMoneda(float $cantidad): string
{
    return '$' . number_format($cantidad, 2);
}

const VERSION = '2.0.0';
```

```php
// Importar la función y constante
use function App\Utilidades\formatearMoneda;
use const App\Utilidades\VERSION;

echo formatearMoneda(1500.5); // $1,500.50
echo VERSION;                  // 2.0.0
```

---

## Namespace Global

El namespace global se representa con `\`. Para acceder a clases del namespace global desde un namespace personalizado:

```php
namespace App\Servicios;

class MiServicio
{
    public function obtenerFecha(): \DateTime
    {
        // \DateTime es del namespace global
        return new \DateTime();
    }

    public function lanzarError(): void
    {
        throw new \RuntimeException('Error inesperado');
    }
}
```

O importa explícitamente:

```php
namespace App\Servicios;

use DateTime;
use RuntimeException;

class MiServicio
{
    public function obtenerFecha(): DateTime
    {
        return new DateTime();
    }
}
```

---

## Sub-namespaces

Los namespaces soportan niveles de anidamiento que generalmente reflejan la estructura de directorios:

```
src/
├── App/
│   ├── Modelos/
│   │   ├── Usuario.php        → App\Modelos\Usuario
│   │   └── Producto.php       → App\Modelos\Producto
│   ├── Controladores/
│   │   └── Api/
│   │       └── V1/
│   │           └── UsuarioController.php → App\Controladores\Api\V1\UsuarioController
│   └── Servicios/
│       ├── Pago/
│       │   └── Stripe.php     → App\Servicios\Pago\Stripe
│       └── Logger.php         → App\Servicios\Logger
```

---

## PSR-4: El Estándar de Autoloading

PSR-4 define cómo los namespaces se mapean a rutas de archivos. La regla es simple:

1. Un prefijo de namespace se mapea a un directorio base.
2. Los sub-namespaces corresponden a subdirectorios.
3. El nombre de la clase corresponde al nombre del archivo.

| Namespace Completo              | Directorio Base | Archivo                          |
|---------------------------------|-----------------|----------------------------------|
| `App\Modelos\Usuario`           | `src/`          | `src/App/Modelos/Usuario.php`    |
| `App\Http\Controladores\Home`   | `src/`          | `src/App/Http/Controladores/Home.php` |

---

## Composer Autoloading

Composer es la herramienta estándar para configurar autoloading en PHP. En `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/App/",
            "Tests\\": "tests/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    }
}
```

Después de modificar esta configuración, ejecuta:

```bash
composer dump-autoload
```

Esto genera los archivos de autoloading en `vendor/composer/`.

---

## Usando autoload.php

En el punto de entrada de tu aplicación, solo necesitas un `require`:

```php
<?php

// index.php o bootstrap.php
require __DIR__ . '/vendor/autoload.php';

use App\Modelos\Usuario;
use App\Servicios\Logger;

// Las clases se cargan automáticamente cuando se usan
$usuario = new Usuario(1, 'Carlos', 'carlos@ejemplo.com');
$logger = new Logger();
$logger->info("Usuario creado: {$usuario->nombre}");
```

> **Tip:** Nunca más necesitas `require` o `include` para tus clases. Composer se encarga de todo.

---

## Tipos de Autoloading en Composer

Composer soporta varios modos de autoloading:

### PSR-4 (Recomendado)

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

### Classmap

Escanea directorios y genera un mapa completo clase → archivo:

```json
{
    "autoload": {
        "classmap": [
            "database/seeders",
            "database/factories"
        ]
    }
}
```

### Files

Carga archivos específicos siempre (útil para funciones helper):

```json
{
    "autoload": {
        "files": [
            "src/helpers.php"
        ]
    }
}
```

---

## Optimización del Autoloading

Para producción, optimiza el autoloader:

```bash
# Genera un classmap optimizado
composer dump-autoload --optimize

# O con el flag corto
composer dump-autoload -o

# Autoloading autoritativo: solo busca en el classmap
composer dump-autoload --classmap-authoritative
```

El autoloading optimizado genera un mapa directo clase → archivo, evitando búsquedas en el sistema de archivos.

---

## Estructura de Directorios Recomendada

Una estructura típica para un proyecto PHP moderno:

```
mi-proyecto/
├── composer.json
├── composer.lock
├── vendor/              # Dependencias (no se versiona)
├── src/                 # Código fuente
│   ├── Controladores/
│   ├── Modelos/
│   ├── Servicios/
│   ├── Repositorios/
│   └── Excepciones/
├── tests/               # Tests
│   ├── Unit/
│   └── Integration/
├── config/              # Configuración
├── public/              # Punto de entrada web
│   └── index.php
└── bin/                 # Scripts CLI
    └── console
```

---

## Ejemplo Completo

```php
<?php
// src/Servicios/Notificador.php

declare(strict_types=1);

namespace App\Servicios;

use App\Contratos\CanalNotificacion;
use App\Modelos\Usuario;

class Notificador
{
    /** @var CanalNotificacion[] */
    private array $canales = [];

    public function agregarCanal(CanalNotificacion $canal): void
    {
        $this->canales[] = $canal;
    }

    public function notificar(Usuario $usuario, string $mensaje): void
    {
        foreach ($this->canales as $canal) {
            $canal->enviar($usuario, $mensaje);
        }
    }
}
```

```php
<?php
// src/Contratos/CanalNotificacion.php

namespace App\Contratos;

use App\Modelos\Usuario;

interface CanalNotificacion
{
    public function enviar(Usuario $usuario, string $mensaje): void;
}
```

---

## Resumen

- Los **namespaces** organizan el código y previenen conflictos de nombres.
- Usa `use` para importar clases y `as` para crear alias.
- **PSR-4** mapea namespaces a directorios de forma predecible.
- **Composer** maneja el autoloading automáticamente con `vendor/autoload.php`.
- Usa `classmap` para directorios sin PSR-4 y `files` para helpers globales.
- Optimiza el autoloader en producción con `composer dump-autoload -o`.
- Mantén una estructura de directorios clara que refleje los namespaces.
