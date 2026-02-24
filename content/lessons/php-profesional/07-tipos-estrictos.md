# Sistema de Tipos y Tipado Estricto en PHP

PHP ha evolucionado de un lenguaje débilmente tipado a uno con un sistema de tipos cada vez más sólido. Aprovechar el tipado estricto reduce bugs, mejora la documentación del código y facilita el uso de herramientas de análisis estático.

---

## declare(strict_types=1)

Por defecto, PHP convierte automáticamente los tipos (type coercion). Con `strict_types`, se exige que los valores coincidan exactamente con el tipo declarado:

```php
<?php
// SIN strict_types (comportamiento por defecto)
function sumar(int $a, int $b): int
{
    return $a + $b;
}

echo sumar(5, "3");    // 8 (PHP convierte "3" a int)
echo sumar(5, "hola"); // TypeError en PHP 8
echo sumar(5, 3.7);    // 8 (trunca 3.7 a 3)
```

```php
<?php

declare(strict_types=1);

function sumar(int $a, int $b): int
{
    return $a + $b;
}

echo sumar(5, 3);      // 8 ✓
echo sumar(5, "3");    // TypeError: se esperaba int, se recibió string ✗
echo sumar(5, 3.7);    // TypeError: se esperaba int, se recibió float ✗
```

> **Importante:** `declare(strict_types=1)` afecta solo al archivo donde se declara, no a funciones llamadas desde otros archivos. Debe ser la **primera instrucción** del archivo.

---

## Declaraciones de Tipo

### Tipos escalares

```php
function procesarDatos(
    int $entero,
    float $decimal,
    string $texto,
    bool $activo
): string {
    return "{$texto}: {$entero}, {$decimal}, " . ($activo ? 'sí' : 'no');
}
```

### Tipos compuestos

```php
function procesarLista(array $elementos): array
{
    return array_map(fn($e) => strtoupper($e), $elementos);
}

function ejecutar(callable $funcion, mixed $argumento): mixed
{
    return $funcion($argumento);
}

function aceptarIterable(iterable $datos): void
{
    foreach ($datos as $dato) {
        echo $dato . "\n";
    }
}
```

### Tipos de retorno especiales

```php
// void: no devuelve nada
function registrarLog(string $mensaje): void
{
    file_put_contents('app.log', $mensaje . PHP_EOL, FILE_APPEND);
    // No hay return, o un return sin valor
}

// never: la función nunca retorna (lanza excepción o termina el script)
function abortar(string $mensaje): never
{
    throw new RuntimeException($mensaje);
    // O: exit(1);
}

// self y static
class Constructor
{
    public static function crear(): static
    {
        return new static();
    }

    public function clonar(): self
    {
        return clone $this;
    }
}
```

---

## Tipos Nullable

Un tipo nullable acepta el valor `null` además del tipo declarado:

```php
function buscarUsuario(int $id): ?Usuario
{
    // Puede devolver un Usuario o null
    return $this->repositorio->buscar($id);
}

function configurar(?string $nombre = null): void
{
    $nombre = $nombre ?? 'predeterminado';
    echo "Configuración: {$nombre}\n";
}
```

---

## Union Types (PHP 8.0)

Los union types permiten aceptar varios tipos:

```php
function formatear(int|float $numero): string
{
    return number_format($numero, 2);
}

function buscar(string|int $identificador): ?Usuario
{
    if (is_int($identificador)) {
        return $this->buscarPorId($identificador);
    }
    return $this->buscarPorEmail($identificador);
}

// false como tipo de retorno
function encontrar(string $clave): string|false
{
    return $this->cache[$clave] ?? false;
}

// Propiedades con union types
class Configuracion
{
    public function __construct(
        private string|int $puerto,
        private string|null $host = null,
        private int|float $timeout = 30
    ) {}
}
```

### Restricciones de Union Types

```php
// Válido
function ejemplo(): int|string { /* ... */ }
function ejemplo2(): Foo|Bar|null { /* ... */ }

// Inválido: void y never no pueden combinarse
// function mal(): void|null { }  // Error
// function mal2(): never|string { }  // Error
```

---

## Intersection Types (PHP 8.1)

Los intersection types exigen que un valor implemente **todos** los tipos indicados:

```php
interface Serializable
{
    public function serializar(): string;
}

interface Logeable
{
    public function toLog(): string;
}

// El parámetro debe implementar AMBAS interfaces
function procesar(Serializable&Logeable $objeto): void
{
    $log = $objeto->toLog();
    $datos = $objeto->serializar();
    echo "Log: {$log}, Datos: {$datos}\n";
}

class Evento implements Serializable, Logeable
{
    public function __construct(private string $nombre) {}

    public function serializar(): string
    {
        return json_encode(['evento' => $this->nombre]);
    }

    public function toLog(): string
    {
        return "Evento: {$this->nombre}";
    }
}

procesar(new Evento('clic')); // ✓
```

### DNF Types (PHP 8.2)

PHP 8.2 introduce tipos en Forma Normal Disyuntiva, combinando union e intersection:

