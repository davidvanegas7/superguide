# Operadores y Estructuras de Control en PHP

En esta lección aprenderás a tomar decisiones en tu código y a repetir acciones.

## Operadores aritméticos

```php
<?php

$a = 10;
$b = 3;

echo $a + $b;   // 13  — suma
echo $a - $b;   // 7   — resta
echo $a * $b;   // 30  — multiplicación
echo $a / $b;   // 3.33 — división
echo $a % $b;   // 1   — módulo (resto)
echo $a ** $b;  // 1000 — potencia
```

## Operadores de comparación

```php
<?php

var_dump(5 == "5");   // true  (igual valor)
var_dump(5 === "5");  // false (igual valor Y tipo)
var_dump(5 != 3);     // true
var_dump(5 > 3);      // true
var_dump(5 <= 5);     // true
```

> 💡 **Usa siempre `===`** en vez de `==` para evitar comparaciones inesperadas.

## Condicional if / elseif / else

```php
<?php

$nota = 85;

if ($nota >= 90) {
    echo "Excelente";
} elseif ($nota >= 70) {
    echo "Aprobado";
} else {
    echo "Reprobado";
}
```

## Switch

```php
<?php

$dia = "lunes";

switch ($dia) {
    case "lunes":
    case "martes":
        echo "Inicio de semana";
        break;
    case "viernes":
        echo "¡Por fin viernes!";
        break;
    default:
        echo "Día normal";
}
```

## Match (PHP 8+)

El operador `match` es más estricto y conciso que `switch`:

```php
<?php

$status = 2;

$mensaje = match($status) {
    1       => "Activo",
    2, 3    => "Pendiente",
    4       => "Inactivo",
    default => "Desconocido",
};

echo $mensaje; // Pendiente
```

## Bucle while

```php
<?php

$i = 1;
while ($i <= 5) {
    echo "Vuelta $i\n";
    $i++;
}
```

## Bucle for

```php
<?php

for ($i = 0; $i < 5; $i++) {
    echo "Número: $i\n";
}
```

## Bucle foreach

Ideal para recorrer **arrays**:

```php
<?php

$frutas = ["manzana", "pera", "uva"];

foreach ($frutas as $index => $fruta) {
    echo "$index: $fruta\n";
}
```

## Resumen

Ahora conoces:
- ✅ Operadores aritméticos y de comparación
- ✅ Condicionales `if`, `switch` y `match`
- ✅ Bucles `while`, `for` y `foreach`
