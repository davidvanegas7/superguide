# Novedades de PHP 8.x

PHP 8 trajo una revolución al lenguaje con cambios que mejoran la expresividad, seguridad de tipos y rendimiento. En esta lección exploraremos las características más importantes de PHP 8.0, 8.1, 8.2 y 8.3.

---

## 1. Named Arguments (PHP 8.0)

Los argumentos con nombre permiten pasar valores a una función especificando el nombre del parámetro, sin importar el orden.

```php
// Antes: debías recordar el orden y pasar todos los argumentos anteriores
str_contains('Hola mundo', 'mundo'); // ¿Cuál es el pajar y cuál la aguja?

// Con named arguments: más legible y flexible
function crearUsuario(
    string $nombre,
    string $email,
    string $rol = 'usuario',
    bool $activo = true,
    ?string $avatar = null,
): void {
    // ...
}

// Puedes saltar parámetros opcionales
crearUsuario(
    nombre: 'Ana García',
    email: 'ana@example.com',
    avatar: '/img/ana.png',
    // $rol y $activo usan sus valores por defecto
);
```

### Combinando con argumentos posicionales

```php
// Los posicionales van primero, luego los nombrados
array_slice($array, offset: 2, length: 5, preserve_keys: true);

// Útil con funciones built-in confusas
setcookie(
    name: 'sesion',
    value: $token,
    expires_or_options: time() + 3600,
    secure: true,
    httponly: true,
    samesite: 'Strict',
);
```

---

## 2. Match Expression (PHP 8.0)

`match` es una versión mejorada de `switch`: usa comparación estricta, retorna un valor y no necesita `break`.

```php
// Con switch (clásico)
switch ($codigo) {
    case 200:
        $mensaje = 'OK';
        break;
    case 404:
        $mensaje = 'No encontrado';
        break;
    default:
        $mensaje = 'Desconocido';
}

// Con match (moderno y conciso)
$mensaje = match ($codigo) {
    200 => 'OK',
    301 => 'Redirección permanente',
    404 => 'No encontrado',
    500 => 'Error del servidor',
    default => 'Código desconocido',
};
```

### Múltiples condiciones y expresiones complejas

```php
// Varios valores para un mismo resultado
$tipo = match ($extension) {
    'jpg', 'jpeg', 'png', 'gif', 'webp' => 'imagen',
    'mp4', 'avi', 'mkv' => 'video',
    'pdf', 'doc', 'docx' => 'documento',
    default => 'otro',
};

// match(true) para condiciones complejas
$categoria = match (true) {
    $edad < 13 => 'niño',
    $edad < 18 => 'adolescente',
    $edad < 65 => 'adulto',
    default => 'adulto mayor',
};
```

> **Tip:** `match` lanza `UnhandledMatchError` si no hay coincidencia y no se define `default`. Esto es más seguro que `switch`, que simplemente ignora los casos no manejados.

---

## 3. Nullsafe Operator (PHP 8.0)

El operador `?->` permite encadenar llamadas a métodos y propiedades sin verificar `null` manualmente en cada paso.

```php
// Antes: verificaciones manuales tediosas
$pais = null;
if ($usuario !== null) {
    $direccion = $usuario->obtenerDireccion();
    if ($direccion !== null) {
        $ciudad = $direccion->obtenerCiudad();
        if ($ciudad !== null) {
            $pais = $ciudad->obtenerPais();
        }
    }
}

// Con nullsafe operator: una sola línea
$pais = $usuario?->obtenerDireccion()?->obtenerCiudad()?->obtenerPais();
// Si cualquier parte es null, toda la expresión retorna null
```

### Combinando con null coalescing

```php
// Valor por defecto si la cadena es null
$nombrePais = $usuario?->perfil?->pais?->nombre ?? 'No especificado';

// Con llamadas a métodos
$total = $carrito?->calcularTotal()?->formatear() ?? '$0.00';
```

---

## 4. Constructor Promotion (PHP 8.0)

La promoción de propiedades en el constructor reduce drásticamente el código repetitivo de las clases.

```php
// Antes: mucho código repetitivo
class Producto
{
    private string $nombre;
    private float $precio;
    private int $stock;
    private ?string $descripcion;

    public function __construct(
        string $nombre,
        float $precio,
        int $stock,
        ?string $descripcion = null
    ) {
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->descripcion = $descripcion;
    }
}

// Con constructor promotion: todo en uno
class Producto
{
    public function __construct(
        private string $nombre,
        private float $precio,
        private int $stock,
        private ?string $descripcion = null,
    ) {}

    public function getPrecioConIva(): float
    {
        return $this->precio * 1.21;
    }
}
```

