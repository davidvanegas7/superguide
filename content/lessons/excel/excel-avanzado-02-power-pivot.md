# Power Pivot y modelo de datos

Power Pivot extiende las capacidades de tablas dinámicas con relaciones entre tablas y fórmulas DAX.

## ¿Qué es Power Pivot?

Un complemento de Excel que permite:
- Trabajar con millones de filas (vs. ~1M límite de Excel)
- Crear relaciones entre tablas (modelo relacional)
- Escribir fórmulas DAX para cálculos avanzados
- Construir medidas reutilizables

## Activar Power Pivot

Archivo → Opciones → Complementos → Complementos COM → **Microsoft Power Pivot for Excel**

## El modelo de datos

### Concepto

En lugar de una sola tabla gigante con datos duplicados, usas múltiples tablas relacionadas:

- **Ventas** (tabla de hechos): Fecha, ID_Producto, ID_Cliente, Monto
- **Productos** (tabla de dimensión): ID_Producto, Nombre, Categoría, Precio
- **Clientes** (tabla de dimensión): ID_Cliente, Nombre, Ciudad, Segmento
- **Calendario** (tabla de dimensión): Fecha, Mes, Trimestre, Año

### Ventajas del modelo relacional

- Sin datos duplicados
- Archivo más pequeño
- Actualizaciones más rápidas
- Análisis multidimensional

## Crear relaciones

### En la vista de diagrama

1. Power Pivot → **Administrar** → Vista de diagrama
2. Arrastra un campo de una tabla a otra
3. Define la cardinalidad (1:muchos generalmente)

### Buenas prácticas

- Relaciones 1:muchos (la tabla de hechos tiene el lado "muchos")
- Las claves deben ser del mismo tipo de dato
- Evita relaciones muchos:muchos cuando sea posible
- Usa un modelo estrella (star schema)

## Tabla de calendario

Esencial para análisis temporal. Power Pivot puede autogenerarla:

```dax
= CALENDAR(DATE(2020,1,1), DATE(2026,12,31))
```

O créala manualmente con columnas:

| Fecha | Año | Trimestre | Mes | NombreMes | DiaSemana | EsLaboral |
|-------|-----|-----------|-----|-----------|-----------|-----------|

### Marcar como tabla de fechas

En Power Pivot: clic derecho en la tabla → **Marcar como tabla de fechas**

## Introducción a DAX

DAX (Data Analysis Expressions) es el lenguaje de fórmulas de Power Pivot.

### Medidas vs. columnas calculadas

| Tipo | Cuándo se calcula | Uso |
|------|-------------------|-----|
| Columna calculada | Al actualizar datos | Filtros, relaciones |
| Medida | Al consultar (en tabla dinámica) | KPIs, cálculos dinámicos |

### Medidas básicas

```dax
Total Ventas := SUM(Ventas[Monto])

Conteo Transacciones := COUNTROWS(Ventas)

Promedio Venta := AVERAGE(Ventas[Monto])

Ticket Promedio := DIVIDE(SUM(Ventas[Monto]), COUNTROWS(Ventas), 0)
```

### CALCULATE: la función más importante

Cambia el contexto de filtro:

```dax
Ventas Norte := CALCULATE(SUM(Ventas[Monto]), Clientes[Región] = "Norte")

Ventas 2026 := CALCULATE(SUM(Ventas[Monto]), Calendario[Año] = 2026)

Ventas Activos := CALCULATE(
    SUM(Ventas[Monto]),
    Clientes[Estado] = "Activo",
    Productos[Categoría] = "Premium"
)
```

### Time Intelligence

```dax
Ventas Mes Anterior := CALCULATE(SUM(Ventas[Monto]), DATEADD(Calendario[Fecha], -1, MONTH))

Ventas YTD := TOTALYTD(SUM(Ventas[Monto]), Calendario[Fecha])

Ventas Acumulado := CALCULATE(
    SUM(Ventas[Monto]),
    DATESYTD(Calendario[Fecha])
)

Crecimiento % := DIVIDE(
    [Total Ventas] - [Ventas Mes Anterior],
    [Ventas Mes Anterior],
    0
)
```

## KPIs en Power Pivot

1. Define la medida base (ej: Total Ventas)
2. Define la meta (otra medida o valor fijo)
3. Power Pivot → KPI → configura umbrales (🔴🟡🟢)

Los KPIs aparecen con iconos en las tablas dinámicas.

## Jerarquías

Crea jerarquías para navegación drill-down:

- Geografía: País → Estado → Ciudad
- Tiempo: Año → Trimestre → Mes → Día
- Producto: Categoría → Subcategoría → Producto

## Resumen

Power Pivot transforma Excel de una hoja de cálculo a una herramienta de análisis multidimensional. El modelo de datos elimina redundancia, DAX permite cálculos imposibles con fórmulas normales, y Time Intelligence automatiza comparaciones temporales.
