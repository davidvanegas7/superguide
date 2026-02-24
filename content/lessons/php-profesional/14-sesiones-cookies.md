# Sesiones y Cookies en PHP

Las sesiones y cookies son mecanismos fundamentales para mantener estado entre peticiones HTTP. En esta lección aprenderás a usarlas de forma segura y profesional.

---

## 1. ¿Cómo funcionan las sesiones?

HTTP es un protocolo sin estado. Las sesiones permiten asociar datos a un usuario a lo largo de múltiples peticiones.

**Flujo:**
1. El servidor crea un ID de sesión único.
2. El ID se envía al cliente como cookie (`PHPSESSID`).
3. En cada petición, el cliente envía el ID.
4. El servidor recupera los datos asociados a ese ID.

```php
// Iniciar la sesión (DEBE ir antes de cualquier salida)
session_start();

// Guardar datos
$_SESSION['usuario'] = 'Carlos';
$_SESSION['rol'] = 'admin';
$_SESSION['login_time'] = time();

// Leer datos
echo $_SESSION['usuario']; // Carlos

// Verificar si existe
if (isset($_SESSION['usuario'])) {
    echo "Sesión activa para: " . $_SESSION['usuario'];
}
```

---

## 2. Configuración de sesiones

### Configurar antes de session_start

```php
// Configuración recomendada para producción
ini_set('session.cookie_httponly', 1);    // No accesible por JavaScript
ini_set('session.cookie_secure', 1);      // Solo HTTPS
ini_set('session.cookie_samesite', 'Lax');// Protección CSRF
ini_set('session.use_strict_mode', 1);    // Rechazar IDs no generados por el servidor
ini_set('session.use_only_cookies', 1);   // No aceptar ID por URL
ini_set('session.gc_maxlifetime', 1800);  // 30 minutos

session_start();
```

### Usando session_set_cookie_params (PHP 7.3+)

```php
session_set_cookie_params([
    'lifetime' => 0,          // Hasta cerrar el navegador
    'path' => '/',
    'domain' => '.midominio.com',
    'secure' => true,         // Solo HTTPS
    'httponly' => true,        // Sin acceso JavaScript
    'samesite' => 'Lax',      // Protección CSRF
]);

session_start();
```

---

## 3. Gestión de sesiones

### Destruir una sesión (Logout)

```php
function cerrarSesion(): void {
    // 1. Limpiar datos de sesión
    $_SESSION = [];

    // 2. Eliminar la cookie de sesión
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // 3. Destruir el archivo de sesión
    session_destroy();
}
```

### Regenerar ID de sesión

Importante para prevenir ataques de fijación de sesión (session fixation).

```php
// Después del login exitoso, SIEMPRE regenerar
function loginExitoso(array $usuario): void {
    session_regenerate_id(true); // true = eliminar sesión anterior

    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['ultimo_acceso'] = time();
}
```

### Control de expiración

```php
function verificarSesion(): bool {
    if (!isset($_SESSION['ultimo_acceso'])) {
        return false;
    }

    $tiempoInactivo = 1800; // 30 minutos

    if (time() - $_SESSION['ultimo_acceso'] > $tiempoInactivo) {
        cerrarSesion();
        return false;
    }

    // Verificar que el user agent no cambió (protección básica)
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        cerrarSesion();
        return false;
    }

    $_SESSION['ultimo_acceso'] = time();
    return true;
}
```

---

## 4. SessionHandlerInterface: Handlers personalizados

PHP permite cambiar dónde y cómo se almacenan las sesiones implementando `SessionHandlerInterface`.

