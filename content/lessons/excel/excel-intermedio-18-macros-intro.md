# Introducción a macros y automatización

Las macros permiten automatizar tareas repetitivas grabando secuencias de acciones.

## ¿Qué es una macro?

Una macro es un programa escrito en VBA (Visual Basic for Applications) que ejecuta una serie de acciones automáticamente.

## Habilitar la pestaña Desarrollador

1. Clic derecho en la cinta de opciones
2. **Personalizar la cinta de opciones**
3. Marca **Desarrollador**

## Grabar una macro

1. Desarrollador → **Grabar macro**
2. Configura:
   - **Nombre**: sin espacios, empieza con letra (ej: `FormatearTabla`)
   - **Método abreviado**: Ctrl + letra (ej: Ctrl+Shift+F)
   - **Almacenar en**: Este libro o Libro de macros personal
   - **Descripción**: qué hace la macro
3. Realiza las acciones que quieres automatizar
4. **Detener grabación**

### Consejos para grabar

- Planifica los pasos antes de grabar
- Ve lento y preciso
- Usa atajos de teclado cuando sea posible
- Evita clics innecesarios

## Ver y editar el código VBA

Alt+F11 abre el **Editor de Visual Basic**

### Estructura básica

```vb
Sub FormatearTabla()
    ' Seleccionar rango
    Range("A1:E1").Select
    
    ' Aplicar negrita
    Selection.Font.Bold = True
    
    ' Color de fondo
    Selection.Interior.Color = RGB(0, 112, 192)
    
    ' Color de fuente
    Selection.Font.Color = RGB(255, 255, 255)
    
    ' Autoajustar columnas
    Cells.EntireColumn.AutoFit
End Sub
```

## Ejecutar macros

- Desarrollador → **Macros** → selecciona y ejecuta
- Atajo de teclado asignado (ej: Ctrl+Shift+F)
- Desde un botón en la hoja

### Agregar botón de macro

1. Desarrollador → **Insertar** → Botón (Control de formulario)
2. Dibuja el botón en la hoja
3. Asigna la macro
4. Edita el texto del botón

## Macros útiles comunes

### Limpiar formato

```vb
Sub LimpiarFormato()
    Cells.ClearFormats
    Cells.EntireColumn.AutoFit
End Sub
```

### Insertar fecha y hora

```vb
Sub InsertarFechaHora()
    ActiveCell.Value = Now
    ActiveCell.NumberFormat = "dd/mm/yyyy hh:mm"
End Sub
```

### Exportar hoja como PDF

```vb
Sub ExportarPDF()
    Dim ruta As String
    ruta = ThisWorkbook.Path & "\" & ActiveSheet.Name & ".pdf"
    ActiveSheet.ExportAsFixedFormat Type:=xlTypePDF, Filename:=ruta
    MsgBox "PDF guardado en: " & ruta
End Sub
```

### Copiar datos a nueva hoja

```vb
Sub CopiarANueva()
    Dim ws As Worksheet
    Set ws = Sheets.Add
    ws.Name = "Copia " & Format(Date, "dd-mm")
    Sheets("Datos").Range("A1").CurrentRegion.Copy ws.Range("A1")
End Sub
```

## Formato de archivo

Los archivos con macros deben guardarse como:
- **.xlsm** (Libro de Excel habilitado para macros)
- **.xlsb** (Binario, más compacto)

> ⚠️ `.xlsx` NO guarda macros. Excel te avisará al guardar.

## Seguridad de macros

Archivo → Opciones → Centro de confianza → **Configuración del Centro de confianza** → **Configuración de macros**

| Nivel | Descripción |
|-------|-------------|
| Deshabilitar todo | Ninguna macro se ejecuta |
| Con notificación | Pregunta antes de ejecutar (recomendado) |
| Excepto firmadas | Solo macros con certificado digital |
| Habilitar todo | ⚠️ Riesgo de seguridad |

## Libro de macros personal

Para macros que uses en cualquier archivo:

1. Al grabar, selecciona **Libro de macros personal**
2. Se guarda en `PERSONAL.XLSB`
3. Se abre automáticamente (oculto) con Excel

## Resumen

Las macros básicas son accesibles para cualquier usuario a través de la grabadora. Para tareas repetitivas como formatear, copiar datos o exportar, las macros ahorran horas de trabajo. El siguiente paso es aprender VBA para crear automatizaciones más sofisticadas.
