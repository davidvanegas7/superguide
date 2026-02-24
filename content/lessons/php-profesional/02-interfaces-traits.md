# Interfaces, Traits y Composición en PHP

PHP ofrece múltiples mecanismos para estructurar y reutilizar código: herencia, interfaces y traits. Saber cuándo usar cada uno es clave para un diseño sólido y mantenible.

---

## ¿Cuándo usar Herencia, Interface o Trait?

| Mecanismo   | Úsalo cuando...                                          |
|-------------|-----------------------------------------------------------|
| Herencia    | Existe una relación "es un" (un Perro **es un** Animal)   |
| Interface   | Quieres definir un contrato ("puede hacer X")             |
| Trait       | Necesitas compartir código entre clases no relacionadas    |

```php
// Herencia: relación jerárquica
class Animal { /* ... */ }
class Perro extends Animal { /* ... */ }

// Interface: contrato de comportamiento
interface Serializable {
    public function serializar(): string;
}

// Trait: reutilización de código
trait ConUuid {
    public string $uuid;
    public function generarUuid(): void {
        $this->uuid = bin2hex(random_bytes(16));
    }
}
```

> **Regla general:** Prefiere composición sobre herencia. Usa interfaces para definir contratos y traits para compartir implementación.

---

## Contratos con Interfaces

Una interface define **qué** debe hacer una clase, no **cómo**. Esto te permite programar contra abstracciones en lugar de implementaciones concretas.

```php
interface RepositorioUsuario
{
    public function buscarPorId(int $id): ?Usuario;
    public function guardar(Usuario $usuario): void;
    public function eliminar(int $id): void;
    public function todos(): array;
}

class RepositorioUsuarioMySQL implements RepositorioUsuario
{
    public function __construct(private PDO $conexion) {}

    public function buscarPorId(int $id): ?Usuario
    {
        $stmt = $this->conexion->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        return $datos ? new Usuario(...$datos) : null;
    }

    public function guardar(Usuario $usuario): void
    {
        // Implementación con MySQL
    }

    public function eliminar(int $id): void
    {
        $this->conexion->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
    }

    public function todos(): array
    {
        return $this->conexion->query('SELECT * FROM usuarios')->fetchAll();
    }
}

class RepositorioUsuarioEnMemoria implements RepositorioUsuario
{
    private array $usuarios = [];

    public function buscarPorId(int $id): ?Usuario
    {
        return $this->usuarios[$id] ?? null;
    }

    public function guardar(Usuario $usuario): void
    {
        $this->usuarios[$usuario->id] = $usuario;
    }

    public function eliminar(int $id): void
    {
        unset($this->usuarios[$id]);
    }

    public function todos(): array
    {
        return array_values($this->usuarios);
    }
}
```

Con este diseño, puedes intercambiar la implementación sin modificar el código que depende de `RepositorioUsuario`.

---

## Interfaces que Extienden Otras Interfaces

Las interfaces pueden extender una o más interfaces:

```php
interface Leible
{
    public function leer(int $id): mixed;
}

interface Escribible
{
    public function escribir(mixed $datos): void;
}

interface Almacenamiento extends Leible, Escribible
{
    public function existeArchivo(string $ruta): bool;
}

class AlmacenamientoLocal implements Almacenamiento
{
    public function leer(int $id): mixed { /* ... */ }
    public function escribir(mixed $datos): void { /* ... */ }
    public function existeArchivo(string $ruta): bool { /* ... */ }
}
```

---

## Constantes en Interfaces

Las interfaces pueden definir constantes:

```php
interface CodigosHttp
{
    const OK = 200;
    const NOT_FOUND = 404;
    const SERVER_ERROR = 500;
}

class Respuesta implements CodigosHttp
{
    public function estado(): int
    {
        return self::OK;
    }
}
```

---

## Traits en Profundidad

### Traits con Propiedades y Métodos Abstractos

Un trait puede exigir que la clase que lo use implemente ciertos métodos:

```php
trait Validable
{
    abstract protected function reglas(): array;

    public function validar(array $datos): array
    {
        $errores = [];
        foreach ($this->reglas() as $campo => $regla) {
            if ($regla === 'requerido' && empty($datos[$campo])) {
                $errores[] = "El campo {$campo} es obligatorio.";
            }
        }
        return $errores;
    }
}

class FormularioContacto
{
    use Validable;

    protected function reglas(): array
    {
        return [
            'nombre' => 'requerido',
            'email'  => 'requerido',
            'mensaje' => 'requerido',
        ];
    }
}

$form = new FormularioContacto();
$errores = $form->validar(['nombre' => '', 'email' => 'a@b.com', 'mensaje' => '']);
// ["El campo nombre es obligatorio.", "El campo mensaje es obligatorio."]
```

---

### Resolución de Conflictos entre Traits

