# Funciones de base de datos

Excel incluye funciones especializadas para trabajar con bases de datos tabulares.

## ¿Qué son las funciones de base de datos?

Calculan valores en un rango aplicando criterios estructurados. Todas siguen el patrón:

```
=BDFUNCION(base_datos, campo, criterios)
```

| Argumento | Descripción |
|-----------|-------------|
| `base_datos` | Rango con encabezados (ej: A1:F100) |
| `campo` | Nombre de la columna (texto) o número de columna |
| `criterios` | Rango con encabezados y condiciones |

## El rango de criterios

Es separado de los datos. Tiene los mismos encabezados que la base de datos:

**Base de datos (A1:F100)**:

| Nombre | Depto | Ciudad | Ventas | Meta | Estado |
|--------|-------|--------|--------|------|--------|
| Ana | Ventas | CDMX | 50000 | 40000 | Activo |

**Criterios (H1:J2)**:

| Depto | Ciudad | Ventas |
|-------|--------|--------|
| Ventas | CDMX | >30000 |

### Reglas de criterios

- **Misma fila** = AND (todas deben cumplirse)
- **Filas diferentes** = OR (cualquiera)

Ejemplo AND:
```
| Depto  | Ventas |
|--------|--------|
| Ventas | >30000 |
→ Depto = "Ventas" Y Ventas > 30000
```

Ejemplo OR:
```
| Depto   |
|---------|
| Ventas  |
| Marketing |
→ Depto = "Ventas" O Depto = "Marketing"
```

## Lista de funciones

| Función | Descripción |
|---------|-------------|
| `BDSUMA` | Suma los valores que cumplen el criterio |
| `BDPROMEDIO` | Promedia los valores |
| `BDCONTAR` | Cuenta celdas con números |
| `BDCONTARA` | Cuenta celdas no vacías |
| `BDMAX` | Valor máximo |
| `BDMIN` | Valor mínimo |
| `BDEXTRAER` | Extrae un solo valor (error si hay más de uno) |
| `BDDESVEST` | Desviación estándar (muestra) |
| `BDVAR` | Varianza (muestra) |
| `BDPRODUCTO` | Producto de los valores |

## Ejemplos prácticos

### BDSUMA: ventas por departamento y ciudad

Criterios:

| Depto | Ciudad |
|-------|--------|
| Ventas | CDMX |

```
=BDSUMA(A1:F100, "Ventas", H1:I2)
```

### BDPROMEDIO: promedio de ventas activas > meta

Criterios:

| Estado | Ventas |
|--------|--------|
| Activo | >40000 |

```
=BDPROMEDIO(A1:F100, "Ventas", H1:I2)
```

### BDCONTAR: cuántos vendedores superan meta

Criterios (usando fórmula):

| Ventas |
|--------|
| >40000 |

```
=BDCONTAR(A1:F100, "Ventas", H1:H2)
```

### BDEXTRAER: obtener el email de un empleado específico

Criterios:

| ID |
|----|
| E001 |

```
=BDEXTRAER(A1:F100, "Email", H1:H2)
```

## Criterios con fórmulas

Para criterios complejos, puedes usar fórmulas en el rango de criterios.

**Importante**: el encabezado del criterio debe ser diferente a los encabezados de la base de datos (o vacío).

| Criterio |
|----------|
| =E2>D2 |

Esto filtra registros donde la columna E > columna D (ventas > meta).

## Funciones de base de datos vs. funciones condicionales

| Aspecto | BD | Condicionales |
|---------|-----|--------------|
| Criterios complejos | Más fácil (rango separado) | Fórmulas largas |
| Múltiples AND/OR | Natural con filas/columnas | Requiere .CONJUNTO |
| Criterios con fórmulas | Sí | Limitado |
| Popularidad | Menos usadas | Más comunes |
| Rendimiento | Similar | Similar |

## Resumen

Las funciones de base de datos ofrecen una forma estructurada de aplicar criterios complejos combinando AND y OR. Son especialmente útiles cuando los criterios cambian frecuentemente, ya que solo necesitas modificar el rango de criterios.
