# Rendimiento y Optimización en PHP

PHP moderno es significativamente más rápido que sus versiones anteriores. Sin embargo, conocer las técnicas de optimización te permitirá exprimir al máximo el rendimiento de tus aplicaciones. En esta lección cubriremos desde OPcache hasta Fibers.

---

## 1. OPcache: Caché de Bytecode

OPcache almacena en memoria compartida el bytecode compilado de los scripts PHP, eliminando la necesidad de compilar el código fuente en cada solicitud.

### Configuración recomendada en `php.ini`

```php
; Habilitar OPcache
opcache.enable=1
opcache.enable_cli=0

; Memoria para almacenar scripts compilados
opcache.memory_consumption=256

; Número máximo de archivos en caché
opcache.max_accelerated_files=20000

; Frecuencia de verificación de cambios (0 en producción)
opcache.revalidate_freq=0
opcache.validate_timestamps=0  ; Desactivar en producción

; Optimizaciones adicionales
opcache.interned_strings_buffer=16
opcache.fast_shutdown=1
opcache.save_comments=1
```

### Verificar el estado de OPcache

```php
// Verificar si OPcache está habilitado
if (function_exists('opcache_get_status')) {
    $estado = opcache_get_status(false);

    echo 'Memoria usada: ' . round($estado['memory_usage']['used_memory'] / 1024 / 1024, 2) . ' MB' . PHP_EOL;
    echo 'Scripts en caché: ' . $estado['opcache_statistics']['num_cached_scripts'] . PHP_EOL;
    echo 'Hit rate: ' . round($estado['opcache_statistics']['opcache_hit_rate'], 2) . '%' . PHP_EOL;
}

// Invalidar la caché manualmente después de un despliegue
opcache_reset();
```

> **Tip:** En producción, desactiva `validate_timestamps` y ejecuta `opcache_reset()` después de cada despliegue para obtener el máximo rendimiento.

---

## 2. JIT (Just-In-Time) — PHP 8.0+

El compilador JIT convierte el bytecode de OPcache en código máquina nativo, ofreciendo mejoras significativas en operaciones CPU-intensivas.

### Configuración del JIT

```php
; En php.ini
opcache.jit=1255          ; Modo tracing (recomendado)
opcache.jit_buffer_size=128M

; Modos del JIT:
; 1205 = function JIT (más conservador)
; 1255 = tracing JIT (más agresivo, mejor rendimiento)
```

### Cuándo el JIT marca diferencia

```php
// ✅ Operaciones CPU-intensivas se benefician del JIT
function calcularFibonacci(int $n): int
{
    if ($n <= 1) return $n;
    return calcularFibonacci($n - 1) + calcularFibonacci($n - 2);
}

// Benchmark
$inicio = hrtime(true);
$resultado = calcularFibonacci(35);
$duracion = (hrtime(true) - $inicio) / 1_000_000;

echo "Fibonacci(35) = {$resultado} en {$duracion} ms" . PHP_EOL;
// Sin JIT: ~500ms | Con JIT: ~120ms (aprox.)
```

> **Nota:** El JIT no mejora operaciones I/O (consultas a base de datos, llamadas HTTP). Su mayor impacto es en cálculos matemáticos, procesamiento de imágenes y bucles intensivos.

---

## 3. Profiling con Xdebug

Xdebug es la herramienta estándar para perfilar aplicaciones PHP e identificar cuellos de botella.

### Configuración para profiling

```php
; En php.ini (solo en desarrollo)
xdebug.mode=profile
xdebug.output_dir=/tmp/xdebug
xdebug.profiler_output_name=cachegrind.out.%R.%t

; Activar profiling solo cuando se solicita
xdebug.start_with_request=trigger
; Añadir ?XDEBUG_PROFILE=1 a la URL para activar
```

### Medir tiempo de ejecución manualmente

```php
class Perfilador
{
    private static array $marcas = [];

    public static function iniciar(string $etiqueta): void
    {
        self::$marcas[$etiqueta] = [
            'inicio' => hrtime(true),
            'memoria_inicio' => memory_get_usage(true),
        ];
    }

    public static function detener(string $etiqueta): array
    {
        $fin = hrtime(true);
        $memoriaFin = memory_get_usage(true);
        $marca = self::$marcas[$etiqueta];

        $resultado = [
            'etiqueta' => $etiqueta,
            'tiempo_ms' => ($fin - $marca['inicio']) / 1_000_000,
            'memoria_mb' => ($memoriaFin - $marca['memoria_inicio']) / 1024 / 1024,
        ];

        unset(self::$marcas[$etiqueta]);
        return $resultado;
    }
}

// Uso
Perfilador::iniciar('consulta_usuarios');
$usuarios = $repositorio->obtenerTodos();
$perfil = Perfilador::detener('consulta_usuarios');

echo "Consulta tomó {$perfil['tiempo_ms']} ms, usó {$perfil['memoria_mb']} MB" . PHP_EOL;
```

