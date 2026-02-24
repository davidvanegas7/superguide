# Manejo de Errores y Excepciones en PHP

El manejo adecuado de errores es fundamental en aplicaciones profesionales. PHP ofrece un sistema robusto de excepciones que, bien utilizado, hace tu código más predecible y fácil de depurar.

---

## Jerarquía de Errores y Excepciones

En PHP, la jerarquía de throwables es:

```
Throwable (interface)
├── Error
│   ├── ArithmeticError
│   │   └── DivisionByZeroError
│   ├── AssertionError
│   ├── CompileError
│   │   └── ParseError
│   ├── TypeError
│   │   └── ArgumentCountError
│   ├── ValueError
│   ├── UnhandledMatchError
│   └── FiberError
└── Exception
    ├── BadFunctionCallException
    │   └── BadMethodCallException
    ├── DomainException
    ├── InvalidArgumentException
    ├── LengthException
    ├── LogicException
    ├── OutOfRangeException
    ├── OverflowException
    ├── RangeException
    ├── RuntimeException
    │   └── OutOfBoundsException
    │   └── OverflowException
    │   └── UnderflowException
    │   └── UnexpectedValueException
    └── UnderflowException
```

> **Importante:** `Error` representa errores del motor de PHP (generalmente no deberías atraparlos). `Exception` es para errores de la lógica de tu aplicación.

---

## try / catch / finally

La estructura básica para manejar excepciones:

```php
try {
    $resultado = dividir(10, 0);
    echo "Resultado: {$resultado}";
} catch (DivisionByZeroError $e) {
    echo "Error: No se puede dividir entre cero.\n";
} catch (InvalidArgumentException $e) {
    echo "Argumento inválido: {$e->getMessage()}\n";
} catch (Exception $e) {
    echo "Error general: {$e->getMessage()}\n";
} finally {
    // Se ejecuta SIEMPRE, haya error o no
    echo "Operación finalizada.\n";
}
```

### Capturar múltiples tipos en un solo catch

```php
try {
    $datos = procesarArchivo('datos.csv');
} catch (FileNotFoundException | PermissionDeniedException $e) {
    echo "Error de archivo: {$e->getMessage()}";
} catch (ParseException $e) {
    echo "Error al procesar: {$e->getMessage()}";
}
```

---

## Lanzar Excepciones

```php
function buscarUsuario(int $id): Usuario
{
    if ($id <= 0) {
        throw new InvalidArgumentException("El ID debe ser positivo, se recibió: {$id}");
    }

    $usuario = $this->repositorio->buscar($id);

    if ($usuario === null) {
        throw new RuntimeException("Usuario con ID {$id} no encontrado");
    }

    return $usuario;
}
```

---

## Excepciones Personalizadas

Crear tus propias excepciones te permite manejar errores de dominio de forma clara:

```php
// Excepción base de la aplicación
class AppException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly array $contexto = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getContexto(): array
    {
        return $this->contexto;
    }
}

// Excepciones específicas del dominio
class UsuarioNoEncontradoException extends AppException
{
    public static function conId(int $id): self
    {
        return new self(
            "Usuario con ID {$id} no encontrado",
            ['usuario_id' => $id],
            404
        );
    }

    public static function conEmail(string $email): self
    {
        return new self(
            "Usuario con email {$email} no encontrado",
            ['email' => $email],
            404
        );
    }
}

class SaldoInsuficienteException extends AppException
{
    public static function crear(float $saldoActual, float $montoRequerido): self
    {
        return new self(
            "Saldo insuficiente: se requieren \${$montoRequerido} pero solo hay \${$saldoActual}",
            [
                'saldo_actual' => $saldoActual,
                'monto_requerido' => $montoRequerido,
            ],
            422
        );
    }
}

class ValidacionException extends AppException
{
    public static function conErrores(array $errores): self
    {
        return new self(
            'Error de validación',
            ['errores' => $errores],
            422
        );
    }
}
```

### Uso

```php
class ServicioCuenta
{
    public function transferir(int $origenId, int $destinoId, float $monto): void
    {
        $origen = $this->repo->buscar($origenId)
            ?? throw UsuarioNoEncontradoException::conId($origenId);

        $destino = $this->repo->buscar($destinoId)
            ?? throw UsuarioNoEncontradoException::conId($destinoId);

        if ($origen->saldo < $monto) {
            throw SaldoInsuficienteException::crear($origen->saldo, $monto);
        }

        $origen->saldo -= $monto;
        $destino->saldo += $monto;
    }
}
```

> **Tip:** Usa métodos estáticos de fábrica en tus excepciones (como `conId()`, `crear()`) para mensajes consistentes y contexto enriquecido.

---

## Excepciones SPL

PHP incluye excepciones SPL (Standard PHP Library) listas para usar:

```php
// Lógica incorrecta en el código (bug del programador)
throw new LogicException('Este método no debería llamarse aquí');

// Argumento inválido
throw new InvalidArgumentException('Se esperaba un string, se recibió int');

// Método no implementado
throw new BadMethodCallException('El método process() no está implementado');

// Valor fuera de rango
throw new OutOfRangeException('El índice debe estar entre 0 y 10');

// Error en tiempo de ejecución
throw new RuntimeException('No se pudo conectar a la base de datos');

// Desbordamiento
throw new OverflowException('La cola está llena');

// Valor inesperado
throw new UnexpectedValueException('Se esperaba JSON válido');

// Longitud inválida
throw new LengthException('El array debe tener al menos 2 elementos');
```

