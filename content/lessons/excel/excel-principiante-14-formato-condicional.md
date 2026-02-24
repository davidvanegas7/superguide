# Formato condicional básico

El formato condicional cambia la apariencia de las celdas automáticamente según su valor.

## Aplicar formato condicional

1. Selecciona el rango
2. Pestaña **Inicio** → **Formato condicional**

## Reglas de resaltado de celdas

| Regla | Ejemplo |
|-------|---------|
| Mayor que… | Resaltar ventas > $10,000 |
| Menor que… | Marcar inventario < 10 unidades |
| Entre… | Valores entre 50 y 100 |
| Igual a… | Celdas que digan "Pendiente" |
| Texto que contiene… | Celdas con "Error" |
| Fecha en… | Ayer, hoy, esta semana, próximo mes |
| Valores duplicados | Encontrar registros repetidos |

## Reglas de barra de datos

Agrega barras horizontales dentro de las celdas, proporcionales al valor:

1. Formato condicional → **Barras de datos**
2. Elige relleno sólido o degradado

Las barras más largas = valores más altos. Muy útil para comparaciones rápidas.

## Escala de colores

Aplica un degradado de colores según el valor:

- **Verde a Rojo**: verde = alto, rojo = bajo
- **Rojo a Verde**: rojo = bajo, verde = alto
- Escalas de 2 o 3 colores

Ideal para mapas de calor (heat maps).

## Conjuntos de iconos

Agrega íconos visuales dentro de las celdas:

- **Flechas**: ↑ ↗ → ↘ ↓
- **Semáforos**: 🟢 🟡 🔴  
- **Estrellas**: ★★★, ★★, ★
- **Banderas**: 🏁

## Regla personalizada

Para condiciones más complejas:

1. Formato condicional → **Nueva regla**
2. **Usar una fórmula para determinar las celdas**
3. Escribe la fórmula y define el formato

**Ejemplo**: Resaltar toda la fila si el estado es "Urgente":
```
=$D1="Urgente"
```

> Note que la referencia `$D1` fija la columna D pero deja la fila relativa.

## Administrar reglas

- **Formato condicional** → **Administrar reglas**
- Puedes ver, editar, eliminar y cambiar el orden de las reglas
- Las reglas se evalúan de arriba a abajo
- Marca "Detener si es verdad" si no quieres que se apliquen reglas posteriores

## Borrar formato condicional

- **Formato condicional** → **Borrar reglas** → de las celdas seleccionadas o de toda la hoja

## Resumen

El formato condicional es una herramienta visual poderosa que permite identificar patrones, valores atípicos y tendencias de un vistazo sin cambiar los datos reales.
