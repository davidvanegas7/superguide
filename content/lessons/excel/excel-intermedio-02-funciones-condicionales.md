# Funciones condicionales: SUMAR.SI, CONTAR.SI, PROMEDIO.SI

Estas funciones aplican cálculos solo a las celdas que cumplen un criterio.

## CONTAR.SI

Cuenta las celdas que cumplen una condición:

```
=CONTAR.SI(rango, criterio)
```

**Ejemplos**:
```
=CONTAR.SI(B2:B100, "Activo")          → cuántos "Activo"
=CONTAR.SI(C2:C100, ">1000")           → cuántos > 1000
=CONTAR.SI(A2:A100, ">=01/01/2026")   → cuántas fechas desde 2026
=CONTAR.SI(B2:B100, "*error*")         → cuántos contienen "error"
=CONTAR.SI(D2:D100, "<>")             → cuántas no están vacías
```

## SUMAR.SI

Suma valores que cumplen una condición:

```
=SUMAR.SI(rango_criterio, criterio, rango_suma)
```

```
=SUMAR.SI(B2:B100, "Ventas", D2:D100)
→ Suma la columna D donde la columna B dice "Ventas"
```

**Con operadores**:
```
=SUMAR.SI(D2:D100, ">5000")           → suma valores > 5000
=SUMAR.SI(A2:A100, ">=01/02/2026", D2:D100)  → suma desde febrero
```

## PROMEDIO.SI

Promedia valores que cumplen una condición:

```
=PROMEDIO.SI(rango_criterio, criterio, rango_promedio)
```

```
=PROMEDIO.SI(C2:C100, "México", E2:E100)
→ Promedio de la columna E donde C = "México"
```

## Versiones con múltiples criterios (.CONJUNTO)

### CONTAR.SI.CONJUNTO

```
=CONTAR.SI.CONJUNTO(rango1, criterio1, rango2, criterio2, ...)
```

```
=CONTAR.SI.CONJUNTO(B:B, "Ventas", C:C, "México", D:D, ">1000")
→ Ventas en México mayores a 1000
```

### SUMAR.SI.CONJUNTO

```
=SUMAR.SI.CONJUNTO(rango_suma, rango1, criterio1, rango2, criterio2, ...)
```

```
=SUMAR.SI.CONJUNTO(E:E, B:B, "Ventas", C:C, "2026")
→ Suma de ventas del año 2026
```

### PROMEDIO.SI.CONJUNTO

```
=PROMEDIO.SI.CONJUNTO(rango_promedio, rango1, criterio1, rango2, criterio2)
```

## Criterios con comodines

| Comodín | Significado | Ejemplo |
|---------|-------------|---------|
| `*` | Cualquier número de caracteres | `"*ción"` → contiene "ción" |
| `?` | Un solo carácter | `"A?"` → A seguida de un carácter |
| `~` | Escape (buscar * o ? literal) | `"~*"` → buscar asterisco |

## Criterios con referencias

Puedes usar celdas como criterio:

```
=SUMAR.SI(B:B, F1, D:D)
→ F1 contiene el criterio dinámico
```

Con operadores y celdas:
```
=SUMAR.SI(D:D, ">"&F1)
→ Suma donde D > el valor de F1
```

## MAX.SI.CONJUNTO y MIN.SI.CONJUNTO

```
=MAX.SI.CONJUNTO(rango_max, rango_criterio, criterio)
=MIN.SI.CONJUNTO(rango_min, rango_criterio, criterio)
```

```
=MAX.SI.CONJUNTO(D:D, B:B, "Ventas")
→ La venta más alta
```

## Resumen

Las funciones condicionales son el puente entre los datos crudos y el análisis. Combinan la potencia del cálculo con la selectividad de los criterios para responder preguntas específicas sobre tus datos.
