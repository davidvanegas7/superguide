# Arrays Avanzados en PHP

Los arrays son una de las estructuras de datos más utilizadas en PHP. En esta lección exploraremos las funciones avanzadas que permiten manipular arrays de forma eficiente, funcional y expresiva.

---

## 1. array_map: Transformar elementos

`array_map` aplica una función a cada elemento del array y devuelve un nuevo array con los resultados.

```php
$precios = [100, 250, 80, 430];

$conIva = array_map(function ($precio) {
    return $precio * 1.16;
}, $precios);

print_r($conIva);
// [116, 290, 92.8, 498.8]
```

Con arrow functions (PHP 7.4+):

```php
$conIva = array_map(fn($p) => $p * 1.16, $precios);
```

También puedes trabajar con múltiples arrays simultáneamente:

```php
$nombres = ['Ana', 'Luis', 'Carlos'];
$edades = [25, 30, 22];

$usuarios = array_map(fn($n, $e) => "$n tiene $e años", $nombres, $edades);
// ['Ana tiene 25 años', 'Luis tiene 30 años', 'Carlos tiene 22 años']
```

---

## 2. array_filter: Filtrar elementos

`array_filter` devuelve solo los elementos que cumplan una condición.

```php
$numeros = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

$pares = array_filter($numeros, fn($n) => $n % 2 === 0);
// [2, 4, 6, 8, 10]
```

> **Tip:** Sin callback, `array_filter` elimina valores falsy (`0`, `""`, `null`, `false`).

```php
$datos = ['PHP', '', null, 'Laravel', 0, false, 'MySQL'];
$limpios = array_filter($datos);
// ['PHP', 'Laravel', 'MySQL']
```

Puedes usar el flag `ARRAY_FILTER_USE_KEY` para filtrar por clave:

```php
$config = ['db_host' => 'localhost', 'app_name' => 'Mi App', 'db_port' => 3306];

$dbConfig = array_filter($config, fn($key) => str_starts_with($key, 'db_'), ARRAY_FILTER_USE_KEY);
// ['db_host' => 'localhost', 'db_port' => 3306]
```

---

## 3. array_reduce: Reducir a un solo valor

`array_reduce` recorre el array acumulando un resultado.

```php
$carrito = [
    ['producto' => 'Teclado', 'precio' => 450],
    ['producto' => 'Mouse', 'precio' => 250],
    ['producto' => 'Monitor', 'precio' => 3200],
];

$total = array_reduce($carrito, function ($acumulado, $item) {
    return $acumulado + $item['precio'];
}, 0);

echo $total; // 3900
```

Ejemplo más complejo — agrupar por categoría:

```php
$productos = [
    ['nombre' => 'PHP Book', 'cat' => 'libros'],
    ['nombre' => 'Mouse', 'cat' => 'tech'],
    ['nombre' => 'JS Book', 'cat' => 'libros'],
];

$agrupados = array_reduce($productos, function ($acc, $item) {
    $acc[$item['cat']][] = $item['nombre'];
    return $acc;
}, []);

// ['libros' => ['PHP Book', 'JS Book'], 'tech' => ['Mouse']]
```

---

## 4. array_walk: Modificar en su lugar

A diferencia de `array_map`, `array_walk` modifica el array original por referencia.

```php
$precios = ['teclado' => 450, 'mouse' => 250, 'monitor' => 3200];

array_walk($precios, function (&$precio, $nombre) {
    $precio = round($precio * 1.16, 2);
});

print_r($precios);
// ['teclado' => 522, 'mouse' => 290, 'monitor' => 3712]
```

> **Nota:** El `&` en `&$precio` es esencial para modificar el valor original.

---

## 5. Ordenamiento avanzado con usort y uasort

### usort — Ordenar por criterio personalizado

```php
$usuarios = [
    ['nombre' => 'Carlos', 'edad' => 28],
    ['nombre' => 'Ana', 'edad' => 22],
    ['nombre' => 'Luis', 'edad' => 35],
];

usort($usuarios, fn($a, $b) => $a['edad'] <=> $b['edad']);
// Ordena de menor a mayor edad
```

El operador spaceship (`<=>`) retorna -1, 0 o 1 según la comparación.

### uasort — Mantiene las claves asociativas

```php
$calificaciones = ['mate' => 85, 'fisica' => 92, 'quimica' => 78];

uasort($calificaciones, fn($a, $b) => $b <=> $a); // Descendente
// ['fisica' => 92, 'mate' => 85, 'quimica' => 78]
```

### Ordenamiento multicampo

```php
$productos = [
    ['nombre' => 'A', 'precio' => 100, 'stock' => 5],
    ['nombre' => 'B', 'precio' => 100, 'stock' => 2],
    ['nombre' => 'C', 'precio' => 50, 'stock' => 10],
];

usort($productos, function ($a, $b) {
    return $a['precio'] <=> $b['precio']
        ?: $b['stock'] <=> $a['stock']; // Si el precio es igual, más stock primero
});
```

---

## 6. array_column: Extraer una columna

