# Optimización y rendimiento de Excel

Técnicas para que tus hojas de cálculo funcionen rápido, incluso con millones de datos.

## Diagnóstico de rendimiento

### Identificar cuellos de botella

1. **Fórmulas volátiles**: funciones que se recalculan siempre
2. **Rangos excesivos**: fórmulas que referencian columnas completas (A:A)
3. **Formato condicional**: demasiadas reglas en rangos grandes
4. **Archivos inflados**: objetos ocultos, estilos duplicados

### Medir tiempo de cálculo

```vb
Sub MedirRecalculo()
    Dim inicio As Double
    inicio = Timer
    
    Application.CalculateFull
    
    Debug.Print "Recálculo completo: " & Format(Timer - inicio, "0.000") & "s"
End Sub
```

## Fórmulas volátiles

Se recalculan CADA VEZ que algo cambia en la hoja:

| Volátil (evitar) | Alternativa |
|-------------------|-------------|
| `HOY()` | Valor fijo o macro que actualiza |
| `AHORA()` | Marca de tiempo con macro |
| `ALEATORIO()` | Solo si es necesario |
| `DESREF()` | Tabla de Excel o INDICE |
| `INDIRECTO()` | Referencias directas |

### Solución: Semi-volátil

Usa una celda auxiliar que se actualiza con un botón:
```vb
Sub ActualizarFecha()
    Range("Config!A1").Value = Date
End Sub
```

Y referencia esa celda en lugar de `HOY()`.

## Optimizar fórmulas

### Evitar columnas completas

❌ `=SUMAR.SI(A:A, "criterio", B:B)` → 1 millón de filas
✅ `=SUMAR.SI(A2:A10000, "criterio", B2:B10000)` → solo datos

### Usar INDICE en lugar de DESREF

❌ `=DESREF(A1, 0, 0, CONTARA(A:A), 1)` → volátil
✅ Convertir a tabla de Excel → auto-expandible sin fórmula

### Reducir anidación

❌ `=SI(A1>90,"A",SI(A1>80,"B",SI(A1>70,"C","F")))`
✅ `=BUSCARV(A1, tabla_calificaciones, 2)` con tabla auxiliar ordenada

### BUSCARV vs INDICE+COINCIDIR

En archivos grandes, `INDICE+COINCIDIR` con `COINCIDIR(,, 0)` es más rápido que BUSCARV porque COINCIDIR puede usar búsqueda binaria en datos ordenados.

## Optimizar el archivo

### Determinar el rango usado real

```vb
Sub LimpiarRangoUsado()
    Dim ws As Worksheet
    For Each ws In ThisWorkbook.Worksheets
        Dim ultimaFila As Long
        Dim ultimaCol As Long
        
        ultimaFila = ws.Cells.Find("*", SearchOrder:=xlByRows, SearchDirection:=xlPrevious).Row
        ultimaCol = ws.Cells.Find("*", SearchOrder:=xlByColumns, SearchDirection:=xlPrevious).Column
        
        ' Eliminar filas después de los datos
        If ultimaFila < ws.Rows.Count Then
            ws.Range(ws.Cells(ultimaFila + 1, 1), ws.Cells(ws.Rows.Count, 1)).EntireRow.Delete
        End If
    Next ws
End Sub
```

### Reducir estilos duplicados

Los estilos duplicados inflan el archivo. Herramientas como **XLStyles Tool** los limpian.

### Comprimir imágenes

Archivo → Información → Propiedades → Tamaño → Comprimir imágenes

### Eliminar nombres definidos huérfanos

```vb
Sub LimpiarNombres()
    Dim n As Name
    For Each n In ThisWorkbook.Names
        If InStr(n.RefersTo, "#REF!") > 0 Then
            n.Delete
        End If
    Next n
End Sub
```

## Modo de cálculo

### Manual vs Automático

```vb
' Cambiar a manual durante procesos
Application.Calculation = xlCalculationManual

' Calcular solo lo necesario
Range("A1:D100").Calculate  ' solo un rango
ActiveSheet.Calculate        ' solo la hoja activa
Application.CalculateFull   ' todo el libro

' Restaurar automático
Application.Calculation = xlCalculationAutomatic
```

## Formato condicional eficiente

- Aplica a rangos específicos, no a columnas enteras
- Limita el número de reglas (< 5 por rango)
- Usa reglas simples (valores, escalas) en lugar de fórmulas
- Elimina reglas que ya no necesitas

### Auditar formato condicional

```vb
Sub AuditarFormatoCondicional()
    Dim ws As Worksheet
    For Each ws In ThisWorkbook.Worksheets
        If ws.Cells.FormatConditions.Count > 0 Then
            Debug.Print ws.Name & ": " & ws.Cells.FormatConditions.Count & " reglas"
        End If
    Next ws
End Sub
```

## VBA optimizado

```vb
Sub ProcesoOptimo()
    ' 1. Desactivar actualizaciones
    Application.ScreenUpdating = False
    Application.EnableEvents = False
    Application.Calculation = xlCalculationManual
    
    ' 2. Usar arrays (no celda por celda)
    Dim datos As Variant
    datos = Range("A1:Z10000").Value
    
    ' 3. Procesar en memoria
    Dim i As Long
    For i = 1 To UBound(datos)
        datos(i, 26) = datos(i, 1) * datos(i, 2)
    Next i
    
    ' 4. Escribir de una vez
    Range("A1:Z10000").Value = datos
    
    ' 5. Restaurar
    Application.ScreenUpdating = True
    Application.EnableEvents = True
    Application.Calculation = xlCalculationAutomatic
End Sub
```

## Resumen

| Problema | Solución |
|----------|----------|
| Fórmulas lentas | Evitar volátiles, limitar rangos |
| Archivo grande | Limpiar estilos, comprimir imágenes |
| VBA lento | Arrays, desactivar pantalla |
| Formato condicional | Rangos específicos, menos reglas |
| Cálculo lento | Modo manual, calcular selectivamente |
