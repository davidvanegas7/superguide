# Tablas dinámicas (Pivot Tables)

Las tablas dinámicas son la herramienta más poderosa de Excel para analizar grandes volúmenes de datos.

## ¿Qué es una tabla dinámica?

Es una tabla interactiva que resume, agrupa, filtra y analiza datos automáticamente. Puedes reorganizar la información arrastrando campos.

## Crear una tabla dinámica

1. Selecciona tus datos (deben tener encabezados)
2. Pestaña **Insertar** → **Tabla dinámica**
3. Elige dónde colocarla (nueva hoja recomendado)
4. Arrastra campos a las áreas

## Las 4 áreas

| Área | Función | Ejemplo |
|------|---------|---------|
| **Filas** | Etiquetas de fila | Categoría, Producto |
| **Columnas** | Etiquetas de columna | Mes, Año |
| **Valores** | Cálculos (suma, conteo, promedio) | Monto de ventas |
| **Filtros** | Filtro general superior | País, Región |

## Ejemplo práctico

Datos de ventas:

| Fecha | Vendedor | Producto | Región | Monto |
|-------|----------|----------|--------|-------|
| 01/01 | Ana | Laptop | Norte | 15000 |
| 02/01 | Carlos | Mouse | Sur | 350 |

**Para ver ventas por vendedor y producto**:
- Filas: Vendedor
- Columnas: Producto
- Valores: Suma de Monto

**Para ver ventas mensuales por región**:
- Filas: Región
- Columnas: Fecha (agrupada por mes)
- Valores: Suma de Monto

## Tipos de resumen en Valores

| Función | Uso |
|---------|-----|
| Suma | Total |
| Cuenta | Número de registros |
| Promedio | Media |
| Máx | Valor más alto |
| Mín | Valor más bajo |
| Producto | Multiplicación |
| DesvEst | Desviación estándar |

Para cambiar: clic derecho en un valor → **Resumir valores por**

## Mostrar valores como

Además del cálculo base, puedes mostrar:

- **% del total general**
- **% del total de columna/fila**
- **Diferencia respecto al anterior**
- **% de crecimiento**
- **Clasificación (ranking)**

Clic derecho → **Mostrar valores como**

## Agrupar datos

### Agrupar fechas
Clic derecho en una fecha → **Agrupar** → elige: Meses, Trimestres, Años

### Agrupar números
Clic derecho en un número → **Agrupar** → define inicio, fin e intervalo

## Segmentaciones (Slicers)

Filtros visuales con botones:

1. Selecciona la tabla dinámica
2. Pestaña **Analizar** → **Insertar segmentación de datos**
3. Elige los campos
4. Haz clic en los botones para filtrar

## Actualizar datos

Las tablas dinámicas no se actualizan automáticamente:

- Clic derecho → **Actualizar**
- O pestaña **Analizar** → **Actualizar todo**

## Campos calculados

Puedes crear campos personalizados:

1. Pestaña **Analizar** → **Campos, elementos y conjuntos** → **Campo calculado**
2. Nombra el campo y escribe la fórmula

```
Margen = Precio_Venta - Costo
```

## Resumen

Las tablas dinámicas transforman datos crudos en información accionable con arrastrar y soltar. Son imprescindibles para análisis de negocios, reportes y toma de decisiones.
