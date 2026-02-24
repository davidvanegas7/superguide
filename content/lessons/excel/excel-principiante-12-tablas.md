# Tablas de Excel

Las tablas de Excel (no confundir con rangos simples) son rangos estructurados con funcionalidades especiales.

## Crear una tabla

1. Selecciona el rango de datos (incluyendo encabezados)
2. Pestaña **Insertar** → **Tabla** (o `Ctrl + T`)
3. Confirma que "La tabla tiene encabezados" está marcado

### Indicadores de una tabla

- Bordes y colores alternados automáticos
- Flechas de filtro en los encabezados
- La tabla se redimensiona automáticamente al agregar datos

## Ventajas de las tablas

| Característica | Beneficio |
|---------------|-----------|
| **Autoexpansión** | Al agregar filas/columnas, la tabla crece |
| **Referencias estructuradas** | Fórmulas legibles como `=SUMA(Tabla1[Ventas])` |
| **Estilos automáticos** | Filas alternadas, encabezados formateados |
| **Filtros integrados** | Siempre disponibles |
| **Fila de totales** | Un clic para agregar totales |
| **Nombres automáticos** | Cada tabla tiene un nombre único |

## Referencias estructuradas

En lugar de `=SUMA(C2:C100)`, dentro de una tabla puedes usar:

```
=SUMA(Tabla1[Ventas])           → toda la columna Ventas
=Tabla1[@Ventas]                → la celda de Ventas en la fila actual
=Tabla1[@Precio]*Tabla1[@Cantidad]  → cálculo en la fila actual
```

| Referencia | Significado |
|-----------|-------------|
| `[Columna]` | Toda la columna |
| `[@Columna]` | Celda de la fila actual |
| `[#Encabezados]` | Fila de encabezados |
| `[#Totales]` | Fila de totales |
| `[#Datos]` | Solo los datos (sin encabezados ni totales) |

## Fila de totales

1. Selecciona la tabla
2. Pestaña **Diseño de tabla** → marca **Fila de totales**
3. En cada celda de totales, elige: Suma, Promedio, Contar, Max, Min…

## Estilos de tabla

En la pestaña **Diseño de tabla** puedes:
- Elegir entre decenas de estilos predefinidos
- Alternar filas/columnas con bandas de color
- Resaltar primera/última columna

## Segmentaciones (Slicers)

Filtros visuales e interactivos:

1. Selecciona la tabla
2. Pestaña **Insertar** → **Segmentación de datos**
3. Elige la columna para filtrar
4. Haz clic en los botones para filtrar

## Convertir tabla a rango

Si ya no necesitas las funcionalidades de tabla:

1. Selecciona la tabla
2. Pestaña **Diseño de tabla** → **Convertir en rango**

## Resumen

Las tablas de Excel automatizan formato, filtrado y referencias. Siempre convierte tus datos a tabla cuando trabajes con listas de información. Es una de las funcionalidades más subutilizadas pero más poderosas de Excel.
