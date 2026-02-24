# DAX: fórmulas avanzadas para análisis

DAX es el lenguaje de Power Pivot y Power BI que permite cálculos analíticos sofisticados.

## Contexto de evaluación

### Contexto de fila

Se evalúa fila por fila (columnas calculadas):

```dax
Margen = Ventas[Precio] - Ventas[Costo]
```

### Contexto de filtro

Se evalúa según los filtros aplicados (medidas en tablas dinámicas):

```dax
Total Ventas := SUM(Ventas[Monto])
```

El valor cambia según los filtros activos (región, período, producto).

## CALCULATE en profundidad

CALCULATE modifica el contexto de filtro:

```dax
CALCULATE(expresión, filtro1, filtro2, ...)
```

### Operadores de filtro

```dax
// Filtro simple
CALCULATE(SUM(Ventas[Monto]), Productos[Categoría] = "Electrónica")

// Múltiples valores
CALCULATE(SUM(Ventas[Monto]), 
    Productos[Categoría] IN {"Electrónica", "Computación"})

// Con tabla de filtro
CALCULATE(SUM(Ventas[Monto]), 
    FILTER(Productos, Productos[Precio] > 1000))
```

### REMOVEFILTERS / ALL

Ignora filtros existentes:

```dax
Total General := CALCULATE(SUM(Ventas[Monto]), ALL(Ventas))

% del Total := DIVIDE(
    SUM(Ventas[Monto]),
    CALCULATE(SUM(Ventas[Monto]), ALL(Ventas)),
    0
)

% de la Categoría := DIVIDE(
    SUM(Ventas[Monto]),
    CALCULATE(SUM(Ventas[Monto]), ALLEXCEPT(Ventas, Productos[Categoría])),
    0
)
```

### KEEPFILTERS

Mantiene filtros existentes en lugar de reemplazarlos:

```dax
CALCULATE(SUM(Ventas[Monto]), 
    KEEPFILTERS(Productos[Categoría] = "Electrónica"))
```

## Funciones de iteración (funciones X)

Evalúan expresión fila por fila y luego agregan:

```dax
// Suma del producto (precio × cantidad) por fila
Ingresos := SUMX(Ventas, Ventas[Precio] * Ventas[Cantidad])

// Promedio del margen por transacción
Margen Promedio := AVERAGEX(Ventas, Ventas[Precio] - Ventas[Costo])

// Máximo ticket
Max Ticket := MAXX(Ventas, Ventas[Precio] * Ventas[Cantidad])

// Contar clientes únicos
Clientes Activos := COUNTAX(
    FILTER(Clientes, Clientes[Estado] = "Activo"),
    Clientes[ID]
)
```

## Time Intelligence avanzado

### Comparaciones temporales

```dax
// Mismo período año anterior
Ventas Año Anterior := CALCULATE(
    SUM(Ventas[Monto]),
    SAMEPERIODLASTYEAR(Calendario[Fecha])
)

// Crecimiento interanual
YoY % := DIVIDE(
    [Total Ventas] - [Ventas Año Anterior],
    [Ventas Año Anterior],
    0
)

// Acumulado del año
YTD := TOTALYTD(SUM(Ventas[Monto]), Calendario[Fecha])

// Acumulado del trimestre
QTD := TOTALQTD(SUM(Ventas[Monto]), Calendario[Fecha])

// Media móvil 3 meses
Media Móvil 3M := CALCULATE(
    AVERAGEX(VALUES(Calendario[Mes]), [Total Ventas]),
    DATESINPERIOD(Calendario[Fecha], MAX(Calendario[Fecha]), -3, MONTH)
)
```

### Períodos paralelos

```dax
// Ventas del mismo mes del año pasado
Ventas_PY := CALCULATE(
    SUM(Ventas[Monto]),
    PARALLELPERIOD(Calendario[Fecha], -12, MONTH)
)
```

## RANKX

Ranking dinámico:

```dax
Ranking Vendedor := RANKX(
    ALL(Vendedores[Nombre]),
    [Total Ventas],
    ,
    DESC,
    Dense
)
```

## Variables en DAX

```dax
Margen % := 
VAR TotalIngresos = SUM(Ventas[Ingreso])
VAR TotalCostos = SUM(Ventas[Costo])
VAR Margen = TotalIngresos - TotalCostos
RETURN DIVIDE(Margen, TotalIngresos, 0)
```

## SWITCH para categorización

```dax
Segmento := SWITCH(TRUE(),
    [Total Ventas] > 100000, "Platinum",
    [Total Ventas] > 50000, "Gold",
    [Total Ventas] > 10000, "Silver",
    "Bronze"
)
```

## Resumen

DAX combina la simplicidad de las funciones de Excel con la potencia del análisis multidimensional. CALCULATE, las funciones X, y Time Intelligence son los pilares fundamentales. Las variables (VAR/RETURN) mejoran la legibilidad y el rendimiento.