### Combinando con readonly (PHP 8.1)

```php
class EventoDominio
{
    public function __construct(
        public readonly string $tipo,
        public readonly array $datos,
        public readonly DateTimeImmutable $fecha = new DateTimeImmutable(),
    ) {}
}

$evento = new EventoDominio(tipo: 'usuario.creado', datos: ['id' => 42]);
echo $evento->tipo;    // 'usuario.creado'
// $evento->tipo = 'x'; // Error: Cannot modify readonly property
```

---

## 5. Enums (PHP 8.1)

Los enums proporcionan un tipo seguro para representar un conjunto fijo de valores posibles.

### Enums puros

```php
enum EstadoPedido
{
    case Pendiente;
    case Procesando;
    case Enviado;
    case Entregado;
    case Cancelado;

    public function esActivo(): bool
    {
        return match ($this) {
            self::Pendiente, self::Procesando, self::Enviado => true,
            self::Entregado, self::Cancelado => false,
        };
    }

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => '⏳ Pendiente',
            self::Procesando => '🔄 Procesando',
            self::Enviado => '📦 Enviado',
            self::Entregado => '✅ Entregado',
            self::Cancelado => '❌ Cancelado',
        };
    }
}

$estado = EstadoPedido::Enviado;
echo $estado->etiqueta();      // 📦 Enviado
echo $estado->esActivo();      // true
```

### Backed Enums (con valor escalar)

```php
enum Rol: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Usuario = 'usuario';
    case Invitado = 'invitado';

    // Crear desde valor de base de datos
    public static function desdeDb(string $valor): self
    {
        return self::from($valor);       // Lanza ValueError si no existe
    }

    public static function desdeDbOpcional(string $valor): ?self
    {
        return self::tryFrom($valor);    // Retorna null si no existe
    }
}

// Uso con type hints
function tienePermiso(Rol $rol, string $accion): bool
{
    return match ($rol) {
        Rol::Admin => true,
        Rol::Editor => in_array($accion, ['leer', 'escribir', 'editar']),
        Rol::Usuario => $accion === 'leer',
        Rol::Invitado => false,
    };
}

$rol = Rol::from('editor');
tienePermiso($rol, 'escribir'); // true
```

### Enums implementando interfaces

```php
interface TieneColor
{
    public function color(): string;
}

enum Prioridad: int implements TieneColor
{
    case Baja = 1;
    case Media = 2;
    case Alta = 3;
    case Critica = 4;

    public function color(): string
    {
        return match ($this) {
            self::Baja => '#28a745',
            self::Media => '#ffc107',
            self::Alta => '#fd7e14',
            self::Critica => '#dc3545',
        };
    }
}
```

---

## 6. Readonly Properties y Classes (PHP 8.1 / 8.2)

### Propiedades readonly

```php
class Transferencia
{
    public function __construct(
        public readonly string $origen,
        public readonly string $destino,
        public readonly float $monto,
        public readonly DateTimeImmutable $fecha,
    ) {}
}

$t = new Transferencia('A', 'B', 500.00, new DateTimeImmutable());
// $t->monto = 1000; // Fatal error: Cannot modify readonly property
```

### Clases readonly (PHP 8.2)

```php
// Todas las propiedades son readonly automáticamente
readonly class Coordenadas
{
    public function __construct(
        public float $latitud,
        public float $longitud,
    ) {}

    public function distanciaA(Coordenadas $otra): float
    {
        // Fórmula de Haversine simplificada
        $dLat = deg2rad($otra->latitud - $this->latitud);
        $dLon = deg2rad($otra->longitud - $this->longitud);
        return sqrt($dLat ** 2 + $dLon ** 2) * 111.32;
    }
}
```

---

## 7. First-Class Callable Syntax (PHP 8.1)

Obtén una referencia a cualquier función o método como un `Closure` usando la sintaxis `func(...)`.

