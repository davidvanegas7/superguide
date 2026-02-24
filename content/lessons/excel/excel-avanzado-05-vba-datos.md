# VBA: manipulación avanzada de datos

Técnicas VBA para procesar, transformar y analizar datos eficientemente.

## Rendimiento: Application settings

Siempre desactiva actualizaciones durante procesos largos:

```vb
Sub ProcesoOptimizado()
    Application.ScreenUpdating = False
    Application.Calculation = xlCalculationManual
    Application.EnableEvents = False
    
    ' ... tu código aquí ...
    
    Application.ScreenUpdating = True
    Application.Calculation = xlCalculationAutomatic
    Application.EnableEvents = True
End Sub
```

## Arrays en VBA

Los arrays son mucho más rápidos que leer celda por celda:

```vb
Sub ProcesarConArrays()
    Dim datos As Variant
    Dim resultado() As Variant
    Dim ultimaFila As Long
    
    ultimaFila = Cells(Rows.Count, 1).End(xlUp).Row
    
    ' Leer todo el rango a un array (instantáneo)
    datos = Range("A1:D" & ultimaFila).Value
    
    ' Procesar en memoria
    ReDim resultado(1 To UBound(datos, 1), 1 To 1)
    
    Dim i As Long
    For i = 1 To UBound(datos, 1)
        resultado(i, 1) = datos(i, 1) * datos(i, 2) ' Cantidad * Precio
    Next i
    
    ' Escribir todo de una vez
    Range("E1:E" & ultimaFila).Value = resultado
End Sub
```

## Diccionarios (Scripting.Dictionary)

Estructura para búsquedas rápidas y eliminar duplicados:

```vb
Sub UsarDiccionario()
    Dim dict As Object
    Set dict = CreateObject("Scripting.Dictionary")
    
    Dim i As Long
    For i = 2 To 1000
        Dim clave As String
        clave = Cells(i, 1).Value
        
        If dict.Exists(clave) Then
            dict(clave) = dict(clave) + Cells(i, 3).Value
        Else
            dict.Add clave, Cells(i, 3).Value
        End If
    Next i
    
    ' Escribir resultados
    Dim fila As Long: fila = 1
    Dim k As Variant
    For Each k In dict.Keys
        Sheets("Resumen").Cells(fila, 1).Value = k
        Sheets("Resumen").Cells(fila, 2).Value = dict(k)
        fila = fila + 1
    Next k
End Sub
```

## Colecciones (Collection)

```vb
Dim col As New Collection
col.Add "Elemento1", "clave1"
col.Add "Elemento2", "clave2"

' Iterar
Dim item As Variant
For Each item In col
    Debug.Print item
Next item
```

## Trabajar con archivos

### FileSystemObject

```vb
Sub TrabajarArchivos()
    Dim fso As Object
    Set fso = CreateObject("Scripting.FileSystemObject")
    
    ' Verificar si existe
    If fso.FileExists("C:\ruta\archivo.csv") Then
        ' Leer archivo de texto
        Dim archivo As Object
        Set archivo = fso.OpenTextFile("C:\ruta\archivo.csv", 1) ' 1=lectura
        
        Do While Not archivo.AtEndOfStream
            Dim linea As String
            linea = archivo.ReadLine
            ' procesar línea
        Loop
        archivo.Close
    End If
    
    ' Listar archivos en carpeta
    Dim carpeta As Object
    Set carpeta = fso.GetFolder("C:\datos\")
    
    Dim f As Object
    For Each f In carpeta.Files
        If Right(f.Name, 4) = ".csv" Then
            Debug.Print f.Name & " - " & f.Size & " bytes"
        End If
    Next f
End Sub
```

### Procesar múltiples archivos Excel

```vb
Sub CombinarArchivos()
    Dim ruta As String
    ruta = "C:\datos\"
    
    Dim archivo As String
    archivo = Dir(ruta & "*.xlsx")
    
    Dim filaDestino As Long: filaDestino = 2
    
    Do While archivo <> ""
        Dim wb As Workbook
        Set wb = Workbooks.Open(ruta & archivo)
        
        Dim ultimaFila As Long
        ultimaFila = wb.Sheets(1).Cells(Rows.Count, 1).End(xlUp).Row
        
        wb.Sheets(1).Range("A2:D" & ultimaFila).Copy _
            ThisWorkbook.Sheets("Consolidado").Cells(filaDestino, 1)
        
        filaDestino = filaDestino + ultimaFila - 1
        
        wb.Close SaveChanges:=False
        archivo = Dir()  ' siguiente archivo
    Loop
End Sub
```

## Manejo de errores

```vb
Sub ConManejodeErrores()
    On Error GoTo ErrorHandler
    
    ' Código que puede fallar
    Dim wb As Workbook
    Set wb = Workbooks.Open("archivo_inexistente.xlsx")
    
    Exit Sub
    
ErrorHandler:
    Select Case Err.Number
        Case 1004
            MsgBox "Archivo no encontrado"
        Case Else
            MsgBox "Error " & Err.Number & ": " & Err.Description
    End Select
    Resume Next  ' o Resume, o Exit Sub
End Sub
```

### Patrón try-resume

```vb
On Error Resume Next
valor = dict(clave)
If Err.Number <> 0 Then
    valor = "No encontrado"
    Err.Clear
End If
On Error GoTo 0  ' restaura manejo normal
```

## Temporizador

```vb
Sub ConTiempo()
    Dim inicio As Double
    inicio = Timer
    
    ' ... proceso ...
    
    Debug.Print "Tiempo: " & Format(Timer - inicio, "0.00") & " segundos"
End Sub
```

## Resumen

La manipulación avanzada de datos en VBA requiere: usar arrays para rendimiento, diccionarios para búsquedas, manejo de errores robusto, y control de Application settings. Estas técnicas pueden reducir procesos de minutos a segundos.
