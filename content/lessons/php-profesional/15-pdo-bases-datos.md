# PDO y Bases de Datos en PHP

PDO (PHP Data Objects) es la extensión estándar para acceder a bases de datos en PHP. Proporciona una interfaz consistente, segura y orientada a objetos para trabajar con múltiples motores de bases de datos.

---

## 1. Conexión con PDO

```php
$dsn = 'mysql:host=localhost;dbname=miapp;charset=utf8mb4';
$usuario = 'root';
$password = 'secreto';

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $usuario, $password, $opciones);
} catch (PDOException $e) {
    throw new RuntimeException('Error de conexión: ' . $e->getMessage());
}
```

### Opciones recomendadas explicadas

| Opción | Valor | Descripción |
|---|---|---|
| `ERRMODE_EXCEPTION` | Excepciones | Lanza excepciones en vez de errores silenciosos |
| `FETCH_ASSOC` | Arrays asociativos | Resultado como `['columna' => valor]` |
| `EMULATE_PREPARES = false` | Preparadas nativas | El servidor MySQL prepara las consultas (más seguro) |

### Conexión a otros motores

```php
// PostgreSQL
$pdo = new PDO('pgsql:host=localhost;dbname=miapp', 'user', 'pass');

// SQLite
$pdo = new PDO('sqlite:/ruta/database.sqlite');

// SQL Server
$pdo = new PDO('sqlsrv:Server=localhost;Database=miapp', 'user', 'pass');
```

---

## 2. Prepared Statements (Consultas preparadas)

Las consultas preparadas son **la forma correcta** de ejecutar SQL con datos del usuario. Previenen inyección SQL.

```php
// NUNCA hacer esto (vulnerable a SQL injection):
$sql = "SELECT * FROM usuarios WHERE email = '$email'"; // ¡PELIGRO!

// SIEMPRE usar prepared statements:
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
$usuario = $stmt->fetch();
```

### Placeholders posicionales (?)

```php
$stmt = $pdo->prepare(
    'INSERT INTO productos (nombre, precio, stock) VALUES (?, ?, ?)'
);
$stmt->execute(['Teclado mecánico', 1500.00, 25]);

echo "Producto insertado con ID: " . $pdo->lastInsertId();
```

### Placeholders nombrados (:nombre)

```php
$stmt = $pdo->prepare(
    'SELECT * FROM productos
     WHERE precio BETWEEN :min AND :max
     AND categoria = :cat'
);

$stmt->execute([
    ':min' => 100,
    ':max' => 5000,
    ':cat' => 'electrónica',
]);

$productos = $stmt->fetchAll();
```

---

## 3. Parameter Binding (Enlace de parámetros)

`bindValue` y `bindParam` ofrecen más control que `execute()` con un array.

### bindValue — Enlaza un valor

```php
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE edad > :edad AND activo = :activo');

$stmt->bindValue(':edad', 18, PDO::PARAM_INT);
$stmt->bindValue(':activo', true, PDO::PARAM_BOOL);
$stmt->execute();
```

### bindParam — Enlaza una referencia a variable

```php
$stmt = $pdo->prepare('INSERT INTO logs (nivel, mensaje) VALUES (:nivel, :mensaje)');

$stmt->bindParam(':nivel', $nivel, PDO::PARAM_STR);
$stmt->bindParam(':mensaje', $mensaje, PDO::PARAM_STR);

$registros = [
    ['INFO', 'Sistema iniciado'],
    ['WARNING', 'Memoria baja'],
    ['ERROR', 'Conexión fallida'],
];

foreach ($registros as [$nivel, $mensaje]) {
    $stmt->execute(); // Usa los valores actuales de $nivel y $mensaje
}
```

### Tipos de datos PDO

| Constante | Tipo |
|---|---|
| `PDO::PARAM_STR` | String (por defecto) |
| `PDO::PARAM_INT` | Entero |
| `PDO::PARAM_BOOL` | Booleano |
| `PDO::PARAM_NULL` | Null |
| `PDO::PARAM_LOB` | Large Object (binario) |

---

## 4. Fetch Modes (Modos de lectura)

PDO ofrece múltiples formas de obtener resultados.

```php
$stmt = $pdo->query('SELECT id, nombre, email FROM usuarios');

// FETCH_ASSOC — Array asociativo (recomendado)
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
// ['id' => 1, 'nombre' => 'Ana', 'email' => 'ana@mail.com']

// FETCH_OBJ — Objeto stdClass
$usuario = $stmt->fetch(PDO::FETCH_OBJ);
echo $usuario->nombre; // Ana

// FETCH_NUM — Array numérico
$usuario = $stmt->fetch(PDO::FETCH_NUM);
// [1, 'Ana', 'ana@mail.com']

// FETCH_COLUMN — Una sola columna
$stmt = $pdo->query('SELECT email FROM usuarios');
$emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
// ['ana@mail.com', 'luis@mail.com', 'carlos@mail.com']

// FETCH_KEY_PAIR — Dos columnas como clave => valor
$stmt = $pdo->query('SELECT id, nombre FROM usuarios');
$mapa = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
// [1 => 'Ana', 2 => 'Luis', 3 => 'Carlos']
```

