# Sistema de Archivos en PHP

PHP ofrece un conjunto robusto de funciones para trabajar con el sistema de archivos: leer, escribir, recorrer directorios y manejar streams. En esta lección cubriremos desde las funciones básicas hasta técnicas avanzadas.

---

## 1. file_get_contents: Leer archivos fácilmente

La forma más simple de leer un archivo completo en memoria.

```php
// Leer archivo local
$contenido = file_get_contents('/ruta/al/archivo.txt');
echo $contenido;

// Leer archivo remoto (requiere allow_url_fopen)
$html = file_get_contents('https://ejemplo.com');

// Con contexto HTTP personalizado
$opciones = [
    'http' => [
        'method' => 'GET',
        'header' => "Accept: application/json\r\n",
        'timeout' => 10,
    ]
];
$contexto = stream_context_create($opciones);
$json = file_get_contents('https://api.ejemplo.com/datos', false, $contexto);
$datos = json_decode($json, true);
```

> **Precaución:** `file_get_contents` carga todo el archivo en memoria. Para archivos muy grandes, usa `fopen` con lectura por bloques.

---

## 2. file_put_contents: Escribir archivos

```php
// Escribir (sobreescribe si existe)
file_put_contents('log.txt', "Entrada de log\n");

// Agregar al final del archivo
file_put_contents('log.txt', "Nueva entrada\n", FILE_APPEND);

// Escribir con bloqueo exclusivo
file_put_contents('datos.json', json_encode($datos), LOCK_EX);

// Escribir array como líneas
$lineas = ["Línea 1\n", "Línea 2\n", "Línea 3\n"];
file_put_contents('salida.txt', $lineas);
```

### Función auxiliar para escritura segura

```php
function escribirSeguro(string $archivo, string $contenido): bool {
    $dir = dirname($archivo);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true); // Crear directorio recursivamente
    }

    $temporal = tempnam($dir, 'tmp_');
    if (file_put_contents($temporal, $contenido, LOCK_EX) === false) {
        unlink($temporal);
        return false;
    }

    return rename($temporal, $archivo); // Reemplazo atómico
}
```

---

## 3. fopen / fclose / fread / fwrite: Control granular

Para mayor control sobre la lectura y escritura de archivos.

### Modos de apertura

| Modo | Descripción |
|---|---|
| `r` | Solo lectura, puntero al inicio |
| `r+` | Lectura y escritura, puntero al inicio |
| `w` | Solo escritura, trunca el archivo |
| `w+` | Lectura y escritura, trunca |
| `a` | Solo escritura, puntero al final |
| `a+` | Lectura y escritura, puntero al final |

```php
// Lectura línea por línea (eficiente en memoria)
$handle = fopen('archivo_grande.csv', 'r');

if ($handle) {
    while (($linea = fgets($handle)) !== false) {
        $campos = str_getcsv($linea);
        procesarRegistro($campos);
    }
    fclose($handle);
}
```

### Lectura de CSV

```php
$handle = fopen('datos.csv', 'r');
$cabeceras = fgetcsv($handle); // Primera línea como cabeceras

$registros = [];
while (($fila = fgetcsv($handle, 0, ',', '"')) !== false) {
    $registros[] = array_combine($cabeceras, $fila);
}
fclose($handle);

// Ahora $registros es un array de arrays asociativos
```

### Escritura con fwrite

```php
$handle = fopen('reporte.txt', 'w');
fwrite($handle, "=== Reporte Diario ===\n");
fwrite($handle, sprintf("Fecha: %s\n", date('Y-m-d')));
fwrite($handle, sprintf("Total registros: %d\n", count($registros)));
fclose($handle);
```

---

## 4. SplFileObject: Enfoque orientado a objetos

`SplFileObject` proporciona una interfaz OOP para manejo de archivos con iteración integrada.

```php
// Leer archivo línea por línea
$archivo = new SplFileObject('datos.txt', 'r');
$archivo->setFlags(SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);

foreach ($archivo as $numLinea => $linea) {
    echo "Línea $numLinea: $linea";
}
```

### Leer CSV con SplFileObject

