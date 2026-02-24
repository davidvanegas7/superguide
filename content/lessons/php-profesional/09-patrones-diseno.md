# Patrones de Diseño en PHP

Los patrones de diseño son soluciones probadas a problemas comunes en el desarrollo de software. Conocerlos te permite escribir código más flexible, mantenible y comunicar ideas con otros desarrolladores usando un vocabulario compartido.

---

## Singleton

Garantiza que una clase tenga una única instancia y proporciona un punto de acceso global a ella.

```php
class BaseDatos
{
    private static ?self $instancia = null;
    private PDO $conexion;

    // Constructor privado: no se puede instanciar desde fuera
    private function __construct()
    {
        $this->conexion = new PDO(
            'mysql:host=localhost;dbname=miapp',
            'root',
            'password',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    // Prevenir clonación
    private function __clone(): void {}

    // Prevenir deserialización
    public function __wakeup(): void
    {
        throw new \RuntimeException('No se puede deserializar un Singleton');
    }

    public static function obtenerInstancia(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function getConexion(): PDO
    {
        return $this->conexion;
    }
}

// Uso
$db = BaseDatos::obtenerInstancia();
$stmt = $db->getConexion()->query('SELECT * FROM usuarios');
```

> **Nota:** El Singleton es considerado un anti-patrón en muchos contextos modernos. Prefiere la Inyección de Dependencias siempre que sea posible.

---

## Factory Method

Define una interfaz para crear objetos, pero permite que las subclases decidan qué clase instanciar.

```php
interface Notificacion
{
    public function enviar(string $destinatario, string $mensaje): void;
}

class NotificacionEmail implements Notificacion
{
    public function enviar(string $destinatario, string $mensaje): void
    {
        echo "Enviando email a {$destinatario}: {$mensaje}\n";
        // mail($destinatario, 'Notificación', $mensaje);
    }
}

class NotificacionSms implements Notificacion
{
    public function enviar(string $destinatario, string $mensaje): void
    {
        echo "Enviando SMS a {$destinatario}: {$mensaje}\n";
    }
}

class NotificacionPush implements Notificacion
{
    public function enviar(string $destinatario, string $mensaje): void
    {
        echo "Enviando push a {$destinatario}: {$mensaje}\n";
    }
}

// Factory Method
class NotificacionFactory
{
    public static function crear(string $tipo): Notificacion
    {
        return match ($tipo) {
            'email' => new NotificacionEmail(),
            'sms'   => new NotificacionSms(),
            'push'  => new NotificacionPush(),
            default => throw new InvalidArgumentException("Tipo de notificación '{$tipo}' no soportado"),
        };
    }
}

// Uso
$notificacion = NotificacionFactory::crear('email');
$notificacion->enviar('usuario@ejemplo.com', 'Bienvenido');
```

---

## Abstract Factory

Proporciona una interfaz para crear familias de objetos relacionados sin especificar sus clases concretas.

```php
interface Boton
{
    public function renderizar(): string;
}

interface Input
{
    public function renderizar(): string;
}

// Familia: componentes web
class BotonWeb implements Boton
{
    public function renderizar(): string
    {
        return '<button class="btn">Click</button>';
    }
}

class InputWeb implements Input
{
    public function renderizar(): string
    {
        return '<input type="text" class="form-control" />';
    }
}

// Familia: componentes CLI
class BotonCli implements Boton
{
    public function renderizar(): string
    {
        return '[ Click ]';
    }
}

class InputCli implements Input
{
    public function renderizar(): string
    {
        return '> _________';
    }
}

// Abstract Factory
interface UIFactory
{
    public function crearBoton(): Boton;
    public function crearInput(): Input;
}

class WebUIFactory implements UIFactory
{
    public function crearBoton(): Boton { return new BotonWeb(); }
    public function crearInput(): Input { return new InputWeb(); }
}

class CliUIFactory implements UIFactory
{
    public function crearBoton(): Boton { return new BotonCli(); }
    public function crearInput(): Input { return new InputCli(); }
}

// Uso: el código no sabe qué familia concreta usa
function renderizarFormulario(UIFactory $factory): void
{
    echo $factory->crearInput()->renderizar() . "\n";
    echo $factory->crearBoton()->renderizar() . "\n";
}

renderizarFormulario(new WebUIFactory());
// <input type="text" class="form-control" />
// <button class="btn">Click</button>
```

