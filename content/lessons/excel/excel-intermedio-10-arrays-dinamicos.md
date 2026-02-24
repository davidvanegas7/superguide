# Fórmulas matriciales y arrays dinámicos

Los arrays dinámicos de Excel 365 revolucionaron la forma de trabajar con fórmulas.

## ¿Qué es una fórmula matricial?

Una fórmula que opera sobre múltiples valores simultáneamente y puede devolver múltiples resultados.

### Fórmulas matriciales clásicas (Legacy)

En versiones pre-365, se confirmaban con `Ctrl+Shift+Enter` (CSE):

```
{=SUMA(SI(A2:A100="Ventas", C2:C100))}
```

Las llaves `{}` las agrega Excel automáticamente.

## Arrays dinámicos (Excel 365)

En Excel 365, las fórmulas pueden "derramarse" (spill) automáticamente a celdas adyacentes.

### Ejemplo básico

```
=A2:A10 * B2:B10
```

Esto devuelve 9 resultados, uno en cada fila, automáticamente.

### Operador de derrame (#)

Para referirse a todo el rango derramado:

```
=SUMA(D2#)
```

El `#` indica "todo el rango que se derramó desde D2".

## FILTRAR

```
=FILTRAR(datos, condición, [si_vacío])
```

```
=FILTRAR(A2:D100, C2:C100="Activo")
=FILTRAR(A2:D100, (C2:C100>1000)*(B2:B100="MX"), "Sin datos")
```

## ORDENAR y ORDENARPOR

```
=ORDENAR(array, [índice_col], [orden])
=ORDENARPOR(array, rango_orden, [orden])
```

```
=ORDENARPOR(A2:C100, C2:C100, -1)  → ordena por columna C descendente
```

## UNICOS

```
=UNICOS(rango)
=UNICOS(rango, por_columnas, exactamente_una_vez)
```

## SECUENCIA

Genera secuencias numéricas:

```
=SECUENCIA(filas, [columnas], [inicio], [incremento])
```

```
=SECUENCIA(10)           → 1,2,3,...,10
=SECUENCIA(5, 3)         → matriz 5×3
=SECUENCIA(12, 1, 1, 1)  → meses 1-12
=SECUENCIA(10, 1, 0, 0.1) → 0, 0.1, 0.2, ..., 0.9
```

## MAKEARRAY (Excel 365)

Crea un array con una función LAMBDA:

```
=MAKEARRAY(filas, columnas, LAMBDA(f, c, f*c))
```

Esto crea una tabla de multiplicar.

## MAP (Excel 365)

Aplica una función a cada elemento:

```
=MAP(A2:A100, LAMBDA(x, MAYUSC(x)))
```

## REDUCE (Excel 365)

Reduce un array a un solo valor:

```
=REDUCE(1, A2:A10, LAMBDA(acc, x, acc * x))
```

Esto calcula el producto de todos los valores.

## SCAN

Como REDUCE pero devuelve los resultados intermedios:

```
=SCAN(0, A2:A10, LAMBDA(acc, x, acc + x))
```

Esto calcula la suma acumulada.

## Combinaciones poderosas

### Top 5 clientes por ventas

```
=ORDENAR(UNICOS(FILTRAR(A2:B100, B2:B100>0)), 2, -1)
```

### Tabla de frecuencias dinámica

```
=LET(
    datos, A2:A100,
    categorias, UNICOS(datos),
    conteos, CONTAR.SI(datos, categorias),
    ORDENARPOR(APILAR.H(categorias, conteos), conteos, -1)
)
```

## Error #DERRAME!

Ocurre cuando las celdas donde debe derramarse el resultado no están vacías. Solución: limpia las celdas adyacentes.

## Resumen

Los arrays dinámicos eliminan la necesidad de fórmulas auxiliares y CSE. FILTRAR, ORDENAR, UNICOS y SECUENCIA son las funciones clave. Con LAMBDA, MAP y REDUCE puedes crear soluciones sofisticadas sin VBA.
