# VBA: fundamentos de programación en Excel

Visual Basic for Applications permite automatizar cualquier tarea en Excel con código.

## El editor VBA

**Alt+F11** abre el editor.

### Estructura del proyecto

```
VBAProject (MiArchivo.xlsm)
├── Microsoft Excel Objects
│   ├── ThisWorkbook (eventos del libro)
│   ├── Hoja1 (eventos de la hoja)
│   └── Hoja2
├── Modules
│   ├── Modulo1 (código general)
│   └── Modulo2
└── Forms (formularios de usuario)
```

### Ventana Inmediato (Ctrl+G)

Ejecuta código línea por línea para pruebas:
```vb
? 2 + 2
? Range("A1").Value
? ActiveSheet.Name
```

## Variables y tipos de datos

```vb
Dim nombre As String
Dim edad As Integer
Dim precio As Double
Dim activo As Boolean
Dim fecha As Date
Dim rango As Range
Dim hoja As Worksheet
Dim libro As Workbook
```

### Option Explicit

Siempre incluye al inicio del módulo:
```vb
Option Explicit
```
Obliga a declarar todas las variables, previniendo errores de tipeo.

## Estructuras de control

### IF

```vb
If celda.Value > 100 Then
    celda.Interior.Color = RGB(0, 255, 0)
ElseIf celda.Value > 50 Then
    celda.Interior.Color = RGB(255, 255, 0)
Else
    celda.Interior.Color = RGB(255, 0, 0)
End If
```

### Select Case

```vb
Select Case calificacion
    Case Is >= 90
        resultado = "A"
    Case Is >= 80
        resultado = "B"
    Case Is >= 70
        resultado = "C"
    Case Else
        resultado = "F"
End Select
```

### For Next

```vb
Dim i As Long
For i = 1 To 100
    Cells(i, 1).Value = i * 2
Next i
```

### For Each

```vb
Dim celda As Range
For Each celda In Range("A1:A100")
    If celda.Value < 0 Then
        celda.Font.Color = RGB(255, 0, 0)
    End If
Next celda
```

### Do While / Do Until

```vb
Dim fila As Long
fila = 1
Do While Cells(fila, 1).Value <> ""
    ' procesar fila
    fila = fila + 1
Loop
```

## Trabajar con rangos

```vb
' Seleccionar
Range("A1:D10").Select
Cells(1, 1).Select           ' fila 1, columna 1

' Leer y escribir
valor = Range("A1").Value
Range("B1").Value = "Hola"

' Última fila con datos
ultimaFila = Cells(Rows.Count, 1).End(xlUp).Row

' Última columna con datos
ultimaCol = Cells(1, Columns.Count).End(xlToLeft).Column

' Rango dinámico
Set rng = Range("A1:D" & ultimaFila)

' Región actual (datos contiguos)
Set rng = Range("A1").CurrentRegion

' Copiar y pegar
Range("A1:D10").Copy Destination:=Range("F1")

' Limpiar
Range("A1:D10").ClearContents  ' solo valores
Range("A1:D10").Clear          ' todo (formato incluido)
```

## Trabajar con hojas y libros

```vb
' Hojas
Dim ws As Worksheet
Set ws = ThisWorkbook.Sheets("Datos")
ws.Activate
ws.Range("A1").Value = "Test"

' Agregar hoja
Dim nuevaHoja As Worksheet
Set nuevaHoja = ThisWorkbook.Sheets.Add
nuevaHoja.Name = "Resultados"

' Libros
Dim wb As Workbook
Set wb = Workbooks.Open("C:\ruta\archivo.xlsx")
wb.Close SaveChanges:=True
```

## Procedimientos Sub y Function

### Sub (no devuelve valor)

```vb
Sub FormatearTabla()
    With Range("A1:E1")
        .Font.Bold = True
        .Interior.Color = RGB(0, 102, 204)
        .Font.Color = RGB(255, 255, 255)
    End With
End Sub
```

### Function (devuelve valor)

```vb
Function CalcularIVA(monto As Double) As Double
    CalcularIVA = monto * 0.16
End Function
```

Puedes usar funciones VBA directamente en celdas:
```
=CalcularIVA(A2)
```

## MsgBox e InputBox

```vb
' Mensaje
MsgBox "Proceso completado", vbInformation, "Éxito"

' Confirmación
respuesta = MsgBox("¿Continuar?", vbYesNo + vbQuestion, "Confirmar")
If respuesta = vbYes Then
    ' continuar
End If

' Entrada de usuario
nombre = InputBox("Ingrese su nombre:", "Datos")
```

## Resumen

VBA es el lenguaje de automatización nativo de Excel. Con variables, bucles, condicionales y manipulación de rangos puedes automatizar prácticamente cualquier tarea. La clave es empezar simple, usar Option Explicit, y construir gradualmente.
