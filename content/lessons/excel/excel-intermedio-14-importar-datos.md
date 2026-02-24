# Importar y conectar datos externos

Técnicas para traer datos de fuentes externas a Excel.

## Importar archivos CSV

### Método 1: Abrir directamente

Archivo → Abrir → selecciona el CSV

**Problema**: Excel puede interpretar mal los delimitadores y formatos.

### Método 2: Importar desde Datos

1. Datos → **Obtener datos** → Desde archivo → Desde texto/CSV
2. Configura:
   - Delimitador (coma, punto y coma, tabulador)
   - Codificación (UTF-8, Latin-1)
   - Tipo de datos por columna
3. **Cargar** o **Transformar datos** (abre Power Query)

## Conexión a bases de datos

### Desde SQL Server

1. Datos → Obtener datos → Desde base de datos → SQL Server
2. Ingresa servidor y base de datos
3. Autenticación
4. Selecciona tablas/vistas

### Desde Access

1. Datos → Obtener datos → Desde base de datos → Access

### Desde ODBC

Para cualquier base de datos con driver ODBC (MySQL, PostgreSQL, Oracle):

1. Configura la conexión ODBC desde el panel de Windows
2. Datos → Obtener datos → Desde otras fuentes → ODBC

## Datos desde la web

1. Datos → Obtener datos → **Desde la web**
2. Ingresa la URL
3. Excel detecta tablas en la página
4. Selecciona la tabla deseada

### APIs REST

Con Power Query puedes consumir APIs:

1. Datos → Obtener datos → Desde la web
2. Avanzado → agrega headers, métodos
3. Transforma el JSON resultante

## Power Query: introducción

Power Query es la herramienta de ETL (Extract, Transform, Load) de Excel.

### Abrir Power Query

- Al importar datos, elige **Transformar datos**
- O: Datos → **Obtener datos** → **Iniciar editor de Power Query**

### Operaciones comunes

| Operación | Descripción |
|-----------|-------------|
| Quitar columnas | Elimina columnas innecesarias |
| Filtrar filas | Aplica filtros |
| Cambiar tipo | Define tipos de datos correctos |
| Dividir columna | Separa por delimitador |
| Agrupar | Agrupa y calcula |
| Combinar consultas | Une datos de varias fuentes |
| Columna personalizada | Agrega columnas calculadas |
| Despivotar | Convierte columnas a filas |
| Pivotar | Convierte filas a columnas |

### Ejemplo: limpieza de CSV sucio

1. Importar CSV
2. Promover primera fila como encabezado
3. Quitar filas vacías
4. Cambiar tipos de datos
5. Dividir columna de nombre completo
6. Quitar duplicados
7. Cargar a Excel

## Actualizar datos

### Actualización manual

Datos → **Actualizar todo**

O clic derecho en la tabla → Actualizar

### Actualización automática

Propiedades de la conexión:
- Actualizar cada X minutos
- Actualizar al abrir el archivo

### Configurar propiedades

1. Datos → Consultas y conexiones
2. Clic derecho en la conexión → Propiedades
3. Configura frecuencia de actualización

## Combinar múltiples archivos

### Power Query: combinar carpeta

1. Obtener datos → Desde archivo → **Desde carpeta**
2. Selecciona la carpeta con los archivos
3. Power Query combina todos automáticamente
4. Aplica transformaciones uniformes

Ideal para: reportes mensuales, datos de sucursales, logs.

## Texto en columnas

Para datos ya pegados en Excel:

1. Selecciona la columna
2. Datos → **Texto en columnas**
3. Elige: Delimitado o Ancho fijo
4. Configura el delimitador
5. Define formato de cada columna

## Resumen

Excel puede conectarse a prácticamente cualquier fuente de datos. Para importaciones simples usa CSV/texto directo. Para transformaciones complejas y conexiones recurrentes, Power Query es la herramienta indicada.