```php
// Antes
$fn = Closure::fromCallable('strlen');
$metodo = Closure::fromCallable([$objeto, 'metodo']);

// PHP 8.1: más limpio
$fn = strlen(...);
$metodo = $objeto->metodo(...);
$estatico = MiClase::metodoEstatico(...);

// Uso práctico con funciones de orden superior
$nombres = ['Ana', 'Juan', 'Pedro', 'María'];

$longitudes = array_map(mb_strlen(...), $nombres);
// [3, 4, 5, 5]

$mayusculas = array_map(mb_strtoupper(...), $nombres);
// ['ANA', 'JUAN', 'PEDRO', 'MARÍA']
```

---

## 8. Fibers (PHP 8.1)

Las Fibers proporcionan la base para la concurrencia cooperativa (ver lección de rendimiento para ejemplos avanzados).

```php
// Ejemplo práctico: pipeline de datos con pausa/reanudación
function productor(): Generator
{
    $datos = ['uno', 'dos', 'tres'];
    foreach ($datos as $dato) {
        yield $dato;
    }
}

$fiber = new Fiber(function (): void {
    foreach (productor() as $item) {
        $resultado = Fiber::suspend($item);
        echo "Procesado: {$resultado}" . PHP_EOL;
    }
});

// Consumir datos de la fiber
$valor = $fiber->start();
while (!$fiber->isTerminated()) {
    $valor = $fiber->resume(strtoupper($valor));
}
```

---

## 9. Intersection Types y DNF Types

### Intersection Types (PHP 8.1)

Requiere que un valor implemente múltiples tipos simultáneamente.

```php
// El parámetro debe implementar AMBAS interfaces
function procesarElemento(Countable&Iterator $coleccion): void
{
    echo "Elementos: " . count($coleccion) . PHP_EOL;

    foreach ($coleccion as $item) {
        echo $item . PHP_EOL;
    }
}
```

### DNF Types — Disjunctive Normal Form (PHP 8.2)

Combinación de union e intersection types.

```php
// (A&B)|C — debe ser (Countable E Iterator) O null
function procesar((Countable&Iterator)|null $datos): int
{
    if ($datos === null) {
        return 0;
    }
    return count($datos);
}

// Otro ejemplo: acepta un Stringable&Countable, o un string simple
function formatear((Stringable&Countable)|string $texto): string
{
    if (is_string($texto)) {
        return $texto;
    }
    return "{$texto} ({$texto->count()} elementos)";
}
```

---

## 10. Otras novedades destacadas

### Constantes en traits (PHP 8.2)

```php
trait Versionable
{
    protected const VERSION = '1.0';

    public function getVersion(): string
    {
        return static::VERSION;
    }
}
```

### Override attribute (PHP 8.3)

```php
class Animal
{
    public function hacerSonido(): string
    {
        return 'sonido genérico';
    }
}

class Perro extends Animal
{
    #[\Override]
    public function hacerSonido(): string
    {
        return '¡Guau!';
    }

    // Si el método padre no existe, PHP lanza un error
    // #[\Override]
    // public function metodoQueNoExiste(): void {} // Fatal error
}
```

### json_validate() (PHP 8.3)

```php
// Validar JSON sin decodificarlo
$json = '{"nombre": "Ana", "edad": 30}';

if (json_validate($json)) {
    $datos = json_decode($json, true);
    // Procesar datos
}

// Antes había que hacer:
// json_decode($json); if (json_last_error() === JSON_ERROR_NONE) { ... }
```

### Typed class constants (PHP 8.3)

```php
class Configuracion
{
    public const string APP_NOMBRE = 'SuperGuide';
    public const int MAX_INTENTOS = 5;
    public const float VERSION = 2.1;
    public const array IDIOMAS_SOPORTADOS = ['es', 'en', 'pt'];
}
```

---

## Resumen de versiones

| Versión | Característica clave                        | Impacto          |
|---------|----------------------------------------------|------------------|
| 8.0     | Named arguments, match, nullsafe, JIT        | Revolucionario   |
| 8.0     | Constructor promotion, union types            | Productividad    |
| 8.1     | Enums, Fibers, readonly, intersection types   | Tipado moderno   |
| 8.1     | First-class callable syntax                   | Funcional        |
| 8.2     | Readonly classes, DNF types, constantes traits| Refinamiento     |
| 8.3     | #[\Override], json_validate, typed constants  | Robustez         |

> **Tip:** Mantén tu versión de PHP actualizada. Cada versión menor trae mejoras de rendimiento significativas además de nuevas características. Consulta la [guía de migración oficial](https://www.php.net/migration83) antes de actualizar.
