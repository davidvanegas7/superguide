# Strings y Expresiones Regulares en PHP

El manejo de cadenas de texto y expresiones regulares es fundamental en cualquier aplicación PHP. En esta lección aprenderás desde funciones multibyte hasta patrones avanzados de regex.

---

## 1. Funciones Multibyte (mb_string)

PHP maneja cadenas como secuencias de bytes. Para textos con caracteres especiales (ñ, á, ü, emojis), necesitas las funciones `mb_*`.

```php
$texto = "Programación en español";

echo strlen($texto);      // 24 (bytes, ó ocupa 2 bytes)
echo mb_strlen($texto);   // 23 (caracteres reales)
```

### Funciones esenciales

```php
$frase = "Diseño técnico profesional";

// Convertir mayúsculas/minúsculas
echo mb_strtoupper($frase); // DISEÑO TÉCNICO PROFESIONAL
echo mb_strtolower($frase); // diseño técnico profesional

// Subcadena segura
echo mb_substr($frase, 0, 6); // Diseño

// Buscar posición
echo mb_strpos($frase, 'técnico'); // 7

// Detectar encoding
echo mb_detect_encoding($frase); // UTF-8

// Convertir encoding
$latin1 = mb_convert_encoding($frase, 'ISO-8859-1', 'UTF-8');
```

> **Tip:** Configura el encoding por defecto en tu aplicación:
> ```php
> mb_internal_encoding('UTF-8');
> ```

---

## 2. preg_match: Buscar patrones

`preg_match` busca la primera coincidencia de un patrón regex.

```php
$email = "usuario@ejemplo.com";

if (preg_match('/^[\w.+-]+@[\w-]+\.[\w.]+$/', $email)) {
    echo "Email válido";
}
```

### Capturar grupos

```php
$fecha = "2026-02-23";

preg_match('/(\d{4})-(\d{2})-(\d{2})/', $fecha, $matches);

echo $matches[0]; // 2026-02-23 (coincidencia completa)
echo $matches[1]; // 2026 (primer grupo)
echo $matches[2]; // 02
echo $matches[3]; // 23
```

### Grupos nombrados

```php
$log = "[2026-02-23 14:30:00] ERROR: Conexión fallida";

$patron = '/\[(?P<fecha>[\d-]+)\s(?P<hora>[\d:]+)\]\s(?P<nivel>\w+):\s(?P<mensaje>.+)/';

if (preg_match($patron, $log, $matches)) {
    echo $matches['fecha'];   // 2026-02-23
    echo $matches['nivel'];   // ERROR
    echo $matches['mensaje']; // Conexión fallida
}
```

### preg_match_all: Todas las coincidencias

```php
$html = '<a href="https://php.net">PHP</a> y <a href="https://laravel.com">Laravel</a>';

preg_match_all('/href="([^"]+)"/', $html, $matches);

print_r($matches[1]);
// ['https://php.net', 'https://laravel.com']
```

---

## 3. preg_replace: Reemplazar con regex

```php
$texto = "Mi teléfono es 55-1234-5678 o 33-9876-5432";

// Ocultar números telefónicos
$censurado = preg_replace('/\d{2}-\d{4}-\d{4}/', '**-****-****', $texto);
echo $censurado; // Mi teléfono es **-****-**** o **-****-****
```

### Usar referencias en el reemplazo

```php
$texto = "Juan García y María López";

// Invertir nombre y apellido
$invertido = preg_replace('/(\w+)\s(\w+)/', '$2, $1', $texto);
echo $invertido; // García, Juan y López, María
```

### preg_replace_callback: Reemplazo dinámico

```php
$plantilla = "Hola {nombre}, tu saldo es {saldo}";

$datos = ['nombre' => 'Ana', 'saldo' => '$1,500'];

$resultado = preg_replace_callback('/\{(\w+)\}/', function ($m) use ($datos) {
    return $datos[$m[1]] ?? $m[0];
}, $plantilla);

echo $resultado; // Hola Ana, tu saldo es $1,500
```

---

## 4. preg_split: Dividir con regex

```php
// Dividir por múltiples separadores
$texto = "PHP,JavaScript;Python|Ruby";

$lenguajes = preg_split('/[,;|]/', $texto);
// ['PHP', 'JavaScript', 'Python', 'Ruby']

// Dividir por uno o más espacios
$linea = "campo1   campo2     campo3";
$campos = preg_split('/\s+/', $linea);
// ['campo1', 'campo2', 'campo3']
```

---

## 5. Lookahead y Lookbehind

Permiten verificar qué hay antes o después de una posición **sin incluirlo** en la coincidencia.

### Lookahead positivo `(?=...)`

```php
// Encontrar palabras seguidas de un número
$texto = "item1 nombre item2 descripción item3";

preg_match_all('/\w+(?=\d)/', $texto, $matches);
// ['item', 'item', 'item']
```

### Lookbehind positivo `(?<=...)`

```php
// Extraer montos después del signo $
$texto = "Precio: $450 y descuento: $50";

preg_match_all('/(?<=\$)\d+/', $texto, $matches);
// ['450', '50']
```

### Lookahead negativo `(?!...)` y Lookbehind negativo `(?<!...)`

```php
// Palabras que NO terminan en "s"
preg_match_all('/\b\w+\b(?!s)/', "gatos perro casas libro", $matches);

// Números que NO están precedidos por $
preg_match_all('/(?<!\$)\b\d+\b/', "precio $100 cantidad 5", $matches);
// ['5']
```