```php
$csv = new SplFileObject('productos.csv', 'r');
$csv->setFlags(SplFileObject::READ_CSV);
$csv->setCsvControl(',', '"', '\\');

$cabeceras = $csv->current();
$csv->next();

$productos = [];
while ($csv->valid()) {
    $fila = $csv->current();
    if (count($fila) === count($cabeceras)) {
        $productos[] = array_combine($cabeceras, $fila);
    }
    $csv->next();
}
```

### Escribir con SplFileObject

```php
$archivo = new SplFileObject('log.csv', 'a');

$archivo->fputcsv(['2026-02-23', 'INFO', 'Proceso completado']);
$archivo->fputcsv(['2026-02-23', 'ERROR', 'Conexión fallida']);
```

---

## 5. DirectoryIterator y RecursiveDirectoryIterator

### Listar archivos de un directorio

```php
$dir = new DirectoryIterator('/ruta/al/directorio');

foreach ($dir as $archivo) {
    if ($archivo->isDot()) continue;

    echo sprintf(
        "%s - %s - %d bytes\n",
        $archivo->getFilename(),
        $archivo->getType(),
        $archivo->getSize()
    );
}
```

### Buscar recursivamente

```php
$directorio = new RecursiveDirectoryIterator('/proyecto/src');
$iterador = new RecursiveIteratorIterator($directorio);

// Filtrar solo archivos PHP
$phpFiles = new RegexIterator($iterador, '/\.php$/');

foreach ($phpFiles as $archivo) {
    echo $archivo->getPathname() . "\n";
}
```

---

## 6. glob: Buscar archivos por patrón

```php
// Todos los archivos PHP
$archivos = glob('/proyecto/src/*.php');

// Búsqueda recursiva con globstar (no nativo, usar función)
function globRecursivo(string $patron, int $flags = 0): array {
    $archivos = glob($patron, $flags);
    foreach (glob(dirname($patron) . '/*', GLOB_ONLYDIR) as $dir) {
        $archivos = array_merge(
            $archivos,
            globRecursivo($dir . '/' . basename($patron), $flags)
        );
    }
    return $archivos;
}

$todos = globRecursivo('/proyecto/**/*.php');

// Patrones útiles
$imagenes = glob('uploads/*.{jpg,png,gif}', GLOB_BRACE);
$configs = glob('config/[a-z]*.php');
```

---

## 7. Streams: php://memory y php://temp

Los streams permiten trabajar con datos como si fueran archivos.

### php://memory — Todo en RAM

```php
$stream = fopen('php://memory', 'r+');

fwrite($stream, "Línea 1\n");
fwrite($stream, "Línea 2\n");

rewind($stream); // Volver al inicio
echo stream_get_contents($stream);

fclose($stream);
```

### php://temp — RAM con fallback a disco

```php
// Usa memoria hasta 2MB, luego disco temporal
$stream = fopen('php://temp/maxmemory:2097152', 'r+');

// Generar CSV en memoria
fputcsv($stream, ['Nombre', 'Email', 'Ciudad']);
foreach ($usuarios as $u) {
    fputcsv($stream, [$u['nombre'], $u['email'], $u['ciudad']]);
}

rewind($stream);
$csv = stream_get_contents($stream);
fclose($stream);

// Enviar como descarga
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="usuarios.csv"');
echo $csv;
```

### php://input — Leer body de la petición

```php
// Útil para APIs que reciben JSON
$body = file_get_contents('php://input');
$datos = json_decode($body, true);
```

### php://output — Escribir directamente a la salida

```php
$handle = fopen('php://output', 'w');
fputcsv($handle, ['ID', 'Producto', 'Precio']);
foreach ($productos as $p) {
    fputcsv($handle, $p);
}
fclose($handle);
```

---

## 8. File Locking (Bloqueo de archivos)

Evita problemas de concurrencia cuando múltiples procesos acceden al mismo archivo.

```php
$handle = fopen('contador.txt', 'c+');

if (flock($handle, LOCK_EX)) { // Bloqueo exclusivo
    $contador = (int) fread($handle, filesize('contador.txt') ?: 1);
    $contador++;

    ftruncate($handle, 0);     // Limpiar archivo
    rewind($handle);           // Volver al inicio
    fwrite($handle, (string) $contador);

    flock($handle, LOCK_UN);   // Liberar bloqueo
} else {
    echo "No se pudo obtener el bloqueo";
}

fclose($handle);
```