---

## 4. Gestión de memoria

PHP libera memoria automáticamente al finalizar cada solicitud, pero gestionar la memoria correctamente es crucial para scripts de larga duración.

### Técnicas de gestión de memoria

```php
// Verificar uso de memoria
echo 'Memoria actual: ' . (memory_get_usage() / 1024 / 1024) . ' MB' . PHP_EOL;
echo 'Pico de memoria: ' . (memory_get_peak_usage() / 1024 / 1024) . ' MB' . PHP_EOL;

// ❌ MALO: Cargar todo en memoria
$lineas = file('archivo_grande.csv'); // Carga TODO el archivo

// ✅ MEJOR: Leer línea por línea con generadores
function leerCsvPorLinea(string $archivo): Generator
{
    $handle = fopen($archivo, 'r');
    if ($handle === false) {
        throw new RuntimeException("No se puede abrir: {$archivo}");
    }

    try {
        while (($linea = fgetcsv($handle)) !== false) {
            yield $linea;
        }
    } finally {
        fclose($handle);
    }
}

// Procesar millones de filas sin agotar la memoria
foreach (leerCsvPorLinea('ventas_2025.csv') as $fila) {
    procesarVenta($fila);
}
```

### Liberar memoria en scripts largos

```php
// Procesar en lotes para mantener la memoria controlada
function procesarEnLotes(PDO $pdo, int $tamanoLote = 1000): void
{
    $offset = 0;

    while (true) {
        $stmt = $pdo->prepare('SELECT * FROM registros LIMIT :limite OFFSET :offset');
        $stmt->bindValue(':limite', $tamanoLote, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $registros = $stmt->fetchAll();
        if (empty($registros)) break;

        foreach ($registros as $registro) {
            procesarRegistro($registro);
        }

        $offset += $tamanoLote;
        unset($registros); // Liberar memoria explícitamente
        gc_collect_cycles(); // Forzar recolección de basura si es necesario
    }
}
```

---

## 5. Lazy Loading (Carga diferida)

Cargar recursos solo cuando realmente se necesitan reduce el tiempo de respuesta y el consumo de memoria.

```php
class ConexionLazy
{
    private ?PDO $pdo = null;

    public function __construct(
        private readonly string $dsn,
        private readonly string $usuario,
        private readonly string $contrasena,
    ) {}

    public function obtenerConexion(): PDO
    {
        // La conexión se crea solo cuando se usa por primera vez
        if ($this->pdo === null) {
            $this->pdo = new PDO($this->dsn, $this->usuario, $this->contrasena, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        }
        return $this->pdo;
    }
}

// Con PHP 8.4+ puedes usar lazy objects nativos
// $reflector = new ReflectionClass(ServicioPesado::class);
// $proxy = $reflector->newLazyProxy(fn () => new ServicioPesado());
```

---

## 6. Estrategias de caché

### Caché en memoria con APCu

```php
// Caché simple con APCu (memoria compartida del servidor)
function obtenerConfiguracion(string $clave): mixed
{
    $cacheClave = "config:{$clave}";

    $valor = apcu_fetch($cacheClave, $exito);
    if ($exito) {
        return $valor;
    }

    // Consultar la base de datos solo si no está en caché
    $valor = consultarConfigDesdeDb($clave);
    apcu_store($cacheClave, $valor, 3600); // TTL: 1 hora

    return $valor;
}
```

### Caché con Redis

```php
class CacheRedis
{
    private Redis $redis;

    public function __construct(string $host = '127.0.0.1', int $puerto = 6379)
    {
        $this->redis = new Redis();
        $this->redis->connect($host, $puerto);
    }

    public function recordar(string $clave, int $ttl, callable $callback): mixed
    {
        $datos = $this->redis->get($clave);
        if ($datos !== false) {
            return unserialize($datos);
        }

        $valor = $callback();
        $this->redis->setex($clave, $ttl, serialize($valor));

        return $valor;
    }

    public function invalidar(string $patron): void
    {
        $claves = $this->redis->keys($patron);
        if (!empty($claves)) {
            $this->redis->del($claves);
        }
    }
}

// Uso
$cache = new CacheRedis();

$productos = $cache->recordar('productos:destacados', 1800, function () use ($repositorio) {
    return $repositorio->obtenerDestacados();
});
```

---

## 7. Preloading (PHP 7.4+)

Preloading permite cargar archivos PHP en memoria al iniciar el servidor, eliminando la sobrecarga de cargarlos en cada solicitud.

### Script de preloading

