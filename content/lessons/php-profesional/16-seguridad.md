# Seguridad en PHP

La seguridad es uno de los pilares fundamentales en el desarrollo web profesional. PHP, al ser uno de los lenguajes más utilizados en la web, es un objetivo frecuente de ataques. En esta lección aprenderás a proteger tus aplicaciones contra las vulnerabilidades más comunes.

---

## 1. Prevención de SQL Injection

La inyección SQL es una de las vulnerabilidades más peligrosas. Ocurre cuando datos del usuario se insertan directamente en una consulta SQL sin sanitizar.

### Ejemplo vulnerable (NUNCA hagas esto)

```php
// ❌ VULNERABLE a SQL Injection
$usuario = $_GET['usuario'];
$query = "SELECT * FROM usuarios WHERE nombre = '$usuario'";
$resultado = mysqli_query($conexion, $query);
```

Un atacante podría enviar `' OR '1'='1` como valor y obtener todos los registros.

### Solución: Consultas preparadas con PDO

```php
// ✅ SEGURO: Uso de consultas preparadas
$pdo = new PDO('mysql:host=localhost;dbname=miapp', 'user', 'pass', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false, // Importante: desactivar emulación
]);

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE nombre = :nombre AND rol = :rol');
$stmt->execute([
    ':nombre' => $nombreUsuario,
    ':rol' => $rol,
]);

$usuarios = $stmt->fetchAll();
```

### Consultas preparadas con MySQLi

```php
// ✅ SEGURO con MySQLi
$stmt = $conexion->prepare('SELECT * FROM productos WHERE precio > ? AND categoria = ?');
$stmt->bind_param('ds', $precioMinimo, $categoria); // d = double, s = string
$stmt->execute();

$resultado = $stmt->get_result();
while ($fila = $resultado->fetch_assoc()) {
    echo $fila['nombre'];
}
```

> **Tip profesional:** Siempre usa `PDO::ATTR_EMULATE_PREPARES => false` para que las consultas preparadas se ejecuten de forma nativa en el servidor de base de datos, no emuladas por PHP.

---

## 2. Prevención de XSS (Cross-Site Scripting)

El XSS permite a un atacante inyectar scripts maliciosos en páginas vistas por otros usuarios.

### Tipos de XSS

- **Reflejado:** El script viene en la URL o formulario y se refleja en la respuesta.
- **Almacenado:** El script se guarda en la base de datos y se muestra a otros usuarios.
- **Basado en DOM:** El script se ejecuta manipulando el DOM del navegador.

### Escapar la salida con `htmlspecialchars`

```php
// ✅ Escapar siempre los datos antes de mostrarlos en HTML
function escapar(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Uso en una vista
echo '<p>Bienvenido, ' . escapar($nombreUsuario) . '</p>';
echo '<input type="text" value="' . escapar($valorInput) . '">';
```

### Escapar en diferentes contextos

```php
// En atributos HTML
echo '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">Enlace</a>';

// En JavaScript embebido (mejor evitar, usar data attributes)
echo '<div data-config="' . htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8') . '"></div>';

// Para URLs, validar el esquema
function urlSegura(string $url): string
{
    $parsed = parse_url($url);
    if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
        return '#';
    }
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}
```

> **Tip:** Nunca confíes en `strip_tags()` como única defensa contra XSS. Usa siempre `htmlspecialchars()` para escapar la salida.

---

## 3. Protección contra CSRF (Cross-Site Request Forgery)

CSRF engaña al usuario para que ejecute acciones no deseadas en un sitio donde está autenticado.

### Implementar tokens CSRF

```php
// Generar token CSRF al iniciar sesión o cargar el formulario
session_start();

function generarTokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarTokenCsrf(string $token): bool
{
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
```

### Uso en formularios

```php
// En el formulario HTML
echo '<form method="POST" action="/perfil/actualizar">';
echo '<input type="hidden" name="csrf_token" value="' . escapar(generarTokenCsrf()) . '">';
echo '<input type="text" name="nombre" value="' . escapar($nombre) . '">';
echo '<button type="submit">Guardar</button>';
echo '</form>';

// En el controlador que procesa el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCsrf($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Token CSRF inválido. Posible ataque CSRF.');
    }

    // Procesar el formulario de forma segura
    actualizarPerfil($_POST['nombre']);
}
```

### Token CSRF por solicitud (más seguro)

```php
function generarTokenCsrfUnico(): string
{
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][] = $token;

    // Mantener solo los últimos 10 tokens
    if (count($_SESSION['csrf_tokens']) > 10) {
        array_shift($_SESSION['csrf_tokens']);
    }

    return $token;
}

function validarYConsumirToken(string $token): bool
{
    $index = array_search($token, $_SESSION['csrf_tokens'] ?? [], true);
    if ($index === false) {
        return false;
    }
    unset($_SESSION['csrf_tokens'][$index]);
    return true;
}
```

---

## 4. Hashing de contraseñas

Nunca almacenes contraseñas en texto plano ni uses MD5 o SHA1.

### Usar `password_hash()` y `password_verify()`

```php
// ✅ Hashear la contraseña al registrar un usuario
$contrasena = $_POST['contrasena'];
$hash = password_hash($contrasena, PASSWORD_BCRYPT, ['cost' => 12]);

// Guardar $hash en la base de datos
$stmt = $pdo->prepare('INSERT INTO usuarios (email, contrasena) VALUES (:email, :contrasena)');
$stmt->execute([
    ':email' => $email,
    ':contrasena' => $hash,
]);
```

### Verificar contraseña al iniciar sesión

