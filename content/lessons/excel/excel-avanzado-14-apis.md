# Automatización con APIs y conexiones web

Conecta Excel con el mundo exterior: APIs REST, web scraping y datos en tiempo real.

## Web Queries con VBA

### Descargar contenido web

```vb
Sub DescargarWeb()
    Dim http As Object
    Set http = CreateObject("MSXML2.XMLHTTP")
    
    http.Open "GET", "https://api.ejemplo.com/datos", False
    http.send
    
    If http.Status = 200 Then
        Debug.Print http.responseText
    Else
        MsgBox "Error: " & http.Status
    End If
End Sub
```

### Consumir API REST (JSON)

```vb
Sub ObtenerDatosAPI()
    Dim http As Object
    Set http = CreateObject("MSXML2.XMLHTTP")
    
    Dim url As String
    url = "https://api.exchangerate-api.com/v4/latest/USD"
    
    http.Open "GET", url, False
    http.setRequestHeader "Content-Type", "application/json"
    http.send
    
    If http.Status = 200 Then
        ' Parsear JSON manualmente o con librería
        Dim json As String
        json = http.responseText
        
        ' Extraer valor con InStr (básico)
        Dim pos As Long
        pos = InStr(json, """MXN"":")
        If pos > 0 Then
            Dim valor As String
            valor = Mid(json, pos + 6, 10)
            valor = Left(valor, InStr(valor, ",") - 1)
            Range("B1").Value = "USD/MXN: " & valor
        End If
    End If
End Sub
```

### Con librería JSON (VBA-JSON)

Instala VBA-JSON (módulo JsonConverter):

```vb
Sub ConsumirAPIConJSON()
    Dim http As Object
    Set http = CreateObject("MSXML2.XMLHTTP")
    
    http.Open "GET", "https://api.ejemplo.com/productos", False
    http.setRequestHeader "Authorization", "Bearer " & Range("Config!B1").Value
    http.send
    
    Dim datos As Object
    Set datos = JsonConverter.ParseJson(http.responseText)
    
    ' Iterar resultados
    Dim fila As Long: fila = 2
    Dim item As Object
    
    For Each item In datos("results")
        Cells(fila, 1).Value = item("id")
        Cells(fila, 2).Value = item("nombre")
        Cells(fila, 3).Value = item("precio")
        fila = fila + 1
    Next item
End Sub
```

### POST con datos

```vb
Sub EnviarDatos()
    Dim http As Object
    Set http = CreateObject("MSXML2.XMLHTTP")
    
    Dim body As String
    body = "{""nombre"":""" & Range("A2").Value & """,""email"":""" & Range("B2").Value & """}"
    
    http.Open "POST", "https://api.ejemplo.com/clientes", False
    http.setRequestHeader "Content-Type", "application/json"
    http.setRequestHeader "Authorization", "Bearer " & token
    http.send body
    
    If http.Status = 201 Then
        MsgBox "Cliente creado exitosamente"
    Else
        MsgBox "Error: " & http.responseText
    End If
End Sub
```

## Power Query para APIs

### Endpoint simple

```m
let
    Origen = Json.Document(Web.Contents("https://api.ejemplo.com/datos")),
    Tabla = Table.FromRecords(Origen[data])
in
    Tabla
```

### Con paginación

```m
let
    ObtenerPagina = (pagina as number) as table =>
    let
        url = "https://api.ejemplo.com/datos?page=" & Text.From(pagina) & "&limit=100",
        respuesta = Json.Document(Web.Contents(url)),
        datos = Table.FromRecords(respuesta[data])
    in
        datos,
    
    totalPaginas = Json.Document(Web.Contents("https://api.ejemplo.com/datos?page=1&limit=100"))[total_pages],
    
    paginas = List.Transform({1..totalPaginas}, each ObtenerPagina(_)),
    Resultado = Table.Combine(paginas)
in
    Resultado
```

### Con autenticación OAuth

```m
let
    token = "tu_token_aqui",
    Origen = Json.Document(Web.Contents(
        "https://api.ejemplo.com/datos",
        [Headers = [Authorization = "Bearer " & token]]
    )),
    Tabla = Table.FromRecords(Origen)
in
    Tabla
```

## Automatización con Outlook

### Enviar emails masivos

```vb
Sub EnviarEmailsMasivos()
    Dim olApp As Object, olMail As Object
    Set olApp = CreateObject("Outlook.Application")
    
    Dim ultimaFila As Long
    ultimaFila = Cells(Rows.Count, 1).End(xlUp).Row
    
    Dim i As Long
    For i = 2 To ultimaFila
        Set olMail = olApp.CreateItem(0)
        
        With olMail
            .To = Cells(i, 2).Value         ' email
            .Subject = "Reporte - " & Cells(i, 1).Value
            .HTMLBody = GenerarHTML(i)
            .Display  ' Cambiar a .Send para envío automático
        End With
        
        Cells(i, 5).Value = "Enviado"
        Cells(i, 6).Value = Now
        
        Set olMail = Nothing
    Next i
End Sub

Function GenerarHTML(fila As Long) As String
    GenerarHTML = "<html><body>" & _
        "<h2>Estimado/a " & Cells(fila, 1).Value & "</h2>" & _
        "<p>Su saldo actual es: <b>$" & Format(Cells(fila, 3).Value, "#,##0.00") & "</b></p>" & _
        "</body></html>"
End Function
```

## Actualización automática de datos

```vb
Sub ConfigurarActualizacion()
    ' Actualizar cada 30 minutos
    Application.OnTime Now + TimeValue("00:30:00"), "ActualizarDesdAPI"
End Sub

Sub ActualizarDesdeAPI()
    Call ObtenerDatosAPI
    
    ' Registrar
    Sheets("Log").Cells(Rows.Count, 1).End(xlUp).Offset(1, 0).Value = Now
    
    ' Reprogramar
    Call ConfigurarActualizacion
End Sub
```

## Resumen

Excel puede conectarse a APIs REST, enviar emails automatizados y actualizar datos en tiempo real. VBA con XMLHTTP para APIs, Power Query para ETL web, y la integración con Outlook crean un ecosistema de automatización completo.
