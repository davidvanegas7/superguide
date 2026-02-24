# Preguntas de Entrevista: PHP

Esta lección cubre las preguntas más frecuentes en entrevistas técnicas para desarrolladores PHP. Cada respuesta incluye explicación, código de ejemplo y las trampas más comunes.

---

## 1. ¿Cuál es la diferencia entre `==` y `===`?

`==` compara solo el **valor** (con conversión de tipos), mientras que `===` compara **valor y tipo** (estricto).

```php
// == hace conversión de tipos (type juggling)
var_dump(0 == 'foo');      // false (PHP 8+), true en PHP 7
var_dump('' == false);     // true
var_dump(0 == false);      // true
var_dump(null == false);   // true
var_dump('1' == true);     // true
var_dump(100 == '1e2');    // true (notación científica)

// === no convierte tipos
var_dump(0 === false);     // false
var_dump('' === false);    // false
var_dump(null === false);  // false
var_dump('1' === 1);       // false
```

### Regla práctica

```php
// ✅ Siempre usa === salvo que tengas una razón específica
if ($valor === null) { /* ... */ }

// Especialmente importante con in_array y array_search
$numeros = [0, 1, 2, 3];
var_dump(in_array('abc', $numeros));          // true (!) sin strict
var_dump(in_array('abc', $numeros, true));    // false ✅ con strict
```

> **Respuesta en entrevista:** "`==` realiza comparación flexible con conversión automática de tipos, `===` compara valor y tipo estrictamente. Siempre usar `===` para evitar bugs sutiles por type juggling."

---

## 2. ¿Cuál es la diferencia entre `abstract class` e `interface`?

```php
// ABSTRACT CLASS: puede tener implementación parcial
abstract class Vehiculo
{
    protected int $velocidad = 0;

    // Método concreto con implementación
    public function acelerar(int $incremento): void
    {
        $this->velocidad += $incremento;
    }

    // Método abstracto: las clases hijas DEBEN implementar
    abstract public function tipoMotor(): string;
}

// INTERFACE: solo define el contrato, sin implementación
interface Conducible
{
    public function arrancar(): void;
    public function frenar(): void;
}

// Una clase puede extender UNA sola clase abstracta
// pero implementar MÚLTIPLES interfaces
class Auto extends Vehiculo implements Conducible, Serializable
{
    public function tipoMotor(): string { return 'combustión'; }
    public function arrancar(): void { /* ... */ }
    public function frenar(): void { /* ... */ }
    // ...
}
```

| Característica         | Abstract Class          | Interface               |
|------------------------|------------------------|-------------------------|
| Herencia múltiple      | No (una sola)          | Sí (múltiples)          |
| Propiedades            | Sí                     | No (solo constantes)    |
| Métodos con cuerpo     | Sí                     | Sí (PHP 8.0, default)   |
| Constructor            | Sí                     | No                      |
| Visibilidad            | Todas                  | Solo public             |

---

## 3. ¿Cómo resuelves conflictos de traits?

Los traits permiten reutilizar código en múltiples clases sin herencia. Cuando dos traits tienen un método con el mismo nombre, hay conflicto.

```php
trait Loggeable
{
    public function registrar(): string
    {
        return 'Registrando en log...';
    }
}

trait Auditable
{
    public function registrar(): string
    {
        return 'Registrando auditoría...';
    }
}

class Servicio
{
    use Loggeable, Auditable {
        // Resolver conflicto: elegir cuál usar
        Loggeable::registrar insteadof Auditable;

        // Crear alias para el otro
        Auditable::registrar as registrarAuditoria;
    }
}

$servicio = new Servicio();
echo $servicio->registrar();            // 'Registrando en log...'
echo $servicio->registrarAuditoria();   // 'Registrando auditoría...'
```

### Cambiar visibilidad con traits

```php
class OtroServicio
{
    use Loggeable {
        registrar as private registrarPrivado;
    }
}
```

---

## 4. ¿Qué es Type Juggling y cómo evitarlo?

Type Juggling es la conversión automática de tipos que PHP realiza en ciertas operaciones.

```php
// Ejemplos peligrosos de type juggling
var_dump('0e12345' == '0e99999');  // true (ambos se interpretan como 0 en notación científica)
var_dump(0 == 'texto');            // false en PHP 8, true en PHP 7
var_dump(null == 0);               // true (!)
var_dump([] == false);             // true
var_dump('' == null);              // true

// Cómo evitarlo
// 1. Usar comparación estricta ===
if ($input === '0') { /* exactamente el string '0' */ }

// 2. Usar declare(strict_types=1)
declare(strict_types=1);

function sumar(int $a, int $b): int
{
    return $a + $b;
}

sumar('5', '3');  // TypeError en strict_types=1
                  // Retornaría 8 sin strict_types

// 3. Usar funciones de tipo
$esVacio = $valor === '' || $valor === null;
// En vez de: empty($valor) — que convierte tipos implícitamente
```

> **Tip de entrevista:** Menciona siempre `declare(strict_types=1)` y explica que lo usas al inicio de cada archivo PHP.

---