```php
function ejemplo((Serializable&Logeable)|null $objeto): void
{
    if ($objeto === null) {
        echo "Sin objeto\n";
        return;
    }
    echo $objeto->toLog();
}
```

---

## El Tipo mixed

`mixed` acepta cualquier tipo. Es equivalente a `int|float|string|bool|array|object|null`:

```php
function cache(string $clave, mixed $valor): void
{
    // $valor puede ser cualquier cosa
    $this->store[$clave] = serialize($valor);
}

// mixed es el tipo por defecto si no se declara ninguno
// Declararlo explícitamente mejora la legibilidad
function procesar(mixed $entrada): mixed
{
    return $entrada;
}
```

> **Tip:** Evita `mixed` cuando sea posible. Los tipos específicos documentan mejor tu código y previenen errores.

---

## Enums como Tipos (PHP 8.1)

Los enums son tipos de primera clase:

```php
enum Rol: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Lector = 'lector';
}

enum Estado
{
    case Activo;
    case Inactivo;
    case Suspendido;
}

class Usuario
{
    public function __construct(
        public readonly string $nombre,
        public readonly Rol $rol,
        public readonly Estado $estado = Estado::Activo
    ) {}

    public function puedeEditar(): bool
    {
        return match ($this->rol) {
            Rol::Admin, Rol::Editor => true,
            Rol::Lector => false,
        };
    }

    public function estaDisponible(): bool
    {
        return $this->estado === Estado::Activo;
    }
}

$admin = new Usuario('Ana', Rol::Admin);
echo $admin->puedeEditar(); // true

// Usar en type hints
function asignarRol(Usuario $usuario, Rol $nuevoRol): void
{
    // El compilador garantiza que $nuevoRol sea un Rol válido
}
```

---

## Type Juggling y Coerción

PHP realiza conversiones automáticas en ciertas situaciones. Comprenderlas evita bugs:

```php
// Comparación flexible vs estricta
var_dump(0 == "foo");     // false (PHP 8+, antes era true)
var_dump(0 == "");        // false (PHP 8+)
var_dump("" == null);     // true  (ambos son "vacíos")
var_dump("" === null);    // false (tipos diferentes)
var_dump(1 == "1");       // true  (coerción)
var_dump(1 === "1");      // false (tipos diferentes)

// Conversiones automáticas en operaciones
$resultado = "5" + 3;     // 8 (int)
$resultado = "5.5" + 1.5; // 7.0 (float)
$resultado = "5 gatos";   // Warning en PHP 8+, antes silencioso
$resultado = true + true;  // 2

// Contexto booleano: valores "falsy"
var_dump((bool) 0);        // false
var_dump((bool) 0.0);      // false
var_dump((bool) "");       // false
var_dump((bool) "0");      // false
var_dump((bool) []);       // false
var_dump((bool) null);     // false
// Todo lo demás es true
```

### Buenas prácticas

```php
declare(strict_types=1);

// Usa === siempre
if ($valor === null) { /* ... */ }
if ($valor === 0) { /* ... */ }
if ($valor === '') { /* ... */ }

// Funciones de verificación de tipo
is_int($valor);
is_string($valor);
is_array($valor);
is_null($valor);
is_numeric($valor);   // "42" y 42 son true

// Casting explícito cuando es necesario
$id = (int) $_GET['id'];
$precio = (float) $input['precio'];
$activo = (bool) $input['activo'];
```

---

## Propiedades Tipadas

```php
class Producto
{
    // PHP 7.4+: propiedades tipadas
    public int $id;
    public string $nombre;
    public float $precio;
    public ?string $descripcion = null;
    public array $etiquetas = [];

    // PHP 8.0+: promoción de propiedades del constructor
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public float $precio,
        public ?string $descripcion = null,
    ) {}
}

// Las propiedades no inicializadas lanzan Error al accederlas
class Ejemplo
{
    public string $nombre; // No inicializada

    public function mostrar(): void
    {
        echo $this->nombre; // Error: propiedad no inicializada
    }
}
```

---

## Constantes Tipadas (PHP 8.3)

PHP 8.3 permite declarar el tipo de las constantes de clase:

```php
class Configuracion
{
    const string VERSION = '2.0.0';
    const int MAX_CONEXIONES = 100;
    const float TIMEOUT = 30.5;
    const bool DEBUG = false;
    const array OPCIONES = ['a', 'b', 'c'];
}
```

---

## Resumen

- Usa `declare(strict_types=1)` en **todos** tus archivos PHP para forzar tipado estricto.
- Los **union types** (`int|string`) aceptan múltiples tipos posibles.
- Los **intersection types** (`A&B`) exigen que el valor implemente todos los tipos.
- `void` indica que la función no devuelve nada; `never` indica que nunca retorna.
- Los **enums** son tipos de primera clase, seguros y expresivos.
- Evita `mixed` cuando sea posible; sé lo más específico posible con los tipos.
- Comprende el **type juggling** para evitar bugs sutiles.
- Usa siempre `===` para comparaciones estrictas.
- Las propiedades tipadas y la promoción de constructor hacen el código más limpio.
