# Proyecto final: sistema de gestión empresarial

Construye un sistema completo integrando todas las técnicas avanzadas aprendidas.

## Objetivo

Crear un sistema de gestión para una empresa ficticia que incluya:
- Base de datos relacional con Power Pivot
- Dashboard ejecutivo interactivo
- Automatización con VBA
- Análisis predictivo
- Reportes automatizados

## Arquitectura del sistema

```
📊 Sistema_Gestion.xlsm
├── 📄 Dashboard (vista ejecutiva)
├── 📄 Entrada_Datos (formulario VBA)
├── 📄 Análisis (tablas dinámicas + DAX)
├── 📄 Predicciones (regresión + pronósticos)
├── 📄 Config (parámetros del sistema)
├── 📊 Modelo de datos (Power Pivot)
│   ├── Ventas (tabla de hechos)
│   ├── Productos
│   ├── Clientes
│   ├── Vendedores
│   └── Calendario
└── 🔧 VBA
    ├── modPrincipal
    ├── modExportar
    ├── modEmail
    ├── clsVenta
    ├── clsCliente
    └── frmEntrada
```

## Parte 1: Modelo de datos (Power Pivot)

### Tablas

**Ventas** (100,000+ registros):
```
ID_Venta | Fecha | ID_Cliente | ID_Producto | ID_Vendedor | Cantidad | Precio_Unit | Descuento
```

**Productos**:
```
ID_Producto | Nombre | Categoría | Subcategoría | Costo | Precio_Lista | Proveedor
```

**Clientes**:
```
ID_Cliente | Nombre | Empresa | Segmento | Ciudad | Estado | País | Fecha_Alta
```

**Vendedores**:
```
ID_Vendedor | Nombre | Región | Equipo | Fecha_Ingreso | Meta_Mensual
```

**Calendario** (generado con DAX):
```dax
Calendar = CALENDAR(DATE(2020,1,1), DATE(2026,12,31))
```

### Relaciones
- Ventas → Productos (1:N)
- Ventas → Clientes (1:N)
- Ventas → Vendedores (1:N)
- Ventas → Calendario (1:N)

### Medidas DAX clave

```dax
// KPIs
Total Ventas := SUMX(Ventas, Ventas[Cantidad] * Ventas[Precio_Unit] * (1-Ventas[Descuento]))
Total Costo := SUMX(Ventas, Ventas[Cantidad] * RELATED(Productos[Costo]))
Margen := [Total Ventas] - [Total Costo]
Margen % := DIVIDE([Margen], [Total Ventas], 0)

// Time Intelligence
Ventas YTD := TOTALYTD([Total Ventas], Calendario[Date])
Ventas PY := CALCULATE([Total Ventas], SAMEPERIODLASTYEAR(Calendario[Date]))
Crecimiento YoY := DIVIDE([Total Ventas] - [Ventas PY], [Ventas PY], 0)

// Métricas de cliente
Clientes Activos := DISTINCTCOUNT(Ventas[ID_Cliente])
Ticket Promedio := DIVIDE([Total Ventas], COUNTROWS(Ventas), 0)
CLV := AVERAGEX(VALUES(Clientes[ID_Cliente]), 
    CALCULATE([Total Ventas]) * 3)

// Rankings
Rank Vendedor := RANKX(ALL(Vendedores[Nombre]), [Total Ventas])
```

## Parte 2: Dashboard ejecutivo

### Diseño

```
┌─────────────────────────────────────────────────┐
│  SISTEMA DE GESTIÓN EMPRESARIAL    [filtros]     │
├──────────┬──────────┬──────────┬────────────────┤
│ Ventas   │ Margen   │ Clientes │  Crecimiento   │
│ $2.5M    │ 35.2%    │ 1,247    │  ▲ +12.3%      │
├──────────┴──────────┼──────────┴────────────────┤
│ Ventas por Mes      │ Top 10 Productos          │
│ [gráfico línea]     │ [gráfico barras]          │
├─────────────────────┼───────────────────────────┤
│ Ventas por Región   │ Rendimiento Vendedores    │
│ [gráfico mapa]      │ [tabla con semáforo]      │
└─────────────────────┴───────────────────────────┘
```

### Segmentaciones
- Período (escala de tiempo)
- Región
- Categoría de producto
- Segmento de cliente

## Parte 3: Formulario VBA de entrada

```vb
' frmNuevaVenta
Private Sub btnRegistrar_Click()
    ' Validaciones
    If Not ValidarCampos() Then Exit Sub
    
    ' Crear objeto venta
    Dim venta As New clsVenta
    venta.Fecha = CDate(txtFecha.Value)
    venta.ClienteID = cmbCliente.Value
    venta.ProductoID = cmbProducto.Value
    venta.Cantidad = CInt(txtCantidad.Value)
    venta.Descuento = CDbl(txtDescuento.Value) / 100
    
    ' Guardar
    If venta.Guardar() Then
        ActualizarDashboard
        LimpiarFormulario
        MsgBox "Venta registrada", vbInformation
    End If
End Sub
```

## Parte 4: Análisis predictivo

### Pronóstico de ventas

```
=PRONOSTICO.ETS(fecha_futura, ventas_históricas, fechas_históricas)
```

Con intervalo de confianza:
```
=PRONOSTICO.ETS.CONFINT(fecha_futura, ventas, fechas, 0.95)
```

### Segmentación RFM con fórmulas

| Métrica | Fórmula |
|---------|---------|
| Recencia | Días desde última compra |
| Frecuencia | Número de compras |
| Monto | Total acumulado |

Score 1-5 para cada métrica → Segmentos de cliente.

## Parte 5: Automatización

### Reporte semanal automático

```vb
Sub ReporteSemanal()
    ' 1. Actualizar datos
    ActiveWorkbook.RefreshAll
    
    ' 2. Generar PDF del dashboard
    Sheets("Dashboard").ExportAsFixedFormat xlTypePDF, _
        ThisWorkbook.Path & "\Reportes\Semanal_" & Format(Date, "yyyymmdd") & ".pdf"
    
    ' 3. Enviar por email
    EnviarReporte "gerencia@empresa.com", "Reporte Semanal"
    
    ' 4. Log
    RegistrarEnLog "Reporte semanal generado y enviado"
End Sub
```

## Criterios de evaluación

| Componente | Puntos | Criterio |
|------------|--------|----------|
| Modelo de datos | 20 | Relaciones correctas, medidas DAX funcionales |
| Dashboard | 25 | KPIs, gráficos, filtros, diseño profesional |
| VBA/Formulario | 20 | Entrada validada, clases, manejo de errores |
| Análisis | 15 | Pronósticos, segmentación, correlaciones |
| Automatización | 10 | Reportes automáticos, emails, logs |
| Documentación | 10 | README, comentarios, instrucciones |

## Entregables

1. Archivo `.xlsm` con todo el sistema
2. Documentación de uso
3. Presentación de 5 minutos demostrando el sistema

## Resumen

Este proyecto integra Power Pivot, DAX, VBA avanzado, dashboards profesionales y automatización. Representa el nivel de competencia esperado de un usuario avanzado de Excel en roles de análisis de datos, business intelligence o administración empresarial.
