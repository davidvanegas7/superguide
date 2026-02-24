# Estándares PSR en PHP

Los PSR (PHP Standards Recommendations) son estándares definidos por PHP-FIG (Framework Interoperability Group) que promueven la interoperabilidad entre frameworks y librerías PHP. Seguir estos estándares hace tu código compatible con el ecosistema PHP moderno.

---

## ¿Qué es PHP-FIG?

PHP-FIG es un grupo de representantes de proyectos PHP importantes (Laravel, Symfony, Drupal, Slim, etc.) que colaboran para definir estándares comunes. Los PSR aceptados son recomendaciones que cualquier proyecto puede adoptar.

---

## PSR-1: Basic Coding Standard

Define las reglas básicas de estilo para asegurar interoperabilidad:

```php
<?php
// ✓ Solo etiquetas <?php o <?=
// ✓ Codificación UTF-8 sin BOM
// ✓ Un archivo debe declarar símbolos O ejecutar efectos secundarios, NO ambos

// ✓ Namespaces y clases siguen PSR-4
namespace App\Servicios;

// ✓ Nombres de clase en PascalCase
class GestorUsuarios
{
    // ✓ Constantes en MAYUSCULAS_CON_GUIONES
    const VERSION_MAYOR = 1;
    const TAMANO_PAGINA = 25;

    // ✓ Métodos en camelCase
    public function buscarPorEmail(string $email): ?Usuario
    {
        // implementación
    }

    public function crearUsuario(array $datos): Usuario
    {
        // implementación
    }
}
```

### Reglas principales de PSR-1

| Regla                          | Ejemplo correcto                  |
|--------------------------------|-----------------------------------|
| Clases en PascalCase           | `class MiClase`                   |
| Métodos en camelCase           | `public function miMetodo()`      |
| Constantes en UPPER_SNAKE_CASE | `const MI_CONSTANTE = 1;`        |
| Archivos UTF-8 sin BOM        | Configurar en el editor           |

---

## PSR-12: Extended Coding Style Guide

PSR-12 extiende PSR-1 con reglas detalladas de formato (reemplaza al antiguo PSR-2):

```php
<?php

declare(strict_types=1);

namespace App\Controladores;

use App\Modelos\Usuario;
use App\Servicios\{Mailer, Logger};
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControladorUsuario extends ControladorBase implements
    ControladorInterface,
    LogeableInterface
{
    // Visibilidad SIEMPRE declarada
    private const LIMITE = 100;

    // Propiedades con visibilidad explícita
    private Logger $logger;
    protected Mailer $mailer;

    public function __construct(Logger $logger, Mailer $mailer)
    {
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    // Llave de apertura en nueva línea para clases, en misma línea para métodos NO
    // En PSR-12: llave de apertura en nueva línea para CLASES
    //            llave de apertura en MISMA línea para estructuras de control
    public function listar(
        ServerRequestInterface $request,
        ResponseInterface $response,
        int $pagina = 1
    ): ResponseInterface {
        // Estructuras de control: llave en la misma línea
        if ($pagina < 1) {
            $pagina = 1;
        } elseif ($pagina > 100) {
            $pagina = 100;
        }

        // switch
        switch ($request->getMethod()) {
            case 'GET':
                $usuarios = $this->obtenerUsuarios($pagina);
                break;
            case 'POST':
                $usuarios = $this->crearUsuario($request);
                break;
            default:
                throw new \RuntimeException('Método no soportado');
        }

        // foreach, while, for
        foreach ($usuarios as $usuario) {
            $this->logger->info("Procesando: {$usuario->nombre}");
        }

        return $response;
    }

    // Closures y funciones flecha
    public function filtrar(array $usuarios): array
    {
        return array_filter(
            $usuarios,
            function (Usuario $usuario): bool {
                return $usuario->estaActivo();
            }
        );
    }

    // Try-catch
    public function ejecutar(): void
    {
        try {
            $this->proceso();
        } catch (ExcepcionA | ExcepcionB $e) {
            $this->logger->error($e->getMessage());
        } finally {
            $this->limpiar();
        }
    }
}
```

### Reglas clave de PSR-12

- 4 espacios de indentación (no tabs).
- Líneas de máximo 120 caracteres (recomendado 80).
- Una línea en blanco después de `namespace` y después del bloque `use`.
- Llaves de apertura de clases/métodos en nueva línea.
- Llaves de apertura de estructuras de control en la misma línea.
- Visibilidad siempre declarada en propiedades y métodos.

---

## PSR-4: Autoloading Standard

PSR-4 define cómo los nombres de clases completamente cualificados se mapean a rutas de archivo:

```
Nombre completo: App\Modelos\Usuario
Prefijo:         App\
Directorio base: src/
Ruta del archivo: src/Modelos/Usuario.php
```

### Configuración en composer.json

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "App\\Tests\\": "tests/"
        }
    }
}
```

### Ejemplos de mapeo

| Clase completamente cualificada         | Prefijo    | Base  | Archivo                        |
|-----------------------------------------|------------|-------|-------------------------------|
| `App\Modelos\Usuario`                   | `App\`     | `src/`| `src/Modelos/Usuario.php`     |
| `App\Http\Controladores\HomeController`| `App\`     | `src/`| `src/Http/Controladores/HomeController.php` |
| `App\Tests\Unit\UsuarioTest`           | `App\Tests\`| `tests/`| `tests/Unit/UsuarioTest.php` |

---

## PSR-7: HTTP Message Interface

PSR-7 define interfaces para mensajes HTTP (request y response). Es la base de muchos frameworks:

```php
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