---

## Strategy

Define una familia de algoritmos, encapsula cada uno y los hace intercambiables.

```php
interface EstrategiaPrecio
{
    public function calcular(float $precioBase): float;
}

class PrecioNormal implements EstrategiaPrecio
{
    public function calcular(float $precioBase): float
    {
        return $precioBase;
    }
}

class DescuentoPorcentaje implements EstrategiaPrecio
{
    public function __construct(private float $porcentaje) {}

    public function calcular(float $precioBase): float
    {
        return $precioBase * (1 - $this->porcentaje / 100);
    }
}

class DescuentoMiembro implements EstrategiaPrecio
{
    public function calcular(float $precioBase): float
    {
        return $precioBase * 0.80; // 20% de descuento para miembros
    }
}

class DescuentoBlackFriday implements EstrategiaPrecio
{
    public function calcular(float $precioBase): float
    {
        return $precioBase * 0.50; // 50% en Black Friday
    }
}

class CarritoCompras
{
    private EstrategiaPrecio $estrategia;
    private array $productos = [];

    public function __construct()
    {
        $this->estrategia = new PrecioNormal();
    }

    public function setEstrategia(EstrategiaPrecio $estrategia): void
    {
        $this->estrategia = $estrategia;
    }

    public function agregar(string $producto, float $precio): void
    {
        $this->productos[] = ['nombre' => $producto, 'precio' => $precio];
    }

    public function total(): float
    {
        $total = 0;
        foreach ($this->productos as $producto) {
            $total += $this->estrategia->calcular($producto['precio']);
        }
        return round($total, 2);
    }
}

// Uso
$carrito = new CarritoCompras();
$carrito->agregar('Laptop', 1000);
$carrito->agregar('Mouse', 50);

echo $carrito->total(); // 1050 (sin descuento)

$carrito->setEstrategia(new DescuentoBlackFriday());
echo $carrito->total(); // 525 (50% descuento)
```

---

## Observer

Define una dependencia uno-a-muchos donde cuando un objeto cambia de estado, todos sus dependientes son notificados.

```php
interface Observador
{
    public function actualizar(string $evento, mixed $datos): void;
}

class SistemaEventos
{
    /** @var array<string, Observador[]> */
    private array $observadores = [];

    public function suscribir(string $evento, Observador $observador): void
    {
        $this->observadores[$evento][] = $observador;
    }

    public function notificar(string $evento, mixed $datos = null): void
    {
        foreach ($this->observadores[$evento] ?? [] as $observador) {
            $observador->actualizar($evento, $datos);
        }
    }
}

class LogObservador implements Observador
{
    public function actualizar(string $evento, mixed $datos): void
    {
        echo "[LOG] Evento: {$evento}\n";
    }
}

class EmailObservador implements Observador
{
    public function actualizar(string $evento, mixed $datos): void
    {
        if ($evento === 'usuario.registrado') {
            echo "Enviando email de bienvenida a {$datos['email']}\n";
        }
    }
}

// Uso
$eventos = new SistemaEventos();
$eventos->suscribir('usuario.registrado', new LogObservador());
$eventos->suscribir('usuario.registrado', new EmailObservador());

$eventos->notificar('usuario.registrado', [
    'nombre' => 'Carlos',
    'email' => 'carlos@ejemplo.com',
]);
// [LOG] Evento: usuario.registrado
// Enviando email de bienvenida a carlos@ejemplo.com
```

---

## Decorator

Agrega responsabilidades a un objeto dinámicamente, sin modificar su clase original.

```php
interface Logger
{
    public function log(string $mensaje): void;
}

class LoggerArchivo implements Logger
{
    public function log(string $mensaje): void
    {
        file_put_contents('app.log', $mensaje . PHP_EOL, FILE_APPEND);
    }
}

// Decoradores
class LoggerConTimestamp implements Logger
{
    public function __construct(private Logger $logger) {}

    public function log(string $mensaje): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $this->logger->log("[{$timestamp}] {$mensaje}");
    }
}

class LoggerConNivel implements Logger
{
    public function __construct(
        private Logger $logger,
        private string $nivel = 'INFO'
    ) {}

    public function log(string $mensaje): void
    {
        $this->logger->log("[{$this->nivel}] {$mensaje}");
    }
}

class LoggerConFormatoJson implements Logger
{
    public function __construct(private Logger $logger) {}

    public function log(string $mensaje): void
    {
        $json = json_encode(['mensaje' => $mensaje, 'timestamp' => time()]);
        $this->logger->log($json);
    }
}

// Composición de decoradores
$logger = new LoggerConTimestamp(
    new LoggerConNivel(
        new LoggerArchivo(),
        'ERROR'
    )
);

$logger->log('Algo falló');
// Escribe en archivo: [2026-02-23 10:00:00] [ERROR] Algo falló
```