```php
// ✅ Verificar la contraseña
$stmt = $pdo->prepare('SELECT id, contrasena FROM usuarios WHERE email = :email');
$stmt->execute([':email' => $emailIngresado]);
$usuario = $stmt->fetch();

if ($usuario && password_verify($contrasenaIngresada, $usuario['contrasena'])) {
    // Verificar si el hash necesita re-hashing (cambio de algoritmo o cost)
    if (password_needs_rehash($usuario['contrasena'], PASSWORD_BCRYPT, ['cost' => 12])) {
        $nuevoHash = password_hash($contrasenaIngresada, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare('UPDATE usuarios SET contrasena = :hash WHERE id = :id');
        $stmt->execute([':hash' => $nuevoHash, ':id' => $usuario['id']]);
    }

    session_regenerate_id(true); // Prevenir session fixation
    $_SESSION['usuario_id'] = $usuario['id'];
} else {
    echo 'Credenciales inválidas.'; // Mensaje genérico
}
```

> **Tip:** Usa `PASSWORD_DEFAULT` en lugar de `PASSWORD_BCRYPT` para que PHP seleccione automáticamente el mejor algoritmo disponible en futuras versiones.

---

## 5. Validación y sanitización de entrada

### Funciones de filtrado de PHP

```php
// Validar email
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if ($email === false) {
    $errores[] = 'Email inválido.';
}

// Sanitizar y validar entero
$edad = filter_input(INPUT_POST, 'edad', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 120],
]);

// Sanitizar string
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);

// Validar URL
$sitioWeb = filter_input(INPUT_POST, 'sitio_web', FILTER_VALIDATE_URL);
```

### Clase de validación personalizada

```php
class Validador
{
    private array $errores = [];

    public function requerido(string $campo, mixed $valor): self
    {
        if (empty($valor) && $valor !== '0') {
            $this->errores[$campo] = "El campo {$campo} es obligatorio.";
        }
        return $this;
    }

    public function longitudMaxima(string $campo, string $valor, int $max): self
    {
        if (mb_strlen($valor) > $max) {
            $this->errores[$campo] = "El campo {$campo} no puede superar los {$max} caracteres.";
        }
        return $this;
    }

    public function esEmail(string $campo, string $valor): self
    {
        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $this->errores[$campo] = 'El email no es válido.';
        }
        return $this;
    }

    public function tieneErrores(): bool
    {
        return count($this->errores) > 0;
    }

    public function obtenerErrores(): array
    {
        return $this->errores;
    }
}

// Uso
$validador = new Validador();
$validador
    ->requerido('nombre', $_POST['nombre'] ?? '')
    ->longitudMaxima('nombre', $_POST['nombre'] ?? '', 100)
    ->esEmail('email', $_POST['email'] ?? '');

if ($validador->tieneErrores()) {
    echo json_encode(['errores' => $validador->obtenerErrores()]);
}
```

---

## 6. CORS (Cross-Origin Resource Sharing)

CORS controla qué dominios pueden acceder a tu API desde el navegador.

```php
// Configurar cabeceras CORS para una API
function configurarCors(): void
{
    $origenesPermitidos = ['https://miapp.com', 'https://admin.miapp.com'];
    $origen = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origen, $origenesPermitidos, true)) {
        header("Access-Control-Allow-Origin: {$origen}");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400'); // Cache preflight por 24h
        header('Access-Control-Allow-Credentials: true');
    }

    // Responder a solicitudes preflight (OPTIONS)
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

configurarCors();
```

> **Tip:** Nunca uses `Access-Control-Allow-Origin: *` junto con `Access-Control-Allow-Credentials: true`. Es inseguro y los navegadores lo bloquearán.

---

## 7. Content-Security-Policy (CSP)

CSP es una capa adicional de seguridad que ayuda a prevenir XSS y otros ataques de inyección de contenido.

```php
// Configurar Content-Security-Policy
function establecerCsp(): void
{
    $nonce = base64_encode(random_bytes(16));

    $csp = implode('; ', [
        "default-src 'self'",
        "script-src 'self' 'nonce-{$nonce}'",
        "style-src 'self' 'unsafe-inline'",  // Idealmente también con nonce
        "img-src 'self' data: https:",
        "font-src 'self' https://fonts.gstatic.com",
        "connect-src 'self' https://api.miapp.com",
        "frame-ancestors 'none'",             // Previene clickjacking
        "base-uri 'self'",
        "form-action 'self'",
    ]);

    header("Content-Security-Policy: {$csp}");

    // Guardar el nonce para usarlo en scripts inline
    define('CSP_NONCE', $nonce);
}

establecerCsp();

// En el HTML, usar el nonce para scripts inline permitidos
echo '<script nonce="' . CSP_NONCE . '">console.log("Script permitido");</script>';
```

### Cabeceras de seguridad adicionales

```php
function cabecerasDeSeguridad(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 0');  // Desactivado: CSP es mejor
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
```

---

## Resumen

| Vulnerabilidad   | Prevención principal                        |
|------------------|---------------------------------------------|
| SQL Injection    | Consultas preparadas (PDO / MySQLi)         |
| XSS              | `htmlspecialchars()` + CSP                   |
| CSRF             | Tokens CSRF en formularios                  |
| Contraseñas      | `password_hash()` / `password_verify()`     |
| Entrada inválida | `filter_input()` + validación personalizada |
| CORS             | Whitelist de orígenes permitidos            |
| Inyección HTML   | Content-Security-Policy con nonces          |

> **Regla de oro:** Nunca confíes en los datos del usuario. Valida la entrada, escapa la salida y usa consultas preparadas siempre.
