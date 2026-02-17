# Introducción a PHP

PHP es un lenguaje de scripting del lado del servidor ampliamente usado para el desarrollo web. Fue creado por Rasmus Lerdorf en 1994 y hoy impulsa más del 75% de los sitios web del mundo, incluyendo WordPress y Facebook.

## ¿Por qué aprender PHP?

- Es **fácil de aprender** para principiantes
- Tiene una comunidad enorme y activa
- Excelente integración con bases de datos MySQL
- Frameworks modernos como Laravel hacen el desarrollo muy productivo

## Tu primer script PHP

Todo archivo PHP comienza con la etiqueta `<?php`. Veamos el clásico "Hola Mundo":

```php
<?php

echo "¡Hola, Mundo!";
```

Para ejecutarlo en el navegador, guárdalo como `index.php` en la carpeta de tu servidor local.

## Variables en PHP

Las variables siempre comienzan con el símbolo `$`:

```php
<?php

$nombre = "Ana";
$edad   = 25;
$altura = 1.68;
$activo = true;

echo "Me llamo $nombre y tengo $edad años.";
```

PHP es un lenguaje de **tipado dinámico**, por lo que no necesitas declarar el tipo de la variable.

## Tipos de datos básicos

| Tipo      | Ejemplo              |
|-----------|----------------------|
| `string`  | `"Hola mundo"`       |
| `int`     | `42`                 |
| `float`   | `3.14`               |
| `bool`    | `true` / `false`     |
| `null`    | `null`               |
| `array`   | `[1, 2, 3]`          |

## Comentarios

```php
<?php

// Esto es un comentario de una línea

/*
   Esto es un comentario
   de múltiples líneas
*/

# También funciona el estilo bash
```

## Resumen

En esta lección aprendiste:
- Qué es PHP y para qué sirve
- Cómo escribir tu primer script
- Cómo declarar variables
- Los tipos de datos básicos

En la siguiente lección veremos **operadores y estructuras de control**.
