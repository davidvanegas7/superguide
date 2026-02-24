# UserForms: formularios interactivos en VBA

Los UserForms permiten construir interfaces gráficas personalizadas para entrada de datos.

## Crear un UserForm

1. Alt+F11 → Editor VBA
2. Insertar → **UserForm**
3. Usa el **Cuadro de herramientas** para agregar controles

## Controles principales

| Control | Uso |
|---------|-----|
| Label | Texto descriptivo |
| TextBox | Entrada de texto |
| ComboBox | Lista desplegable |
| ListBox | Lista de selección |
| CommandButton | Botón de acción |
| CheckBox | Casilla de verificación |
| OptionButton | Botón de opción (radio) |
| Frame | Agrupar controles |
| SpinButton | Incrementar/decrementar |
| Image | Mostrar imagen |
| MultiPage | Pestañas |

## Propiedades importantes

### TextBox
```
Name: txtNombre
MaxLength: 50
PasswordChar: * (para contraseñas)
MultiLine: True/False
```

### ComboBox
```
Name: cmbPais
Style: fmStyleDropDownList (no editable)
ColumnCount: 2
BoundColumn: 1
```

### CommandButton
```
Name: btnGuardar
Caption: Guardar
Default: True (se activa con Enter)
Cancel: True (se activa con Escape)
```

## Eventos del formulario

### Inicializar (al abrir)

```vb
Private Sub UserForm_Initialize()
    ' Llenar combo con datos de una hoja
    Dim ultimaFila As Long
    ultimaFila = Sheets("Catálogos").Cells(Rows.Count, 1).End(xlUp).Row
    
    Dim i As Long
    For i = 2 To ultimaFila
        cmbPais.AddItem Sheets("Catálogos").Cells(i, 1).Value
    Next i
    
    ' Valores por defecto
    txtFecha.Value = Format(Date, "dd/mm/yyyy")
    cmbEstado.Value = "Activo"
    
    ' Centrar formulario
    Me.StartUpPosition = 0
    Me.Left = Application.Left + (Application.Width - Me.Width) / 2
    Me.Top = Application.Top + (Application.Height - Me.Height) / 2
End Sub
```

### Botón Guardar

```vb
Private Sub btnGuardar_Click()
    ' Validar campos requeridos
    If txtNombre.Value = "" Then
        MsgBox "El nombre es requerido", vbExclamation
        txtNombre.SetFocus
        Exit Sub
    End If
    
    If Not IsNumeric(txtMonto.Value) Then
        MsgBox "El monto debe ser numérico", vbExclamation
        txtMonto.SetFocus
        Exit Sub
    End If
    
    ' Encontrar siguiente fila vacía
    Dim ws As Worksheet
    Set ws = Sheets("Datos")
    Dim fila As Long
    fila = ws.Cells(Rows.Count, 1).End(xlUp).Row + 1
    
    ' Escribir datos
    ws.Cells(fila, 1).Value = txtNombre.Value
    ws.Cells(fila, 2).Value = cmbPais.Value
    ws.Cells(fila, 3).Value = CDbl(txtMonto.Value)
    ws.Cells(fila, 4).Value = CDate(txtFecha.Value)
    ws.Cells(fila, 5).Value = IIf(chkActivo.Value, "Activo", "Inactivo")
    
    ' Limpiar formulario
    LimpiarFormulario
    
    MsgBox "Registro guardado exitosamente", vbInformation
End Sub
```

### Botón Cancelar

```vb
Private Sub btnCancelar_Click()
    Unload Me
End Sub
```

### Limpiar formulario

```vb
Private Sub LimpiarFormulario()
    Dim ctrl As Control
    For Each ctrl In Me.Controls
        Select Case TypeName(ctrl)
            Case "TextBox"
                ctrl.Value = ""
            Case "ComboBox"
                ctrl.ListIndex = -1
            Case "CheckBox"
                ctrl.Value = False
        End Select
    Next ctrl
    txtNombre.SetFocus
End Sub
```

## Listas desplegables dependientes

```vb
Private Sub cmbPais_Change()
    cmbCiudad.Clear
    
    Select Case cmbPais.Value
        Case "México"
            cmbCiudad.AddItem "CDMX"
            cmbCiudad.AddItem "Guadalajara"
            cmbCiudad.AddItem "Monterrey"
        Case "Colombia"
            cmbCiudad.AddItem "Bogotá"
            cmbCiudad.AddItem "Medellín"
            cmbCiudad.AddItem "Cali"
    End Select
End Sub
```

## Editar registros existentes

```vb
Private Sub lstRegistros_DblClick(ByVal Cancel As MSForms.ReturnBoolean)
    Dim fila As Long
    fila = lstRegistros.ListIndex + 2  ' +2 por encabezado y base 0
    
    txtNombre.Value = Sheets("Datos").Cells(fila, 1).Value
    cmbPais.Value = Sheets("Datos").Cells(fila, 2).Value
    txtMonto.Value = Sheets("Datos").Cells(fila, 3).Value
    
    ' Guardar fila para actualización
    Me.Tag = fila
End Sub
```

## Mostrar el formulario

Desde un módulo estándar:

```vb
Sub MostrarFormulario()
    Dim frm As New frmIngresoDatos
    frm.Show
End Sub
```

O como ventana no modal (permite interactuar con Excel):
```vb
frm.Show vbModeless
```

## Resumen

Los UserForms llevan la interacción con Excel al nivel de una aplicación de escritorio. Combinan validación, listas dinámicas y formato profesional para crear experiencias de usuario intuitivas y a prueba de errores.