### FETCH_CLASS — Mapear a una clase

```php
class Usuario {
    public int $id;
    public string $nombre;
    public string $email;

    public function nombreCompleto(): string {
        return strtoupper($this->nombre);
    }
}

$stmt = $pdo->query('SELECT id, nombre, email FROM usuarios');
$usuarios = $stmt->fetchAll(PDO::FETCH_CLASS, Usuario::class);

foreach ($usuarios as $u) {
    echo $u->nombreCompleto(); // ANA, LUIS, etc.
}
```

---

## 5. Transacciones

Las transacciones agrupan operaciones para que se ejecuten todas o ninguna (atomicidad).

```php
try {
    $pdo->beginTransaction();

    // Descontar del inventario
    $stmt = $pdo->prepare('UPDATE productos SET stock = stock - ? WHERE id = ?');
    $stmt->execute([2, $productoId]);

    // Registrar la venta
    $stmt = $pdo->prepare(
        'INSERT INTO ventas (producto_id, cantidad, total, fecha)
         VALUES (?, ?, ?, NOW())'
    );
    $stmt->execute([$productoId, 2, $total]);

    // Actualizar el saldo del cliente
    $stmt = $pdo->prepare('UPDATE clientes SET saldo = saldo - ? WHERE id = ?');
    $stmt->execute([$total, $clienteId]);

    $pdo->commit();
    echo "Venta procesada exitosamente";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
```

### Transacción con verificación de stock

```php
function procesarCompra(PDO $pdo, int $productoId, int $cantidad): bool {
    $pdo->beginTransaction();

    try {
        // Bloquear la fila para lectura (FOR UPDATE)
        $stmt = $pdo->prepare(
            'SELECT stock, precio FROM productos WHERE id = ? FOR UPDATE'
        );
        $stmt->execute([$productoId]);
        $producto = $stmt->fetch();

        if (!$producto || $producto['stock'] < $cantidad) {
            $pdo->rollBack();
            return false; // Stock insuficiente
        }

        $total = $producto['precio'] * $cantidad;

        $pdo->prepare('UPDATE productos SET stock = stock - ? WHERE id = ?')
            ->execute([$cantidad, $productoId]);

        $pdo->prepare('INSERT INTO ventas (producto_id, cantidad, total) VALUES (?, ?, ?)')
            ->execute([$productoId, $cantidad, $total]);

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
```

---

## 6. Error Modes (Modos de error)

```php
// ERRMODE_SILENT — Sin errores (peligroso, hay que verificar manualmente)
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$stmt = $pdo->query('SELECT * FROM tabla_inexistente');
// $stmt es false, sin excepción

// ERRMODE_WARNING — Genera un warning PHP
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// ERRMODE_EXCEPTION — Lanza excepciones (RECOMENDADO)
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

> **Siempre usa `ERRMODE_EXCEPTION` en producción.** Permite manejar errores con try/catch de forma limpia.

### Manejo de errores en producción

```php
try {
    $stmt = $pdo->prepare('INSERT INTO usuarios (email) VALUES (?)');
    $stmt->execute([$email]);
} catch (PDOException $e) {
    // Código de error SQLSTATE
    if ($e->getCode() === '23000') {
        echo "El email ya está registrado";
    } else {
        // Loguear el error real, mostrar mensaje genérico
        error_log("Error BD: " . $e->getMessage());
        echo "Error interno. Intenta más tarde.";
    }
}
```

---

## 7. Patrón de Migraciones

Las migraciones permiten versionar los cambios en la estructura de la base de datos.

```php
class MigrationRunner {
    private PDO $pdo;
    private string $directorio;

    public function __construct(PDO $pdo, string $directorio) {
        $this->pdo = $pdo;
        $this->directorio = $directorio;
        $this->crearTablaMigraciones();
    }

    private function crearTablaMigraciones(): void {
        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ');
    }