## 5. ¿Qué es Late Static Binding?

Late Static Binding (LSB) resuelve la clase en tiempo de ejecución usando `static::` en lugar de `self::`.

```php
class ModeloBase
{
    protected static string $tabla = 'base';

    public static function obtenerTabla(): string
    {
        return static::$tabla;  // Resuelve en tiempo de ejecución
    }

    public static function obtenerTablaSelf(): string
    {
        return self::$tabla;    // Siempre resuelve a ModeloBase
    }

    public static function crear(array $datos): static
    {
        // `static` como tipo de retorno = la clase real que llama
        $instancia = new static();
        // ... configurar
        return $instancia;
    }
}

class Usuario extends ModeloBase
{
    protected static string $tabla = 'usuarios';
}

class Producto extends ModeloBase
{
    protected static string $tabla = 'productos';
}

echo Usuario::obtenerTabla();      // 'usuarios' (static::)
echo Usuario::obtenerTablaSelf();  // 'base' (self::)
echo Producto::obtenerTabla();     // 'productos'

$usuario = Usuario::crear([]);     // Instancia de Usuario, no de ModeloBase
```

> **Respuesta clave:** "`self::` se resuelve en la clase donde se define el método. `static::` se resuelve en la clase que realmente invoca el método (Late Static Binding)."

---

## 6. Closures y funciones anónimas

```php
// Closure básica
$saludar = function (string $nombre): string {
    return "Hola, {$nombre}";
};

echo $saludar('Ana'); // Hola, Ana

// Closure con `use` para capturar variables externas
$iva = 0.21;
$calcularPrecio = function (float $precio) use ($iva): float {
    return $precio * (1 + $iva);
};

echo $calcularPrecio(100); // 121.0

// Captura por referencia
$contador = 0;
$incrementar = function () use (&$contador): void {
    $contador++;
};
$incrementar();
$incrementar();
echo $contador; // 2

// Arrow functions (PHP 7.4+): capturan variables automáticamente
$multiplicador = fn(float $x) => $x * $iva;
echo $multiplicador(200); // 42.0
```

### Closures como callbacks

```php
$usuarios = [
    ['nombre' => 'Ana', 'edad' => 25],
    ['nombre' => 'Juan', 'edad' => 30],
    ['nombre' => 'Pedro', 'edad' => 20],
];

// Ordenar por edad
usort($usuarios, fn($a, $b) => $a['edad'] <=> $b['edad']);

// Filtrar mayores de edad
$mayores = array_filter($usuarios, fn($u) => $u['edad'] >= 18);

// Transformar
$nombres = array_map(fn($u) => $u['nombre'], $usuarios);
```

---

## 7. ¿Qué es PSR-4 y cómo funciona el autoloading?

PSR-4 es el estándar de autoloading que mapea namespaces a directorios del sistema de archivos.

```php
// composer.json
// {
//     "autoload": {
//         "psr-4": {
//             "App\\": "src/"
//         }
//     }
// }

// Archivo: src/Servicios/EmailService.php
namespace App\Servicios;

class EmailService
{
    public function enviar(string $para, string $asunto): bool
    {
        // Lógica de envío
        return true;
    }
}

// Archivo: src/Controladores/UsuarioController.php
namespace App\Controladores;

use App\Servicios\EmailService; // Autoloading lo carga automáticamente

class UsuarioController
{
    public function __construct(
        private EmailService $emailService,
    ) {}
}
```

> **Respuesta de entrevista:** "PSR-4 define una convención donde cada namespace raíz se mapea a un directorio base. Composer genera un autoloader optimizado que carga las clases automáticamente cuando se referencian. Usar `composer dump-autoload -o` en producción para optimizar."

---

## 8. ¿Qué es la Inyección de Dependencias?

La Inyección de Dependencias (DI) pasa las dependencias a una clase en lugar de crearlas internamente.

```php
// ❌ MALO: Dependencia acoplada
class ServicioNotificacion
{
    public function notificar(string $mensaje): void
    {
        $mailer = new SmtpMailer(); // Acoplamiento fuerte
        $mailer->enviar($mensaje);
    }
}

// ✅ BIEN: Inyección por constructor
interface Mailer
{
    public function enviar(string $destino, string $mensaje): bool;
}

class SmtpMailer implements Mailer
{
    public function enviar(string $destino, string $mensaje): bool
    {
        // Enviar por SMTP
        return true;
    }
}

class ServicioNotificacion
{
    public function __construct(
        private readonly Mailer $mailer,  // Depende de una abstracción
    ) {}

    public function notificar(string $destino, string $mensaje): bool
    {
        return $this->mailer->enviar($destino, $mensaje);
    }
}

// Fácil de testear con un mock
$mockMailer = new class implements Mailer {
    public array $enviados = [];
    public function enviar(string $destino, string $mensaje): bool
    {
        $this->enviados[] = [$destino, $mensaje];
        return true;
    }
};

$servicio = new ServicioNotificacion($mockMailer);
$servicio->notificar('ana@test.com', 'Hola');
assert(count($mockMailer->enviados) === 1);
```

---

