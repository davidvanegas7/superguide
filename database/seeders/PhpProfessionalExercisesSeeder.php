<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhpProfessionalExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'php-profesional')->first();

        if (! $course) {
            $this->command->warn('PHP Professional course not found. Run CourseSeeder + PhpProfessionalLessonSeeder first.');
            return;
        }

        $lessons = Lesson::where('course_id', $course->id)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('sort_order');

        $exercises = $this->exercises($lessons);
        $now = now();

        foreach ($exercises as $ex) {
            DB::table('lesson_exercises')->updateOrInsert(
                ['lesson_id' => $ex['lesson_id']],
                array_merge($ex, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $this->command->info('PHP Professional exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── Lección 1: POO Avanzada ────────────────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Clases abstractas, interfaces y enums',
            'language'     => 'php',
            'description'  => <<<'MD'
Practica POO avanzada con PHP 8.1+.

1. Crea una interfaz `Renderable` con método `render(): string`.
2. Crea una clase abstracta `Shape` que implemente `Renderable` con `abstract area(): float` y constructor con `readonly string $color`.
3. Crea `Circle extends Shape` con `readonly float $radius`. Implementa `area()` y `render()` que retorne `"Circle({$color}, area={$area})"`.
4. Crea un Enum `ShapeType: string` con casos `Circle = 'circle'`, `Rectangle = 'rectangle'`. Método `create(mixed ...$args): Shape` que retorne la instancia correspondiente.
MD,
            'starter_code' => <<<'PHP'
<?php

interface Renderable {
    public function render(): string;
}

abstract class Shape implements Renderable {
    // Constructor con readonly $color
    // abstract area(): float
}

class Circle extends Shape {
    // Constructor con readonly $radius
    // Implementa area() y render()
}

enum ShapeType: string {
    // Casos Circle y Rectangle
    // Método create()
}
PHP,
            'solution_code' => <<<'PHP'
<?php

interface Renderable {
    public function render(): string;
}

abstract class Shape implements Renderable {
    public function __construct(
        public readonly string $color
    ) {}

    abstract public function area(): float;
}

class Circle extends Shape {
    public function __construct(
        string $color,
        public readonly float $radius
    ) {
        parent::__construct($color);
    }

    public function area(): float {
        return M_PI * $this->radius ** 2;
    }

    public function render(): string {
        return sprintf("Circle(%s, area=%.2f)", $this->color, $this->area());
    }
}

class Rectangle extends Shape {
    public function __construct(
        string $color,
        public readonly float $width,
        public readonly float $height
    ) {
        parent::__construct($color);
    }

    public function area(): float {
        return $this->width * $this->height;
    }

    public function render(): string {
        return sprintf("Rectangle(%s, area=%.2f)", $this->color, $this->area());
    }
}

enum ShapeType: string {
    case Circle = 'circle';
    case Rectangle = 'rectangle';

    public function create(mixed ...$args): Shape {
        return match($this) {
            self::Circle    => new Circle(...$args),
            self::Rectangle => new Rectangle(...$args),
        };
    }
}
PHP,
        ];

        // ── Lección 2: Interfaces, Traits y Composición ────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Traits con resolución de conflictos',
            'language'     => 'php',
            'description'  => <<<'MD'
Practica traits y resolución de conflictos.

1. Crea el trait `Timestampable` con métodos `getCreatedAt(): string` y `touch(): void` (actualiza timestamp).
2. Crea el trait `SoftDeletable` con `delete(): void` (marca `$deletedAt`), `restore(): void`, `isDeleted(): bool`.
3. Ambos traits tienen método `reset(): void` — `Timestampable::reset` limpia timestamps, `SoftDeletable::reset` limpia deleted.
4. Crea clase `Post` que use ambos traits. Resuelve el conflicto de `reset()` usando `insteadof` y crea `resetTimestamps()` como alias.
MD,
            'starter_code' => <<<'PHP'
<?php

trait Timestampable {
    private string $createdAt;
    private string $updatedAt;

    // getCreatedAt(), touch(), reset()
}

trait SoftDeletable {
    private ?string $deletedAt = null;

    // delete(), restore(), isDeleted(), reset()
}

class Post {
    // use ambos traits con resolución de conflictos
    public string $title;

    public function __construct(string $title) {
        $this->title = $title;
    }
}
PHP,
            'solution_code' => <<<'PHP'
<?php

trait Timestampable {
    private string $createdAt;
    private string $updatedAt;

    public function initTimestamps(): void {
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = $this->createdAt;
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    public function touch(): void {
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function reset(): void {
        $this->createdAt = '';
        $this->updatedAt = '';
    }
}

trait SoftDeletable {
    private ?string $deletedAt = null;

    public function delete(): void {
        $this->deletedAt = date('Y-m-d H:i:s');
    }

    public function restore(): void {
        $this->deletedAt = null;
    }

    public function isDeleted(): bool {
        return $this->deletedAt !== null;
    }

    public function reset(): void {
        $this->deletedAt = null;
    }
}

class Post {
    use Timestampable, SoftDeletable {
        SoftDeletable::reset insteadof Timestampable;
        Timestampable::reset as resetTimestamps;
    }

    public string $title;

    public function __construct(string $title) {
        $this->title = $title;
        $this->initTimestamps();
    }
}
PHP,
        ];

        // ── Lección 3: Namespaces y Autoloading ────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini autoloader PSR-4',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un mini autoloader PSR-4 en PHP.

1. Crea clase `Psr4Autoloader` con:
   - `register()` — registra el autoloader con `spl_autoload_register`.
   - `addNamespace(string $prefix, string $baseDir)` — mapea un namespace prefix a un directorio.
   - `loadClass(string $class): bool` — busca el archivo correspondiente y lo retorna (true si encontró, false si no). Convierte `\` a `/` y agrega `.php`.
2. `resolveFile(string $class): ?string` — retorna el path completo del archivo o null.
MD,
            'starter_code' => <<<'PHP'
<?php

class Psr4Autoloader {
    private array $prefixes = [];

    public function register(): void {
        // Registra con spl_autoload_register
    }

    public function addNamespace(string $prefix, string $baseDir): void {
        // Mapea namespace a directorio
    }

    public function loadClass(string $class): bool {
        // Carga la clase
    }

    public function resolveFile(string $class): ?string {
        // Resuelve el path del archivo
    }
}
PHP,
            'solution_code' => <<<'PHP'
<?php

class Psr4Autoloader {
    private array $prefixes = [];

    public function register(): void {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function addNamespace(string $prefix, string $baseDir): void {
        $prefix  = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, '/') . '/';
        $this->prefixes[$prefix] = $baseDir;
    }

    public function loadClass(string $class): bool {
        $file = $this->resolveFile($class);
        if ($file !== null && file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }

    public function resolveFile(string $class): ?string {
        foreach ($this->prefixes as $prefix => $baseDir) {
            if (strpos($class, $prefix) === 0) {
                $relativeClass = substr($class, strlen($prefix));
                $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                return $file;
            }
        }
        return null;
    }
}
PHP,
        ];

        // ── Lección 4: Composer ─────────────────────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de gestión de dependencias',
            'language'     => 'php',
            'description'  => <<<'MD'
Simula el sistema de resolución de dependencias de Composer.

1. `DependencyResolver` — `require(string $package, string $version)` agrega dependencia. `resolve(): array` retorna array ordenado de paquetes a instalar. Detecta conflictos de versión.
2. `VersionConstraint` — método estático `satisfies(string $version, string $constraint): bool`. Soporta `^` (compatible), `~` (tilde), `>=`, `>`, `<`, `<=` y exacta.
3. `Lockfile` — `lock(array $resolved)` guarda versiones exactas. `isLocked(string $package): bool`. `getLockedVersion(string $package): ?string`.
MD,
            'starter_code' => <<<'PHP'
<?php

class VersionConstraint {
    public static function satisfies(string $version, string $constraint): bool {
        // Evalúa si la versión cumple la restricción
    }
}

class DependencyResolver {
    private array $requirements = [];

    public function require(string $package, string $version): void {
        // Agrega dependencia
    }

    public function resolve(): array {
        // Retorna paquetes ordenados
    }
}

class Lockfile {
    private array $locked = [];

    public function lock(array $resolved): void {}
    public function isLocked(string $package): bool {}
    public function getLockedVersion(string $package): ?string {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php

class VersionConstraint {
    public static function satisfies(string $version, string $constraint): bool {
        if ($constraint === '*') return true;

        if (str_starts_with($constraint, '^')) {
            $base = substr($constraint, 1);
            $parts = explode('.', $base);
            $major = (int) $parts[0];
            return version_compare($version, $base, '>=')
                && version_compare($version, ($major + 1) . '.0.0', '<');
        }

        if (str_starts_with($constraint, '~')) {
            $base = substr($constraint, 1);
            $parts = explode('.', $base);
            $minor = (int) ($parts[1] ?? 0);
            $nextMinor = $parts[0] . '.' . ($minor + 1) . '.0';
            return version_compare($version, $base, '>=')
                && version_compare($version, $nextMinor, '<');
        }

        foreach (['>=', '<=', '>', '<'] as $op) {
            if (str_starts_with($constraint, $op)) {
                return version_compare($version, substr($constraint, strlen($op)), $op);
            }
        }

        return version_compare($version, $constraint, '==');
    }
}

class DependencyResolver {
    private array $requirements = [];

    public function require(string $package, string $version): void {
        $this->requirements[$package] = $version;
    }

    public function resolve(): array {
        $resolved = [];
        foreach ($this->requirements as $package => $version) {
            $resolved[] = ['package' => $package, 'version' => $version];
        }
        usort($resolved, fn($a, $b) => strcmp($a['package'], $b['package']));
        return $resolved;
    }
}

class Lockfile {
    private array $locked = [];

    public function lock(array $resolved): void {
        foreach ($resolved as $item) {
            $this->locked[$item['package']] = $item['version'];
        }
    }

    public function isLocked(string $package): bool {
        return isset($this->locked[$package]);
    }

    public function getLockedVersion(string $package): ?string {
        return $this->locked[$package] ?? null;
    }
}
PHP,
        ];

        // ── Lección 5: Manejo de Errores ──────────────────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Excepciones personalizadas y Result pattern',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un sistema robusto de manejo de errores.

1. Crea jerarquía de excepciones: `AppException extends \RuntimeException`, `ValidationException extends AppException` con `array $errors`, `NotFoundException extends AppException`.
2. Crea clase genérica `Result<T>`:
   - `Result::ok(mixed $value)` — factory para éxito.
   - `Result::fail(string $error)` — factory para error.
   - `isOk(): bool`, `isError(): bool`, `value()`, `error()`.
   - `map(callable $fn): Result` — transforma el valor si es ok.
   - `flatMap(callable $fn): Result` — encadena Results.
3. `ExceptionHandler` — `handle(\Throwable $e): array` retorna `['type' => class, 'message' => msg, 'code' => code]`.
MD,
            'starter_code' => <<<'PHP'
<?php

class AppException extends \RuntimeException {}

class ValidationException extends AppException {
    public function __construct(
        public readonly array $errors,
        string $message = "Validation failed"
    ) {
        parent::__construct($message);
    }
}

class NotFoundException extends AppException {}

class Result {
    private function __construct(
        private readonly bool $ok,
        private readonly mixed $value,
        private readonly ?string $error
    ) {}

    public static function ok(mixed $value): self {}
    public static function fail(string $error): self {}
    public function isOk(): bool {}
    public function isError(): bool {}
    public function value(): mixed {}
    public function error(): ?string {}
    public function map(callable $fn): self {}
    public function flatMap(callable $fn): self {}
}

class ExceptionHandler {
    public function handle(\Throwable $e): array {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php

class AppException extends \RuntimeException {}

class ValidationException extends AppException {
    public function __construct(
        public readonly array $errors,
        string $message = "Validation failed"
    ) {
        parent::__construct($message);
    }
}

class NotFoundException extends AppException {}

class Result {
    private function __construct(
        private readonly bool $ok,
        private readonly mixed $value,
        private readonly ?string $error
    ) {}

    public static function ok(mixed $value): self {
        return new self(true, $value, null);
    }

    public static function fail(string $error): self {
        return new self(false, null, $error);
    }

    public function isOk(): bool { return $this->ok; }
    public function isError(): bool { return !$this->ok; }

    public function value(): mixed {
        if (!$this->ok) throw new \LogicException("Cannot get value from error Result");
        return $this->value;
    }

    public function error(): ?string { return $this->error; }

    public function map(callable $fn): self {
        return $this->ok ? self::ok($fn($this->value)) : $this;
    }

    public function flatMap(callable $fn): self {
        return $this->ok ? $fn($this->value) : $this;
    }
}

class ExceptionHandler {
    public function handle(\Throwable $e): array {
        return [
            'type'    => get_class($e),
            'message' => $e->getMessage(),
            'code'    => $e->getCode(),
        ];
    }
}
PHP,
        ];

        // ── Lección 6: Generadores e Iteradores ────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Generadores y pipeline lazy',
            'language'     => 'php',
            'description'  => <<<'MD'
Trabaja con generadores PHP para procesamiento lazy.

1. `fibonacci(): Generator` — generador infinito de Fibonacci.
2. `chunkGenerator(iterable $data, int $size): Generator` — divide datos en chunks usando yield.
3. `Pipeline` — clase que encadena operaciones lazy:
   - `from(iterable $source)` — factory con la fuente.
   - `map(callable $fn): self` — transforma cada elemento.
   - `filter(callable $fn): self` — filtra elementos.
   - `take(int $n): self` — limita a N elementos.
   - `toArray(): array` — materializa el resultado.
   Internamente usa generadores para no cargar todo en memoria.
MD,
            'starter_code' => <<<'PHP'
<?php

function fibonacci(): Generator {
    // Generador infinito
}

function chunkGenerator(iterable $data, int $size): Generator {
    // Divide en chunks
}

class Pipeline {
    private iterable $source;
    private array $operations = [];

    public static function from(iterable $source): self {}
    public function map(callable $fn): self {}
    public function filter(callable $fn): self {}
    public function take(int $n): self {}
    public function toArray(): array {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php

function fibonacci(): Generator {
    $a = 0;
    $b = 1;
    while (true) {
        yield $a;
        [$a, $b] = [$b, $a + $b];
    }
}

function chunkGenerator(iterable $data, int $size): Generator {
    $chunk = [];
    foreach ($data as $item) {
        $chunk[] = $item;
        if (count($chunk) === $size) {
            yield $chunk;
            $chunk = [];
        }
    }
    if (!empty($chunk)) {
        yield $chunk;
    }
}

class Pipeline {
    private iterable $source;
    private array $operations = [];

    private function __construct(iterable $source) {
        $this->source = $source;
    }

    public static function from(iterable $source): self {
        return new self($source);
    }

    public function map(callable $fn): self {
        $this->operations[] = ['type' => 'map', 'fn' => $fn];
        return $this;
    }

    public function filter(callable $fn): self {
        $this->operations[] = ['type' => 'filter', 'fn' => $fn];
        return $this;
    }

    public function take(int $n): self {
        $this->operations[] = ['type' => 'take', 'n' => $n];
        return $this;
    }

    public function toArray(): array {
        $result = [];
        $count  = 0;
        $limit  = PHP_INT_MAX;

        foreach ($this->operations as $op) {
            if ($op['type'] === 'take') $limit = $op['n'];
        }

        foreach ($this->source as $item) {
            $skip = false;
            foreach ($this->operations as $op) {
                match ($op['type']) {
                    'map'    => $item = ($op['fn'])($item),
                    'filter' => $skip = !($op['fn'])($item),
                    'take'   => null,
                };
                if ($skip) break;
            }
            if (!$skip) {
                $result[] = $item;
                if (++$count >= $limit) break;
            }
        }
        return $result;
    }
}
PHP,
        ];

        // ── Lección 7: Tipado Estricto ─────────────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Colección tipada con genéricos simulados',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa una colección tipada que simule genéricos.

1. `TypedCollection` — `__construct(string $type)` define el tipo aceptado. `add(mixed $item): void` valida tipo (usando `instanceof` para clases o `get_debug_type()` para primitivos). `get(int $index): mixed`. `count(): int`. `toArray(): array`.
2. `TypeSafe` — trait con método `assertType(mixed $value, string $type): void` que lanza `\TypeError` si no coincide.
3. `Pair` — clase con `readonly mixed $first` y `readonly mixed $second`. Constructor que valida tipos. Método `map(callable $fnFirst, callable $fnSecond): Pair`.
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

trait TypeSafe {
    public function assertType(mixed $value, string $type): void {
        // Valida tipo o lanza TypeError
    }
}

class TypedCollection {
    use TypeSafe;
    private array $items = [];

    public function __construct(private readonly string $type) {}
    public function add(mixed $item): void {}
    public function get(int $index): mixed {}
    public function count(): int {}
    public function toArray(): array {}
}

class Pair {
    public function __construct(
        public readonly mixed $first,
        public readonly mixed $second
    ) {}

    public function map(callable $fnFirst, callable $fnSecond): self {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

trait TypeSafe {
    public function assertType(mixed $value, string $type): void {
        $actualType = get_debug_type($value);
        if ($actualType !== $type && !($value instanceof $type)) {
            throw new \TypeError("Expected {$type}, got {$actualType}");
        }
    }
}

class TypedCollection {
    use TypeSafe;
    private array $items = [];

    public function __construct(private readonly string $type) {}

    public function add(mixed $item): void {
        $this->assertType($item, $this->type);
        $this->items[] = $item;
    }

    public function get(int $index): mixed {
        if (!isset($this->items[$index])) {
            throw new \OutOfRangeException("Index {$index} out of range");
        }
        return $this->items[$index];
    }

    public function count(): int {
        return count($this->items);
    }

    public function toArray(): array {
        return $this->items;
    }
}

class Pair {
    public function __construct(
        public readonly mixed $first,
        public readonly mixed $second
    ) {}

    public function map(callable $fnFirst, callable $fnSecond): self {
        return new self($fnFirst($this->first), $fnSecond($this->second));
    }
}
PHP,
        ];

        // ── Lección 8: Estándares PSR ──────────────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini PSR-7 HTTP Messages y PSR-11 Container',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa mini versiones de PSR-7 y PSR-11.

1. `ServerRequest` (PSR-7 simplificado):
   - `__construct(string $method, string $uri, array $headers, string $body)`.
   - `getMethod()`, `getUri()`, `getHeaders()`, `getBody()`, `getQueryParams(): array` (parse de query string).
   - `withHeader(string $name, string $value): self` — inmutable, retorna copia.

2. `Container` (PSR-11):
   - `set(string $id, callable $factory)` — registra un factory.
   - `get(string $id): mixed` — resuelve (singleton). `has(string $id): bool`.
   - `singleton(string $id, callable $factory)` — marca como singleton.
   - Lanza `NotFoundException` si no existe.
MD,
            'starter_code' => <<<'PHP'
<?php

class ServerRequest {
    public function __construct(
        private string $method,
        private string $uri,
        private array $headers = [],
        private string $body = ''
    ) {}

    public function getMethod(): string {}
    public function getUri(): string {}
    public function getHeaders(): array {}
    public function getBody(): string {}
    public function getQueryParams(): array {}
    public function withHeader(string $name, string $value): self {}
}

class ContainerNotFoundException extends \RuntimeException {}

class Container {
    private array $factories = [];
    private array $instances = [];

    public function set(string $id, callable $factory): void {}
    public function get(string $id): mixed {}
    public function has(string $id): bool {}
    public function singleton(string $id, callable $factory): void {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php

class ServerRequest {
    public function __construct(
        private string $method,
        private string $uri,
        private array $headers = [],
        private string $body = ''
    ) {}

    public function getMethod(): string { return $this->method; }
    public function getUri(): string { return $this->uri; }
    public function getHeaders(): array { return $this->headers; }
    public function getBody(): string { return $this->body; }

    public function getQueryParams(): array {
        $parts = parse_url($this->uri);
        $params = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $params);
        }
        return $params;
    }

    public function withHeader(string $name, string $value): self {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }
}

class ContainerNotFoundException extends \RuntimeException {}

class Container {
    private array $factories  = [];
    private array $instances  = [];
    private array $singletons = [];

    public function set(string $id, callable $factory): void {
        $this->factories[$id] = $factory;
    }

    public function get(string $id): mixed {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        if (!$this->has($id)) {
            throw new ContainerNotFoundException("Service '{$id}' not found");
        }
        $instance = ($this->factories[$id])($this);
        if (isset($this->singletons[$id])) {
            $this->instances[$id] = $instance;
        }
        return $instance;
    }

    public function has(string $id): bool {
        return isset($this->factories[$id]);
    }

    public function singleton(string $id, callable $factory): void {
        $this->factories[$id]  = $factory;
        $this->singletons[$id] = true;
    }
}
PHP,
        ];

        // ── Lección 9: Patrones de Diseño ──────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Strategy, Observer y Repository patterns',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa tres patrones de diseño en PHP.

1. **Strategy**: Interfaz `SortStrategy` con `sort(array &$data): void`. Implementaciones: `BubbleSort`, `QuickSort` (usa `sort()`). Clase `Sorter` con `setStrategy(SortStrategy)` y `sort(array &$data)`.

2. **Observer**: Interfaz `Observer` con `update(string $event, mixed $data): void`. Trait `Observable` con `subscribe(string $event, Observer $obs)`, `notify(string $event, mixed $data)`. Clase `EventBus` que usa el trait.

3. **Repository**: Interfaz `Repository<T>` con `find(int $id): ?array`, `findAll(): array`, `save(array $entity): array`, `delete(int $id): bool`. Implementación `InMemoryRepository`.
MD,
            'starter_code' => <<<'PHP'
<?php

// Strategy
interface SortStrategy {
    public function sort(array &$data): void;
}

class BubbleSort implements SortStrategy {
    public function sort(array &$data): void {}
}

class Sorter {
    public function setStrategy(SortStrategy $strategy): void {}
    public function sort(array &$data): void {}
}

// Observer
interface Observer {
    public function update(string $event, mixed $data): void;
}

trait Observable {
    public function subscribe(string $event, Observer $observer): void {}
    public function notify(string $event, mixed $data): void {}
}

// Repository
interface RepositoryInterface {
    public function find(int $id): ?array;
    public function findAll(): array;
    public function save(array $entity): array;
    public function delete(int $id): bool;
}

class InMemoryRepository implements RepositoryInterface {
    // Implementa con array en memoria
}
PHP,
            'solution_code' => <<<'PHP'
<?php

// Strategy
interface SortStrategy {
    public function sort(array &$data): void;
}

class BubbleSort implements SortStrategy {
    public function sort(array &$data): void {
        $n = count($data);
        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = 0; $j < $n - $i - 1; $j++) {
                if ($data[$j] > $data[$j + 1]) {
                    [$data[$j], $data[$j + 1]] = [$data[$j + 1], $data[$j]];
                }
            }
        }
    }
}

class QuickSort implements SortStrategy {
    public function sort(array &$data): void {
        sort($data);
    }
}

class Sorter {
    private SortStrategy $strategy;

    public function setStrategy(SortStrategy $strategy): void {
        $this->strategy = $strategy;
    }

    public function sort(array &$data): void {
        $this->strategy->sort($data);
    }
}

// Observer
interface Observer {
    public function update(string $event, mixed $data): void;
}

trait Observable {
    private array $listeners = [];

    public function subscribe(string $event, Observer $observer): void {
        $this->listeners[$event][] = $observer;
    }

    public function notify(string $event, mixed $data): void {
        foreach ($this->listeners[$event] ?? [] as $observer) {
            $observer->update($event, $data);
        }
    }
}

class EventBus {
    use Observable;
}

// Repository
interface RepositoryInterface {
    public function find(int $id): ?array;
    public function findAll(): array;
    public function save(array $entity): array;
    public function delete(int $id): bool;
}

class InMemoryRepository implements RepositoryInterface {
    private array $store = [];
    private int $nextId = 1;

    public function find(int $id): ?array {
        return $this->store[$id] ?? null;
    }

    public function findAll(): array {
        return array_values($this->store);
    }

    public function save(array $entity): array {
        if (!isset($entity['id'])) {
            $entity['id'] = $this->nextId++;
        }
        $this->store[$entity['id']] = $entity;
        return $entity;
    }

    public function delete(int $id): bool {
        if (isset($this->store[$id])) {
            unset($this->store[$id]);
            return true;
        }
        return false;
    }
}
PHP,
        ];

        // ── Lección 10: Testing con PHPUnit ────────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini framework de testing',
            'language'     => 'php',
            'description'  => <<<'MD'
Construye un mini framework de testing al estilo PHPUnit.

1. `TestCase` — clase base. Métodos: `assertEquals($expected, $actual)`, `assertTrue($value)`, `assertFalse($value)`, `assertCount(int $expected, array $array)`. Cada assert lanza `AssertionFailedException` si falla.
2. `TestRunner` — `addTest(string $className)`. `run(): TestResult`. Ejecuta todos los métodos que empiecen con `test` en cada clase.
3. `TestResult` — `passed: int`, `failed: int`, `errors: array` con detalles de cada fallo (className, method, message).
MD,
            'starter_code' => <<<'PHP'
<?php

class AssertionFailedException extends \RuntimeException {}

class TestCase {
    public function assertEquals(mixed $expected, mixed $actual): void {}
    public function assertTrue(mixed $value): void {}
    public function assertFalse(mixed $value): void {}
    public function assertCount(int $expected, array $array): void {}
}

class TestResult {
    public int $passed = 0;
    public int $failed = 0;
    public array $errors = [];
}

class TestRunner {
    private array $testClasses = [];

    public function addTest(string $className): void {}
    public function run(): TestResult {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php

class AssertionFailedException extends \RuntimeException {}

class TestCase {
    public function assertEquals(mixed $expected, mixed $actual): void {
        if ($expected !== $actual) {
            throw new AssertionFailedException(
                "Expected " . var_export($expected, true) . ", got " . var_export($actual, true)
            );
        }
    }

    public function assertTrue(mixed $value): void {
        if ($value !== true) {
            throw new AssertionFailedException("Expected true, got " . var_export($value, true));
        }
    }

    public function assertFalse(mixed $value): void {
        if ($value !== false) {
            throw new AssertionFailedException("Expected false, got " . var_export($value, true));
        }
    }

    public function assertCount(int $expected, array $array): void {
        $actual = count($array);
        if ($actual !== $expected) {
            throw new AssertionFailedException("Expected count {$expected}, got {$actual}");
        }
    }
}

class TestResult {
    public int $passed = 0;
    public int $failed = 0;
    public array $errors = [];
}

class TestRunner {
    private array $testClasses = [];

    public function addTest(string $className): void {
        $this->testClasses[] = $className;
    }

    public function run(): TestResult {
        $result = new TestResult();

        foreach ($this->testClasses as $className) {
            $reflection = new \ReflectionClass($className);
            $instance   = new $className();

            foreach ($reflection->getMethods() as $method) {
                if (str_starts_with($method->getName(), 'test')) {
                    try {
                        $instance->{$method->getName()}();
                        $result->passed++;
                    } catch (AssertionFailedException $e) {
                        $result->failed++;
                        $result->errors[] = [
                            'class'   => $className,
                            'method'  => $method->getName(),
                            'message' => $e->getMessage(),
                        ];
                    }
                }
            }
        }

        return $result;
    }
}
PHP,
        ];

        // ── Lección 11: Arrays Avanzados ────────────────────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Funciones de array y transformaciones',
            'language'     => 'php',
            'description'  => <<<'MD'
Practica funciones avanzadas de arrays en PHP.

1. `groupBy(array $items, string $key): array` — agrupa array de arrays asociativos por una clave. Ej: `[['role'=>'admin','name'=>'A'], ['role'=>'user','name'=>'B']]` → `['admin' => [...], 'user' => [...]]`.
2. `pluck(array $items, string $key, ?string $indexBy = null): array` — extrae valores de una clave, opcionalmente indexados.
3. `pipeline(array $data, callable ...$fns): array` — aplica funciones secuencialmente.
4. `arrayFlatten(array $nested, int $depth = INF): array` — aplana array recursivamente hasta profundidad dada.
5. `arrayOnly(array $data, array $keys): array` — retorna solo las claves especificadas.
MD,
            'starter_code' => <<<'PHP'
<?php

function groupBy(array $items, string $key): array {}
function pluck(array $items, string $key, ?string $indexBy = null): array {}
function pipeline(array $data, callable ...$fns): array {}
function arrayFlatten(array $nested, int $depth = INF): array {}
function arrayOnly(array $data, array $keys): array {}
PHP,
            'solution_code' => <<<'PHP'
<?php

function groupBy(array $items, string $key): array {
    $result = [];
    foreach ($items as $item) {
        $groupKey = $item[$key] ?? '_none';
        $result[$groupKey][] = $item;
    }
    return $result;
}

function pluck(array $items, string $key, ?string $indexBy = null): array {
    $result = [];
    foreach ($items as $item) {
        if ($indexBy !== null) {
            $result[$item[$indexBy]] = $item[$key];
        } else {
            $result[] = $item[$key];
        }
    }
    return $result;
}

function pipeline(array $data, callable ...$fns): array {
    return array_reduce($fns, fn($carry, $fn) => $fn($carry), $data);
}

function arrayFlatten(array $nested, int $depth = INF): array {
    $result = [];
    foreach ($nested as $item) {
        if (is_array($item) && $depth > 0) {
            $result = array_merge($result, arrayFlatten($item, $depth - 1));
        } else {
            $result[] = $item;
        }
    }
    return $result;
}

function arrayOnly(array $data, array $keys): array {
    return array_intersect_key($data, array_flip($keys));
}
PHP,
        ];

        // ── Lección 12: Strings y Regex ─────────────────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Procesador de texto con regex',
            'language'     => 'php',
            'description'  => <<<'MD'
Procesa texto con funciones de string y expresiones regulares.

1. `extractEmails(string $text): array` — extrae todas las direcciones de email usando regex.
2. `slugify(string $text): string` — convierte texto a slug (lowercase, sin acentos, espacios → hyphens, sin caracteres especiales).
3. `highlight(string $text, string $term): string` — envuelve cada ocurrencia de `$term` en `<mark>$term</mark>` (case-insensitive).
4. `parseMarkdownLinks(string $md): array` — extrae links de markdown `[text](url)`. Retorna array de `['text' => ..., 'url' => ...]`.
5. `maskEmail(string $email): string` — enmascara: `john.doe@example.com` → `j*****e@example.com`.
MD,
            'starter_code' => <<<'PHP'
<?php

function extractEmails(string $text): array {}
function slugify(string $text): string {}
function highlight(string $text, string $term): string {}
function parseMarkdownLinks(string $md): array {}
function maskEmail(string $email): string {}
PHP,
            'solution_code' => <<<'PHP'
<?php

function extractEmails(string $text): array {
    preg_match_all('/[\w.+-]+@[\w-]+\.[\w.]+/', $text, $matches);
    return $matches[0];
}

function slugify(string $text): string {
    $text = mb_strtolower($text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function highlight(string $text, string $term): string {
    return preg_replace(
        '/(' . preg_quote($term, '/') . ')/i',
        '<mark>$1</mark>',
        $text
    );
}

function parseMarkdownLinks(string $md): array {
    preg_match_all('/\[([^\]]+)\]\(([^)]+)\)/', $md, $matches, PREG_SET_ORDER);
    return array_map(fn($m) => ['text' => $m[1], 'url' => $m[2]], $matches);
}

function maskEmail(string $email): string {
    [$local, $domain] = explode('@', $email);
    $len = mb_strlen($local);
    if ($len <= 2) return $local . '@' . $domain;
    $masked = $local[0] . str_repeat('*', $len - 2) . $local[$len - 1];
    return $masked . '@' . $domain;
}
PHP,
        ];

        // ── Lección 13: Sistema de Archivos ─────────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Gestor de archivos y streams',
            'language'     => 'php',
            'description'  => <<<'MD'
Crea un gestor de archivos usando las APIs de PHP.

1. `FileManager` — Clase con:
   - `read(string $path): string` — lee contenido completo. Lanza excepción si no existe.
   - `write(string $path, string $content): int` — escribe y retorna bytes escritos.
   - `append(string $path, string $content): int` — agrega al final.
   - `exists(string $path): bool`.
   - `delete(string $path): bool`.
2. `CsvParser` — `parse(string $content, string $delimiter = ','): array` — parsea CSV string a array de arrays asociativos (primera fila = headers).
3. `JsonStore` — `__construct(string $filePath)`. `get(string $key): mixed`. `set(string $key, mixed $value): void`. `all(): array`. Persiste en archivo JSON.
MD,
            'starter_code' => <<<'PHP'
<?php

class FileManager {
    public function read(string $path): string {}
    public function write(string $path, string $content): int {}
    public function append(string $path, string $content): int {}
    public function exists(string $path): bool {}
    public function delete(string $path): bool {}
}

class CsvParser {
    public function parse(string $content, string $delimiter = ','): array {}
}

class JsonStore {
    public function __construct(private string $filePath) {}
    public function get(string $key): mixed {}
    public function set(string $key, mixed $value): void {}
    public function all(): array {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php

class FileManager {
    public function read(string $path): string {
        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }
        return file_get_contents($path);
    }

    public function write(string $path, string $content): int {
        return file_put_contents($path, $content);
    }

    public function append(string $path, string $content): int {
        return file_put_contents($path, $content, FILE_APPEND);
    }

    public function exists(string $path): bool {
        return file_exists($path);
    }

    public function delete(string $path): bool {
        return file_exists($path) && unlink($path);
    }
}

class CsvParser {
    public function parse(string $content, string $delimiter = ','): array {
        $lines   = explode("\n", trim($content));
        $headers = str_getcsv(array_shift($lines), $delimiter);
        $result  = [];

        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $values   = str_getcsv($line, $delimiter);
            $result[] = array_combine($headers, $values);
        }

        return $result;
    }
}

class JsonStore {
    private array $data;

    public function __construct(private string $filePath) {
        $this->data = file_exists($filePath)
            ? json_decode(file_get_contents($filePath), true) ?? []
            : [];
    }

    public function get(string $key): mixed {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, mixed $value): void {
        $this->data[$key] = $value;
        file_put_contents($this->filePath, json_encode($this->data, JSON_PRETTY_PRINT));
    }

    public function all(): array {
        return $this->data;
    }
}
PHP,
        ];

        // ── Lección 14: Sesiones y Cookies ──────────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Gestor de sesiones y CSRF',
            'language'     => 'php',
            'description'  => <<<'MD'
Simula el manejo de sesiones y protección CSRF.

1. `SessionManager` — `start()`, `set(string $key, mixed $value)`, `get(string $key, mixed $default = null): mixed`, `has(string $key): bool`, `remove(string $key)`, `destroy()`, `flash(string $key, mixed $value)`, `getFlash(string $key): mixed` (se elimina al leer).
2. `CsrfGuard` — `generateToken(): string` (genera y almacena en sesión). `validateToken(string $token): bool`. `getTokenField(): string` (retorna HTML input hidden).
3. `CookieJar` — `set(string $name, string $value, array $options = [])`, `get(string $name): ?string`, `delete(string $name)`. Options: `expires`, `path`, `httponly`, `secure`, `samesite`.
MD,
            'starter_code' => <<<'PHP'
<?php

class SessionManager {
    private array $store = [];
    private array $flash = [];

    public function start(): void {}
    public function set(string $key, mixed $value): void {}
    public function get(string $key, mixed $default = null): mixed {}
    public function has(string $key): bool {}
    public function remove(string $key): void {}
    public function destroy(): void {}
    public function flash(string $key, mixed $value): void {}
    public function getFlash(string $key): mixed {}
}

class CsrfGuard {
    public function __construct(private SessionManager $session) {}
    public function generateToken(): string {}
    public function validateToken(string $token): bool {}
    public function getTokenField(): string {}
}

class CookieJar {
    private array $cookies = [];
    public function set(string $name, string $value, array $options = []): void {}
    public function get(string $name): ?string {}
    public function delete(string $name): void {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php

class SessionManager {
    private array $store = [];
    private array $flash = [];
    private bool $started = false;

    public function start(): void {
        $this->started = true;
    }

    public function set(string $key, mixed $value): void {
        $this->store[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed {
        return $this->store[$key] ?? $default;
    }

    public function has(string $key): bool {
        return isset($this->store[$key]);
    }

    public function remove(string $key): void {
        unset($this->store[$key]);
    }

    public function destroy(): void {
        $this->store = [];
        $this->flash = [];
    }

    public function flash(string $key, mixed $value): void {
        $this->flash[$key] = $value;
    }

    public function getFlash(string $key): mixed {
        $value = $this->flash[$key] ?? null;
        unset($this->flash[$key]);
        return $value;
    }
}

class CsrfGuard {
    public function __construct(private SessionManager $session) {}

    public function generateToken(): string {
        $token = bin2hex(random_bytes(32));
        $this->session->set('_csrf_token', $token);
        return $token;
    }

    public function validateToken(string $token): bool {
        $stored = $this->session->get('_csrf_token');
        return $stored !== null && hash_equals($stored, $token);
    }

    public function getTokenField(): string {
        $token = $this->session->get('_csrf_token') ?? $this->generateToken();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token) . '">';
    }
}

class CookieJar {
    private array $cookies = [];

    public function set(string $name, string $value, array $options = []): void {
        $this->cookies[$name] = array_merge(
            ['value' => $value, 'expires' => 0, 'path' => '/', 'httponly' => true, 'secure' => false, 'samesite' => 'Lax'],
            $options
        );
    }

    public function get(string $name): ?string {
        return isset($this->cookies[$name]) ? $this->cookies[$name]['value'] : null;
    }

    public function delete(string $name): void {
        unset($this->cookies[$name]);
    }
}
PHP,
        ];

        // ── Lección 15: PDO y Bases de Datos ───────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Query Builder con PDO',
            'language'     => 'php',
            'description'  => <<<'MD'
Construye un Query Builder que genere SQL.

Implementa `QueryBuilder`:
1. `table(string $name): self` — define la tabla.
2. `select(string ...$columns): self` — columnas (default `*`).
3. `where(string $column, string $operator, mixed $value): self` — agrega condición WHERE.
4. `orderBy(string $column, string $direction = 'ASC'): self`.
5. `limit(int $n): self`.
6. `toSql(): string` — genera el SQL.
7. `getBindings(): array` — retorna los valores de bindings.
8. `insert(array $data): string` — genera INSERT INTO.
9. `update(array $data): string` — genera UPDATE con WHEREs.
10. `delete(): string` — genera DELETE FROM con WHEREs.
MD,
            'starter_code' => <<<'PHP'
<?php

class QueryBuilder {
    private string $table = '';
    private array $columns = ['*'];
    private array $wheres = [];
    private array $bindings = [];
    private ?string $orderBy = null;
    private ?int $limit = null;

    public function table(string $name): self {}
    public function select(string ...$columns): self {}
    public function where(string $column, string $operator, mixed $value): self {}
    public function orderBy(string $column, string $direction = 'ASC'): self {}
    public function limit(int $n): self {}
    public function toSql(): string {}
    public function getBindings(): array {}
    public function insert(array $data): string {}
    public function update(array $data): string {}
    public function delete(): string {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php

class QueryBuilder {
    private string $table = '';
    private array $columns = ['*'];
    private array $wheres = [];
    private array $bindings = [];
    private ?string $orderBy = null;
    private ?int $limit = null;

    public function table(string $name): self {
        $this->table = $name;
        return $this;
    }

    public function select(string ...$columns): self {
        $this->columns = $columns ?: ['*'];
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self {
        $this->wheres[]   = "{$column} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self {
        $this->orderBy = "{$column} {$direction}";
        return $this;
    }

    public function limit(int $n): self {
        $this->limit = $n;
        return $this;
    }

    public function toSql(): string {
        $sql = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        }
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        return $sql;
    }

    public function getBindings(): array {
        return $this->bindings;
    }

    public function insert(array $data): string {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $this->bindings = array_merge($this->bindings, array_values($data));
        return "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
    }

    public function update(array $data): string {
        $sets = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
        $this->bindings = array_merge(array_values($data), $this->bindings);
        $sql = "UPDATE {$this->table} SET {$sets}";
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        return $sql;
    }

    public function delete(): string {
        $sql = "DELETE FROM {$this->table}";
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        return $sql;
    }
}
PHP,
        ];

        // ── Lección 16: Seguridad ──────────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sanitización, hashing y validación segura',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa funciones de seguridad en PHP.

1. `sanitizeInput(string $input): string` — elimina tags HTML y aplica `htmlspecialchars`.
2. `hashPassword(string $password): string` — usa `password_hash` con `PASSWORD_BCRYPT`.
3. `verifyPassword(string $password, string $hash): bool` — usa `password_verify`.
4. `Validator` — clase con `rules(array $rules)` y `validate(array $data): array`. Soporta reglas: `required`, `email`, `min:N`, `max:N`, `alpha`. Retorna array de errores o vacío si válido.
5. `generateSecureToken(int $length = 32): string` — genera token con `random_bytes`.
MD,
            'starter_code' => <<<'PHP'
<?php

function sanitizeInput(string $input): string {}
function hashPassword(string $password): string {}
function verifyPassword(string $password, string $hash): bool {}
function generateSecureToken(int $length = 32): string {}

class Validator {
    private array $rules = [];

    public function rules(array $rules): self {}
    public function validate(array $data): array {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php

function sanitizeInput(string $input): string {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}

function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function generateSecureToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

class Validator {
    private array $rules = [];

    public function rules(array $rules): self {
        $this->rules = $rules;
        return $this;
    }

    public function validate(array $data): array {
        $errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($rules as $rule) {
                $params = null;
                if (str_contains($rule, ':')) {
                    [$rule, $params] = explode(':', $rule, 2);
                }

                $error = match ($rule) {
                    'required' => ($value === null || $value === '') ? "{$field} is required" : null,
                    'email'    => ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) ? "{$field} must be a valid email" : null,
                    'min'      => ($value !== null && mb_strlen((string) $value) < (int) $params) ? "{$field} must be at least {$params} characters" : null,
                    'max'      => ($value !== null && mb_strlen((string) $value) > (int) $params) ? "{$field} must be at most {$params} characters" : null,
                    'alpha'    => ($value && !ctype_alpha($value)) ? "{$field} must contain only letters" : null,
                    default    => null,
                };

                if ($error) {
                    $errors[$field][] = $error;
                }
            }
        }

        return $errors;
    }
}
PHP,
        ];

        // ── Lección 17: Rendimiento ─────────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Cache, memoización y profiling',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa herramientas de rendimiento y caching.

1. `Cache` — `get(string $key): mixed`, `set(string $key, mixed $value, int $ttl = 3600): void`, `has(string $key): bool`, `delete(string $key)`, `clear()`. Con TTL (time-to-live). `remember(string $key, int $ttl, callable $fn): mixed` — cachea resultado de callable.
2. `memoize(callable $fn): callable` — retorna una versión memoizada de la función. Usa el hash de los argumentos como key.
3. `Profiler` — `start(string $label)`, `stop(string $label): float` (retorna ms), `report(): array` — retorna array de `['label' => ..., 'duration_ms' => ...]` ordenado por duración desc.
MD,
            'starter_code' => <<<'PHP'
<?php

class Cache {
    private array $store = [];

    public function get(string $key): mixed {}
    public function set(string $key, mixed $value, int $ttl = 3600): void {}
    public function has(string $key): bool {}
    public function delete(string $key): void {}
    public function clear(): void {}
    public function remember(string $key, int $ttl, callable $fn): mixed {}
}

function memoize(callable $fn): callable {}

class Profiler {
    private array $timers = [];
    private array $results = [];

    public function start(string $label): void {}
    public function stop(string $label): float {}
    public function report(): array {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php

class Cache {
    private array $store = [];

    public function get(string $key): mixed {
        if (!$this->has($key)) return null;
        return $this->store[$key]['value'];
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void {
        $this->store[$key] = [
            'value'      => $value,
            'expires_at' => time() + $ttl,
        ];
    }

    public function has(string $key): bool {
        if (!isset($this->store[$key])) return false;
        if (time() > $this->store[$key]['expires_at']) {
            unset($this->store[$key]);
            return false;
        }
        return true;
    }

    public function delete(string $key): void {
        unset($this->store[$key]);
    }

    public function clear(): void {
        $this->store = [];
    }

    public function remember(string $key, int $ttl, callable $fn): mixed {
        if ($this->has($key)) return $this->get($key);
        $value = $fn();
        $this->set($key, $value, $ttl);
        return $value;
    }
}

function memoize(callable $fn): callable {
    $cache = [];
    return function () use ($fn, &$cache) {
        $key = md5(serialize(func_get_args()));
        if (!array_key_exists($key, $cache)) {
            $cache[$key] = $fn(...func_get_args());
        }
        return $cache[$key];
    };
}

class Profiler {
    private array $timers = [];
    private array $results = [];

    public function start(string $label): void {
        $this->timers[$label] = hrtime(true);
    }

    public function stop(string $label): float {
        $elapsed = (hrtime(true) - $this->timers[$label]) / 1e6;
        $this->results[] = ['label' => $label, 'duration_ms' => round($elapsed, 3)];
        unset($this->timers[$label]);
        return round($elapsed, 3);
    }

    public function report(): array {
        usort($this->results, fn($a, $b) => $b['duration_ms'] <=> $a['duration_ms']);
        return $this->results;
    }
}
PHP,
        ];

        // ── Lección 18: PHP 8.x Novedades ──────────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Features de PHP 8: match, enums, fibers',
            'language'     => 'php',
            'description'  => <<<'MD'
Practica las funcionalidades modernas de PHP 8.x.

1. `HttpStatus` — Enum backed (int) con casos `Ok = 200`, `NotFound = 404`, `ServerError = 500`. Método `label(): string` que retorna texto descriptivo. Método estático `fromCode(int $code): self`.
2. `Config` — clase con readonly properties y constructor promotion. Implementa `__construct(readonly string $appName, readonly string $env = 'production', readonly int $debug = 0)`. Método `isDebug(): bool`.
3. `matchTransform(mixed $value): string` — usa `match` para transformar: `int` → `"entero:{$value}"`, `string` → `"texto:{$value}"`, `array` → `"lista:N"`, `null` → `"nulo"`, default → `"desconocido"`.
4. `nullsafeChain(array $data): ?string` — simula el operador nullsafe accediendo a `$data['user']['address']['city']` de forma segura.
MD,
            'starter_code' => <<<'PHP'
<?php

enum HttpStatus: int {
    // Casos Ok, NotFound, ServerError
    // Método label()
    // Método estático fromCode()
}

class Config {
    // Constructor promotion con readonly
    // Método isDebug()
}

function matchTransform(mixed $value): string {
    // Usa match expression
}

function nullsafeChain(array $data): ?string {
    // Acceso seguro anidado
}
PHP,
            'solution_code' => <<<'PHP'
<?php

enum HttpStatus: int {
    case Ok          = 200;
    case NotFound    = 404;
    case ServerError = 500;

    public function label(): string {
        return match($this) {
            self::Ok          => 'OK',
            self::NotFound    => 'Not Found',
            self::ServerError => 'Internal Server Error',
        };
    }

    public static function fromCode(int $code): self {
        return self::from($code);
    }
}

class Config {
    public function __construct(
        public readonly string $appName,
        public readonly string $env = 'production',
        public readonly int $debug = 0
    ) {}

    public function isDebug(): bool {
        return $this->debug !== 0;
    }
}

function matchTransform(mixed $value): string {
    return match (true) {
        is_int($value)    => "entero:{$value}",
        is_string($value) => "texto:{$value}",
        is_array($value)  => "lista:" . count($value),
        is_null($value)   => "nulo",
        default           => "desconocido",
    };
}

function nullsafeChain(array $data): ?string {
    return $data['user']['address']['city'] ?? null;
}
PHP,
        ];

        // ── Lección 19: Preguntas de Entrevista ─────────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Algoritmos y conceptos de entrevista PHP',
            'language'     => 'php',
            'description'  => <<<'MD'
Resuelve problemas clásicos de entrevistas PHP.

1. `compareValues(mixed $a, mixed $b): array` — retorna `['loose' => ($a == $b), 'strict' => ($a === $b), 'types' => [gettype($a), gettype($b)]]`. Demuestra type juggling.
2. `lateStaticBinding(): array` — Crea `ParentClass` con `static create(): static` y `getName(): string` retornando el nombre de la clase. Crea `ChildClass extends ParentClass`. Retorna `['parent' => ParentClass::create()->getName(), 'child' => ChildClass::create()->getName()]`.
3. `closureExample(): Closure` — retorna un closure que funciona como contador (cada llamada incrementa). Usa variable capturada por referencia (`use (&$count)`).
4. `diContainer(): array` — implementa mini DI: registra servicios, resuelve dependencias. Retorna `['registered' => N, 'resolved' => $instance]`.
MD,
            'starter_code' => <<<'PHP'
<?php

function compareValues(mixed $a, mixed $b): array {
    // Demuestra == vs ===
}

function lateStaticBinding(): array {
    // Crea clases y demuestra late static binding
}

function closureExample(): Closure {
    // Retorna closure contador
}

function diContainer(): array {
    // Mini dependency injection
}
PHP,
            'solution_code' => <<<'PHP'
<?php

function compareValues(mixed $a, mixed $b): array {
    return [
        'loose'  => ($a == $b),
        'strict' => ($a === $b),
        'types'  => [gettype($a), gettype($b)],
    ];
}

function lateStaticBinding(): array {
    $parent = new class {
        public static function create(): static {
            return new static();
        }
        public function getName(): string {
            return static::class;
        }
    };

    $parentClass = get_class($parent);
    $child = new class extends $parent {};

    return [
        'parent' => $parent::create()->getName(),
        'child'  => $child::create()->getName(),
    ];
}

function closureExample(): Closure {
    $count = 0;
    return function () use (&$count): int {
        return ++$count;
    };
}

function diContainer(): array {
    $services  = [];
    $instances = [];

    $register = function (string $name, callable $factory) use (&$services) {
        $services[$name] = $factory;
    };

    $resolve = function (string $name) use (&$services, &$instances) {
        if (!isset($instances[$name])) {
            $instances[$name] = ($services[$name])();
        }
        return $instances[$name];
    };

    $register('logger', fn() => new class {
        public function log(string $msg): string { return "LOG: {$msg}"; }
    });

    return [
        'registered' => count($services),
        'resolved'   => $resolve('logger'),
    ];
}
PHP,
        ];

        return $ex;
    }
}
