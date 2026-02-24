# Proyecto: Dashboard de ventas interactivo

Aplica todo lo aprendido creando un dashboard profesional de análisis de ventas.

## Objetivo del proyecto

Crear un dashboard interactivo que:
- Resuma ventas por región, producto y período
- Incluya gráficos dinámicos
- Tenga filtros interactivos
- Se actualice con nuevos datos
- Tenga formato profesional

## Paso 1: Estructura de datos

Crea una hoja "Datos" con esta estructura:

| Fecha | Vendedor | Región | Producto | Categoría | Unidades | Precio_Unit | Total | Meta |
|-------|----------|--------|----------|-----------|----------|-------------|-------|------|

Genera al menos 200 registros de ejemplo.

### Fórmulas en la tabla de datos

```
Total:  =Unidades * Precio_Unit
Meta:   =SI(Región="Norte", 5000, SI(Región="Sur", 4000, 3500))
Cumple: =SI(Total>=Meta, "✓", "✗")
```

## Paso 2: Tabla dinámica

1. Selecciona los datos → Insertar → **Tabla dinámica** (nueva hoja)
2. Configura:
   - Filas: Región, Vendedor
   - Columnas: TEXTO(Fecha, "yyyy-mm")
   - Valores: Suma de Total

3. Crea una segunda tabla dinámica:
   - Filas: Categoría, Producto
   - Valores: Suma de Unidades, Promedio de Precio_Unit

## Paso 3: Hoja de resumen con fórmulas

Crea una hoja "Resumen" con KPIs:

```
Total ventas:      =SUMA(Datos[Total])
Promedio por venta: =PROMEDIO(Datos[Total])
Vendedor top:      =INDICE(Datos[Vendedor], COINCIDIR(MAX(...), ..., 0))
% cumplimiento:    =CONTAR.SI(Datos[Cumple],"✓")/CONTARA(Datos[Cumple])
```

### Ventas por región (SUMAR.SI.CONJUNTO)

```
=SUMAR.SI.CONJUNTO(Datos[Total], Datos[Región], "Norte", Datos[Fecha], ">="&FECHA(2026,1,1))
```

### Ranking de vendedores

Usando ORDENAR y UNICOS:
```
=ORDENARPOR(UNICOS(Datos[Vendedor]), 
            SUMAR.SI(Datos[Vendedor], UNICOS(Datos[Vendedor]), Datos[Total]), -1)
```

## Paso 4: Dashboard visual

Crea una hoja "Dashboard":

### Layout (cuadrícula 2×3)

```
┌──────────────────┬──────────────────┐
│   KPIs (tarjetas)│  Ventas por Mes  │
│  Total | Promedio │  (gráfico línea) │
├──────────────────┼──────────────────┤
│ Ventas x Región  │ Top Productos    │
│ (gráfico barras) │ (gráfico barras) │
├──────────────────┼──────────────────┤
│ Cumplimiento     │ Vendedores       │
│ (gráfico dona)   │ (tabla ranking)  │
└──────────────────┴──────────────────┘
```

### Tarjetas KPI

1. Celdas grandes con los valores
2. Formato: fuente grande (24pt), color según rendimiento
3. Formato condicional: verde si > meta, rojo si < meta

### Gráficos

1. **Ventas por mes**: gráfico de línea con marcadores
2. **Ventas por región**: gráfico de barras horizontal
3. **Top productos**: gráfico de barras vertical
4. **Cumplimiento**: gráfico de dona (% cumple vs no cumple)

## Paso 5: Filtros interactivos

### Segmentaciones (Slicers)

1. Selecciona la tabla dinámica
2. Analizar → **Insertar segmentación**
3. Agrega: Región, Categoría, Período
4. Formatea las segmentaciones con colores de marca

### Línea de tiempo

1. Analizar → **Insertar escala de tiempo**
2. Selecciona el campo Fecha
3. Filtra por meses, trimestres o años

### Conectar segmentaciones a múltiples tablas dinámicas

Clic derecho en la segmentación → **Conexiones de informe** → selecciona todas las tablas dinámicas.

## Paso 6: Formato profesional

### Diseño

- Fondo de la hoja: gris oscuro (#2D2D2D) o blanco limpio
- Tarjetas KPI: con bordes redondeados (usando formas)
- Colores consistentes en todos los gráficos
- Sin líneas de cuadrícula visibles
- Logo de la empresa en la esquina

### Configurar para presentación

1. Vista → **Ocultar líneas de cuadrícula**
2. Vista → Ocultar encabezados de fila/columna
3. Vista → Zoom al 85-90%
4. Congelar paneles en la fila de título

## Paso 7: Automatización

### Macro de actualización

```vb
Sub ActualizarDashboard()
    Dim pt As PivotTable
    For Each pt In ActiveWorkbook.PivotTables
        pt.RefreshTable
    Next pt
    MsgBox "Dashboard actualizado"
End Sub
```

### Botón de actualización

Inserta un botón en el dashboard que ejecute la macro.

## Criterios de evaluación

| Criterio | Puntos |
|----------|--------|
| Datos correctos y completos | 15 |
| Tablas dinámicas funcionales | 20 |
| Fórmulas de resumen correctas | 20 |
| Gráficos profesionales | 20 |
| Filtros interactivos | 15 |
| Diseño y presentación | 10 |

## Resumen

Este proyecto integra tablas dinámicas, fórmulas condicionales, gráficos avanzados, segmentaciones y macros básicas. Es un ejemplo real de lo que se espera de un usuario intermedio de Excel en un entorno profesional.
