<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaravelExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'laravel-fullstack')->first();

        if (! $course) {
            $this->command->warn('Laravel course not found. Run CourseSeeder + LaravelLessonSeeder first.');
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

        $this->command->info('Laravel exercises seeded: ' . count($exercises) . ' exercises.');
    }

    private function exercises(\Illuminate\Support\Collection $lessons): array
    {
        $ex = [];

        // ── Lección 1: Instalación y Estructura ────────────────────
        if ($l = $lessons->get(1)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Explorar la estructura de un proyecto Laravel',
            'language'     => 'php',
            'description'  => <<<'MD'
Crea funciones que simulen la exploración de un proyecto Laravel.

```php
/**
 * Retorna un array asociativo con la estructura principal del proyecto.
 * Claves: 'app', 'routes', 'config', 'database', 'resources', 'public'
 * Valores: descripción breve de cada carpeta (string).
 */
function projectStructure(): array {
    // tu código
}

/**
 * Parsea un string de .env y retorna un array asociativo clave => valor.
 * Ignora líneas vacías y comentarios (que empiezan con #).
 * Ejemplo: "APP_NAME=SuperGuide\nDB_HOST=127.0.0.1" => ['APP_NAME' => 'SuperGuide', 'DB_HOST' => '127.0.0.1']
 */
function parseEnv(string $envContent): array {
    // tu código
}

/**
 * Simula el comando artisan: recibe un $command string y retorna un mensaje descriptivo.
 * Comandos soportados: 'serve', 'migrate', 'make:model', 'make:controller', 'tinker'
 * Si el comando no es soportado, retorna "Comando desconocido: {$command}"
 */
function artisanCommand(string $command): string {
    // tu código
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

function projectStructure(): array {
    // Retorna array con claves: app, routes, config, database, resources, public
}

function parseEnv(string $envContent): array {
    // Parsea contenido .env: ignora vacíos y comentarios (#)
}

function artisanCommand(string $command): string {
    // Simula artisan: serve, migrate, make:model, make:controller, tinker
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

function projectStructure(): array {
    return [
        'app'       => 'Contiene modelos, controladores, middleware y lógica de la aplicación',
        'routes'    => 'Define las rutas web, API y de consola',
        'config'    => 'Archivos de configuración de la aplicación',
        'database'  => 'Migraciones, seeders y factories',
        'resources' => 'Vistas Blade, assets CSS/JS y archivos de idioma',
        'public'    => 'Punto de entrada (index.php) y assets públicos',
    ];
}

function parseEnv(string $envContent): array {
    $result = [];
    $lines = explode("\n", $envContent);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $pos = strpos($line, '=');
        if ($pos !== false) {
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            $result[$key] = $value;
        }
    }
    return $result;
}

function artisanCommand(string $command): string {
    return match ($command) {
        'serve'           => 'Iniciando servidor de desarrollo en http://127.0.0.1:8000',
        'migrate'         => 'Ejecutando migraciones pendientes',
        'make:model'      => 'Creando nuevo modelo Eloquent',
        'make:controller' => 'Creando nuevo controlador',
        'tinker'          => 'Iniciando REPL interactivo de Laravel',
        default           => "Comando desconocido: {$command}",
    };
}
PHP,
        ];

        // ── Lección 2: Rutas y Controladores ──────────────────────
        if ($l = $lessons->get(2)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Simulador de Router de Laravel',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un mini-router que simule el sistema de rutas de Laravel.

```php
class MiniRouter {
    private array $routes = [];

    /**
     * Registra una ruta. $method: GET|POST|PUT|DELETE.
     * $uri puede contener parámetros: '/users/{id}' 
     * $action: callable o string 'Controller@method'
     */
    public function addRoute(string $method, string $uri, string|callable $action): void {}

    /**
     * Registra rutas CRUD automáticas para un recurso.
     * resource('posts', 'PostController') genera:
     *   GET /posts -> PostController@index
     *   POST /posts -> PostController@store
     *   GET /posts/{id} -> PostController@show
     *   PUT /posts/{id} -> PostController@update
     *   DELETE /posts/{id} -> PostController@destroy
     */
    public function resource(string $name, string $controller): void {}

    /**
     * Busca la ruta que coincide con $method y $uri.
     * Retorna ['action' => ..., 'params' => [...]] o null si no hay match.
     * Los parámetros de URI se extraen: '/users/42' matchea '/users/{id}' con params ['id' => '42']
     */
    public function resolve(string $method, string $uri): ?array {}

    /** Retorna todas las rutas registradas */
    public function getRoutes(): array {}
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class MiniRouter {
    private array $routes = [];

    public function addRoute(string $method, string $uri, string|callable $action): void {
        // Registra ruta con método, URI y acción
    }

    public function resource(string $name, string $controller): void {
        // Genera rutas CRUD: index, store, show, update, destroy
    }

    public function resolve(string $method, string $uri): ?array {
        // Busca match y extrae parámetros de URI
    }

    public function getRoutes(): array {
        return $this->routes;
    }
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class MiniRouter {
    private array $routes = [];

    public function addRoute(string $method, string $uri, string|callable $action): void {
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri'    => $uri,
            'action' => $action,
        ];
    }

    public function resource(string $name, string $controller): void {
        $this->addRoute('GET',    "/{$name}",       "{$controller}@index");
        $this->addRoute('POST',   "/{$name}",       "{$controller}@store");
        $this->addRoute('GET',    "/{$name}/{id}",   "{$controller}@show");
        $this->addRoute('PUT',    "/{$name}/{id}",   "{$controller}@update");
        $this->addRoute('DELETE', "/{$name}/{id}",   "{$controller}@destroy");
    }

    public function resolve(string $method, string $uri): ?array {
        $method = strtoupper($method);
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route['uri']);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, fn ($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
                return ['action' => $route['action'], 'params' => $params];
            }
        }
        return null;
    }

    public function getRoutes(): array {
        return $this->routes;
    }
}
PHP,
        ];

        // ── Lección 3: Vistas Blade ───────────────────────────────
        if ($l = $lessons->get(3)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini motor de plantillas Blade',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un motor de plantillas simplificado que procese directivas Blade.

```php
class MiniBladeEngine {
    /**
     * Procesa un template string reemplazando directivas Blade:
     * - {{ $var }} → htmlspecialchars del valor
     * - {!! $var !!} → valor sin escapar
     * - @if($cond) ... @else ... @endif
     * - @foreach($items as $item) ... @endforeach
     * $data es un array asociativo con las variables disponibles.
     */
    public function render(string $template, array $data = []): string {}

    /**
     * Procesa @extends('layout') y @section('name') ... @endsection.
     * $layouts es un array ['layout' => 'template string con @yield("name")'].
     * Retorna el layout con las secciones insertadas.
     */
    public function renderWithLayout(string $template, array $data, array $layouts): string {}
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class MiniBladeEngine {
    public function render(string $template, array $data = []): string {
        // Procesa {{ }}, {!! !!}, @if/@else/@endif, @foreach/@endforeach
    }

    public function renderWithLayout(string $template, array $data, array $layouts): string {
        // Procesa @extends y @section/@yield
    }
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class MiniBladeEngine {
    public function render(string $template, array $data = []): string {
        extract($data);
        // Escapar {{ $var }}
        $template = preg_replace_callback('/\{\{\s*\$(\w+)\s*\}\}/', function ($m) use ($data) {
            return htmlspecialchars((string)($data[$m[1]] ?? ''), ENT_QUOTES, 'UTF-8');
        }, $template);

        // Sin escapar {!! $var !!}
        $template = preg_replace_callback('/\{!!\s*\$(\w+)\s*!!\}/', function ($m) use ($data) {
            return (string)($data[$m[1]] ?? '');
        }, $template);

        // @foreach($items as $item) ... @endforeach
        $template = preg_replace_callback(
            '/@foreach\(\$(\w+)\s+as\s+\$(\w+)\)\s*(.*?)@endforeach/s',
            function ($m) use ($data) {
                $items = $data[$m[1]] ?? [];
                $body = $m[3];
                $itemVar = $m[2];
                $result = '';
                foreach ($items as $item) {
                    $line = preg_replace_callback('/\{\{\s*\$' . $itemVar . '\s*\}\}/', function () use ($item) {
                        return htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8');
                    }, $body);
                    $result .= $line;
                }
                return $result;
            },
            $template
        );

        // @if($cond) ... @else ... @endif
        $template = preg_replace_callback(
            '/@if\(\$(\w+)\)\s*(.*?)(?:@else\s*(.*?))?@endif/s',
            function ($m) use ($data) {
                $cond = !empty($data[$m[1]]);
                if ($cond) return trim($m[2]);
                return isset($m[3]) ? trim($m[3]) : '';
            },
            $template
        );

        return $template;
    }

    public function renderWithLayout(string $template, array $data, array $layouts): string {
        // Extraer @extends('layout')
        preg_match('/@extends\([\'"](\w+)[\'"]\)/', $template, $extMatch);
        $layoutName = $extMatch[1] ?? null;
        if (!$layoutName || !isset($layouts[$layoutName])) {
            return $this->render($template, $data);
        }
        // Extraer secciones
        preg_match_all('/@section\([\'"](\w+)[\'"]\)\s*(.*?)@endsection/s', $template, $sections);
        $layout = $layouts[$layoutName];
        foreach ($sections[1] as $i => $name) {
            $content = $this->render(trim($sections[2][$i]), $data);
            $layout = str_replace("@yield(\"{$name}\")", $content, $layout);
            $layout = str_replace("@yield('{$name}')", $content, $layout);
        }
        // Limpiar yields no usados
        $layout = preg_replace('/@yield\([\'"].*?[\'"]\)/', '', $layout);
        return $this->render($layout, $data);
    }
}
PHP,
        ];

        // ── Lección 4: Migraciones y Schema ──────────────────────
        if ($l = $lessons->get(4)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Schema Builder simulado',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un Schema Builder simplificado que genere sentencias SQL.

```php
class MiniSchema {
    private string $table;
    private array $columns = [];
    private array $indexes = [];
    private array $foreignKeys = [];

    public function __construct(string $table) {}

    /** Agrega columna: id(), string($name, $length), integer($name), text($name), boolean($name), timestamps() */
    public function id(): self {}
    public function string(string $name, int $length = 255): self {}
    public function integer(string $name): self {}
    public function text(string $name): self {}
    public function boolean(string $name): self {}
    public function timestamps(): self {}

    /** Agrega nullable() a la última columna */
    public function nullable(): self {}

    /** Agrega default($value) a la última columna */
    public function default(mixed $value): self {}

    /** Agrega un índice a la columna dada */
    public function index(string $column): self {}

    /** Agrega foreign key: foreign('user_id')->references('id')->on('users') */
    public function foreign(string $column): self {}
    public function references(string $column): self {}
    public function on(string $table): self {}

    /** Genera la sentencia CREATE TABLE como string */
    public function toSql(): string {}
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class MiniSchema {
    private string $table;
    private array $columns = [];
    private array $indexes = [];
    private array $foreignKeys = [];
    private ?array $pendingFk = null;

    public function __construct(string $table) {
        $this->table = $table;
    }

    public function id(): self { /* auto-increment PK */ }
    public function string(string $name, int $length = 255): self {}
    public function integer(string $name): self {}
    public function text(string $name): self {}
    public function boolean(string $name): self {}
    public function timestamps(): self {}
    public function nullable(): self {}
    public function default(mixed $value): self {}
    public function index(string $column): self {}
    public function foreign(string $column): self {}
    public function references(string $column): self {}
    public function on(string $table): self {}
    public function toSql(): string {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class MiniSchema {
    private string $table;
    private array $columns = [];
    private array $indexes = [];
    private array $foreignKeys = [];
    private ?array $pendingFk = null;

    public function __construct(string $table) {
        $this->table = $table;
    }

    public function id(): self {
        $this->columns[] = ['name' => 'id', 'type' => 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY', 'nullable' => false, 'default' => null];
        return $this;
    }

    public function string(string $name, int $length = 255): self {
        $this->columns[] = ['name' => $name, 'type' => "VARCHAR({$length})", 'nullable' => false, 'default' => null];
        return $this;
    }

    public function integer(string $name): self {
        $this->columns[] = ['name' => $name, 'type' => 'INT', 'nullable' => false, 'default' => null];
        return $this;
    }

    public function text(string $name): self {
        $this->columns[] = ['name' => $name, 'type' => 'TEXT', 'nullable' => false, 'default' => null];
        return $this;
    }

    public function boolean(string $name): self {
        $this->columns[] = ['name' => $name, 'type' => 'TINYINT(1)', 'nullable' => false, 'default' => null];
        return $this;
    }

    public function timestamps(): self {
        $this->columns[] = ['name' => 'created_at', 'type' => 'TIMESTAMP', 'nullable' => true, 'default' => null];
        $this->columns[] = ['name' => 'updated_at', 'type' => 'TIMESTAMP', 'nullable' => true, 'default' => null];
        return $this;
    }

    public function nullable(): self {
        if (!empty($this->columns)) {
            $this->columns[array_key_last($this->columns)]['nullable'] = true;
        }
        return $this;
    }

    public function default(mixed $value): self {
        if (!empty($this->columns)) {
            $this->columns[array_key_last($this->columns)]['default'] = $value;
        }
        return $this;
    }

    public function index(string $column): self {
        $this->indexes[] = $column;
        return $this;
    }

    public function foreign(string $column): self {
        $this->pendingFk = ['column' => $column];
        return $this;
    }

    public function references(string $column): self {
        if ($this->pendingFk) $this->pendingFk['references'] = $column;
        return $this;
    }

    public function on(string $table): self {
        if ($this->pendingFk) {
            $this->pendingFk['on'] = $table;
            $this->foreignKeys[] = $this->pendingFk;
            $this->pendingFk = null;
        }
        return $this;
    }

    public function toSql(): string {
        $parts = [];
        foreach ($this->columns as $col) {
            $line = "`{$col['name']}` {$col['type']}";
            if (!$col['nullable'] && !str_contains($col['type'], 'PRIMARY KEY')) $line .= ' NOT NULL';
            if ($col['default'] !== null) {
                $def = is_string($col['default']) ? "'{$col['default']}'" : $col['default'];
                $line .= " DEFAULT {$def}";
            }
            $parts[] = $line;
        }
        foreach ($this->indexes as $idx) {
            $parts[] = "INDEX (`{$idx}`)";
        }
        foreach ($this->foreignKeys as $fk) {
            $parts[] = "FOREIGN KEY (`{$fk['column']}`) REFERENCES `{$fk['on']}`(`{$fk['references']}`)";
        }
        return "CREATE TABLE `{$this->table}` (\n  " . implode(",\n  ", $parts) . "\n);";
    }
}
PHP,
        ];

        // ── Lección 5: Eloquent Fundamentos ───────────────────────
        if ($l = $lessons->get(5)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Mini-Eloquent: modelo con CRUD y scopes',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un modelo Eloquent simplificado con CRUD en memoria.

```php
class MiniModel {
    protected static array $records = [];
    protected static int $nextId = 1;
    protected array $attributes = [];
    protected array $fillable = [];
    protected array $casts = [];

    /**
     * Crea un nuevo registro con mass assignment (solo keys en $fillable).
     * Asigna id auto-incremental. Retorna instancia.
     */
    public static function create(array $data): static {}

    /** Busca por id. Retorna instancia o null. */
    public static function find(int $id): ?static {}

    /** Retorna todos los registros como array de instancias. */
    public static function all(): array {}

    /** Actualiza atributos (respetando $fillable). Retorna $this. */
    public function update(array $data): static {}

    /** Elimina el registro actual. Retorna true. */
    public function delete(): bool {}

    /** Scope: filtra registros con un callable. Retorna array de instancias. */
    public static function where(callable $filter): array {}

    /** Accessor mágico: __get aplica casts automáticos. */
    public function __get(string $name): mixed {}
}

class Post extends MiniModel {
    protected array $fillable = ['title', 'body', 'published'];
    protected array $casts = ['published' => 'bool'];
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class MiniModel {
    protected static array $records = [];
    protected static int $nextId = 1;
    protected array $attributes = [];
    protected array $fillable = [];
    protected array $casts = [];

    public static function create(array $data): static {}
    public static function find(int $id): ?static {}
    public static function all(): array {}
    public function update(array $data): static {}
    public function delete(): bool {}
    public static function where(callable $filter): array {}
    public function __get(string $name): mixed {}
}

class Post extends MiniModel {
    protected array $fillable = ['title', 'body', 'published'];
    protected array $casts = ['published' => 'bool'];
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class MiniModel {
    protected static array $records = [];
    protected static int $nextId = 1;
    protected array $attributes = [];
    protected array $fillable = [];
    protected array $casts = [];

    public static function create(array $data): static {
        $instance = new static();
        $filtered = array_intersect_key($data, array_flip($instance->fillable));
        $filtered['id'] = static::$nextId++;
        $instance->attributes = $filtered;
        static::$records[$filtered['id']] = $filtered;
        return $instance;
    }

    public static function find(int $id): ?static {
        if (!isset(static::$records[$id])) return null;
        $instance = new static();
        $instance->attributes = static::$records[$id];
        return $instance;
    }

    public static function all(): array {
        return array_map(function ($attrs) {
            $instance = new static();
            $instance->attributes = $attrs;
            return $instance;
        }, array_values(static::$records));
    }

    public function update(array $data): static {
        $filtered = array_intersect_key($data, array_flip($this->fillable));
        $this->attributes = array_merge($this->attributes, $filtered);
        static::$records[$this->attributes['id']] = $this->attributes;
        return $this;
    }

    public function delete(): bool {
        unset(static::$records[$this->attributes['id']]);
        return true;
    }

    public static function where(callable $filter): array {
        $results = [];
        foreach (static::$records as $attrs) {
            $instance = new static();
            $instance->attributes = $attrs;
            if ($filter($instance)) $results[] = $instance;
        }
        return $results;
    }

    public function __get(string $name): mixed {
        $value = $this->attributes[$name] ?? null;
        if (isset($this->casts[$name])) {
            return match ($this->casts[$name]) {
                'bool', 'boolean' => (bool) $value,
                'int', 'integer'  => (int) $value,
                'float', 'double' => (float) $value,
                'string'          => (string) $value,
                'array'           => is_string($value) ? json_decode($value, true) : (array) $value,
                default           => $value,
            };
        }
        return $value;
    }
}

class Post extends MiniModel {
    protected array $fillable = ['title', 'body', 'published'];
    protected array $casts = ['published' => 'bool'];
}
PHP,
        ];

        // ── Lección 6: Eloquent Relaciones ────────────────────────
        if ($l = $lessons->get(6)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Relaciones Eloquent simuladas',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa relaciones hasMany/belongsTo/belongsToMany simuladas en memoria.

```php
class RelationStore {
    private static array $tables = [];

    public static function insert(string $table, array $record): array {}
    public static function all(string $table): array {}
    public static function where(string $table, string $key, mixed $value): array {}
    public static function reset(): void {}
}

/**
 * hasMany: dado un user_id, retorna todos los posts con ese user_id.
 */
function hasMany(int $userId): array {}

/**
 * belongsTo: dado un post (array con 'user_id'), retorna el user correspondiente.
 */
function belongsTo(array $post): ?array {}

/**
 * belongsToMany: relación muchos-a-muchos con tabla pivote.
 * Dado un post_id, retorna los tags asociados vía 'post_tag' pivot table.
 */
function belongsToMany(int $postId): array {}

/**
 * eagerLoad: dado un array de posts, carga todos los users en UNA sola consulta
 * (evitando N+1) y retorna posts con 'user' incluido.
 */
function eagerLoad(array $posts): array {}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class RelationStore {
    private static array $tables = [];

    public static function insert(string $table, array $record): array {
        if (!isset(self::$tables[$table])) self::$tables[$table] = [];
        $record['id'] = count(self::$tables[$table]) + 1;
        self::$tables[$table][] = $record;
        return $record;
    }

    public static function all(string $table): array {
        return self::$tables[$table] ?? [];
    }

    public static function where(string $table, string $key, mixed $value): array {
        return array_values(array_filter(
            self::$tables[$table] ?? [],
            fn ($r) => ($r[$key] ?? null) === $value
        ));
    }

    public static function reset(): void { self::$tables = []; }
}

function hasMany(int $userId): array {}
function belongsTo(array $post): ?array {}
function belongsToMany(int $postId): array {}
function eagerLoad(array $posts): array {}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class RelationStore {
    private static array $tables = [];

    public static function insert(string $table, array $record): array {
        if (!isset(self::$tables[$table])) self::$tables[$table] = [];
        $record['id'] = count(self::$tables[$table]) + 1;
        self::$tables[$table][] = $record;
        return $record;
    }

    public static function all(string $table): array {
        return self::$tables[$table] ?? [];
    }

    public static function where(string $table, string $key, mixed $value): array {
        return array_values(array_filter(
            self::$tables[$table] ?? [],
            fn ($r) => ($r[$key] ?? null) === $value
        ));
    }

    public static function reset(): void { self::$tables = []; }
}

function hasMany(int $userId): array {
    return RelationStore::where('posts', 'user_id', $userId);
}

function belongsTo(array $post): ?array {
    $users = RelationStore::where('users', 'id', $post['user_id'] ?? null);
    return $users[0] ?? null;
}

function belongsToMany(int $postId): array {
    $pivots = RelationStore::where('post_tag', 'post_id', $postId);
    $tags = [];
    foreach ($pivots as $pivot) {
        $found = RelationStore::where('tags', 'id', $pivot['tag_id']);
        if (!empty($found)) $tags[] = $found[0];
    }
    return $tags;
}

function eagerLoad(array $posts): array {
    $userIds = array_unique(array_column($posts, 'user_id'));
    $allUsers = RelationStore::all('users');
    $userMap = [];
    foreach ($allUsers as $u) {
        if (in_array($u['id'], $userIds)) $userMap[$u['id']] = $u;
    }
    return array_map(function ($post) use ($userMap) {
        $post['user'] = $userMap[$post['user_id']] ?? null;
        return $post;
    }, $posts);
}
PHP,
        ];

        // ── Lección 7: Seeders y Factories ─────────────────────
        if ($l = $lessons->get(7)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Factory y Seeder simulados',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un sistema de factories y seeders simplificado.

```php
class MiniFactory {
    private string $model;
    private array $definition = [];
    private array $states = [];
    private int $count = 1;

    public function __construct(string $model, array $definition) {}

    /** Define un state override. Ej: ->state('published', ['published' => true]) */
    public function state(string $name, array $overrides): self {}

    /** Establece cuántas instancias generar */
    public function count(int $n): self {}

    /** Aplica un state registrado */
    public function applyState(string $name): self {}

    /**
     * Genera las instancias como array de arrays.
     * Cada instancia combina definition + states aplicados + id auto-incremental.
     * Los valores callable en definition se ejecutan para generar datos dinámicos.
     */
    public function make(): array {}

    /**
     * Genera e inserta (simula un create). Igual que make pero agrega 'saved' => true.
     */
    public function create(): array {}
}

/**
 * Fake helpers: retorna datos falsos.
 */
function fakeName(): string {}       // Retorna un nombre random de un array predefinido
function fakeEmail(): string {}      // Retorna email basado en nombre
function fakeSentence(): string {}   // Retorna una oración random
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class MiniFactory {
    private string $model;
    private array $definition = [];
    private array $states = [];
    private array $appliedStates = [];
    private int $count = 1;
    private static int $nextId = 1;

    public function __construct(string $model, array $definition) {
        $this->model = $model;
        $this->definition = $definition;
    }

    public function state(string $name, array $overrides): self {}
    public function count(int $n): self {}
    public function applyState(string $name): self {}
    public function make(): array {}
    public function create(): array {}
}

function fakeName(): string {}
function fakeEmail(): string {}
function fakeSentence(): string {}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class MiniFactory {
    private string $model;
    private array $definition = [];
    private array $states = [];
    private array $appliedStates = [];
    private int $count = 1;
    private static int $nextId = 1;

    public function __construct(string $model, array $definition) {
        $this->model = $model;
        $this->definition = $definition;
    }

    public function state(string $name, array $overrides): self {
        $this->states[$name] = $overrides;
        return $this;
    }

    public function count(int $n): self {
        $this->count = $n;
        return $this;
    }

    public function applyState(string $name): self {
        if (isset($this->states[$name])) {
            $this->appliedStates[] = $name;
        }
        return $this;
    }

    public function make(): array {
        $results = [];
        for ($i = 0; $i < $this->count; $i++) {
            $record = ['id' => self::$nextId++];
            foreach ($this->definition as $key => $value) {
                $record[$key] = is_callable($value) ? $value() : $value;
            }
            foreach ($this->appliedStates as $state) {
                $record = array_merge($record, $this->states[$state]);
            }
            $results[] = $record;
        }
        return $results;
    }

    public function create(): array {
        return array_map(fn ($r) => array_merge($r, ['saved' => true]), $this->make());
    }
}

function fakeName(): string {
    $names = ['Ana García', 'Carlos López', 'María Torres', 'Pedro Ruiz', 'Laura Sánchez'];
    return $names[array_rand($names)];
}

function fakeEmail(): string {
    $name = strtolower(str_replace(' ', '.', fakeName()));
    return $name . '@example.com';
}

function fakeSentence(): string {
    $sentences = [
        'Lorem ipsum dolor sit amet.',
        'El desarrollo web evoluciona constantemente.',
        'Laravel es un framework elegante.',
        'PHP sigue siendo relevante en 2025.',
        'La práctica hace al maestro.',
    ];
    return $sentences[array_rand($sentences)];
}
PHP,
        ];

        // ── Lección 8: Validación ─────────────────────────────────
        if ($l = $lessons->get(8)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Validador de datos estilo Laravel',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un validador de datos inspirado en las reglas de Laravel.

```php
class MiniValidator {
    private array $data;
    private array $rules;
    private array $messages;
    private array $errors = [];

    public function __construct(array $data, array $rules, array $messages = []) {}

    /**
     * Ejecuta la validación. Retorna true si todo es válido.
     * Reglas soportadas (separadas por |):
     *   required, email, min:N, max:N, between:min,max,
     *   numeric, string, in:val1,val2,val3, confirmed (busca {field}_confirmation)
     */
    public function validate(): bool {}

    /** Retorna errores: ['field' => ['msg1', 'msg2']] */
    public function errors(): array {}

    /** Retorna solo los datos validados (fields que tienen regla y pasaron) */
    public function validated(): array {}
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class MiniValidator {
    private array $data;
    private array $rules;
    private array $messages;
    private array $errors = [];

    public function __construct(array $data, array $rules, array $messages = []) {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    public function validate(): bool {}
    public function errors(): array {}
    public function validated(): array {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class MiniValidator {
    private array $data;
    private array $rules;
    private array $messages;
    private array $errors = [];

    public function __construct(array $data, array $rules, array $messages = []) {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    public function validate(): bool {
        $this->errors = [];
        foreach ($this->rules as $field => $ruleStr) {
            $rules = explode('|', $ruleStr);
            $value = $this->data[$field] ?? null;
            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                $error = $this->checkRule($field, $value, $rule, $params);
                if ($error) {
                    $key = "{$field}.{$rule}";
                    $this->errors[$field][] = $this->messages[$key] ?? $error;
                }
            }
        }
        return empty($this->errors);
    }

    private function checkRule(string $field, mixed $value, string $rule, array $params): ?string {
        return match ($rule) {
            'required'  => (is_null($value) || $value === '') ? "El campo {$field} es obligatorio." : null,
            'email'     => ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) ? "El campo {$field} debe ser un email válido." : null,
            'numeric'   => ($value && !is_numeric($value)) ? "El campo {$field} debe ser numérico." : null,
            'string'    => ($value && !is_string($value)) ? "El campo {$field} debe ser texto." : null,
            'min'       => ($value !== null && strlen((string)$value) < (int)$params[0]) ? "El campo {$field} debe tener al menos {$params[0]} caracteres." : null,
            'max'       => ($value !== null && strlen((string)$value) > (int)$params[0]) ? "El campo {$field} no debe exceder {$params[0]} caracteres." : null,
            'between'   => ($value !== null && (strlen((string)$value) < (int)$params[0] || strlen((string)$value) > (int)$params[1])) ? "El campo {$field} debe tener entre {$params[0]} y {$params[1]} caracteres." : null,
            'in'        => ($value && !in_array((string)$value, $params)) ? "El campo {$field} debe ser uno de: " . implode(', ', $params) . "." : null,
            'confirmed' => ($value !== ($this->data["{$field}_confirmation"] ?? null)) ? "La confirmación de {$field} no coincide." : null,
            default     => null,
        };
    }

    public function errors(): array {
        return $this->errors;
    }

    public function validated(): array {
        $valid = [];
        foreach ($this->rules as $field => $rules) {
            if (!isset($this->errors[$field]) && array_key_exists($field, $this->data)) {
                $valid[$field] = $this->data[$field];
            }
        }
        return $valid;
    }
}
PHP,
        ];

        // ── Lección 9: Middleware ─────────────────────────────────
        if ($l = $lessons->get(9)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Pipeline de Middleware',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un pipeline de middleware inspirado en Laravel.

```php
class Request {
    public array $headers = [];
    public array $data = [];
    public string $method = 'GET';
    public string $path = '/';
    public ?string $user = null;
}

class Response {
    public int $status;
    public string $body;
    public array $headers = [];

    public function __construct(int $status = 200, string $body = '') {}
}

interface Middleware {
    public function handle(Request $request, callable $next): Response;
}

class MiddlewarePipeline {
    private array $middlewares = [];

    /** Agrega un middleware al pipeline */
    public function pipe(Middleware $middleware): self {}

    /**
     * Ejecuta el pipeline: cada middleware llama a $next($request), 
     * el último middleware llama al $destination callback.
     */
    public function process(Request $request, callable $destination): Response {}
}

/** Middleware de autenticación: si $request->user es null, retorna 401 sin llamar $next */
class AuthMiddleware implements Middleware {}

/** Middleware CORS: agrega headers Access-Control-Allow-* a la respuesta */
class CorsMiddleware implements Middleware {}

/** Middleware de logging: agrega 'X-Request-Time' header con microtime */
class LogMiddleware implements Middleware {}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class Request {
    public array $headers = [];
    public array $data = [];
    public string $method = 'GET';
    public string $path = '/';
    public ?string $user = null;
}

class Response {
    public int $status;
    public string $body;
    public array $headers = [];

    public function __construct(int $status = 200, string $body = '') {
        $this->status = $status;
        $this->body = $body;
    }
}

interface Middleware {
    public function handle(Request $request, callable $next): Response;
}

class MiddlewarePipeline {
    private array $middlewares = [];

    public function pipe(Middleware $middleware): self {}
    public function process(Request $request, callable $destination): Response {}
}

class AuthMiddleware implements Middleware {
    public function handle(Request $request, callable $next): Response {}
}

class CorsMiddleware implements Middleware {
    public function handle(Request $request, callable $next): Response {}
}

class LogMiddleware implements Middleware {
    public function handle(Request $request, callable $next): Response {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class Request {
    public array $headers = [];
    public array $data = [];
    public string $method = 'GET';
    public string $path = '/';
    public ?string $user = null;
}

class Response {
    public int $status;
    public string $body;
    public array $headers = [];

    public function __construct(int $status = 200, string $body = '') {
        $this->status = $status;
        $this->body = $body;
    }
}

interface Middleware {
    public function handle(Request $request, callable $next): Response;
}

class MiddlewarePipeline {
    private array $middlewares = [];

    public function pipe(Middleware $middleware): self {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function process(Request $request, callable $destination): Response {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            fn (callable $next, Middleware $mw) => fn (Request $req) => $mw->handle($req, $next),
            $destination
        );
        return $pipeline($request);
    }
}

class AuthMiddleware implements Middleware {
    public function handle(Request $request, callable $next): Response {
        if ($request->user === null) {
            return new Response(401, 'Unauthorized');
        }
        return $next($request);
    }
}

class CorsMiddleware implements Middleware {
    public function handle(Request $request, callable $next): Response {
        $response = $next($request);
        $response->headers['Access-Control-Allow-Origin'] = '*';
        $response->headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE';
        $response->headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization';
        return $response;
    }
}

class LogMiddleware implements Middleware {
    public function handle(Request $request, callable $next): Response {
        $start = microtime(true);
        $response = $next($request);
        $response->headers['X-Request-Time'] = (string)(microtime(true) - $start);
        return $response;
    }
}
PHP,
        ];

        // ── Lección 10: Autenticación ─────────────────────────────
        if ($l = $lessons->get(10)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de autenticación',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un sistema de autenticación simplificado.

```php
class AuthManager {
    private array $users = [];
    private ?array $currentUser = null;
    private array $tokens = [];

    /**
     * Registra un usuario: hashea la contraseña, guarda name/email/password_hash.
     * Retorna el user creado (sin password_hash). Falla si email ya existe.
     */
    public function register(string $name, string $email, string $password): array {}

    /**
     * Login con email y password. Si es válido, genera un token aleatorio,
     * establece currentUser. Retorna ['user' => ..., 'token' => ...] o null.
     */
    public function login(string $email, string $password): ?array {}

    /** Logout: limpia el currentUser y elimina su token. */
    public function logout(): void {}

    /** Retorna el usuario autenticado actual o null. */
    public function user(): ?array {}

    /** Verifica si el token es válido. Retorna el user asociado o null. */
    public function validateToken(string $token): ?array {}

    /** Genera un token para "remember me" con expiración (timestamp). */
    public function rememberToken(string $email, int $ttlSeconds = 86400): ?string {}

    /** Genera un token de reset de password. */
    public function passwordResetToken(string $email): ?string {}

    /** Resetea password usando el token. */
    public function resetPassword(string $token, string $newPassword): bool {}
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class AuthManager {
    private array $users = [];
    private ?array $currentUser = null;
    private array $tokens = [];
    private array $resetTokens = [];

    public function register(string $name, string $email, string $password): array {}
    public function login(string $email, string $password): ?array {}
    public function logout(): void {}
    public function user(): ?array {}
    public function validateToken(string $token): ?array {}
    public function rememberToken(string $email, int $ttlSeconds = 86400): ?string {}
    public function passwordResetToken(string $email): ?string {}
    public function resetPassword(string $token, string $newPassword): bool {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class AuthManager {
    private array $users = [];
    private ?array $currentUser = null;
    private array $tokens = [];
    private array $resetTokens = [];

    public function register(string $name, string $email, string $password): array {
        foreach ($this->users as $u) {
            if ($u['email'] === $email) throw new \RuntimeException("Email ya registrado: {$email}");
        }
        $user = [
            'id'            => count($this->users) + 1,
            'name'          => $name,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ];
        $this->users[] = $user;
        return ['id' => $user['id'], 'name' => $name, 'email' => $email];
    }

    public function login(string $email, string $password): ?array {
        foreach ($this->users as $u) {
            if ($u['email'] === $email && password_verify($password, $u['password_hash'])) {
                $token = bin2hex(random_bytes(32));
                $public = ['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email']];
                $this->tokens[$token] = ['user' => $public, 'expires' => null];
                $this->currentUser = $public;
                return ['user' => $public, 'token' => $token];
            }
        }
        return null;
    }

    public function logout(): void {
        if ($this->currentUser) {
            $this->tokens = array_filter($this->tokens, fn ($t) => $t['user']['id'] !== $this->currentUser['id']);
            $this->currentUser = null;
        }
    }

    public function user(): ?array {
        return $this->currentUser;
    }

    public function validateToken(string $token): ?array {
        if (!isset($this->tokens[$token])) return null;
        $entry = $this->tokens[$token];
        if ($entry['expires'] !== null && time() > $entry['expires']) {
            unset($this->tokens[$token]);
            return null;
        }
        return $entry['user'];
    }

    public function rememberToken(string $email, int $ttlSeconds = 86400): ?string {
        foreach ($this->users as $u) {
            if ($u['email'] === $email) {
                $token = bin2hex(random_bytes(32));
                $public = ['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email']];
                $this->tokens[$token] = ['user' => $public, 'expires' => time() + $ttlSeconds];
                return $token;
            }
        }
        return null;
    }

    public function passwordResetToken(string $email): ?string {
        foreach ($this->users as $u) {
            if ($u['email'] === $email) {
                $token = bin2hex(random_bytes(16));
                $this->resetTokens[$token] = $u['email'];
                return $token;
            }
        }
        return null;
    }

    public function resetPassword(string $token, string $newPassword): bool {
        if (!isset($this->resetTokens[$token])) return false;
        $email = $this->resetTokens[$token];
        foreach ($this->users as &$u) {
            if ($u['email'] === $email) {
                $u['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
                unset($this->resetTokens[$token]);
                return true;
            }
        }
        return false;
    }
}
PHP,
        ];

        // ── Lección 11: Autorización Gates y Policies ─────────────
        if ($l = $lessons->get(11)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Gates y Policies',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un sistema de autorización con Gates y Policies.

```php
class Gate {
    private static array $abilities = [];
    private static array $policies = [];

    /** Define un gate: Gate::define('edit-post', fn($user, $post) => $user['id'] === $post['user_id']) */
    public static function define(string $ability, callable $callback): void {}

    /** Verifica si el usuario puede realizar la acción */
    public static function allows(string $ability, array $user, mixed ...$args): bool {}

    /** Opuesto de allows */
    public static function denies(string $ability, array $user, mixed ...$args): bool {}

    /** Registra una policy para un tipo de modelo */
    public static function policy(string $model, string $policyClass): void {}

    /** Resuelve la policy para el modelo dado */
    public static function resolvePolicy(string $model): ?object {}

    public static function reset(): void {}
}

class PostPolicy {
    public function view(array $user, array $post): bool {}
    public function update(array $user, array $post): bool {}
    public function delete(array $user, array $post): bool {}
    public function create(array $user): bool {}
}

/**
 * authorize: lanza RuntimeException si Gate::denies. Retorna true si pasa.
 */
function authorize(string $ability, array $user, mixed ...$args): bool {}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class Gate {
    private static array $abilities = [];
    private static array $policies = [];

    public static function define(string $ability, callable $callback): void {}
    public static function allows(string $ability, array $user, mixed ...$args): bool {}
    public static function denies(string $ability, array $user, mixed ...$args): bool {}
    public static function policy(string $model, string $policyClass): void {}
    public static function resolvePolicy(string $model): ?object {}
    public static function reset(): void { self::$abilities = []; self::$policies = []; }
}

class PostPolicy {
    public function view(array $user, array $post): bool {}
    public function update(array $user, array $post): bool {}
    public function delete(array $user, array $post): bool {}
    public function create(array $user): bool {}
}

function authorize(string $ability, array $user, mixed ...$args): bool {}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class Gate {
    private static array $abilities = [];
    private static array $policies = [];

    public static function define(string $ability, callable $callback): void {
        self::$abilities[$ability] = $callback;
    }

    public static function allows(string $ability, array $user, mixed ...$args): bool {
        if (isset(self::$abilities[$ability])) {
            return (bool)(self::$abilities[$ability])($user, ...$args);
        }
        return false;
    }

    public static function denies(string $ability, array $user, mixed ...$args): bool {
        return !self::allows($ability, $user, ...$args);
    }

    public static function policy(string $model, string $policyClass): void {
        self::$policies[$model] = $policyClass;
    }

    public static function resolvePolicy(string $model): ?object {
        if (!isset(self::$policies[$model])) return null;
        $class = self::$policies[$model];
        return new $class();
    }

    public static function reset(): void {
        self::$abilities = [];
        self::$policies = [];
    }
}

class PostPolicy {
    public function view(array $user, array $post): bool {
        return true; // todos pueden ver
    }

    public function update(array $user, array $post): bool {
        return $user['id'] === $post['user_id'];
    }

    public function delete(array $user, array $post): bool {
        return $user['id'] === $post['user_id'] || ($user['role'] ?? '') === 'admin';
    }

    public function create(array $user): bool {
        return !empty($user['id']);
    }
}

function authorize(string $ability, array $user, mixed ...$args): bool {
    if (Gate::denies($ability, $user, ...$args)) {
        throw new \RuntimeException("No autorizado para: {$ability}");
    }
    return true;
}
PHP,
        ];

        // ── Lección 12: APIs RESTful y Resources ──────────────────
        if ($l = $lessons->get(12)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'API Resource y colecciones',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un sistema de API Resources para transformar datos.

```php
class JsonResource {
    protected array $resource;

    public function __construct(array $resource) {}

    /**
     * Transforma el recurso. Subclases sobrescriben este método.
     * Retorna array con los campos deseados para la API.
     */
    public function toArray(): array {}

    /**
     * Incluye un campo condicionalmente.
     * when($condition, $value): retorna $value si $condition es true, sino omite.
     */
    protected function when(bool $condition, mixed $value): mixed {}

    /**
     * whenLoaded: incluye relación solo si existe en el recurso.
     */
    protected function whenLoaded(string $relation): mixed {}

    /** Envuelve la respuesta: ['data' => toArray()] */
    public function toResponse(): array {}
}

class ResourceCollection {
    private array $items;
    private string $resourceClass;

    public function __construct(array $items, string $resourceClass) {}

    /** Transforma la colección con paginación simulada */
    public function toResponse(int $page = 1, int $perPage = 15): array {}
}

class UserResource extends JsonResource {
    public function toArray(): array {}  // id, name, email, whenLoaded('posts')
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class JsonResource {
    protected array $resource;

    public function __construct(array $resource) {
        $this->resource = $resource;
    }

    public function toArray(): array { return $this->resource; }
    protected function when(bool $condition, mixed $value): mixed {}
    protected function whenLoaded(string $relation): mixed {}
    public function toResponse(): array {}
}

class ResourceCollection {
    private array $items;
    private string $resourceClass;

    public function __construct(array $items, string $resourceClass) {
        $this->items = $items;
        $this->resourceClass = $resourceClass;
    }

    public function toResponse(int $page = 1, int $perPage = 15): array {}
}

class UserResource extends JsonResource {
    public function toArray(): array {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class JsonResource {
    protected array $resource;
    private const OMIT = '__OMIT__';

    public function __construct(array $resource) {
        $this->resource = $resource;
    }

    public function toArray(): array {
        return $this->resource;
    }

    protected function when(bool $condition, mixed $value): mixed {
        return $condition ? $value : self::OMIT;
    }

    protected function whenLoaded(string $relation): mixed {
        if (!array_key_exists($relation, $this->resource)) return self::OMIT;
        return $this->resource[$relation];
    }

    public function toResponse(): array {
        $data = $this->toArray();
        $data = array_filter($data, fn ($v) => $v !== self::OMIT);
        return ['data' => $data];
    }
}

class ResourceCollection {
    private array $items;
    private string $resourceClass;

    public function __construct(array $items, string $resourceClass) {
        $this->items = $items;
        $this->resourceClass = $resourceClass;
    }

    public function toResponse(int $page = 1, int $perPage = 15): array {
        $total = count($this->items);
        $lastPage = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $lastPage));
        $offset = ($page - 1) * $perPage;
        $slice = array_slice($this->items, $offset, $perPage);

        $class = $this->resourceClass;
        $data = array_map(fn ($item) => (new $class($item))->toArray(), $slice);
        $data = array_map(fn ($d) => array_filter($d, fn ($v) => $v !== '__OMIT__'), $data);

        return [
            'data' => array_values($data),
            'meta' => [
                'current_page' => $page,
                'last_page'    => $lastPage,
                'per_page'     => $perPage,
                'total'        => $total,
            ],
        ];
    }
}

class UserResource extends JsonResource {
    public function toArray(): array {
        return [
            'id'    => $this->resource['id'],
            'name'  => $this->resource['name'],
            'email' => $this->resource['email'],
            'posts' => $this->whenLoaded('posts'),
        ];
    }
}
PHP,
        ];

        // ── Lección 13: Colas y Jobs ──────────────────────────────
        if ($l = $lessons->get(13)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de colas y jobs',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un sistema de colas y jobs simplificado.

```php
interface ShouldQueue {
    public function handle(): mixed;
}

class MiniJob implements ShouldQueue {
    public string $name;
    public array $data;
    public int $tries;
    public int $attempts = 0;
    public ?string $error = null;

    public function __construct(string $name, array $data = [], int $tries = 3) {}
    public function handle(): mixed {}
}

class JobQueue {
    private array $queues = ['default' => []];
    private array $failed = [];

    /** dispatch: agrega un job a la cola especificada */
    public function dispatch(ShouldQueue $job, string $queue = 'default'): void {}

    /** Procesa el siguiente job de la cola. Retorna resultado o maneja fallo. */
    public function processNext(string $queue = 'default'): mixed {}

    /** Procesa todos los jobs de la cola */
    public function work(string $queue = 'default'): array {}

    /** chain: ejecuta jobs en secuencia, se detiene si uno falla */
    public function chain(array $jobs): array {}

    /** Retorna jobs fallidos */
    public function failed(): array {}

    /** Retry: reintenta un job fallido */
    public function retry(int $index): mixed {}

    /** Retorna el tamaño de la cola */
    public function size(string $queue = 'default'): int {}
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

interface ShouldQueue {
    public function handle(): mixed;
}

class MiniJob implements ShouldQueue {
    public string $name;
    public array $data;
    public int $tries;
    public int $attempts = 0;
    public ?string $error = null;

    public function __construct(string $name, array $data = [], int $tries = 3) {
        $this->name = $name;
        $this->data = $data;
        $this->tries = $tries;
    }

    public function handle(): mixed {
        return "Processed: {$this->name}";
    }
}

class JobQueue {
    private array $queues = ['default' => []];
    private array $failed = [];

    public function dispatch(ShouldQueue $job, string $queue = 'default'): void {}
    public function processNext(string $queue = 'default'): mixed {}
    public function work(string $queue = 'default'): array {}
    public function chain(array $jobs): array {}
    public function failed(): array {}
    public function retry(int $index): mixed {}
    public function size(string $queue = 'default'): int {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

interface ShouldQueue {
    public function handle(): mixed;
}

class MiniJob implements ShouldQueue {
    public string $name;
    public array $data;
    public int $tries;
    public int $attempts = 0;
    public ?string $error = null;

    public function __construct(string $name, array $data = [], int $tries = 3) {
        $this->name = $name;
        $this->data = $data;
        $this->tries = $tries;
    }

    public function handle(): mixed {
        return "Processed: {$this->name}";
    }
}

class JobQueue {
    private array $queues = ['default' => []];
    private array $failed = [];

    public function dispatch(ShouldQueue $job, string $queue = 'default'): void {
        if (!isset($this->queues[$queue])) $this->queues[$queue] = [];
        $this->queues[$queue][] = $job;
    }

    public function processNext(string $queue = 'default'): mixed {
        if (empty($this->queues[$queue])) return null;
        $job = array_shift($this->queues[$queue]);
        $job->attempts++;
        try {
            return $job->handle();
        } catch (\Throwable $e) {
            $job->error = $e->getMessage();
            if ($job->attempts < $job->tries) {
                $this->queues[$queue][] = $job; // re-queue
                return $this->processNext($queue);
            }
            $this->failed[] = $job;
            return null;
        }
    }

    public function work(string $queue = 'default'): array {
        $results = [];
        while (!empty($this->queues[$queue])) {
            $results[] = $this->processNext($queue);
        }
        return $results;
    }

    public function chain(array $jobs): array {
        $results = [];
        foreach ($jobs as $job) {
            $job->attempts++;
            try {
                $results[] = $job->handle();
            } catch (\Throwable $e) {
                $job->error = $e->getMessage();
                $this->failed[] = $job;
                break; // cadena se interrumpe
            }
        }
        return $results;
    }

    public function failed(): array {
        return $this->failed;
    }

    public function retry(int $index): mixed {
        if (!isset($this->failed[$index])) return null;
        $job = $this->failed[$index];
        array_splice($this->failed, $index, 1);
        $job->error = null;
        $job->attempts = 0;
        $job->attempts++;
        try {
            return $job->handle();
        } catch (\Throwable $e) {
            $job->error = $e->getMessage();
            $this->failed[] = $job;
            return null;
        }
    }

    public function size(string $queue = 'default'): int {
        return count($this->queues[$queue] ?? []);
    }
}
PHP,
        ];

        // ── Lección 14: Eventos y Listeners ───────────────────────
        if ($l = $lessons->get(14)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Event Dispatcher con listeners',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un dispatcher de eventos con listeners y subscribers.

```php
class EventDispatcher {
    private array $listeners = [];
    private array $log = [];

    /** Registra un listener para un evento */
    public function listen(string $event, callable $listener): void {}

    /**
     * Registra un subscriber: un objeto con método subscribe()
     * que recibe el dispatcher y registra sus listeners.
     */
    public function subscribe(object $subscriber): void {}

    /** Despacha un evento: ejecuta todos los listeners registrados en orden. */
    public function dispatch(string $event, array $payload = []): array {}

    /** Retorna true si hay listeners para el evento */
    public function hasListeners(string $event): bool {}

    /** Retorna el log de eventos despachados */
    public function getLog(): array {}

    /** Limpia todos los listeners */
    public function flush(): void {}
}

class UserEventSubscriber {
    public function subscribe(EventDispatcher $events): void {
        // Registra listeners para user.created, user.updated, user.deleted
    }

    public function onCreated(array $payload): string {}
    public function onUpdated(array $payload): string {}
    public function onDeleted(array $payload): string {}
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class EventDispatcher {
    private array $listeners = [];
    private array $log = [];

    public function listen(string $event, callable $listener): void {}
    public function subscribe(object $subscriber): void {}
    public function dispatch(string $event, array $payload = []): array {}
    public function hasListeners(string $event): bool {}
    public function getLog(): array {}
    public function flush(): void {}
}

class UserEventSubscriber {
    public function subscribe(EventDispatcher $events): void {}
    public function onCreated(array $payload): string {}
    public function onUpdated(array $payload): string {}
    public function onDeleted(array $payload): string {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class EventDispatcher {
    private array $listeners = [];
    private array $log = [];

    public function listen(string $event, callable $listener): void {
        $this->listeners[$event][] = $listener;
    }

    public function subscribe(object $subscriber): void {
        $subscriber->subscribe($this);
    }

    public function dispatch(string $event, array $payload = []): array {
        $this->log[] = ['event' => $event, 'payload' => $payload];
        $results = [];
        foreach ($this->listeners[$event] ?? [] as $listener) {
            $results[] = $listener($payload);
        }
        return $results;
    }

    public function hasListeners(string $event): bool {
        return !empty($this->listeners[$event]);
    }

    public function getLog(): array {
        return $this->log;
    }

    public function flush(): void {
        $this->listeners = [];
        $this->log = [];
    }
}

class UserEventSubscriber {
    public function subscribe(EventDispatcher $events): void {
        $events->listen('user.created', [$this, 'onCreated']);
        $events->listen('user.updated', [$this, 'onUpdated']);
        $events->listen('user.deleted', [$this, 'onDeleted']);
    }

    public function onCreated(array $payload): string {
        return "Usuario creado: " . ($payload['name'] ?? 'unknown');
    }

    public function onUpdated(array $payload): string {
        return "Usuario actualizado: " . ($payload['name'] ?? 'unknown');
    }

    public function onDeleted(array $payload): string {
        return "Usuario eliminado: " . ($payload['email'] ?? 'unknown');
    }
}
PHP,
        ];

        // ── Lección 15: Notificaciones y Mail ─────────────────────
        if ($l = $lessons->get(15)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Sistema de notificaciones multicanal',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un sistema de notificaciones con múltiples canales.

```php
interface NotificationChannel {
    public function send(array $notifiable, Notification $notification): array;
}

abstract class Notification {
    /** Canales por los que se envía: ['mail', 'database', 'broadcast'] */
    abstract public function via(): array;

    /** Representación para mail */
    public function toMail(array $notifiable): array { return []; }

    /** Representación para database */
    public function toDatabase(array $notifiable): array { return []; }

    /** Representación para broadcast */
    public function toBroadcast(array $notifiable): array { return []; }
}

class MailChannel implements NotificationChannel {}
class DatabaseChannel implements NotificationChannel {}

class NotificationManager {
    private array $channels = [];
    private array $sent = [];

    public function registerChannel(string $name, NotificationChannel $channel): void {}

    /** Envía una notificación al notifiable a través de todos los canales via() */
    public function send(array $notifiable, Notification $notification): array {}

    /** Envía a múltiples notifiables */
    public function sendToMany(array $notifiables, Notification $notification): array {}

    /** Retorna todas las notificaciones enviadas */
    public function sent(): array {}
}

class WelcomeNotification extends Notification {
    public function via(): array { return ['mail', 'database']; }
    public function toMail(array $notifiable): array {}
    public function toDatabase(array $notifiable): array {}
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

interface NotificationChannel {
    public function send(array $notifiable, Notification $notification): array;
}

abstract class Notification {
    abstract public function via(): array;
    public function toMail(array $notifiable): array { return []; }
    public function toDatabase(array $notifiable): array { return []; }
    public function toBroadcast(array $notifiable): array { return []; }
}

class MailChannel implements NotificationChannel {
    public function send(array $notifiable, Notification $notification): array {}
}

class DatabaseChannel implements NotificationChannel {
    public function send(array $notifiable, Notification $notification): array {}
}

class NotificationManager {
    private array $channels = [];
    private array $sent = [];

    public function registerChannel(string $name, NotificationChannel $channel): void {}
    public function send(array $notifiable, Notification $notification): array {}
    public function sendToMany(array $notifiables, Notification $notification): array {}
    public function sent(): array {}
}

class WelcomeNotification extends Notification {
    public function via(): array { return ['mail', 'database']; }
    public function toMail(array $notifiable): array {}
    public function toDatabase(array $notifiable): array {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

interface NotificationChannel {
    public function send(array $notifiable, Notification $notification): array;
}

abstract class Notification {
    abstract public function via(): array;
    public function toMail(array $notifiable): array { return []; }
    public function toDatabase(array $notifiable): array { return []; }
    public function toBroadcast(array $notifiable): array { return []; }
}

class MailChannel implements NotificationChannel {
    public function send(array $notifiable, Notification $notification): array {
        $data = $notification->toMail($notifiable);
        return ['channel' => 'mail', 'to' => $notifiable['email'] ?? '', 'data' => $data];
    }
}

class DatabaseChannel implements NotificationChannel {
    public function send(array $notifiable, Notification $notification): array {
        $data = $notification->toDatabase($notifiable);
        return ['channel' => 'database', 'notifiable_id' => $notifiable['id'] ?? 0, 'data' => $data, 'read' => false];
    }
}

class NotificationManager {
    private array $channels = [];
    private array $sent = [];

    public function registerChannel(string $name, NotificationChannel $channel): void {
        $this->channels[$name] = $channel;
    }

    public function send(array $notifiable, Notification $notification): array {
        $results = [];
        foreach ($notification->via() as $channelName) {
            if (isset($this->channels[$channelName])) {
                $result = $this->channels[$channelName]->send($notifiable, $notification);
                $results[] = $result;
                $this->sent[] = $result;
            }
        }
        return $results;
    }

    public function sendToMany(array $notifiables, Notification $notification): array {
        $results = [];
        foreach ($notifiables as $notifiable) {
            $results[] = $this->send($notifiable, $notification);
        }
        return $results;
    }

    public function sent(): array {
        return $this->sent;
    }
}

class WelcomeNotification extends Notification {
    public function via(): array {
        return ['mail', 'database'];
    }

    public function toMail(array $notifiable): array {
        return [
            'subject' => 'Bienvenido a SuperGuide',
            'greeting' => "Hola {$notifiable['name']}!",
            'body' => 'Gracias por registrarte en nuestra plataforma.',
            'action' => ['text' => 'Comenzar', 'url' => '/dashboard'],
        ];
    }

    public function toDatabase(array $notifiable): array {
        return [
            'type' => 'welcome',
            'message' => "Bienvenido {$notifiable['name']} a SuperGuide",
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }
}
PHP,
        ];

        // ── Lección 16: Testing ───────────────────────────────────
        if ($l = $lessons->get(16)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Framework de testing simulado',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un framework de testing básico inspirado en PHPUnit/Pest.

```php
class TestResponse {
    public int $status;
    public array $headers;
    public string $body;
    public array $json;

    public function __construct(int $status, string $body = '', array $headers = []) {}

    public function assertStatus(int $expected): self {}
    public function assertJson(array $expected): self {}
    public function assertSee(string $text): self {}
    public function assertHeader(string $key, ?string $value = null): self {}
    public function assertRedirect(string $uri): self {}
}

class TestCase {
    protected array $assertions = [];
    private array $faked = [];

    /** Simula una petición HTTP y retorna TestResponse */
    public function get(string $uri, array $headers = []): TestResponse {}
    public function post(string $uri, array $data = [], array $headers = []): TestResponse {}

    /** Registra un fake para un servicio (Mail, Queue, Event, etc.) */
    public function fake(string $service): void {}

    /** Verifica que un servicio fakeado fue llamado N veces */
    public function assertFakedCount(string $service, int $expected): self {}

    /** Asserts básicos */
    public function assertEquals(mixed $expected, mixed $actual, string $msg = ''): self {}
    public function assertTrue(mixed $value, string $msg = ''): self {}
    public function assertCount(int $expected, array $array, string $msg = ''): self {}

    /** Retorna resumen de assertions */
    public function summary(): array {}
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class TestResponse {
    public int $status;
    public array $headers;
    public string $body;
    public array $json;

    public function __construct(int $status, string $body = '', array $headers = []) {
        $this->status = $status;
        $this->body = $body;
        $this->headers = $headers;
        $this->json = json_decode($body, true) ?? [];
    }

    public function assertStatus(int $expected): self {}
    public function assertJson(array $expected): self {}
    public function assertSee(string $text): self {}
    public function assertHeader(string $key, ?string $value = null): self {}
    public function assertRedirect(string $uri): self {}
}

class TestCase {
    protected array $assertions = [];
    private array $faked = [];

    public function get(string $uri, array $headers = []): TestResponse {}
    public function post(string $uri, array $data = [], array $headers = []): TestResponse {}
    public function fake(string $service): void {}
    public function assertFakedCount(string $service, int $expected): self {}
    public function assertEquals(mixed $expected, mixed $actual, string $msg = ''): self {}
    public function assertTrue(mixed $value, string $msg = ''): self {}
    public function assertCount(int $expected, array $array, string $msg = ''): self {}
    public function summary(): array {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class TestResponse {
    public int $status;
    public array $headers;
    public string $body;
    public array $json;

    public function __construct(int $status, string $body = '', array $headers = []) {
        $this->status = $status;
        $this->body = $body;
        $this->headers = $headers;
        $this->json = json_decode($body, true) ?? [];
    }

    public function assertStatus(int $expected): self {
        if ($this->status !== $expected) throw new \RuntimeException("Expected status {$expected}, got {$this->status}");
        return $this;
    }

    public function assertJson(array $expected): self {
        foreach ($expected as $k => $v) {
            if (!array_key_exists($k, $this->json) || $this->json[$k] !== $v) {
                throw new \RuntimeException("JSON missing key '{$k}' or value mismatch");
            }
        }
        return $this;
    }

    public function assertSee(string $text): self {
        if (!str_contains($this->body, $text)) throw new \RuntimeException("Text '{$text}' not found in body");
        return $this;
    }

    public function assertHeader(string $key, ?string $value = null): self {
        if (!isset($this->headers[$key])) throw new \RuntimeException("Header '{$key}' not found");
        if ($value !== null && $this->headers[$key] !== $value) {
            throw new \RuntimeException("Header '{$key}' expected '{$value}', got '{$this->headers[$key]}'");
        }
        return $this;
    }

    public function assertRedirect(string $uri): self {
        if ($this->status < 300 || $this->status >= 400) throw new \RuntimeException("Not a redirect: {$this->status}");
        $location = $this->headers['Location'] ?? '';
        if ($location !== $uri) throw new \RuntimeException("Redirect expected to '{$uri}', got '{$location}'");
        return $this;
    }
}

class TestCase {
    protected array $assertions = [];
    private array $faked = [];

    public function get(string $uri, array $headers = []): TestResponse {
        return new TestResponse(200, json_encode(['path' => $uri]), $headers);
    }

    public function post(string $uri, array $data = [], array $headers = []): TestResponse {
        return new TestResponse(201, json_encode($data), $headers);
    }

    public function fake(string $service): void {
        $this->faked[$service] = ($this->faked[$service] ?? 0) + 1;
    }

    public function assertFakedCount(string $service, int $expected): self {
        $actual = $this->faked[$service] ?? 0;
        if ($actual !== $expected) throw new \RuntimeException("Fake '{$service}' expected {$expected} calls, got {$actual}");
        $this->assertions[] = ['type' => 'fakedCount', 'pass' => true];
        return $this;
    }

    public function assertEquals(mixed $expected, mixed $actual, string $msg = ''): self {
        $pass = $expected === $actual;
        $this->assertions[] = ['type' => 'assertEquals', 'pass' => $pass, 'msg' => $msg];
        if (!$pass) throw new \RuntimeException($msg ?: "Expected " . var_export($expected, true) . ", got " . var_export($actual, true));
        return $this;
    }

    public function assertTrue(mixed $value, string $msg = ''): self {
        $pass = $value === true;
        $this->assertions[] = ['type' => 'assertTrue', 'pass' => $pass, 'msg' => $msg];
        if (!$pass) throw new \RuntimeException($msg ?: "Expected true, got " . var_export($value, true));
        return $this;
    }

    public function assertCount(int $expected, array $array, string $msg = ''): self {
        $actual = count($array);
        $pass = $actual === $expected;
        $this->assertions[] = ['type' => 'assertCount', 'pass' => $pass, 'msg' => $msg];
        if (!$pass) throw new \RuntimeException($msg ?: "Expected count {$expected}, got {$actual}");
        return $this;
    }

    public function summary(): array {
        $passed = count(array_filter($this->assertions, fn ($a) => $a['pass']));
        $failed = count($this->assertions) - $passed;
        return ['total' => count($this->assertions), 'passed' => $passed, 'failed' => $failed];
    }
}
PHP,
        ];

        // ── Lección 17: Livewire ──────────────────────────────────
        if ($l = $lessons->get(17)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Componente Livewire simulado',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa un componente Livewire simplificado con reactividad.

```php
class LivewireComponent {
    protected array $state = [];
    protected array $rules = [];
    private array $listeners = [];
    private array $emitted = [];
    private array $errors = [];

    /** Obtiene un valor del estado */
    public function __get(string $name): mixed {}

    /** Establece un valor (simula wire:model) y valida si hay regla */
    public function __set(string $name, mixed $value): void {}

    /** Registra un listener para un evento */
    public function on(string $event, string $method): void {}

    /** Emite un evento con payload */
    public function emit(string $event, mixed ...$args): void {}

    /** Ejecuta una acción (simula wire:click) */
    public function callAction(string $method, mixed ...$args): mixed {}

    /** Valida el estado actual contra $rules. Retorna true si válido. */
    public function validate(): bool {}

    /** Retorna los errores de validación */
    public function getErrors(): array {}

    /** Simula el render: retorna el estado como "HTML" */
    public function render(): string {}
}

class TodoComponent extends LivewireComponent {
    protected array $state = ['newTodo' => '', 'todos' => [], 'filter' => 'all'];
    protected array $rules = ['newTodo' => 'required|min:3'];

    public function addTodo(): void {}
    public function toggleTodo(int $index): void {}
    public function removeTodo(int $index): void {}
    public function filteredTodos(): array {}
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class LivewireComponent {
    protected array $state = [];
    protected array $rules = [];
    private array $listeners = [];
    private array $emitted = [];
    private array $errors = [];

    public function __get(string $name): mixed {}
    public function __set(string $name, mixed $value): void {}
    public function on(string $event, string $method): void {}
    public function emit(string $event, mixed ...$args): void {}
    public function callAction(string $method, mixed ...$args): mixed {}
    public function validate(): bool {}
    public function getErrors(): array {}
    public function render(): string {}
}

class TodoComponent extends LivewireComponent {
    protected array $state = ['newTodo' => '', 'todos' => [], 'filter' => 'all'];
    protected array $rules = ['newTodo' => 'required|min:3'];

    public function addTodo(): void {}
    public function toggleTodo(int $index): void {}
    public function removeTodo(int $index): void {}
    public function filteredTodos(): array {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class LivewireComponent {
    protected array $state = [];
    protected array $rules = [];
    private array $listeners = [];
    private array $emitted = [];
    private array $errors = [];

    public function __get(string $name): mixed {
        return $this->state[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void {
        $this->state[$name] = $value;
        if (isset($this->rules[$name])) {
            $this->validateField($name, $value);
        }
    }

    private function validateField(string $field, mixed $value): void {
        unset($this->errors[$field]);
        $rules = explode('|', $this->rules[$field]);
        foreach ($rules as $rule) {
            if ($rule === 'required' && ($value === '' || $value === null)) {
                $this->errors[$field][] = "{$field} es obligatorio";
            }
            if (str_starts_with($rule, 'min:')) {
                $min = (int)substr($rule, 4);
                if (is_string($value) && strlen($value) < $min) {
                    $this->errors[$field][] = "{$field} debe tener al menos {$min} caracteres";
                }
            }
        }
    }

    public function on(string $event, string $method): void {
        $this->listeners[$event] = $method;
    }

    public function emit(string $event, mixed ...$args): void {
        $this->emitted[] = ['event' => $event, 'args' => $args];
        if (isset($this->listeners[$event]) && method_exists($this, $this->listeners[$event])) {
            $this->{$this->listeners[$event]}(...$args);
        }
    }

    public function callAction(string $method, mixed ...$args): mixed {
        if (!method_exists($this, $method)) throw new \RuntimeException("Action not found: {$method}");
        return $this->$method(...$args);
    }

    public function validate(): bool {
        $this->errors = [];
        foreach ($this->rules as $field => $ruleStr) {
            $this->validateField($field, $this->state[$field] ?? null);
        }
        return empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function render(): string {
        return json_encode($this->state);
    }
}

class TodoComponent extends LivewireComponent {
    protected array $state = ['newTodo' => '', 'todos' => [], 'filter' => 'all'];
    protected array $rules = ['newTodo' => 'required|min:3'];

    public function addTodo(): void {
        if (!$this->validate()) return;
        $this->state['todos'][] = ['text' => $this->state['newTodo'], 'done' => false];
        $this->state['newTodo'] = '';
        $this->emit('todo-added');
    }

    public function toggleTodo(int $index): void {
        if (isset($this->state['todos'][$index])) {
            $this->state['todos'][$index]['done'] = !$this->state['todos'][$index]['done'];
        }
    }

    public function removeTodo(int $index): void {
        array_splice($this->state['todos'], $index, 1);
    }

    public function filteredTodos(): array {
        return match ($this->state['filter']) {
            'active'    => array_values(array_filter($this->state['todos'], fn ($t) => !$t['done'])),
            'completed' => array_values(array_filter($this->state['todos'], fn ($t) => $t['done'])),
            default     => $this->state['todos'],
        };
    }
}
PHP,
        ];

        // ── Lección 18: Deploy y Producción ───────────────────────
        if ($l = $lessons->get(18)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Herramientas de deploy y optimización',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa utilidades para deploy y optimización de producción.

```php
class DeployManager {
    private array $steps = [];
    private array $log = [];
    private array $config = [];

    /**
     * Agrega un paso al deploy. Cada paso: ['name' => string, 'command' => callable]
     */
    public function addStep(string $name, callable $command): void {}

    /** Ejecuta todos los pasos. Si uno falla, detiene y retorna error. */
    public function deploy(): array {}

    /** Retorna el log de ejecución */
    public function getLog(): array {}
}

class ConfigCache {
    private array $configs = [];
    private ?string $cached = null;

    /** Agrega configuraciones desde un array */
    public function load(string $name, array $config): void {}

    /** Genera cache: serializa todas las configs en un string */
    public function cache(): string {}

    /** Lee del cache: deserializa. Si no hay cache, retorna de configs. */
    public function get(string $name, string $key = null): mixed {}

    /** Limpia el cache */
    public function clear(): void {}
}

class HealthCheck {
    private array $checks = [];

    /** Registra un check: ['name' => string, 'check' => callable que retorna bool] */
    public function register(string $name, callable $check): void {}

    /** Ejecuta todos los checks. Retorna ['status' => 'ok'|'fail', 'checks' => [...]] */
    public function run(): array {}
}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class DeployManager {
    private array $steps = [];
    private array $log = [];

    public function addStep(string $name, callable $command): void {}
    public function deploy(): array {}
    public function getLog(): array {}
}

class ConfigCache {
    private array $configs = [];
    private ?string $cached = null;

    public function load(string $name, array $config): void {}
    public function cache(): string {}
    public function get(string $name, ?string $key = null): mixed {}
    public function clear(): void {}
}

class HealthCheck {
    private array $checks = [];

    public function register(string $name, callable $check): void {}
    public function run(): array {}
}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class DeployManager {
    private array $steps = [];
    private array $log = [];

    public function addStep(string $name, callable $command): void {
        $this->steps[] = ['name' => $name, 'command' => $command];
    }

    public function deploy(): array {
        $this->log = [];
        foreach ($this->steps as $step) {
            try {
                $result = ($step['command'])();
                $this->log[] = ['step' => $step['name'], 'status' => 'ok', 'result' => $result];
            } catch (\Throwable $e) {
                $this->log[] = ['step' => $step['name'], 'status' => 'fail', 'error' => $e->getMessage()];
                return ['status' => 'failed', 'failed_at' => $step['name'], 'error' => $e->getMessage(), 'log' => $this->log];
            }
        }
        return ['status' => 'success', 'log' => $this->log];
    }

    public function getLog(): array {
        return $this->log;
    }
}

class ConfigCache {
    private array $configs = [];
    private ?string $cached = null;

    public function load(string $name, array $config): void {
        $this->configs[$name] = $config;
        $this->cached = null;
    }

    public function cache(): string {
        $this->cached = serialize($this->configs);
        return $this->cached;
    }

    public function get(string $name, ?string $key = null): mixed {
        $source = $this->cached ? unserialize($this->cached) : $this->configs;
        if (!isset($source[$name])) return null;
        if ($key === null) return $source[$name];
        return $source[$name][$key] ?? null;
    }

    public function clear(): void {
        $this->cached = null;
    }
}

class HealthCheck {
    private array $checks = [];

    public function register(string $name, callable $check): void {
        $this->checks[$name] = $check;
    }

    public function run(): array {
        $results = [];
        $allPassed = true;
        foreach ($this->checks as $name => $check) {
            try {
                $pass = (bool)$check();
            } catch (\Throwable $e) {
                $pass = false;
            }
            $results[$name] = $pass ? 'ok' : 'fail';
            if (!$pass) $allPassed = false;
        }
        return ['status' => $allPassed ? 'ok' : 'fail', 'checks' => $results];
    }
}
PHP,
        ];

        // ── Lección 19: Preguntas de Entrevista ───────────────────
        if ($l = $lessons->get(19)) $ex[] = [
            'lesson_id'    => $l->id,
            'title'        => 'Conceptos clave de entrevista Laravel',
            'language'     => 'php',
            'description'  => <<<'MD'
Implementa funciones que demuestren conceptos clave de entrevista Laravel.

```php
/**
 * Service Container simplificado: registro y resolución de dependencias con singletons.
 */
class Container {
    private array $bindings = [];
    private array $singletons = [];
    private array $instances = [];

    public function bind(string $abstract, callable $factory): void {}
    public function singleton(string $abstract, callable $factory): void {}
    public function make(string $abstract): mixed {}
    public function has(string $abstract): bool {}
}

/**
 * Facade simplificado: acceso estático a un servicio del Container.
 */
class Facade {
    protected static ?Container $container = null;
    public static function setContainer(Container $c): void {}

    /** Subclases definen qué servicio resuelven */
    protected static function accessor(): string { return ''; }

    /** Magic static method: redirige al servicio resuelto */
    public static function __callStatic(string $method, array $args): mixed {}
}

/**
 * Demuestra N+1: dado un array de posts con user_id, getAuthors() hace N queries (ineficiente).
 * getAuthorsOptimized() hace 1 query (eager loading simulado).
 */
function getAuthors(array $posts, callable $findUser): array {}
function getAuthorsOptimized(array $posts, callable $findUsers): array {}

/**
 * middlewarePipeline: recibe un request y un array de callables (middleware),
 * ejecuta en orden Onion (el primero envuelve al segundo, etc.).
 */
function middlewarePipeline(array $request, array $middlewares, callable $core): array {}
```
MD,
            'starter_code' => <<<'PHP'
<?php
declare(strict_types=1);

class Container {
    private array $bindings = [];
    private array $singletons = [];
    private array $instances = [];

    public function bind(string $abstract, callable $factory): void {}
    public function singleton(string $abstract, callable $factory): void {}
    public function make(string $abstract): mixed {}
    public function has(string $abstract): bool {}
}

class Facade {
    protected static ?Container $container = null;
    public static function setContainer(Container $c): void {}
    protected static function accessor(): string { return ''; }
    public static function __callStatic(string $method, array $args): mixed {}
}

function getAuthors(array $posts, callable $findUser): array {}
function getAuthorsOptimized(array $posts, callable $findUsers): array {}
function middlewarePipeline(array $request, array $middlewares, callable $core): array {}
PHP,
            'solution_code' => <<<'PHP'
<?php
declare(strict_types=1);

class Container {
    private array $bindings = [];
    private array $singletons = [];
    private array $instances = [];

    public function bind(string $abstract, callable $factory): void {
        $this->bindings[$abstract] = $factory;
    }

    public function singleton(string $abstract, callable $factory): void {
        $this->singletons[$abstract] = $factory;
    }

    public function make(string $abstract): mixed {
        // Singleton ya instanciado
        if (isset($this->instances[$abstract])) return $this->instances[$abstract];
        // Singleton pendiente
        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = ($this->singletons[$abstract])($this);
            return $this->instances[$abstract];
        }
        // Binding normal
        if (isset($this->bindings[$abstract])) {
            return ($this->bindings[$abstract])($this);
        }
        throw new \RuntimeException("No binding for: {$abstract}");
    }

    public function has(string $abstract): bool {
        return isset($this->bindings[$abstract]) || isset($this->singletons[$abstract]) || isset($this->instances[$abstract]);
    }
}

class Facade {
    protected static ?Container $container = null;
    public static function setContainer(Container $c): void { static::$container = $c; }
    protected static function accessor(): string { return ''; }

    public static function __callStatic(string $method, array $args): mixed {
        $instance = static::$container->make(static::accessor());
        return $instance->$method(...$args);
    }
}

// N+1: una query por post (ineficiente)
function getAuthors(array $posts, callable $findUser): array {
    return array_map(fn ($post) => $findUser($post['user_id']), $posts);
}

// Eager loading: una sola query para todos los user_ids
function getAuthorsOptimized(array $posts, callable $findUsers): array {
    $userIds = array_unique(array_column($posts, 'user_id'));
    $users = $findUsers($userIds); // una sola "query"
    $userMap = [];
    foreach ($users as $u) $userMap[$u['id']] = $u;
    return array_map(fn ($post) => $userMap[$post['user_id']] ?? null, $posts);
}

// Middleware onion pipeline
function middlewarePipeline(array $request, array $middlewares, callable $core): array {
    $pipeline = $core;
    foreach (array_reverse($middlewares) as $mw) {
        $pipeline = fn (array $req) => $mw($req, $pipeline);
    }
    return $pipeline($request);
}
PHP,
        ];

        return $ex;
    }
}