---

## Repository Pattern

Abstrae la capa de acceso a datos, proporcionando una colección de objetos de dominio.

```php
interface RepositorioProducto
{
    public function buscarPorId(int $id): ?Producto;
    public function buscarPorCategoria(string $categoria): array;
    public function guardar(Producto $producto): void;
    public function eliminar(int $id): void;
    public function todos(int $limite = 50, int $offset = 0): array;
}

class RepositorioProductoMySQL implements RepositorioProducto
{
    public function __construct(private PDO $pdo) {}

    public function buscarPorId(int $id): ?Producto
    {
        $stmt = $this->pdo->prepare('SELECT * FROM productos WHERE id = ?');
        $stmt->execute([$id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ? Producto::desdeFila($fila) : null;
    }

    public function buscarPorCategoria(string $categoria): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM productos WHERE categoria = ?');
        $stmt->execute([$categoria]);
        return array_map(
            fn($fila) => Producto::desdeFila($fila),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function guardar(Producto $producto): void
    {
        $sql = 'INSERT INTO productos (nombre, precio, categoria)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE nombre = ?, precio = ?';
        $this->pdo->prepare($sql)->execute([
            $producto->nombre, $producto->precio, $producto->categoria,
            $producto->nombre, $producto->precio,
        ]);
    }

    public function eliminar(int $id): void
    {
        $this->pdo->prepare('DELETE FROM productos WHERE id = ?')->execute([$id]);
    }

    public function todos(int $limite = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM productos LIMIT ? OFFSET ?');
        $stmt->execute([$limite, $offset]);
        return array_map(fn($f) => Producto::desdeFila($f), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
```

---

## Dependency Injection Container

Un contenedor DI crea y gestiona las dependencias de tu aplicación:

```php
class Contenedor
{
    private array $definiciones = [];
    private array $instancias = [];

    public function set(string $id, callable $fabrica): void
    {
        $this->definiciones[$id] = $fabrica;
    }

    public function get(string $id): object
    {
        if (!isset($this->instancias[$id])) {
            if (!isset($this->definiciones[$id])) {
                throw new \RuntimeException("Servicio '{$id}' no registrado");
            }
            $this->instancias[$id] = ($this->definiciones[$id])($this);
        }
        return $this->instancias[$id];
    }
}

// Configuración
$contenedor = new Contenedor();

$contenedor->set(PDO::class, fn() => new PDO('mysql:host=localhost;dbname=app', 'root', ''));

$contenedor->set(RepositorioProducto::class, fn(Contenedor $c) =>
    new RepositorioProductoMySQL($c->get(PDO::class))
);

$contenedor->set(ServicioProducto::class, fn(Contenedor $c) =>
    new ServicioProducto($c->get(RepositorioProducto::class))
);

// Uso: se resuelven las dependencias automáticamente
$servicio = $contenedor->get(ServicioProducto::class);
```

> **Tip:** En la práctica, usa contenedores de frameworks como el de Laravel o Symfony que ofrecen auto-wiring y configuración avanzada.

---

## Resumen

- **Singleton** garantiza una sola instancia; úsalo con moderación.
- **Factory Method** encapsula la creación de objetos relacionados.
- **Abstract Factory** crea familias de objetos sin especificar clases concretas.
- **Strategy** permite intercambiar algoritmos en tiempo de ejecución.
- **Observer** desacopla componentes con un sistema de eventos.
- **Decorator** añade comportamiento sin modificar la clase original.
- **Repository** abstrae el acceso a datos como una colección.
- **Dependency Injection Container** gestiona la creación y resolución de dependencias.
- Los patrones de diseño son herramientas, no reglas: aplícalos solo donde aporten claridad y flexibilidad.