### Cuándo usar cada una

| Excepción                  | Úsalo cuando...                                    |
|----------------------------|-----------------------------------------------------|
| `InvalidArgumentException` | Un parámetro de función tiene valor inválido         |
| `RuntimeException`         | Ocurre un error en tiempo de ejecución               |
| `LogicException`           | Hay un error de lógica en el programa                |
| `BadMethodCallException`   | Se llama a un método indefinido o no disponible      |
| `OutOfRangeException`      | Un índice está fuera del rango esperado              |
| `LengthException`          | Una longitud es inválida                             |

---

## set_exception_handler

Configura un manejador global para excepciones no capturadas:

```php
set_exception_handler(function (Throwable $e): void {
    $timestamp = date('Y-m-d H:i:s');
    $tipo = get_class($e);
    $mensaje = $e->getMessage();
    $archivo = $e->getFile();
    $linea = $e->getLine();
    $traza = $e->getTraceAsString();

    // Registrar en archivo de log
    $log = "[{$timestamp}] {$tipo}: {$mensaje} en {$archivo}:{$linea}\n{$traza}\n\n";
    error_log($log, 3, __DIR__ . '/errores.log');

    // Mostrar página de error amigable
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        echo '<h1>Error interno del servidor</h1>';
        echo '<p>Lo sentimos, algo salió mal. Intenta más tarde.</p>';
    } else {
        echo "Error fatal: {$mensaje}\n";
    }
});
```

---

## set_error_handler

Convierte errores tradicionales de PHP en excepciones:

```php
set_error_handler(function (
    int $nivel,
    string $mensaje,
    string $archivo,
    int $linea
): bool {
    // Respetar el operador @ de supresión
    if (!(error_reporting() & $nivel)) {
        return false;
    }

    throw new ErrorException($mensaje, 0, $nivel, $archivo, $linea);
});

// Ahora los warnings y notices se convierten en excepciones
try {
    $contenido = file_get_contents('archivo_inexistente.txt');
} catch (ErrorException $e) {
    echo "Capturado: {$e->getMessage()}\n";
}
```

---

## Niveles de Error Reporting

```php
// Reportar todos los errores
error_reporting(E_ALL);

// Reportar todos excepto notices
error_reporting(E_ALL & ~E_NOTICE);

// Reportar solo errores fatales y warnings
error_reporting(E_ERROR | E_WARNING);

// No reportar nada
error_reporting(0);

// En php.ini o en tiempo de ejecución
ini_set('display_errors', '0');       // No mostrar en producción
ini_set('log_errors', '1');           // Sí registrar en logs
ini_set('error_log', '/var/log/php/errores.log');
```

### Niveles comunes

| Constante       | Descripción                           |
|-----------------|---------------------------------------|
| `E_ERROR`       | Error fatal, detiene la ejecución     |
| `E_WARNING`     | Advertencia, no detiene ejecución     |
| `E_NOTICE`      | Aviso informativo                     |
| `E_DEPRECATED`  | Funcionalidad obsoleta                |
| `E_STRICT`      | Sugerencias de compatibilidad         |
| `E_ALL`         | Todos los errores                     |

---

## Re-lanzar Excepciones

A veces necesitas capturar, procesar y volver a lanzar una excepción:

```php
class ServicioBaseDatos
{
    public function ejecutarConsulta(string $sql): array
    {
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Registrar el error con contexto
            $this->logger->error('Error en consulta SQL', [
                'sql' => $sql,
                'error' => $e->getMessage(),
            ]);

            // Re-lanzar como excepción del dominio
            throw new RuntimeException(
                'Error al ejecutar la consulta en base de datos',
                previous: $e  // Encadenar la excepción original
            );
        }
    }
}
```

---

## Patrón: Resultado en Lugar de Excepciones

Para flujos esperados, puedes usar un objeto Result en lugar de excepciones:

```php
class Resultado
{
    private function __construct(
        private readonly bool $exitoso,
        private readonly mixed $valor = null,
        private readonly ?string $error = null
    ) {}

    public static function exito(mixed $valor): self
    {
        return new self(true, $valor);
    }

    public static function fallo(string $error): self
    {
        return new self(false, error: $error);
    }

    public function esExitoso(): bool { return $this->exitoso; }
    public function getValor(): mixed { return $this->valor; }
    public function getError(): ?string { return $this->error; }
}

// Uso
function validarEdad(int $edad): Resultado
{
    if ($edad < 0 || $edad > 150) {
        return Resultado::fallo("Edad inválida: {$edad}");
    }
    return Resultado::exito($edad);
}

$resultado = validarEdad(25);
if ($resultado->esExitoso()) {
    echo "Edad válida: {$resultado->getValor()}";
} else {
    echo "Error: {$resultado->getError()}";
}
```

---

## Resumen

- Usa **try/catch/finally** para manejar excepciones de forma estructurada.
- Crea **excepciones personalizadas** para errores de dominio con contexto rico.
- Las **excepciones SPL** cubren los casos más comunes: `InvalidArgumentException`, `RuntimeException`, etc.
- Configura `set_exception_handler` y `set_error_handler` como red de seguridad global.
- Diferencia entre `Error` (errores del motor) y `Exception` (errores de aplicación).
- Encadena excepciones con el parámetro `previous` para no perder información.
- En producción: desactiva `display_errors` y activa `log_errors`.
