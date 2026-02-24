# Arrays dinámicos y funciones LAMBDA

Las funciones modernas de Excel 365 que eliminan la necesidad de fórmulas auxiliares.

## Arrays dinámicos: el paradigma

En Excel 365, una fórmula puede devolver múltiples valores que se "derraman" automáticamente.

### Comportamiento de derrame (Spill)

```
=FILTRAR(A2:D100, C2:C100="Activo")
```

Esta fórmula puede devolver 50 filas. Excel las coloca automáticamente debajo de la celda.

### Operador de derrame (#)

Referencia al rango derramado completo:

```
=SUMA(F2#)       → suma todo el rango derramado desde F2
=CONTARA(F2#)    → cuenta el rango derramado
```

## Funciones clave de arrays dinámicos

### SECUENCIA

```
=SECUENCIA(filas, [columnas], [inicio], [incremento])
```

```
=SECUENCIA(10)                    → 1 a 10
=SECUENCIA(5, 5, 1, 1)           → tabla 5×5
=SECUENCIA(10, 1, 100, -5)       → 100, 95, 90, ...
=SECUENCIA(12, 1, FECHA(2026,1,1), 30)  → 12 fechas mensuales
```

### FILTRAR

```
=FILTRAR(array, incluir, [si_vacío])
```

```
// Múltiples condiciones AND
=FILTRAR(A2:E100, (B2:B100="Activo")*(D2:D100>1000))

// Múltiples condiciones OR
=FILTRAR(A2:E100, (C2:C100="MX")+(C2:C100="CO"))

// Con valor por defecto
=FILTRAR(datos, condición, "Sin resultados")
```

### ORDENAR y ORDENARPOR

```
=ORDENAR(array, [indice_col], [orden], [por_col])
=ORDENARPOR(array, rango_orden1, [orden1], ...)
```

```
// Ordenar por múltiples columnas
=ORDENARPOR(A2:D100, D2:D100, -1, B2:B100, 1)

// Top 10
=INDICE(ORDENAR(A2:D100, 4, -1), SECUENCIA(10), {1,2,3,4})
```

### UNICOS

```
=UNICOS(array, [por_col], [exactamente_una_vez])
```

## LAMBDA: funciones personalizadas

### Sintaxis básica

```
=LAMBDA(parámetro1, parámetro2, expresión)
```

### Crear función con nombre

1. Fórmulas → Administrador de nombres → Nuevo
2. Nombre: `IVA`
3. Se refiere a: `=LAMBDA(monto, monto * 0.16)`

Uso: `=IVA(A2)` → calcula el 16% de A2

### Ejemplos

```
// Conversión de temperatura
CTOF = LAMBDA(c, c * 9/5 + 32)
=CTOF(100) → 212

// Calificación por letra
LETRA = LAMBDA(nota,
    SI(nota>=90,"A", SI(nota>=80,"B", SI(nota>=70,"C", SI(nota>=60,"D","F"))))
)
=LETRA(85) → "B"

// Precio con descuento
NETO = LAMBDA(precio, descuento, precio * (1 - descuento) * 1.16)
=NETO(1000, 0.10) → 1044
```

## Funciones LAMBDA auxiliares

### MAP

Aplica una función a cada elemento:

```
=MAP(A2:A100, LAMBDA(x, MAYUSC(RECORTAR(x))))
=MAP(B2:B100, LAMBDA(x, SI(x>100, "Alto", "Normal")))
```

### REDUCE

Reduce un array a un solo valor:

```
=REDUCE(0, A2:A10, LAMBDA(acc, val, acc + val))
→ equivale a SUMA

=REDUCE(1, A2:A10, LAMBDA(acc, val, acc * val))
→ producto de todos los valores

=REDUCE("", A2:A5, LAMBDA(acc, val, acc & SI(acc="","",", ") & val))
→ concatenar con comas
```

### SCAN

Como REDUCE pero devuelve valores intermedios:

```
=SCAN(0, A2:A10, LAMBDA(acc, val, acc + val))
→ suma acumulada
```

### MAKEARRAY

Crea un array con una función:

```
=MAKEARRAY(10, 10, LAMBDA(f, c, f * c))
→ tabla de multiplicar 10×10
```

### BYCOL y BYROW

Aplica una función por columna o fila:

```
=BYCOL(A2:D100, LAMBDA(col, PROMEDIO(col)))
→ promedio de cada columna

=BYROW(A2:D10, LAMBDA(fila, MAX(fila)))
→ máximo de cada fila
```

## LAMBDA recursiva

Para algoritmos que necesitan llamarse a sí mismos:

```
// Factorial
FACT = LAMBDA(n, SI(n<=1, 1, n * FACT(n-1)))
=FACT(5) → 120

// Fibonacci
FIB = LAMBDA(n, SI(n<=1, n, FIB(n-1) + FIB(n-2)))
```

> Dale un nombre a la LAMBDA para que pueda referenciarse a sí misma.

## Combinaciones poderosas

### Dashboard dinámico sin tablas dinámicas

```
=LET(
    datos, Tabla1,
    categorias, UNICOS(Tabla1[Categoría]),
    totales, MAP(categorias, LAMBDA(cat, 
        SUMAR.SI(Tabla1[Categoría], cat, Tabla1[Monto]))),
    ORDENARPOR(APILAR.H(categorias, totales), totales, -1)
)
```

## Resumen

LAMBDA y los arrays dinámicos representan el futuro de Excel. Eliminan hojas auxiliares, columnas calculadas temporales y fórmulas CSE. MAP, REDUCE y las funciones de array convierten a Excel en un entorno casi funcional.
