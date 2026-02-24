# Manejo de errores y debugging en VBA

Técnicas profesionales para escribir código VBA robusto y depurable.

## Tipos de errores

### Errores de compilación
Se detectan antes de ejecutar: sintaxis incorrecta, variables no declaradas.

```vb
' Error de compilación: variable no declarada (con Option Explicit)
Sub Ejemplo()
    x = 10  ' ❌ x no está declarada
End Sub
```

### Errores de ejecución (Runtime)
Ocurren durante la ejecución: división por cero, archivo no encontrado.

```vb
Sub Ejemplo()
    Dim resultado As Double
    resultado = 10 / 0  ' ❌ Error 11: División por cero
End Sub
```

### Errores lógicos
El código se ejecuta sin errores pero produce resultados incorrectos.

## On Error: manejo de errores

### On Error GoTo

```vb
Sub ConManejoErrores()
    On Error GoTo ErrorHandler
    
    ' Código que puede fallar
    Dim wb As Workbook
    Set wb = Workbooks.Open("C:\archivo.xlsx")
    
    ' Si todo va bien, saltar el handler
    Exit Sub

ErrorHandler:
    MsgBox "Error " & Err.Number & ": " & Err.Description, vbCritical
    ' Opciones:
    ' Resume        → reintentar la línea que falló
    ' Resume Next   → saltar a la siguiente línea
    ' Exit Sub      → salir del procedimiento
End Sub
```

### On Error Resume Next

```vb
Sub BuscarValor()
    On Error Resume Next
    
    Dim valor As Variant
    valor = Application.WorksheetFunction.VLookup("clave", Range("A:B"), 2, False)
    
    If Err.Number <> 0 Then
        valor = "No encontrado"
        Err.Clear
    End If
    
    On Error GoTo 0  ' Restaurar manejo normal
    
    Debug.Print valor
End Sub
```

### Errores personalizados

```vb
Sub ValidarDatos(edad As Integer)
    If edad < 0 Or edad > 150 Then
        Err.Raise Number:=vbObjectError + 1, _
                  Source:="ValidarDatos", _
                  Description:="Edad no válida: " & edad
    End If
End Sub
```

## El objeto Err

| Propiedad | Descripción |
|-----------|-------------|
| `.Number` | Código del error |
| `.Description` | Mensaje descriptivo |
| `.Source` | Origen del error |
| `.Clear` | Limpia el error actual |
| `.Raise` | Genera un error |

## Patrón de manejo robusto

```vb
Sub ProcesoRobusto()
    On Error GoTo ErrorHandler
    
    ' Configuración
    Application.ScreenUpdating = False
    Application.Calculation = xlCalculationManual
    
    ' *** CÓDIGO PRINCIPAL ***
    Dim datos As Variant
    datos = Range("A1:D100").Value
    
    Dim i As Long
    For i = 1 To UBound(datos)
        ' Procesar con validación
        If IsNumeric(datos(i, 3)) Then
            datos(i, 4) = datos(i, 2) * datos(i, 3)
        Else
            datos(i, 4) = "ERROR"
        End If
    Next i
    
    Range("A1:D100").Value = datos
    ' *** FIN CÓDIGO PRINCIPAL ***

Cleanup:
    ' SIEMPRE se ejecuta (como finally)
    Application.ScreenUpdating = True
    Application.Calculation = xlCalculationAutomatic
    Exit Sub

ErrorHandler:
    MsgBox "Error en fila " & i & ": " & Err.Description, vbCritical
    Resume Cleanup  ' Ir a limpieza
End Sub
```

## Debugging

### Herramientas del editor VBA

| Herramienta | Atajo | Función |
|-------------|-------|---------|
| Punto de interrupción | F9 | Pausa la ejecución en esa línea |
| Paso a paso | F8 | Ejecuta línea por línea |
| Paso sobre procedimiento | Shift+F8 | Ejecuta sub completo |
| Ventana de inspección | | Monitorea variables |
| Ventana Inmediato | Ctrl+G | Ejecuta código y muestra valores |
| Ventana Locales | | Ve todas las variables locales |

### Debug.Print

```vb
Sub ConDebug()
    Dim total As Double
    Dim i As Long
    
    For i = 1 To 100
        total = total + Cells(i, 1).Value
        
        ' Imprimir en ventana Inmediato
        If i Mod 10 = 0 Then
            Debug.Print "Fila " & i & " | Total acumulado: " & total
        End If
    Next i
    
    Debug.Print "=== RESULTADO FINAL: " & total & " ==="
End Sub
```

### Debug.Assert

```vb
Sub ConAssert()
    Dim valor As Double
    valor = CalcularTotal()
    
    ' Detiene ejecución si la condición es False
    Debug.Assert valor >= 0  ' ¿El total es positivo?
    Debug.Assert valor < 1000000  ' ¿Es razonable?
End Sub
```

### Watch Expressions

En el editor VBA:
1. Debug → Add Watch
2. Define la expresión a monitorear
3. Tipo: Watch Expression, Break When True, Break When Changed

## Logging

```vb
' modLogger
Private logFile As Integer

Sub IniciarLog(ruta As String)
    logFile = FreeFile
    Open ruta For Append As #logFile
    Log "=== Sesión iniciada ==="
End Sub

Sub Log(mensaje As String)
    Print #logFile, Format(Now, "yyyy-mm-dd hh:mm:ss") & " | " & mensaje
End Sub

Sub CerrarLog()
    Log "=== Sesión finalizada ==="
    Close #logFile
End Sub
```

### Uso

```vb
Sub ProcesoConLog()
    IniciarLog ThisWorkbook.Path & "\log.txt"
    
    On Error GoTo ErrorHandler
    
    Log "Iniciando proceso..."
    Log "Filas a procesar: " & ultimaFila
    
    ' ... proceso ...
    
    Log "Proceso completado. " & contadorOK & " OK, " & contadorError & " errores"
    CerrarLog
    Exit Sub

ErrorHandler:
    Log "ERROR: " & Err.Number & " - " & Err.Description
    CerrarLog
    Resume Next
End Sub
```

## Resumen

El manejo de errores y debugging profesional en VBA requiere: patrones On Error con cleanup, Debug.Print estratégico, breakpoints, y logging a archivo. Siempre restaura Application settings en la sección Cleanup.
