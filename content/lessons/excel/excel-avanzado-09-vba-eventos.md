# VBA: eventos y automatización avanzada

Programa Excel para reaccionar automáticamente a acciones del usuario.

## Eventos de libro (ThisWorkbook)

### Al abrir el libro

```vb
Private Sub Workbook_Open()
    ' Ir al dashboard
    Sheets("Dashboard").Activate
    
    ' Actualizar datos
    ActiveWorkbook.RefreshAll
    
    ' Registro de uso
    Sheets("Log").Cells(Rows.Count, 1).End(xlUp).Offset(1, 0).Value = Now
    Sheets("Log").Cells(Rows.Count, 1).End(xlUp).Offset(0, 1).Value = Environ("USERNAME")
End Sub
```

### Antes de guardar

```vb
Private Sub Workbook_BeforeSave(ByVal SaveAsUI As Boolean, Cancel As Boolean)
    ' Actualizar fecha de última modificación
    Sheets("Config").Range("B1").Value = Now
    
    ' Ir a la primera hoja
    Sheets(1).Activate
    Range("A1").Select
End Sub
```

### Antes de cerrar

```vb
Private Sub Workbook_BeforeClose(Cancel As Boolean)
    Dim resp As VbMsgBoxResult
    resp = MsgBox("¿Desea exportar el reporte antes de cerrar?", vbYesNo)
    
    If resp = vbYes Then
        Call ExportarReporte
    End If
End Sub
```

## Eventos de hoja

### Cambio de celda (Worksheet_Change)

```vb
Private Sub Worksheet_Change(ByVal Target As Range)
    ' Solo si cambia la columna A
    If Target.Column <> 1 Then Exit Sub
    If Target.Count > 1 Then Exit Sub  ' evitar pegado múltiple
    
    ' Registro automático de fecha/usuario
    Application.EnableEvents = False  ' evitar loop infinito
    
    Target.Offset(0, 5).Value = Now
    Target.Offset(0, 6).Value = Environ("USERNAME")
    
    Application.EnableEvents = True
End Sub
```

### Validación en tiempo real

```vb
Private Sub Worksheet_Change(ByVal Target As Range)
    If Not Intersect(Target, Range("C2:C1000")) Is Nothing Then
        Application.EnableEvents = False
        
        If Not IsNumeric(Target.Value) And Target.Value <> "" Then
            MsgBox "Solo se permiten números", vbExclamation
            Target.ClearContents
            Target.Select
        ElseIf Target.Value < 0 Then
            MsgBox "No se permiten valores negativos", vbExclamation
            Target.ClearContents
        End If
        
        Application.EnableEvents = True
    End If
End Sub
```

### Selección de celda (SelectionChange)

```vb
Private Sub Worksheet_SelectionChange(ByVal Target As Range)
    ' Resaltar fila y columna activa
    Cells.Interior.ColorIndex = xlNone
    
    If Target.Count = 1 Then
        Target.EntireRow.Interior.Color = RGB(230, 240, 255)
        Target.EntireColumn.Interior.Color = RGB(230, 240, 255)
    End If
End Sub
```

## Temporizadores automáticos

### Ejecutar cada X minutos

```vb
' En un módulo estándar
Dim proximaEjecucion As Date

Sub IniciarTemporizador()
    proximaEjecucion = Now + TimeValue("00:05:00")  ' cada 5 min
    Application.OnTime proximaEjecucion, "ActualizarDatos"
End Sub

Sub DetenerTemporizador()
    On Error Resume Next
    Application.OnTime proximaEjecucion, "ActualizarDatos", , False
    On Error GoTo 0
End Sub

Sub ActualizarDatos()
    ' Tu lógica de actualización
    ActiveWorkbook.RefreshAll
    Sheets("Config").Range("B2").Value = "Última actualización: " & Format(Now, "hh:mm:ss")
    
    ' Reprogramar
    Call IniciarTemporizador
End Sub
```

## Automatizar emails con Outlook

```vb
Sub EnviarReportePorEmail()
    Dim olApp As Object
    Dim olMail As Object
    
    Set olApp = CreateObject("Outlook.Application")
    Set olMail = olApp.CreateItem(0)
    
    With olMail
        .To = "gerencia@empresa.com"
        .CC = "equipo@empresa.com"
        .Subject = "Reporte de ventas - " & Format(Date, "dd/mm/yyyy")
        .HTMLBody = "<h2>Reporte automático</h2>" & _
                    "<p>Total ventas: $" & Format(Range("B1").Value, "#,##0") & "</p>" & _
                    "<p>Ver archivo adjunto para detalle.</p>"
        .Attachments.Add ThisWorkbook.FullName
        .Display  ' o .Send para enviar directamente
    End With
    
    Set olMail = Nothing
    Set olApp = Nothing
End Sub
```

## Automatizar exportación PDF

```vb
Sub ExportarHojasAPDF()
    Dim ws As Worksheet
    Dim ruta As String
    ruta = ThisWorkbook.Path & "\Reportes\"
    
    ' Crear carpeta si no existe
    If Dir(ruta, vbDirectory) = "" Then MkDir ruta
    
    For Each ws In ThisWorkbook.Worksheets
        If Left(ws.Name, 4) = "Rpt_" Then
            ws.ExportAsFixedFormat _
                Type:=xlTypePDF, _
                Filename:=ruta & ws.Name & "_" & Format(Date, "yyyymmdd") & ".pdf", _
                Quality:=xlQualityStandard
        End If
    Next ws
    
    MsgBox "PDFs exportados a: " & ruta
End Sub
```

## Barra de progreso personalizada

```vb
Sub ProcesoConProgreso()
    Dim total As Long: total = 10000
    Dim i As Long
    
    For i = 1 To total
        ' Tu proceso
        Cells(i, 1).Value = i
        
        ' Actualizar barra cada 100 iteraciones
        If i Mod 100 = 0 Then
            Application.StatusBar = "Procesando... " & _
                Format(i / total, "0%") & " (" & i & "/" & total & ")"
            DoEvents  ' permite cancelar
        End If
    Next i
    
    Application.StatusBar = False  ' restaurar
End Sub
```

## Resumen

Los eventos VBA convierten a Excel en una aplicación reactiva. Worksheet_Change para validación y logging, Workbook_Open para inicialización, y Application.OnTime para tareas programadas. Combina con automatización de Outlook y PDF para workflows completos.