```php
class DatabaseSessionHandler implements SessionHandlerInterface {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function open(string $path, string $name): bool {
        return true; // Conexión ya establecida
    }

    public function close(): bool {
        return true;
    }

    public function read(string $id): string|false {
        $stmt = $this->db->prepare(
            'SELECT data FROM sessions WHERE id = ? AND expires_at > NOW()'
        );
        $stmt->execute([$id]);
        $result = $stmt->fetchColumn();

        return $result !== false ? $result : '';
    }

    public function write(string $id, string $data): bool {
        $stmt = $this->db->prepare(
            'REPLACE INTO sessions (id, data, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))'
        );
        return $stmt->execute([$id, $data]);
    }

    public function destroy(string $id): bool {
        $stmt = $this->db->prepare('DELETE FROM sessions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false {
        $stmt = $this->db->prepare('DELETE FROM sessions WHERE expires_at < NOW()');
        $stmt->execute();
        return $stmt->rowCount();
    }
}

// Registrar el handler
$handler = new DatabaseSessionHandler($pdo);
session_set_save_handler($handler, true);
session_start();
```

### Tabla SQL para el handler

```sql
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    data TEXT NOT NULL,
    expires_at DATETIME NOT NULL,
    INDEX idx_expires (expires_at)
);
```

---

## 5. Cookies: Fundamentos

Las cookies son pequeños datos que el servidor envía al navegador y que se reenvían en cada petición.

```php
// Establecer una cookie (debe ir ANTES de cualquier salida HTML)
setcookie('idioma', 'es', time() + (86400 * 30), '/'); // 30 días

// Leer una cookie
$idioma = $_COOKIE['idioma'] ?? 'en';

// Eliminar una cookie
setcookie('idioma', '', time() - 3600, '/');
```

---

## 6. Opciones avanzadas de cookies

### Sintaxis moderna (PHP 7.3+)

```php
setcookie('preferencias', json_encode(['tema' => 'oscuro']), [
    'expires' => time() + (86400 * 365),  // 1 año
    'path' => '/',
    'domain' => '.midominio.com',
    'secure' => true,      // Solo enviada por HTTPS
    'httponly' => true,     // No accesible por JavaScript
    'samesite' => 'Strict' // Protección contra CSRF
]);
```

### Atributos de seguridad explicados

| Atributo | Valor | Descripción |
|---|---|---|
| `secure` | `true` | Solo se envía por conexiones HTTPS |
| `httponly` | `true` | No se puede leer con `document.cookie` en JS |
| `samesite` | `Strict` | Nunca se envía en peticiones cross-site |
| `samesite` | `Lax` | Se envía en navegación top-level (enlaces) |
| `samesite` | `None` | Se envía siempre (requiere `secure: true`) |

> **Tip:** Para la mayoría de aplicaciones, usa `SameSite=Lax`. Usa `Strict` para cookies muy sensibles como tokens de autenticación.

---

## 7. Cookie de "Recuérdame" segura

```php
class RememberMe {
    private PDO $db;

    public function crear(int $userId): void {
        $selector = bin2hex(random_bytes(16));
        $validator = random_bytes(32);
        $hashedValidator = hash('sha256', $validator);
        $expira = date('Y-m-d H:i:s', time() + 86400 * 30);

        $stmt = $this->db->prepare(
            'INSERT INTO auth_tokens (selector, validator, user_id, expires_at)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$selector, $hashedValidator, $userId, $expira]);

        $token = $selector . ':' . bin2hex($validator);

        setcookie('remember_me', $token, [
            'expires' => time() + (86400 * 30),
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public function verificar(): ?int {
        if (!isset($_COOKIE['remember_me'])) return null;

        [$selector, $validatorHex] = explode(':', $_COOKIE['remember_me']);
        $validator = hex2bin($validatorHex);

        $stmt = $this->db->prepare(
            'SELECT * FROM auth_tokens WHERE selector = ? AND expires_at > NOW()'
        );
        $stmt->execute([$selector]);
        $token = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$token) return null;

        if (hash_equals($token['validator'], hash('sha256', $validator))) {
            return (int) $token['user_id'];
        }

        return null;
    }
}
```

---

## 8. Protección CSRF con tokens

Cross-Site Request Forgery (CSRF) ocurre cuando un sitio malicioso envía peticiones en nombre del usuario autenticado.

### Generar y validar token CSRF