    public function ejecutadas(): array {
        $stmt = $this->pdo->query('SELECT filename FROM migrations ORDER BY id');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function migrar(): void {
        $ejecutadas = $this->ejecutadas();
        $archivos = glob($this->directorio . '/*.sql');
        sort($archivos);

        foreach ($archivos as $archivo) {
            $nombre = basename($archivo);
            if (in_array($nombre, $ejecutadas)) continue;

            echo "Ejecutando: $nombre\n";

            $sql = file_get_contents($archivo);
            $this->pdo->beginTransaction();

            try {
                $this->pdo->exec($sql);
                $this->pdo->prepare('INSERT INTO migrations (filename) VALUES (?)')
                    ->execute([$nombre]);
                $this->pdo->commit();
                echo "  ✓ Completada\n";
            } catch (Exception $e) {
                $this->pdo->rollBack();
                echo "  ✗ Error: " . $e->getMessage() . "\n";
                break;
            }
        }
    }
}

// Uso
$runner = new MigrationRunner($pdo, __DIR__ . '/migrations');
$runner->migrar();
```

### Ejemplo de archivo de migración

```sql
-- migrations/001_crear_usuarios.sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_usuarios_email ON usuarios(email);
```

---

## 8. Query Builder simple

Un query builder permite construir consultas SQL de forma programática y segura.

```php
class QueryBuilder {
    private PDO $pdo;
    private string $tabla;
    private array $condiciones = [];
    private array $parametros = [];
    private ?string $orden = null;
    private ?int $limite = null;
    private ?int $offset = null;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function tabla(string $tabla): static {
        $this->tabla = $tabla;
        return $this;
    }

    public function where(string $columna, string $operador, mixed $valor): static {
        $placeholder = ':w' . count($this->condiciones);
        $this->condiciones[] = "$columna $operador $placeholder";
        $this->parametros[$placeholder] = $valor;
        return $this;
    }

    public function orderBy(string $columna, string $direccion = 'ASC'): static {
        $this->orden = "$columna $direccion";
        return $this;
    }

    public function limit(int $limite, int $offset = 0): static {
        $this->limite = $limite;
        $this->offset = $offset;
        return $this;
    }

    public function select(string $columnas = '*'): array {
        $sql = "SELECT $columnas FROM {$this->tabla}";

        if (!empty($this->condiciones)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->condiciones);
        }

        if ($this->orden) {
            $sql .= " ORDER BY {$this->orden}";
        }

        if ($this->limite !== null) {
            $sql .= " LIMIT {$this->limite} OFFSET {$this->offset}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->parametros);

        return $stmt->fetchAll();
    }

    public function insert(array $datos): int {
        $columnas = implode(', ', array_keys($datos));
        $placeholders = implode(', ', array_map(fn($k) => ":$k", array_keys($datos)));

        $sql = "INSERT INTO {$this->tabla} ($columnas) VALUES ($placeholders)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($datos);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(array $datos): int {
        $sets = array_map(fn($k) => "$k = :set_$k", array_keys($datos));
        $sql = "UPDATE {$this->tabla} SET " . implode(', ', $sets);

        if (!empty($this->condiciones)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->condiciones);
        }

        $params = $this->parametros;
        foreach ($datos as $k => $v) {
            $params[":set_$k"] = $v;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function delete(): int {
        $sql = "DELETE FROM {$this->tabla}";

        if (!empty($this->condiciones)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->condiciones);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->parametros);

        return $stmt->rowCount();
    }
}
```

### Uso del Query Builder

```php
$qb = new QueryBuilder($pdo);

// SELECT
$activos = $qb->tabla('usuarios')
    ->where('activo', '=', true)
    ->where('edad', '>=', 18)
    ->orderBy('nombre')
    ->limit(10)
    ->select();

// INSERT
$id = (new QueryBuilder($pdo))
    ->tabla('productos')
    ->insert([
        'nombre' => 'Monitor 4K',
        'precio' => 8500,
        'stock' => 15,
    ]);

// UPDATE
(new QueryBuilder($pdo))
    ->tabla('productos')
    ->where('id', '=', $id)
    ->update(['precio' => 7999, 'stock' => 20]);

// DELETE
(new QueryBuilder($pdo))
    ->tabla('sesiones')
    ->where('expires_at', '<', date('Y-m-d H:i:s'))
    ->delete();
```

---

## 9. Clase de conexión Singleton

```php
class Database {
    private static ?PDO $instancia = null;

    public static function getConnection(): PDO {
        if (self::$instancia === null) {
            $config = require __DIR__ . '/config/database.php';

            self::$instancia = new PDO(
                $config['dsn'],
                $config['usuario'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$instancia;
    }

    // Evitar clonación e instanciación externa
    private function __construct() {}
    private function __clone() {}
}

// Uso
$pdo = Database::getConnection();
```

---

## Resumen

| Concepto | Descripción |
|---|---|
| `new PDO(dsn, user, pass)` | Crear conexión |
| `prepare()` + `execute()` | Consultas preparadas (seguro) |
| `bindValue` / `bindParam` | Enlace de parámetros tipado |
| `beginTransaction` / `commit` / `rollBack` | Transacciones |
| `FETCH_ASSOC`, `FETCH_CLASS` | Modos de lectura |
| `ERRMODE_EXCEPTION` | Manejo de errores con excepciones |
| Migraciones | Versionado de esquema |
| Query Builder | Construcción programática de SQL |

PDO es el estándar para acceder a bases de datos en PHP moderno. Dominar estas técnicas es esencial para construir aplicaciones seguras y mantenibles.