## 9. Patrones de diseño más preguntados

### Singleton

```php
class Configuracion
{
    private static ?self $instancia = null;
    private array $datos = [];

    private function __construct() {}         // Evitar new
    private function __clone() {}             // Evitar clone
    public function __wakeup() { throw new \Exception('No deserializable'); }

    public static function obtener(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function set(string $clave, mixed $valor): void { $this->datos[$clave] = $valor; }
    public function get(string $clave): mixed { return $this->datos[$clave] ?? null; }
}
```

### Strategy

```php
interface EstrategiaPago
{
    public function procesar(float $monto): bool;
}

class PagoTarjeta implements EstrategiaPago
{
    public function procesar(float $monto): bool
    {
        echo "Procesando ${$monto} con tarjeta" . PHP_EOL;
        return true;
    }
}

class PagoPaypal implements EstrategiaPago
{
    public function procesar(float $monto): bool
    {
        echo "Procesando ${$monto} con PayPal" . PHP_EOL;
        return true;
    }
}

class ProcesadorPago
{
    public function __construct(private EstrategiaPago $estrategia) {}

    public function cobrar(float $monto): bool
    {
        return $this->estrategia->procesar($monto);
    }
}

// Uso: intercambiable en tiempo de ejecución
$procesador = new ProcesadorPago(new PagoPaypal());
$procesador->cobrar(99.99);
```

### Observer

```php
class EventDispatcher
{
    /** @var array<string, list<Closure>> */
    private array $listeners = [];

    public function escuchar(string $evento, Closure $callback): void
    {
        $this->listeners[$evento][] = $callback;
    }

    public function despachar(string $evento, mixed $datos = null): void
    {
        foreach ($this->listeners[$evento] ?? [] as $listener) {
            $listener($datos);
        }
    }
}

$dispatcher = new EventDispatcher();
$dispatcher->escuchar('usuario.creado', fn($u) => enviarEmailBienvenida($u));
$dispatcher->escuchar('usuario.creado', fn($u) => registrarEnAnalytics($u));
$dispatcher->despachar('usuario.creado', $nuevoUsuario);
```

---

## 10. Características clave de PHP 8 para entrevistas

Preguntas rápidas que deberías poder responder de inmediato:

```php
// Named arguments
array_fill(start_index: 0, count: 5, value: 'x');

// Match expression
$resultado = match($tipo) {
    'a', 'b' => 'grupo 1',
    'c' => 'grupo 2',
    default => 'otro',
};

// Nullsafe operator
$nombre = $usuario?->perfil?->nombre ?? 'Anónimo';

// Enums
enum Color: string {
    case Rojo = '#FF0000';
    case Verde = '#00FF00';
}

// Constructor promotion + readonly
class DTO {
    public function __construct(
        public readonly string $nombre,
        public readonly int $edad,
    ) {}
}

// Fibers
$fiber = new Fiber(fn() => Fiber::suspend('hola'));
echo $fiber->start(); // 'hola'

// First-class callables
$fn = strtoupper(...);
echo $fn('hola'); // 'HOLA'

// Union types
function procesar(int|string $valor): string|false { /* ... */ }

// Intersection types
function aceptar(Countable&Iterator $col): void { /* ... */ }

// #[Override]
class Hijo extends Padre {
    #[\Override]
    public function metodo(): void {}
}
```

---

## Preguntas trampa comunes

```php
// ¿Qué imprime esto?
echo '1' + '2';           // 3 (int)
echo '1' . '2';           // '12' (string)
echo true + true;         // 2
echo 'php' + 1;           // 1
echo '5abc' + 0;          // 5 (PHP 7), Warning + 5 (PHP 8)

// ¿Diferencia entre echo y print?
echo 'hola', 'mundo';  // echo acepta múltiples argumentos
print('hola');          // print retorna 1 (se puede usar en expresiones)

// ¿Qué retorna esto?
var_dump(0.1 + 0.2 == 0.3);   // false (punto flotante IEEE 754)
var_dump(round(0.1 + 0.2, 1) === 0.3); // true

// ¿Diferencia entre isset, empty y is_null?
$x = '';
var_dump(isset($x));    // true (existe y no es null)
var_dump(empty($x));    // true (es '', 0, null, false, [])
var_dump(is_null($x));  // false (no es null)
```

---

## Resumen: lo que el entrevistador busca

| Área               | Lo que demuestra                              |
|--------------------|-----------------------------------------------|
| `==` vs `===`      | Entiendes las trampas del lenguaje            |
| Abstract/Interface | Conoces OOP sólidamente                       |
| Late Static Binding| Dominas herencia avanzada                     |
| DI y patrones      | Escribes código mantenible y testeable        |
| PSR-4              | Conoces el ecosistema y estándares            |
| PHP 8 features     | Estás actualizado y usas el lenguaje moderno  |
| Type Juggling      | Escribes código robusto y seguro              |

> **Consejo final:** No solo memorices respuestas. Practica escribiendo código que use estos conceptos. Los mejores entrevistadores piden que escribas código en vivo o expliques tu razonamiento paso a paso.