```php
// preload.php — se ejecuta UNA vez al iniciar PHP-FPM
$archivos = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/app/Models/Usuario.php',
    __DIR__ . '/app/Models/Producto.php',
    __DIR__ . '/app/Services/AuthService.php',
];

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        opcache_compile_file($archivo);
    }
}

// Precargar un directorio completo
function precargarDirectorio(string $directorio): int
{
    $conteo = 0;
    $iterador = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directorio, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterador as $archivo) {
        if ($archivo->getExtension() === 'php') {
            opcache_compile_file($archivo->getPathname());
            $conteo++;
        }
    }
    return $conteo;
}

$total = precargarDirectorio(__DIR__ . '/app');
// En php.ini: opcache.preload=/ruta/a/preload.php
```

---

## 8. Fibers (PHP 8.1+)

Las Fibers permiten pausar y reanudar la ejecución de funciones, habilitando patrones de concurrencia cooperativa.

```php
// Ejemplo básico de Fiber
$fiber = new Fiber(function (): void {
    $valor = Fiber::suspend('primer pausa');
    echo "Fiber recibió: {$valor}" . PHP_EOL;

    Fiber::suspend('segunda pausa');
    echo 'Fiber completada' . PHP_EOL;
});

$resultado1 = $fiber->start();          // "primer pausa"
$resultado2 = $fiber->resume('hola');   // Imprime: Fiber recibió: hola
$fiber->resume();                        // Imprime: Fiber completada
```

### Uso práctico: Scheduler de tareas

```php
class PlanificadorTareas
{
    /** @var SplQueue<Fiber> */
    private SplQueue $cola;

    public function __construct()
    {
        $this->cola = new SplQueue();
    }

    public function agregar(Closure $tarea): void
    {
        $this->cola->enqueue(new Fiber($tarea));
    }

    public function ejecutar(): void
    {
        while (!$this->cola->isEmpty()) {
            $fiber = $this->cola->dequeue();

            if (!$fiber->isStarted()) {
                $fiber->start();
            } elseif ($fiber->isSuspended()) {
                $fiber->resume();
            }

            // Si la fiber no terminó, re-encolar
            if (!$fiber->isTerminated()) {
                $this->cola->enqueue($fiber);
            }
        }
    }
}

$planificador = new PlanificadorTareas();
$planificador->agregar(function () {
    echo "Tarea 1: inicio\n";
    Fiber::suspend();
    echo "Tarea 1: fin\n";
});
$planificador->agregar(function () {
    echo "Tarea 2: inicio\n";
    Fiber::suspend();
    echo "Tarea 2: fin\n";
});
$planificador->ejecutar();
// Salida: Tarea 1: inicio → Tarea 2: inicio → Tarea 1: fin → Tarea 2: fin
```

---

## 9. Connection Pooling

El pooling de conexiones reutiliza conexiones a la base de datos en lugar de crear una nueva en cada solicitud.

```php
class PoolConexiones
{
    /** @var SplQueue<PDO> */
    private SplQueue $disponibles;
    private int $activas = 0;

    public function __construct(
        private readonly string $dsn,
        private readonly string $usuario,
        private readonly string $contrasena,
        private readonly int $maxConexiones = 10,
    ) {
        $this->disponibles = new SplQueue();
    }

    public function obtener(): PDO
    {
        if (!$this->disponibles->isEmpty()) {
            return $this->disponibles->dequeue();
        }

        if ($this->activas < $this->maxConexiones) {
            $this->activas++;
            return new PDO($this->dsn, $this->usuario, $this->contrasena, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true,
            ]);
        }

        throw new RuntimeException('Pool de conexiones agotado.');
    }

    public function devolver(PDO $conexion): void
    {
        $this->disponibles->enqueue($conexion);
    }
}

// Uso
$pool = new PoolConexiones('mysql:host=localhost;dbname=app', 'user', 'pass');

$conexion = $pool->obtener();
// ... realizar consultas ...
$pool->devolver($conexion);
```

> **Tip:** En PHP-FPM tradicional, cada worker maneja una solicitud a la vez. Para un pooling real de conexiones, considera usar herramientas como **PgBouncer** (PostgreSQL) o **ProxySQL** (MySQL), o frameworks asíncronos como **Swoole** o **ReactPHP**.

---

## Resumen de técnicas

| Técnica             | Impacto       | Complejidad | Cuándo usar                    |
|---------------------|---------------|-------------|--------------------------------|
| OPcache             | ⭐⭐⭐⭐⭐  | Baja        | Siempre en producción          |
| JIT                 | ⭐⭐⭐        | Baja        | Cálculos intensivos            |
| Preloading          | ⭐⭐⭐        | Media       | Aplicaciones con muchas clases |
| APCu / Redis        | ⭐⭐⭐⭐      | Media       | Datos consultados con frecuencia|
| Generadores         | ⭐⭐⭐⭐      | Baja        | Procesamiento de grandes datos |
| Connection Pooling  | ⭐⭐⭐        | Alta        | Alto volumen de conexiones     |
| Fibers              | ⭐⭐⭐        | Alta        | I/O concurrente cooperativo    |

> **Regla de oro:** Mide antes de optimizar. Usa herramientas de profiling para identificar los cuellos de botella reales antes de aplicar optimizaciones.
