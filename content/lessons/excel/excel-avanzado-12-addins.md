# Add-ins y complementos personalizados

Crea herramientas reutilizables que puedes distribuir e instalar en cualquier Excel.

## ¿Qué es un Add-in?

Un archivo `.xlam` que agrega funcionalidades a Excel:
- Funciones personalizadas disponibles en todos los libros
- Nuevas opciones en la cinta de opciones
- Menús y barras de herramientas
- Automatizaciones que siempre están disponibles

## Crear un Add-in básico

### Paso 1: Crear el proyecto

1. Nuevo libro de Excel
2. Alt+F11 → agregar módulos con tu código
3. Guardar como → **Complemento de Excel (.xlam)**
4. Se guarda automáticamente en la carpeta de Add-ins

### Paso 2: Agregar funciones

```vb
' modFunciones
Public Function RFC_VALIDO(rfc As String) As Boolean
    ' Valida formato de RFC mexicano
    If Len(rfc) = 12 Or Len(rfc) = 13 Then
        RFC_VALIDO = True
    Else
        RFC_VALIDO = False
    End If
End Function

Public Function QUITAR_ACENTOS(texto As String) As String
    Dim i As Long
    Dim c As String
    Dim resultado As String
    
    For i = 1 To Len(texto)
        c = Mid(texto, i, 1)
        Select Case c
            Case "á", "à", "ä", "â": c = "a"
            Case "é", "è", "ë", "ê": c = "e"
            Case "í", "ì", "ï", "î": c = "i"
            Case "ó", "ò", "ö", "ô": c = "o"
            Case "ú", "ù", "ü", "û": c = "u"
            Case "Á", "À", "Ä", "Â": c = "A"
            Case "É", "È", "Ë", "Ê": c = "E"
            Case "Í", "Ì", "Ï", "Î": c = "I"
            Case "Ó", "Ò", "Ö", "Ô": c = "O"
            Case "Ú", "Ù", "Ü", "Û": c = "U"
            Case "ñ": c = "n"
            Case "Ñ": c = "N"
        End Select
        resultado = resultado & c
    Next i
    
    QUITAR_ACENTOS = resultado
End Function

Public Function EDAD(fechaNacimiento As Date) As Long
    EDAD = DateDiff("yyyy", fechaNacimiento, Date)
    If Date < DateSerial(Year(Date), Month(fechaNacimiento), Day(fechaNacimiento)) Then
        EDAD = EDAD - 1
    End If
End Function
```

### Paso 3: Instalar

1. Archivo → Opciones → Complementos
2. Complementos de Excel → Ir
3. **Examinar** → selecciona el archivo `.xlam`
4. Marca la casilla → Aceptar

## Cinta de opciones personalizada (Ribbon)

### Usando XML del Ribbon

1. Crea el archivo XML del Ribbon (customUI.xml):

```xml
<customUI xmlns="http://schemas.microsoft.com/office/2009/07/customui">
  <ribbon>
    <tabs>
      <tab id="miTab" label="Mis Herramientas">
        <group id="grpDatos" label="Datos">
          <button id="btnLimpiar" label="Limpiar Datos"
                  imageMso="ClearFormatting"
                  size="large"
                  onAction="btnLimpiar_Click"/>
          <button id="btnExportar" label="Exportar PDF"
                  imageMso="FileSaveAsPdfOrXps"
                  size="large"
                  onAction="btnExportar_Click"/>
        </group>
        <group id="grpFormato" label="Formato">
          <button id="btnTabla" label="Formatear Tabla"
                  imageMso="TableDesign"
                  onAction="btnTabla_Click"/>
        </group>
      </tab>
    </tabs>
  </ribbon>
</customUI>
```

2. Los callbacks en VBA:

```vb
Sub btnLimpiar_Click(control As IRibbonControl)
    ' Lógica de limpieza
    Call LimpiarDatos
End Sub
```

### Herramientas para editar el Ribbon

- **Custom UI Editor**: editor gratuito para XML del Ribbon
- O: renombrar .xlam a .zip → editar customUI/customUI.xml

## Menú contextual personalizado

```vb
Sub AgregarMenuContextual()
    Dim menuBar As CommandBar
    Set menuBar = Application.CommandBars("Cell")
    
    ' Limpiar menú anterior
    On Error Resume Next
    menuBar.Controls("Mis Herramientas").Delete
    On Error GoTo 0
    
    ' Agregar submenú
    Dim subMenu As CommandBarPopup
    Set subMenu = menuBar.Controls.Add(Type:=msoControlPopup)
    subMenu.Caption = "Mis Herramientas"
    
    ' Agregar opciones
    With subMenu.Controls.Add(Type:=msoControlButton)
        .Caption = "Limpiar selección"
        .OnAction = "LimpiarSeleccion"
        .FaceId = 67
    End With
End Sub
```

## Distribuir el Add-in

### Método simple

Comparte el archivo `.xlam` por email o red compartida.

### Con instalador

Crea un script de instalación:

```vb
Sub InstalarAddin()
    Dim rutaOrigen As String
    Dim rutaDestino As String
    
    rutaOrigen = ThisWorkbook.Path & "\MiAddin.xlam"
    rutaDestino = Application.UserLibraryPath & "MiAddin.xlam"
    
    FileCopy rutaOrigen, rutaDestino
    
    AddIns.Add rutaDestino
    AddIns("MiAddin").Installed = True
    
    MsgBox "Add-in instalado correctamente"
End Sub
```

## Resumen

Los Add-ins encapsulan funcionalidades reutilizables que se integran naturalmente en Excel. Combinan funciones UDF, automatizaciones VBA y personalización del Ribbon para crear herramientas profesionales distribuibles.