```php
class CsrfProtection {
    public static function generarToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function campo(): string {
        $token = self::generarToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    public static function validar(string $token): bool {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function verificarPeticion(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf_token'] ?? '';
            if (!self::validar($token)) {
                http_response_code(403);
                die('Token CSRF inválido');
            }
        }
    }
}
```

### Uso en formularios

```php
// En el formulario HTML
session_start();
?>
<form method="POST" action="/perfil/actualizar">
    <?= CsrfProtection::campo() ?>
    <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>">
    <button type="submit">Guardar</button>
</form>

<?php
// En el procesamiento del formulario
session_start();
CsrfProtection::verificarPeticion();

// Si llegamos aquí, el token es válido
$nombre = $_POST['nombre'];
```

### Token CSRF por petición (más seguro)

```php
class CsrfPerRequest {
    public static function generarToken(): string {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][] = $token;

        // Mantener solo los últimos 10 tokens
        if (count($_SESSION['csrf_tokens']) > 10) {
            array_shift($_SESSION['csrf_tokens']);
        }

        return $token;
    }

    public static function validar(string $token): bool {
        $tokens = $_SESSION['csrf_tokens'] ?? [];
        $index = array_search($token, $tokens, true);

        if ($index === false) return false;

        // Eliminar token usado (single-use)
        unset($_SESSION['csrf_tokens'][$index]);
        return true;
    }
}
```

---

## 9. Flash Messages con sesiones

Los mensajes flash se muestran una sola vez y luego se eliminan.

```php
class FlashMessage {
    public static function set(string $tipo, string $mensaje): void {
        $_SESSION['flash_messages'][] = [
            'tipo' => $tipo,
            'mensaje' => $mensaje,
        ];
    }

    public static function get(): array {
        $mensajes = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']); // Eliminar después de leer
        return $mensajes;
    }

    public static function render(): string {
        $html = '';
        foreach (self::get() as $flash) {
            $tipo = htmlspecialchars($flash['tipo']);
            $msg = htmlspecialchars($flash['mensaje']);
            $html .= "<div class=\"alert alert-{$tipo}\">{$msg}</div>\n";
        }
        return $html;
    }
}

// Uso
FlashMessage::set('success', 'Perfil actualizado correctamente');
FlashMessage::set('error', 'No se pudo enviar el email');

// En la vista (después del redirect)
echo FlashMessage::render();
```

---

## 10. Buenas prácticas de seguridad

```php
// 1. Siempre configurar cookies seguras en producción
$esProduccion = getenv('APP_ENV') === 'production';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $esProduccion,
    'httponly' => true,
    'samesite' => 'Lax',
]);

// 2. Regenerar ID después del login
session_regenerate_id(true);

// 3. Nunca almacenar datos sensibles sin cifrar
// MAL:
$_SESSION['tarjeta'] = '4111-1111-1111-1111';

// BIEN: No almacenar datos de tarjeta en sesión
// O al menos cifrar si es absolutamente necesario
$_SESSION['tarjeta_cifrada'] = openssl_encrypt(
    $tarjeta,
    'aes-256-gcm',
    $clave,
    0,
    $iv,
    $tag
);

// 4. Validar la integridad de la sesión
$_SESSION['fingerprint'] = hash('sha256',
    $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']
);

// 5. Establecer encabezados de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
```

---

## Resumen

| Concepto | Uso |
|---|---|
| `session_start()` | Iniciar sesión |
| `$_SESSION` | Almacenar/leer datos de sesión |
| `session_regenerate_id()` | Prevenir fijación de sesión |
| `SessionHandlerInterface` | Handler de sesión personalizado |
| `setcookie()` | Crear/eliminar cookies |
| `httponly` / `secure` / `samesite` | Seguridad de cookies |
| Token CSRF | Protección contra falsificación de peticiones |
| Flash Messages | Mensajes de un solo uso |

La gestión segura de sesiones y cookies es esencial para cualquier aplicación web profesional en PHP.
