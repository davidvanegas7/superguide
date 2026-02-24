# POO Avanzada en PHP

La Programación Orientada a Objetos (POO) en PHP va mucho más allá de crear clases y objetos simples. En esta lección exploraremos las características avanzadas que te permitirán escribir código más robusto, mantenible y profesional.

---

## Clases Abstractas

Una clase abstracta no puede ser instanciada directamente. Sirve como plantilla base para otras clases que la extiendan.

```php
abstract class Vehiculo
{
    protected string $marca;
    protected int $anio;

    public function __construct(string $marca, int $anio)
    {
        $this->marca = $marca;
        $this->anio = $anio;
    }

    // Método abstracto: las clases hijas DEBEN implementarlo
    abstract public function calcularConsumo(): float;

    // Método concreto: las clases hijas lo heredan tal cual
    public function descripcion(): string
    {
        return "{$this->marca} ({$this->anio})";
    }
}

class Auto extends Vehiculo
{
    public function calcularConsumo(): float
    {
        return 8.5; // litros por 100 km
    }
}

class Camion extends Vehiculo
{
    public function calcularConsumo(): float
    {
        return 25.0;
    }
}

// $v = new Vehiculo('Test', 2024); // Error: no se puede instanciar clase abstracta
$auto = new Auto('Toyota', 2024);
echo $auto->calcularConsumo(); // 8.5
```

> **Tip:** Usa clases abstractas cuando quieras compartir código base entre clases relacionadas y forzar la implementación de ciertos métodos.

---

## Interfaces

Las interfaces definen un contrato que las clases deben cumplir, sin proporcionar implementación alguna.

```php
interface Exportable
{
    public function toArray(): array;
    public function toJson(): string;
}

interface Imprimible
{
    public function imprimir(): void;
}

class Reporte implements Exportable, Imprimible
{
    public function __construct(
        private string $titulo,
        private array $datos
    ) {}

    public function toArray(): array
    {
        return ['titulo' => $this->titulo, 'datos' => $this->datos];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    public function imprimir(): void
    {
        echo "Reporte: {$this->titulo}\n";
        foreach ($this->datos as $dato) {
            echo "- {$dato}\n";
        }
    }
}
```

Una clase puede implementar múltiples interfaces, lo cual es la forma en PHP de lograr un comportamiento similar a la herencia múltiple.

---

## Traits

Los traits permiten reutilizar métodos en varias clases sin necesidad de herencia. Son especialmente útiles para compartir funcionalidad transversal.

```php
trait Timestamps
{
    private ?DateTime $creadoEn = null;
    private ?DateTime $actualizadoEn = null;

    public function setCreado(): void
    {
        $this->creadoEn = new DateTime();
    }

    public function setActualizado(): void
    {
        $this->actualizadoEn = new DateTime();
    }

    public function getCreado(): ?DateTime
    {
        return $this->creadoEn;
    }
}

trait SoftDelete
{
    private bool $eliminado = false;

    public function eliminar(): void
    {
        $this->eliminado = true;
    }

    public function estaEliminado(): bool
    {
        return $this->eliminado;
    }
}

class Articulo
{
    use Timestamps, SoftDelete;

    public function __construct(
        private string $titulo
    ) {
        $this->setCreado();
    }
}

$articulo = new Articulo('Mi primer post');
$articulo->eliminar();
echo $articulo->estaEliminado(); // true
```

---

## Readonly Properties (PHP 8.1)

Las propiedades `readonly` solo pueden ser asignadas una vez, generalmente en el constructor.

```php
class Coordenada
{
    public function __construct(
        public readonly float $latitud,
        public readonly float $longitud
    ) {}
}

$coord = new Coordenada(19.4326, -99.1332);
echo $coord->latitud; // 19.4326

// $coord->latitud = 20.0; // Error: no se puede modificar propiedad readonly
```

A partir de PHP 8.2, puedes marcar una clase entera como `readonly`:

```php
readonly class Moneda
{
    public function __construct(
        public string $codigo,
        public string $nombre,
        public int $decimales
    ) {}
}
```

---

## Enums (PHP 8.1)

Los enums (enumeraciones) permiten definir un conjunto fijo de valores posibles.

