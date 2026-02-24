# Funciones matemáticas y estadísticas

Funciones esenciales para análisis numérico profesional.

## Funciones de redondeo

| Función | Descripción | Ejemplo | Resultado |
|---------|-------------|---------|-----------|
| `REDONDEAR` | Al más cercano | `=REDONDEAR(3.456, 2)` | 3.46 |
| `REDONDEAR.MAS` | Siempre hacia arriba | `=REDONDEAR.MAS(3.421, 2)` | 3.43 |
| `REDONDEAR.MENOS` | Siempre hacia abajo | `=REDONDEAR.MENOS(3.459, 2)` | 3.45 |
| `MULTIPLO.SUPERIOR.EXACTO` | Al múltiplo superior | `=MULTIPLO.SUPERIOR.EXACTO(23, 5)` | 25 |
| `MULTIPLO.INFERIOR.EXACTO` | Al múltiplo inferior | `=MULTIPLO.INFERIOR.EXACTO(23, 5)` | 20 |
| `ENTERO` | Parte entera (hacia abajo) | `=ENTERO(3.9)` | 3 |
| `TRUNCAR` | Elimina decimales | `=TRUNCAR(3.9, 0)` | 3 |

## SUMAPRODUCTO

Multiplica elemento por elemento y suma los resultados:

```
=SUMAPRODUCTO(cantidades, precios)
=SUMAPRODUCTO(B2:B10, C2:C10)
```

Equivale a: `=B2*C2 + B3*C3 + ... + B10*C10`

### Usos avanzados de SUMAPRODUCTO

**Contar con condiciones**:
```
=SUMAPRODUCTO((A2:A100="Ventas")*1)
```

**Suma con condiciones**:
```
=SUMAPRODUCTO((A2:A100="Ventas")*(B2:B100>1000)*C2:C100)
```

## Funciones estadísticas

### Medidas de tendencia central

```
=PROMEDIO(rango)        → media aritmética
=MEDIANA(rango)         → valor central
=MODA.UNO(rango)        → valor más frecuente
=PROMEDIO.PONDERADO     → no existe, usa SUMAPRODUCTO/SUMA
```

**Promedio ponderado**:
```
=SUMAPRODUCTO(valores, pesos) / SUMA(pesos)
```

### Medidas de dispersión

```
=DESVEST(rango)         → desviación estándar (muestra)
=DESVEST.P(rango)       → desviación estándar (población)
=VAR(rango)             → varianza (muestra)
=VAR.P(rango)           → varianza (población)
```

### Percentiles y cuartiles

```
=PERCENTIL(rango, k)     → percentil k (0 a 1)
=CUARTIL(rango, cuartil)  → 1=Q1, 2=Q2, 3=Q3
```

```
=PERCENTIL(A2:A100, 0.9)  → el valor del percentil 90
=CUARTIL(A2:A100, 1)       → primer cuartil (25%)
```

### K-ésimo mayor/menor

```
=K.ESIMO.MAYOR(rango, k)  → k-ésimo valor más grande
=K.ESIMO.MENOR(rango, k)  → k-ésimo valor más pequeño
```

```
=K.ESIMO.MAYOR(A2:A100, 3)  → tercer valor más alto
```

## RESIDUO y COCIENTE

```
=RESIDUO(dividendo, divisor)   → resto de la división
=COCIENTE(dividendo, divisor)  → parte entera de la división
```

```
=RESIDUO(17, 5)   → 2
=COCIENTE(17, 5)  → 3
```

## Funciones de conteo avanzadas

```
=CONTAR.SI.CONJUNTO(...)        → contar con múltiples criterios
=FRECUENCIA(datos, intervalos)  → distribución de frecuencias
=JERARQUIA(valor, rango, orden)  → ranking de un valor
```

## SUBTOTALES

Calcula subtotales ignorando otras filas de subtotal:

```
=SUBTOTALES(función_num, rango)
```

| Número | Función | Ignora ocultos |
|--------|---------|----------------|
| 1/101 | PROMEDIO | No/Sí |
| 2/102 | CONTAR | No/Sí |
| 9/109 | SUMA | No/Sí |

```
=SUBTOTALES(109, A2:A100)  → suma ignorando filas ocultas por filtros
```

## Resumen

Las funciones matemáticas y estadísticas permiten análisis profundos de datos. SUMAPRODUCTO es la más versátil, y las funciones estadísticas son esenciales para reportes profesionales.