function manejarPeticion(
    ServerRequestInterface $request,
    ResponseInterface $response
): ResponseInterface {
    // Acceder a datos del request
    $metodo = $request->getMethod();              // GET, POST, etc.
    $uri = $request->getUri()->getPath();          // /api/usuarios
    $query = $request->getQueryParams();           // ?page=1
    $cuerpo = $request->getParsedBody();           // Datos POST
    $headers = $request->getHeaders();             // Todos los headers
    $contentType = $request->getHeaderLine('Content-Type');

    // Los objetos son INMUTABLES: withX() devuelve una nueva instancia
    $response = $response
        ->withStatus(200)
        ->withHeader('Content-Type', 'application/json');

    $response->getBody()->write(json_encode([
        'mensaje' => 'Hola mundo',
        'metodo' => $metodo,
    ]));

    return $response;
}
```

### Interfaces principales de PSR-7

| Interface                    | Descripción                           |
|-------------------------------|---------------------------------------|
| `MessageInterface`            | Base para request y response          |
| `RequestInterface`            | Petición HTTP del cliente             |
| `ServerRequestInterface`      | Petición del lado del servidor        |
| `ResponseInterface`           | Respuesta HTTP                        |
| `StreamInterface`             | Cuerpo del mensaje                    |
| `UriInterface`                | URI del request                       |
| `UploadedFileInterface`       | Archivo subido                        |

---

## PSR-11: Container Interface

PSR-11 define una interfaz estándar para contenedores de inyección de dependencias:

```php
use Psr\Container\ContainerInterface;

class MiContenedor implements ContainerInterface
{
    private array $servicios = [];
    private array $fabricas = [];

    public function registrar(string $id, callable $fabrica): void
    {
        $this->fabricas[$id] = $fabrica;
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException("Servicio '{$id}' no encontrado");
        }

        if (!isset($this->servicios[$id])) {
            $this->servicios[$id] = ($this->fabricas[$id])($this);
        }

        return $this->servicios[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->fabricas[$id]);
    }
}

// Uso
$contenedor = new MiContenedor();
$contenedor->registrar(Logger::class, fn() => new Logger('/var/log/app.log'));
$contenedor->registrar(Mailer::class, fn(ContainerInterface $c) => new Mailer(
    $c->get(Logger::class)
));

$mailer = $contenedor->get(Mailer::class);
```

---

## PSR-15: HTTP Server Request Handlers

PSR-15 define interfaces para manejadores y middleware HTTP:

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

// Handler: maneja una petición y produce una respuesta
class HomeHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('<h1>Bienvenido</h1>');
        return $response;
    }
}

// Middleware: procesa la petición antes/después del handler
class AuthMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $token = $request->getHeaderLine('Authorization');

        if (empty($token)) {
            return new Response(401, [], 'No autorizado');
        }

        // Pasar al siguiente handler/middleware
        return $handler->handle($request);
    }
}

class LogMiddleware implements MiddlewareInterface
{
    public function __construct(private Logger $logger) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $inicio = microtime(true);

        // Antes del handler
        $this->logger->info("Petición: {$request->getMethod()} {$request->getUri()}");

        $response = $handler->handle($request);

        // Después del handler
        $duracion = microtime(true) - $inicio;
        $this->logger->info("Respuesta: {$response->getStatusCode()} en {$duracion}s");

        return $response;
    }
}
```

---

## PSR-18: HTTP Client

PSR-18 define una interfaz estándar para clientes HTTP:

```php
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServicioApi
{
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory
    ) {}

    public function obtenerUsuarios(): array
    {
        $request = $this->requestFactory
            ->createRequest('GET', 'https://api.ejemplo.com/usuarios')
            ->withHeader('Accept', 'application/json');

        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Error al obtener usuarios');
        }

        return json_decode(
            $response->getBody()->getContents(),
            true
        );
    }
}
```

---

## PSR-3: Logger Interface

PSR-3 define una interfaz común para loggers:

```php
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class MiServicio
{
    public function __construct(private LoggerInterface $logger) {}

    public function procesar(): void
    {
        $this->logger->info('Iniciando procesamiento');

        try {
            // Lógica...
            $this->logger->debug('Paso intermedio completado', [
                'registros_procesados' => 150,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error en procesamiento', [
                'excepcion' => $e->getMessage(),
                'traza' => $e->getTraceAsString(),
            ]);
        }

        $this->logger->info('Procesamiento finalizado');
    }
}

// Niveles de log (de más a menos severo):
// emergency, alert, critical, error, warning, notice, info, debug
```

---

## Resumen

- **PHP-FIG** define estándares PSR para interoperabilidad entre proyectos PHP.
- **PSR-1** y **PSR-12** establecen convenciones de codificación (PascalCase, camelCase, indentación).
- **PSR-4** estandariza el autoloading, mapeando namespaces a directorios.
- **PSR-7** provee interfaces inmutables para mensajes HTTP.
- **PSR-11** define una interfaz común para contenedores de dependencias.
- **PSR-15** estandariza handlers y middleware HTTP del servidor.
- **PSR-18** y **PSR-3** homogeneizan los clientes HTTP y loggers respectivamente.
- Adoptar los PSR hace tu código compatible con el ecosistema PHP y facilita la integración con frameworks y librerías.