### Tipos de bloqueo

| Constante | Tipo | Uso |
|---|---|---|
| `LOCK_SH` | Compartido | Múltiples lectores simultáneos |
| `LOCK_EX` | Exclusivo | Un solo escritor |
| `LOCK_UN` | Desbloquear | Liberar el bloqueo |
| `LOCK_NB` | No bloqueante | Combinar con SH/EX: `LOCK_EX \| LOCK_NB` |

```php
// Bloqueo no bloqueante
if (flock($handle, LOCK_EX | LOCK_NB)) {
    // Obtuvimos el bloqueo inmediatamente
} else {
    // El archivo está bloqueado por otro proceso
    echo "Recurso ocupado, intenta más tarde";
}
```

---

## 9. Funciones auxiliares del sistema de archivos

```php
// Verificar existencia
file_exists('archivo.txt');   // Archivo o directorio
is_file('archivo.txt');       // Solo archivos
is_dir('carpeta');            // Solo directorios

// Información del archivo
$info = pathinfo('/ruta/archivo.config.php');
echo $info['dirname'];    // /ruta
echo $info['basename'];   // archivo.config.php
echo $info['extension'];  // php
echo $info['filename'];   // archivo.config

// Tamaño legible
function tamanoLegible(int $bytes): string {
    $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($unidades) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $unidades[$i];
}

echo tamanoLegible(filesize('video.mp4')); // 1.25 GB

// Permisos y propiedad
chmod('script.sh', 0755);
chown('archivo.txt', 'www-data');

// Copiar, mover, eliminar
copy('origen.txt', 'destino.txt');
rename('viejo.txt', 'nuevo.txt');
unlink('temporal.txt');

// Crear/eliminar directorios
mkdir('nueva/carpeta/profunda', 0755, true);
rmdir('carpeta_vacia');
```

---

## 10. Ejemplo práctico: Logger simple con rotación

```php
class FileLogger {
    private string $directorio;
    private int $maxTamano;

    public function __construct(string $directorio, int $maxTamanoMB = 5) {
        $this->directorio = rtrim($directorio, '/');
        $this->maxTamano = $maxTamanoMB * 1024 * 1024;

        if (!is_dir($this->directorio)) {
            mkdir($this->directorio, 0755, true);
        }
    }

    public function log(string $nivel, string $mensaje): void {
        $archivo = $this->directorio . '/app.log';

        if (file_exists($archivo) && filesize($archivo) >= $this->maxTamano) {
            $this->rotar($archivo);
        }

        $linea = sprintf(
            "[%s] %s: %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($nivel),
            $mensaje
        );

        file_put_contents($archivo, $linea, FILE_APPEND | LOCK_EX);
    }

    private function rotar(string $archivo): void {
        $rotado = sprintf('%s/app-%s.log', $this->directorio, date('Y-m-d_His'));
        rename($archivo, $rotado);

        // Eliminar logs antiguos (mantener últimos 5)
        $logs = glob($this->directorio . '/app-*.log');
        rsort($logs);
        foreach (array_slice($logs, 5) as $viejo) {
            unlink($viejo);
        }
    }
}

$logger = new FileLogger('/var/log/miapp');
$logger->log('info', 'Aplicación iniciada');
$logger->log('error', 'Falló la conexión a la BD');
```

---

## Resumen

| Herramienta | Uso principal |
|---|---|
| `file_get_contents` / `file_put_contents` | Lectura/escritura rápida |
| `fopen` / `fread` / `fwrite` | Control granular |
| `SplFileObject` | Manejo OOP de archivos |
| `DirectoryIterator` | Recorrer directorios |
| `glob` | Buscar por patrón |
| `php://memory` / `php://temp` | Streams en memoria |
| `flock` | Bloqueo de archivos |

Dominar el sistema de archivos de PHP te permite construir loggers, procesadores de CSV, sistemas de caché y mucho más.