---

## 6. Heredoc y Nowdoc

### Heredoc — Permite interpolación de variables

```php
$nombre = "Carlos";
$lenguaje = "PHP";

$mensaje = <<<EOT
Hola $nombre,

Bienvenido al curso de $lenguaje.
Este texto mantiene los saltos de línea
y permite usar "comillas" sin escapar.
EOT;

echo $mensaje;
```

### Nowdoc — Sin interpolación (como comillas simples)

```php
$raw = <<<'EOT'
Esto es texto literal.
Las variables como $nombre NO se interpretan.
Ideal para plantillas o código fuente.
EOT;
```

> **Tip:** Desde PHP 7.3, el cierre puede estar indentado:
> ```php
> $html = <<<HTML
>     <div>
>         <p>Contenido</p>
>     </div>
>     HTML;
> ```

---

## 7. sprintf: Formateo de cadenas

`sprintf` permite crear cadenas con formato controlado.

```php
// Formato numérico
$precio = 1234.5;
echo sprintf("Precio: $%,.2f", $precio);
// Precio: $1,234.50

// Padding con ceros
$orden = 42;
echo sprintf("ORD-%05d", $orden);
// ORD-00042

// Múltiples valores
echo sprintf("%s tiene %d años y mide %.1f m", "Ana", 25, 1.65);
// Ana tiene 25 años y mide 1.7 m
```

### Especificadores comunes

| Especificador | Tipo | Ejemplo |
|---|---|---|
| `%s` | String | `"hola"` |
| `%d` | Entero | `42` |
| `%f` | Float | `3.140000` |
| `%.2f` | Float 2 decimales | `3.14` |
| `%05d` | Entero con padding | `00042` |
| `%x` | Hexadecimal | `2a` |

```php
// Generar colores hex
$r = 255; $g = 128; $b = 0;
echo sprintf('#%02x%02x%02x', $r, $g, $b); // #ff8000
```

---

## 8. Funciones de PHP 8: str_contains, str_starts_with, str_ends_with

Antes de PHP 8, verificar subcadenas requería funciones complicadas o propensas a errores:

```php
// Antes (PHP < 8) — propenso a bugs con strpos
if (strpos($url, 'https') !== false) { /* ... */ }

// Ahora (PHP 8+) — claro y legible
if (str_contains($url, 'https')) { /* ... */ }
```

### str_starts_with y str_ends_with

```php
$archivo = "documento.pdf";

if (str_ends_with($archivo, '.pdf')) {
    echo "Es un PDF";
}

$ruta = "/api/v2/usuarios";

if (str_starts_with($ruta, '/api/')) {
    echo "Es una ruta de API";
}
```

### Comparación con regex

```php
// Regex (más potente pero más lento)
if (preg_match('/\.pdf$/i', $archivo)) { /* ... */ }

// str_ends_with (más rápido para casos simples)
if (str_ends_with(strtolower($archivo), '.pdf')) { /* ... */ }
```

> **Regla práctica:** Usa las funciones `str_*` para verificaciones simples y regex para patrones complejos.

---

## 9. Otras funciones útiles de strings

```php
// Repetir cadena
echo str_repeat('=-', 20); // =-=-=-=-=-=-...

// Rellenar cadena
echo str_pad('42', 6, '0', STR_PAD_LEFT); // 000042

// Dividir en array
$csv = "uno,dos,tres";
$partes = explode(',', $csv); // ['uno', 'dos', 'tres']

// Unir array en string
echo implode(' | ', $partes); // uno | dos | tres

// Recortar espacios
echo trim("  hola  ");   // "hola"
echo ltrim("  hola  ");  // "hola  "
echo rtrim("  hola  ");  // "  hola"

// Reemplazar (sin regex, más rápido)
echo str_replace('mundo', 'PHP', 'Hola mundo'); // Hola PHP
```

---

## 10. Ejemplo práctico: Validador de contraseña

```php
function validarPassword(string $password): array {
    $errores = [];

    if (mb_strlen($password) < 8) {
        $errores[] = 'Mínimo 8 caracteres';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errores[] = 'Debe contener al menos una mayúscula';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errores[] = 'Debe contener al menos una minúscula';
    }
    if (!preg_match('/\d/', $password)) {
        $errores[] = 'Debe contener al menos un número';
    }
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $password)) {
        $errores[] = 'Debe contener al menos un carácter especial';
    }

    return $errores;
}

$errores = validarPassword('MiClave123!');
if (empty($errores)) {
    echo "Contraseña válida";
} else {
    echo "Errores:\n" . implode("\n", $errores);
}
```

---

## Resumen

| Función / Concepto | Uso |
|---|---|
| `mb_strlen`, `mb_substr` | Manejo seguro de UTF-8 |
| `preg_match` | Buscar patrón regex |
| `preg_replace` | Reemplazar con regex |
| `preg_split` | Dividir con regex |
| Lookahead / Lookbehind | Verificar contexto sin capturar |
| Heredoc / Nowdoc | Strings multilínea |
| `sprintf` | Formateo de cadenas |
| `str_contains` (PHP 8) | Verificar subcadena |
| `str_starts_with` / `str_ends_with` | Verificar inicio/final |

Combinar estas herramientas te permite manipular texto de forma profesional y segura en cualquier aplicación PHP.
