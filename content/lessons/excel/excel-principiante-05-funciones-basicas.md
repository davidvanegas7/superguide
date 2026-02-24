# Funciones básicas: PROMEDIO, MAX, MIN, CONTAR

Excel tiene más de 400 funciones integradas. Aquí cubrimos las esenciales.

## Anatomía de una función

```
=NOMBRE_FUNCION(argumento1, argumento2, ...)
```

- Siempre comienza con `=`
- El nombre va seguido de paréntesis
- Los argumentos se separan con comas (o punto y coma según tu configuración regional)

## PROMEDIO

Calcula la media aritmética:

```
=PROMEDIO(A1:A10)        → promedio del rango
=PROMEDIO(A1,B1,C1)      → promedio de celdas específicas
=PROMEDIO(A1:A10,C1:C10) → promedio de dos rangos
```

> **Nota**: PROMEDIO ignora las celdas vacías y las que contienen texto.

## MAX y MIN

Encuentran el valor más alto y más bajo:

```
=MAX(A1:A50)    → valor máximo
=MIN(A1:A50)    → valor mínimo
```

**Ejemplo práctico**: En una lista de calificaciones:
- `=MAX(B2:B30)` → la nota más alta
- `=MIN(B2:B30)` → la nota más baja

## CONTAR y CONTARA

| Función | Cuenta |
|---------|--------|
| `CONTAR(rango)` | Solo celdas con números |
| `CONTARA(rango)` | Celdas con cualquier dato (no vacías) |
| `CONTAR.BLANCO(rango)` | Celdas vacías |

```
=CONTAR(A1:A100)         → ¿cuántos números hay?
=CONTARA(A1:A100)        → ¿cuántas celdas tienen dato?
=CONTAR.BLANCO(A1:A100)  → ¿cuántas están vacías?
```

## REDONDEAR

Controla la cantidad de decimales del resultado:

```
=REDONDEAR(3.14159, 2)   → 3.14
=REDONDEAR(3.14159, 0)   → 3
=REDONDEAR(1234, -2)     → 1200
```

## ABS

Devuelve el valor absoluto (sin signo):

```
=ABS(-15)    → 15
=ABS(15)     → 15
```

## ALEATORIO y ALEATORIO.ENTRE

Generan números aleatorios:

```
=ALEATORIO()              → número entre 0 y 1
=ALEATORIO.ENTRE(1,100)   → entero entre 1 y 100
```

> Estos valores cambian cada vez que se recalcula la hoja.

## Anidar funciones

Puedes usar una función dentro de otra:

```
=REDONDEAR(PROMEDIO(A1:A10), 2)
```

Esto calcula el promedio y lo redondea a 2 decimales.

## Resumen

| Función | Propósito |
|---------|-----------|
| `SUMA` | Sumar valores |
| `PROMEDIO` | Media aritmética |
| `MAX` | Valor máximo |
| `MIN` | Valor mínimo |
| `CONTAR` | Contar números |
| `CONTARA` | Contar no vacías |
| `REDONDEAR` | Controlar decimales |
