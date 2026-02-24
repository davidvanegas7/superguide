# Formato condicional avanzado

Lleva el formato condicional al siguiente nivel con fórmulas personalizadas y técnicas avanzadas.

## Fórmulas en formato condicional

**Inicio** → **Formato condicional** → **Nueva regla** → **Usar una fórmula**

### Resaltar filas completas

Para colorear toda la fila cuando una columna cumple una condición:

1. Selecciona todo el rango de datos (ej: A2:F100)
2. Fórmula: `=$E2>10000` (nota el `$` en la columna, no en la fila)
3. Define el formato (relleno verde)

### Resaltar filas alternas (cebra manual)

```
=RESIDUO(FILA(), 2)=0
```

### Resaltar columna actual

```
=COLUMNA()=CELDA("col")
```

### Comparar con la media

Resaltar valores por encima del promedio:
```
=A2>PROMEDIO($A$2:$A$100)
```

## Semáforo con fórmulas

Crear un sistema de semáforo personalizado:

| Estado | Condición | Color |
|--------|-----------|-------|
| 🟢 A tiempo | `=$D2<=$E2` | Verde |
| 🟡 Por vencer | `=$D2<=($E2+3)` | Amarillo |
| 🔴 Vencido | `=$D2>$E2` | Rojo |

Aplica 3 reglas al mismo rango, en orden de prioridad.

## Barras de datos personalizadas

Más allá de las barras predeterminadas:

1. Formato condicional → Barras de datos → Más reglas
2. Configura:
   - Valor mínimo y máximo
   - Color de la barra
   - Relleno sólido o degradado
   - Dirección de la barra
   - Mostrar solo la barra (ocultar el número)

## Escala de colores personalizada

1. Formato condicional → Escalas de color → Más reglas
2. Define 2 o 3 puntos:
   - **Mínimo**: color y tipo (número, porcentaje, percentil)
   - **Punto medio**: opcional
   - **Máximo**: color y tipo

## Mapas de calor (Heat Maps)

Ideal para matrices de datos:

1. Selecciona la matriz numérica
2. Aplica escala de colores de 3 puntos:
   - Bajo: Blanco
   - Medio: Amarillo
   - Alto: Rojo intenso

## Formato condicional con BUSCARV

Resaltar celdas que coincidan con una lista:

```
=CONTAR.SI($H$1:$H$20, A2)>0
```

Esto resalta cualquier celda de la columna A que aparezca en la lista H1:H20.

## Fechas vencidas

Resaltar fechas pasadas en rojo:
```
=Y(A2<>"", A2<HOY())
```

Resaltar fechas que vencen esta semana:
```
=Y(A2>=HOY(), A2<=HOY()+7)
```

## Duplicados avanzados

Resaltar la primera ocurrencia en verde y las duplicadas en rojo:

**Primera ocurrencia**:
```
=CONTAR.SI($A$2:A2, A2)=1
```

**Duplicados**:
```
=CONTAR.SI($A$2:A2, A2)>1
```

## Rendimiento

- Demasiadas reglas pueden hacer lento el archivo
- Usa rangos específicos en lugar de columnas completas
- Las fórmulas con BUSCARV en formato condicional son especialmente pesadas

## Resumen

El formato condicional con fórmulas permite crear visualizaciones sofisticadas que se actualizan en tiempo real. La clave es dominar las referencias mixtas ($) para que las fórmulas funcionen correctamente en todo el rango.