```php
enum Estado
{
    case Activo;
    case Inactivo;
    case Pendiente;
}

// Backed Enums: enums con valor escalar asociado
enum Color: string
{
    case Rojo = '#FF0000';
    case Verde = '#00FF00';
    case Azul = '#0000FF';

    // Los enums pueden tener métodos
    public function esCalido(): bool
    {
        return $this === self::Rojo;
    }
}

function aplicarEstilo(Color $color): string
{
    return "color: {$color->value};";
}

echo aplicarEstilo(Color::Rojo); // color: #FF0000;

// Crear desde valor
$color = Color::from('#00FF00'); // Color::Verde
$color = Color::tryFrom('#999999'); // null (no lanza excepción)
```

Los enums pueden implementar interfaces pero no pueden ser extendidos.

---

## Type Casting

PHP permite convertir valores entre tipos de forma explícita.

```php
$numero = "42";

$entero = (int) $numero;       // 42
$flotante = (float) $numero;   // 42.0
$booleano = (bool) $numero;    // true
$cadena = (string) 42;         // "42"
$arreglo = (array) $numero;    // ["42"]
$objeto = (object) ['a' => 1]; // stdClass con propiedad $a

// settype() modifica la variable original
$valor = "3.14";
settype($valor, 'float');
echo $valor; // 3.14 (ahora es float)
```

> **Cuidado:** El type juggling automático de PHP puede causar bugs sutiles. Usa `===` en lugar de `==` para comparaciones estrictas.

---

## Clases Finales

Una clase `final` no puede ser extendida. Un método `final` no puede ser sobrescrito.

```php
final class Configuracion
{
    private static ?self $instancia = null;

    private function __construct(
        private array $valores = []
    ) {}

    public static function obtener(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    final public function get(string $clave, mixed $default = null): mixed
    {
        return $this->valores[$clave] ?? $default;
    }
}

// class MiConfig extends Configuracion {} // Error: no se puede extender clase final
```

---

## Clonación Profunda

Al clonar un objeto con `clone`, las propiedades que sean objetos se copian por referencia. Usa `__clone()` para hacer una copia profunda.

```php
class Direccion
{
    public function __construct(
        public string $calle,
        public string $ciudad
    ) {}
}

class Persona
{
    public function __construct(
        public string $nombre,
        public Direccion $direccion
    ) {}

    public function __clone(): void
    {
        // Clonar también el objeto interno
        $this->direccion = clone $this->direccion;
    }
}

$original = new Persona('Ana', new Direccion('Reforma 100', 'CDMX'));
$copia = clone $original;
$copia->direccion->calle = 'Insurgentes 200';

echo $original->direccion->calle; // Reforma 100 (no fue afectada)
echo $copia->direccion->calle;    // Insurgentes 200
```

---

## Comparación de Objetos

PHP diferencia entre comparación con `==` y `===` para objetos:

```php
class Punto
{
    public function __construct(
        public int $x,
        public int $y
    ) {}
}

$a = new Punto(1, 2);
$b = new Punto(1, 2);
$c = $a;

var_dump($a == $b);  // true  (mismas propiedades y valores)
var_dump($a === $b); // false (no son la misma instancia)
var_dump($a === $c); // true  (misma instancia)
```

Para comparaciones personalizadas, puedes implementar un método `equals()`:

```php
class Dinero
{
    public function __construct(
        private float $cantidad,
        private string $moneda
    ) {}

    public function equals(self $otro): bool
    {
        return $this->cantidad === $otro->cantidad
            && $this->moneda === $otro->moneda;
    }
}
```

---

## Resumen

En esta lección cubrimos los aspectos avanzados de la POO en PHP:

- **Clases abstractas** definen plantillas que las clases hijas deben completar.
- **Interfaces** establecen contratos sin implementación.
- **Traits** permiten reutilizar código de forma horizontal.
- **Readonly properties** garantizan inmutabilidad después de la asignación.
- **Enums** representan conjuntos fijos de valores con seguridad de tipos.
- **Type casting** convierte valores entre tipos de forma explícita.
- **Clases finales** previenen la herencia no deseada.
- **Clonación profunda** con `__clone()` copia objetos internos.
- **Comparación de objetos** difiere entre `==` (valores) y `===` (identidad).

Dominar estos conceptos es esencial para escribir PHP profesional y prepararte para frameworks como Laravel y Symfony.
