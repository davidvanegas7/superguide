# Power Query: transformación de datos profesional

Power Query es el motor ETL (Extract, Transform, Load) de Excel para preparar datos a escala.

## ¿Qué es Power Query?

Un editor visual que permite:
- Conectar a múltiples fuentes de datos
- Transformar y limpiar datos
- Combinar tablas
- Automatizar el proceso (se repite con un clic)

## Abrir el editor

- Datos → Obtener datos → Iniciar editor de Power Query
- O importar datos y elegir "Transformar datos"

## Interfaz del editor

| Zona | Función |
|------|---------|
| Panel izquierdo | Consultas (queries) |
| Centro | Vista previa de datos |
| Panel derecho | Pasos aplicados |
| Cinta superior | Herramientas de transformación |

## Pasos aplicados

Cada transformación se graba como un paso. Puedes:
- Reordenar pasos
- Eliminar pasos intermedios
- Insertar pasos
- Editar el código M de cada paso

## Transformaciones esenciales

### Promover encabezados
```m
= Table.PromoteHeaders(Origen, [PromoteAllScalars=true])
```

### Cambiar tipos de datos
```m
= Table.TransformColumnTypes(Paso_anterior, {
    {"Fecha", type date},
    {"Monto", type number},
    {"Nombre", type text}
})
```

### Quitar columnas
```m
= Table.RemoveColumns(Paso_anterior, {"Col_innecesaria1", "Col_innecesaria2"})
```

### Filtrar filas
```m
= Table.SelectRows(Paso_anterior, each [Estado] = "Activo" and [Monto] > 1000)
```

### Reemplazar valores
```m
= Table.ReplaceValue(Paso_anterior, "viejo", "nuevo", Replacer.ReplaceText, {"Columna"})
```

### Dividir columna
```m
= Table.SplitColumn(Paso_anterior, "Nombre_Completo", 
    Splitter.SplitTextByDelimiter(" ", QuoteStyle.Csv))
```

## Columnas personalizadas

### Columna calculada
```m
= Table.AddColumn(Paso_anterior, "Total", each [Cantidad] * [Precio])
```

### Columna condicional
```m
= Table.AddColumn(Paso_anterior, "Categoría", each 
    if [Monto] > 10000 then "Alto"
    else if [Monto] > 5000 then "Medio"
    else "Bajo")
```

### Columna de fecha
```m
= Table.AddColumn(Paso_anterior, "Mes", each Date.Month([Fecha]))
= Table.AddColumn(Paso_anterior, "Trimestre", each "Q" & Text.From(Date.QuarterOfYear([Fecha])))
```

## Agrupar datos

```m
= Table.Group(Paso_anterior, {"Región"}, {
    {"Total_Ventas", each List.Sum([Monto]), type number},
    {"Conteo", each Table.RowCount(_), Int64.Type},
    {"Promedio", each List.Average([Monto]), type number}
})
```

## Despivotar columnas

Convertir columnas de meses en filas:

| Producto | Ene | Feb | Mar |
|----------|-----|-----|-----|
| A | 100 | 200 | 150 |

→ Se convierte en:

| Producto | Mes | Valor |
|----------|-----|-------|
| A | Ene | 100 |
| A | Feb | 200 |
| A | Mar | 150 |

Selecciona las columnas de meses → **Despivotar columnas**

## Combinar consultas

### Anexar (Append)
Apila filas de múltiples consultas (como UNION en SQL):

Inicio → **Anexar consultas**

### Combinar (Merge)
Une tablas por columnas de relación (como JOIN en SQL):

Inicio → **Combinar consultas**

Tipos de combinación:
- **Interna**: solo coincidencias
- **Externa izquierda**: todos de la izquierda
- **Externa derecha**: todos de la derecha
- **Externa completa**: todos de ambas
- **Anti izquierda**: solo los que NO coinciden

## Combinar archivos de una carpeta

1. Obtener datos → Desde carpeta
2. Selecciona la carpeta
3. Power Query muestra la lista de archivos
4. **Combinar y transformar**
5. Define la transformación (se aplica a todos los archivos)

## Cargar datos

- **Cargar en tabla**: inserta en una hoja de Excel
- **Solo conexión**: mantiene los datos disponibles sin ocupar espacio en hojas
- **Cargar en modelo de datos**: para Power Pivot (análisis avanzado)

## Resumen

Power Query reemplaza horas de limpieza manual con un flujo reproducible y actualizable. Es la herramienta fundamental para cualquier analista de datos que trabaje con Excel.