```php
$usuarios = [
    ['id' => 1, 'nombre' => 'Ana', 'email' => 'ana@mail.com'],
    ['id' => 2, 'nombre' => 'Luis', 'email' => 'luis@mail.com'],
    ['id' => 3, 'nombre' => 'Carlos', 'email' => 'carlos@mail.com'],
];

$nombres = array_column($usuarios, 'nombre');
// ['Ana', 'Luis', 'Carlos']

// Indexar por otra columna
$porId = array_column($usuarios, 'nombre', 'id');
// [1 => 'Ana', 2 => 'Luis', 3 => 'Carlos']

// Obtener registros completos indexados por id
$indexados = array_column($usuarios, null, 'id');
```

---

## 7. array_combine y array_chunk

### array_combine — Crear array asociativo desde dos arrays

```php
$claves = ['nombre', 'edad', 'ciudad'];
$valores = ['Ana', 25, 'CDMX'];

$persona = array_combine($claves, $valores);
// ['nombre' => 'Ana', 'edad' => 25, 'ciudad' => 'CDMX']
```

### array_chunk — Dividir en sub-arrays

```php
$items = range(1, 10);

$grupos = array_chunk($items, 3);
// [[1,2,3], [4,5,6], [7,8,9], [10]]

// Útil para paginación o procesamiento por lotes
foreach (array_chunk($registros, 100) as $lote) {
    procesarLote($lote);
}
```

---

## 8. Spread operator con arrays

PHP 7.4 introdujo el operador spread (`...`) para arrays.

```php
$base = [1, 2, 3];
$extra = [4, 5, 6];

$combinado = [...$base, ...$extra];
// [1, 2, 3, 4, 5, 6]
```

También funciona con arrays asociativos (PHP 8.1+):

```php
$defaults = ['color' => 'azul', 'tamaño' => 'M'];
$custom = ['color' => 'rojo'];

$config = [...$defaults, ...$custom];
// ['color' => 'rojo', 'tamaño' => 'M']
```

Para pasar elementos como argumentos de función:

```php
function sumar(int $a, int $b, int $c): int {
    return $a + $b + $c;
}

$nums = [10, 20, 30];
echo sumar(...$nums); // 60
```

---

## 9. list() y Destructuring

`list()` o su sintaxis corta `[]` permiten asignar elementos de un array a variables.

```php
$coordenadas = [19.4326, -99.1332];

[$latitud, $longitud] = $coordenadas;
echo "Lat: $latitud, Lng: $longitud";
```

Con arrays asociativos (PHP 7.1+):

```php
$usuario = ['nombre' => 'Ana', 'edad' => 25, 'ciudad' => 'CDMX'];

['nombre' => $nombre, 'edad' => $edad] = $usuario;
echo "$nombre, $edad años"; // Ana, 25 años
```

En bucles:

```php
$puntos = [[1, 2], [3, 4], [5, 6]];

foreach ($puntos as [$x, $y]) {
    echo "($x, $y) ";
}
// (1, 2) (3, 4) (5, 6)
```

Ignorar valores con destructuring:

```php
[, $segundo, , $cuarto] = [10, 20, 30, 40];
echo "$segundo, $cuarto"; // 20, 40
```

---

## 10. array_key_exists vs isset

Ambas verifican si una clave existe, pero se comportan diferente con valores `null`.

```php
$config = [
    'debug' => true,
    'cache' => null,
    'timeout' => 0,
];

var_dump(isset($config['cache']));            // false (¡null!)
var_dump(array_key_exists('cache', $config)); // true

var_dump(isset($config['noExiste']));            // false
var_dump(array_key_exists('noExiste', $config)); // false
```

> **Regla práctica:**
> - Usa `isset()` cuando quieres verificar que la clave existe **y** tiene un valor no-null.
> - Usa `array_key_exists()` cuando solo te importa si la clave está definida, sin importar su valor.

### Null coalescing operator (??)

```php
$nombre = $config['usuario'] ?? 'invitado';
// Si no existe o es null, usa 'invitado'

// Encadenado
$valor = $datos['a'] ?? $datos['b'] ?? 'default';
```

---

## Combinando funciones: Pipeline de datos

```php
$ventas = [
    ['producto' => 'Laptop', 'monto' => 15000, 'estado' => 'completada'],
    ['producto' => 'Mouse', 'monto' => 250, 'estado' => 'cancelada'],
    ['producto' => 'Teclado', 'monto' => 800, 'estado' => 'completada'],
    ['producto' => 'Monitor', 'monto' => 5000, 'estado' => 'completada'],
    ['producto' => 'Cable', 'monto' => 50, 'estado' => 'completada'],
];

// 1. Filtrar solo completadas
// 2. Extraer montos
// 3. Calcular total
$total = array_reduce(
    array_column(
        array_filter($ventas, fn($v) => $v['estado'] === 'completada'),
        null
    ),
    fn($acc, $v) => $acc + $v['monto'],
    0
);

echo "Total ventas completadas: \$$total"; // $20850
```

---

## Resumen

| Función | Propósito |
|---|---|
| `array_map` | Transformar cada elemento |
| `array_filter` | Filtrar por condición |
| `array_reduce` | Reducir a un valor |
| `array_walk` | Modificar en su lugar |
| `usort` / `uasort` | Ordenar con criterio custom |
| `array_column` | Extraer una columna |
| `array_combine` | Unir claves y valores |
| `array_chunk` | Dividir en bloques |
| `...` (spread) | Expandir arrays |
| `list()` / `[]` | Destructuring |

Dominar estas funciones te permitirá escribir código PHP más limpio, funcional y eficiente.