Cuando dos traits definen un método con el mismo nombre, debes resolver el conflicto explícitamente:

```php
trait LogTexto
{
    public function log(string $mensaje): void
    {
        file_put_contents('app.log', $mensaje . PHP_EOL, FILE_APPEND);
    }
}

trait LogConsola
{
    public function log(string $mensaje): void
    {
        echo "[LOG] {$mensaje}\n";
    }
}

class Aplicacion
{
    use LogTexto, LogConsola {
        LogTexto::log as logArchivo;      // Renombrar
        LogConsola::log insteadof LogTexto; // Preferir LogConsola::log
    }
}

$app = new Aplicacion();
$app->log('Iniciando');        // Usa LogConsola::log
$app->logArchivo('Iniciando'); // Usa LogTexto::log
```

### Cambiar Visibilidad con Traits

Puedes cambiar la visibilidad de un método importado de un trait:

```php
trait Secreto
{
    public function revelar(): string
    {
        return 'dato secreto';
    }
}

class CajaFuerte
{
    use Secreto {
        revelar as private; // Ahora es privado
    }

    public function acceder(string $clave): string
    {
        if ($clave === '1234') {
            return $this->revelar();
        }
        return 'Acceso denegado';
    }
}
```

---

## Múltiples Traits Combinados

Es común combinar varios traits para componer funcionalidad:

```php
trait TieneNombre
{
    private string $nombre;
    public function getNombre(): string { return $this->nombre; }
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
}

trait TieneEmail
{
    private string $email;
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }
}

trait TieneTimestamps
{
    private DateTime $creadoEn;
    public function marcarCreacion(): void { $this->creadoEn = new DateTime(); }
    public function getCreadoEn(): DateTime { return $this->creadoEn; }
}

class Contacto
{
    use TieneNombre, TieneEmail, TieneTimestamps;

    public function __construct(string $nombre, string $email)
    {
        $this->setNombre($nombre);
        $this->setEmail($email);
        $this->marcarCreacion();
    }
}
```

---

## Adapter Pattern con Interfaces

El patrón Adapter usa interfaces para adaptar una clase existente a una interfaz esperada:

```php
interface PasarelaPago
{
    public function cobrar(float $monto, string $moneda): bool;
    public function reembolsar(string $transaccionId): bool;
}

// Librería externa con API diferente
class StripeSDK
{
    public function createCharge(int $amountCents, string $currency): object
    {
        // Lógica de Stripe
        return (object) ['id' => 'ch_123', 'status' => 'succeeded'];
    }

    public function refundCharge(string $chargeId): object
    {
        return (object) ['status' => 'refunded'];
    }
}

// Adapter: adapta StripeSDK a nuestra interfaz
class StripeAdapter implements PasarelaPago
{
    public function __construct(private StripeSDK $stripe) {}

    public function cobrar(float $monto, string $moneda): bool
    {
        $centavos = (int) ($monto * 100);
        $resultado = $this->stripe->createCharge($centavos, $moneda);
        return $resultado->status === 'succeeded';
    }

    public function reembolsar(string $transaccionId): bool
    {
        $resultado = $this->stripe->refundCharge($transaccionId);
        return $resultado->status === 'refunded';
    }
}

// Uso: el código solo conoce la interfaz
function procesarPago(PasarelaPago $pasarela, float $monto): void
{
    if ($pasarela->cobrar($monto, 'MXN')) {
        echo "Pago exitoso\n";
    }
}
```

---

## PHP No Tiene Implementaciones por Defecto en Interfaces

A diferencia de Java o C#, PHP **no soporta** métodos por defecto en interfaces. Si necesitas proporcionar implementación compartida junto a un contrato, combina una interface con un trait:

```php
interface Cacheable
{
    public function cacheKey(): string;
    public function cacheTTL(): int;
}

trait CacheableDefaults
{
    public function cacheTTL(): int
    {
        return 3600; // 1 hora por defecto
    }
}

class Producto implements Cacheable
{
    use CacheableDefaults;

    public function __construct(private int $id) {}

    public function cacheKey(): string
    {
        return "producto_{$this->id}";
    }
    // cacheTTL() ya viene del trait
}
```

---

## Resumen

- **Herencia** modela relaciones "es un" y permite compartir código entre padre e hijo.
- **Interfaces** definen contratos sin implementación; una clase puede implementar múltiples.
- **Traits** ofrecen reutilización horizontal de código entre clases sin relación jerárquica.
- Usa `insteadof` y `as` para resolver **conflictos entre traits**.
- Combina interfaces + traits para lograr contratos con implementaciones por defecto.
- El **Adapter Pattern** con interfaces permite integrar librerías externas sin acoplar tu código.
- Prefiere siempre **composición sobre herencia** para un diseño más flexible.
