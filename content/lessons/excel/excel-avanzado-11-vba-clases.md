# Clases y programación orientada a objetos en VBA

VBA soporta clases para crear código más organizado y reutilizable.

## Crear un módulo de clase

1. En el editor VBA: Insertar → **Módulo de clase**
2. Nombra la clase en la ventana de Propiedades (ej: `clsProducto`)

## Propiedades

### Con variables públicas (simple)

```vb
' clsProducto
Public Nombre As String
Public Precio As Double
Public Stock As Integer
```

### Con Property Get/Let (encapsulación)

```vb
' clsProducto
Private pNombre As String
Private pPrecio As Double
Private pStock As Integer

Public Property Get Nombre() As String
    Nombre = pNombre
End Property

Public Property Let Nombre(valor As String)
    If Len(valor) > 0 Then
        pNombre = valor
    Else
        Err.Raise 1001, "clsProducto", "El nombre no puede estar vacío"
    End If
End Property

Public Property Get Precio() As Double
    Precio = pPrecio
End Property

Public Property Let Precio(valor As Double)
    If valor >= 0 Then
        pPrecio = valor
    End If
End Property

Public Property Get PrecioConIVA() As Double
    PrecioConIVA = pPrecio * 1.16
End Property
```

### Property Set (para objetos)

```vb
Private pCategoria As clsCategoria

Public Property Set Categoria(valor As clsCategoria)
    Set pCategoria = valor
End Property

Public Property Get Categoria() As clsCategoria
    Set Categoria = pCategoria
End Property
```

## Métodos

```vb
' clsProducto
Public Function CalcularDescuento(porcentaje As Double) As Double
    CalcularDescuento = pPrecio * (1 - porcentaje)
End Function

Public Sub AgregarStock(cantidad As Integer)
    If cantidad > 0 Then
        pStock = pStock + cantidad
    End If
End Sub

Public Sub QuitarStock(cantidad As Integer)
    If cantidad > 0 And cantidad <= pStock Then
        pStock = pStock - cantidad
    Else
        Err.Raise 1002, "clsProducto", "Stock insuficiente"
    End If
End Sub
```

## Eventos de clase

### Class_Initialize (constructor)

```vb
Private Sub Class_Initialize()
    pStock = 0
    pPrecio = 0
    pNombre = "Sin nombre"
End Sub
```

### Class_Terminate (destructor)

```vb
Private Sub Class_Terminate()
    ' Liberar recursos
    Set pCategoria = Nothing
    Debug.Print "Producto destruido: " & pNombre
End Sub
```

## Usar la clase

```vb
Sub UsarProducto()
    ' Crear instancia
    Dim prod As New clsProducto
    
    ' Asignar propiedades
    prod.Nombre = "Laptop Pro"
    prod.Precio = 25000
    prod.AgregarStock 50
    
    ' Usar métodos
    Debug.Print prod.Nombre & ": $" & prod.PrecioConIVA
    Debug.Print "Con 10% desc: $" & prod.CalcularDescuento(0.1)
    
    ' Liberar
    Set prod = Nothing
End Sub
```

## Colecciones de objetos

```vb
' clsInventario (Collection wrapper)
Private colProductos As Collection

Private Sub Class_Initialize()
    Set colProductos = New Collection
End Sub

Public Sub Agregar(prod As clsProducto)
    colProductos.Add prod, prod.Nombre
End Sub

Public Function Obtener(nombre As String) As clsProducto
    Set Obtener = colProductos(nombre)
End Function

Public Property Get Cantidad() As Long
    Cantidad = colProductos.Count
End Property

Public Function BuscarPorPrecio(minimo As Double) As Collection
    Dim resultado As New Collection
    Dim prod As clsProducto
    
    For Each prod In colProductos
        If prod.Precio >= minimo Then
            resultado.Add prod
        End If
    Next prod
    
    Set BuscarPorPrecio = resultado
End Function

Public Property Get Items() As Collection
    Set Items = colProductos
End Property
```

### Uso del inventario

```vb
Sub GestionarInventario()
    Dim inv As New clsInventario
    
    Dim p1 As New clsProducto
    p1.Nombre = "Laptop"
    p1.Precio = 25000
    inv.Agregar p1
    
    Dim p2 As New clsProducto
    p2.Nombre = "Mouse"
    p2.Precio = 500
    inv.Agregar p2
    
    Debug.Print "Total productos: " & inv.Cantidad
    
    ' Buscar caros
    Dim caros As Collection
    Set caros = inv.BuscarPorPrecio(1000)
    
    Dim p As clsProducto
    For Each p In caros
        Debug.Print p.Nombre & " - $" & p.Precio
    Next p
End Sub
```

## Interfaces (simulación)

VBA no tiene interfaces formales, pero puedes simularlas:

```vb
' clsIExportable (clase abstracta)
Public Sub Exportar(ruta As String)
    ' Debe ser implementada
    Err.Raise 1003, , "Método no implementado"
End Sub

Public Function ToJSON() As String
    Err.Raise 1003, , "Método no implementado"
End Function
```

Cada clase que "implementa" la interfaz sobreescribe los métodos.

## Patrón Factory

```vb
' modFactory (módulo estándar)
Public Function CrearProductoDesdeRango(rng As Range) As clsProducto
    Dim prod As New clsProducto
    prod.Nombre = rng.Cells(1, 1).Value
    prod.Precio = rng.Cells(1, 2).Value
    prod.AgregarStock CInt(rng.Cells(1, 3).Value)
    Set CrearProductoDesdeRango = prod
End Function
```

## Resumen

Las clases VBA permiten crear código modular, encapsulado y reutilizable. Property Get/Let controla el acceso a datos, los métodos encapsulan la lógica, y las colecciones de objetos manejan grupos de instancias.
