# Power Query avanzado: lenguaje M

El lenguaje M es el motor detrás de Power Query. Dominarlo desbloquea transformaciones imposibles con la interfaz visual.

## Estructura del lenguaje M

Cada consulta es una expresión `let...in`:

```m
let
    Origen = Excel.CurrentWorkbook(){[Name="Tabla1"]}[Content],
    TipoCambiado = Table.TransformColumnTypes(Origen, {
        {"Fecha", type date},
        {"Monto", type number}
    }),
    Filtrado = Table.SelectRows(TipoCambiado, each [Monto] > 1000),
    Resultado = Table.AddColumn(Filtrado, "IVA", each [Monto] * 0.16)
in
    Resultado
```

### Conceptos clave

- Cada paso es una variable
- El último valor después de `in` es lo que se devuelve
- M es case-sensitive
- Los comentarios usan `//` o `/* */`

## Tipos de datos en M

```m
// Primitivos
"texto"           // text
42                // number
true              // logical
#date(2026,1,15)  // date
#time(14,30,0)    // time
#datetime(2026,1,15,14,30,0)  // datetime
null              // null

// Estructurados
{1, 2, 3}           // list (lista)
[Nombre="Ana", Edad=30]  // record (registro)
#table({"Col1","Col2"}, {{"a",1},{"b",2}})  // table
```

## Funciones de tabla avanzadas

### Table.TransformColumns

Modifica valores en columnas existentes:

```m
= Table.TransformColumns(Origen, {
    {"Nombre", Text.Upper},
    {"Precio", each _ * 1.16},
    {"Fecha", each Date.Year(_)}
})
```

### Table.ReplaceValue con función

```m
= Table.ReplaceValue(Origen, each [Precio], 
    each if [Categoría] = "Premium" then [Precio] * 1.2 else [Precio],
    Replacer.ReplaceValue, {"Precio"})
```

### Table.Pivot y Table.Unpivot

```m
// Pivotar: filas → columnas
= Table.Pivot(Origen, List.Distinct(Origen[Mes]), "Mes", "Valor", List.Sum)

// Despivotar
= Table.UnpivotOtherColumns(Origen, {"Producto", "Región"}, "Atributo", "Valor")
```

## Funciones personalizadas en M

### Crear función

```m
let
    MiFuncion = (monto as number, tasa as number) as number =>
        monto * (1 + tasa),
    
    Resultado = MiFuncion(1000, 0.16)
in
    Resultado
```

### Función como consulta reutilizable

Crea una consulta llamada `fnLimpiarTexto`:

```m
(texto as text) as text =>
let
    sinEspacios = Text.Trim(texto),
    sinSaltosLinea = Text.Replace(sinEspacios, "#(lf)", " "),
    limpio = Text.Clean(sinSaltosLinea),
    resultado = Text.Proper(limpio)
in
    resultado
```

Úsala en otra consulta:
```m
= Table.TransformColumns(Origen, {{"Nombre", fnLimpiarTexto}})
```

### Función con parámetro de tabla

```m
fnProcesarArchivo = (archivo as binary) as table =>
let
    Contenido = Csv.Document(archivo, [Delimiter=",", Encoding=65001]),
    Encabezados = Table.PromoteHeaders(Contenido),
    Tipos = Table.TransformColumnTypes(Encabezados, {
        {"Fecha", type date},
        {"Monto", type number}
    })
in
    Tipos
```

## Manejo de errores en M

### try...otherwise

```m
= Table.AddColumn(Origen, "Seguro", each 
    try Number.FromText([Valor]) otherwise 0)
```

### try con registro de error

```m
= Table.AddColumn(Origen, "Resultado", each
    let
        intento = try Number.FromText([Valor])
    in
        if intento[HasError] then 
            "Error: " & intento[Error][Message]
        else 
            intento[Value])
```

## Parámetros de consulta

1. Inicio → **Administrar parámetros** → Nuevo parámetro
2. Define nombre, tipo y valor
3. Usa en consultas:

```m
= Table.SelectRows(Origen, each [Fecha] >= FechaInicio and [Fecha] <= FechaFin)
```

Los parámetros se pueden vincular a celdas de Excel.

## API REST con M

```m
let
    Url = "https://api.ejemplo.com/datos",
    Headers = [#"Authorization" = "Bearer " & Token, #"Content-Type" = "application/json"],
    Respuesta = Web.Contents(Url, [Headers=Headers]),
    JSON = Json.Document(Respuesta),
    Tabla = Table.FromRecords(JSON[data])
in
    Tabla
```

## Rendimiento

- Usa `Table.Buffer` para tablas referenciadas múltiples veces
- Filtra temprano (antes de transformar)
- Quita columnas innecesarias pronto
- Evita `Table.Combine` con muchas tablas (usa `Table.FromList` + función)

## Resumen

El lenguaje M desbloquea el potencial completo de Power Query. Las funciones personalizadas, el manejo de errores y la conexión a APIs permiten crear pipelines de datos sofisticados y mantenibles.
