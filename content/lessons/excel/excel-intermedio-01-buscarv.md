# BUSCARV y BUSCARH: búsquedas en tablas

BUSCARV (VLOOKUP) es probablemente la función más usada en entornos profesionales.

## BUSCARV (VLOOKUP)

Busca un valor en la primera columna de un rango y devuelve un valor de otra columna.

```
=BUSCARV(valor_buscado, rango_tabla, num_columna, [coincidencia])
```

| Argumento | Descripción |
|-----------|-------------|
| `valor_buscado` | Lo que buscas |
| `rango_tabla` | Donde buscar (la primera columna es la clave) |
| `num_columna` | Número de columna del resultado (1, 2, 3…) |
| `coincidencia` | FALSO = exacta, VERDADERO = aproximada |

### Ejemplo básico

Tabla de productos en A1:C100:

| ID | Producto | Precio |
|----|----------|--------|
| 101 | Laptop | 15000 |
| 102 | Mouse | 350 |
| 103 | Teclado | 800 |

```
=BUSCARV(102, A1:C100, 3, FALSO)  → 350
=BUSCARV(102, A1:C100, 2, FALSO)  → "Mouse"
```

### Coincidencia exacta vs aproximada

- **FALSO** (exacta): busca el valor idéntico. Si no lo encuentra → `#N/A`
- **VERDADERO** (aproximada): busca el valor más cercano menor o igual. Los datos DEBEN estar ordenados.

> **Siempre usa FALSO** a menos que trabajes con rangos de valores (comisiones, impuestos).

## BUSCARH (HLOOKUP)

Igual que BUSCARV pero busca en la primera **fila** (horizontal):

```
=BUSCARH(valor_buscado, rango, num_fila, coincidencia)
```

Se usa cuando los encabezados están en filas en lugar de columnas.

## Limitaciones de BUSCARV

1. Solo busca hacia la **derecha** (la columna clave debe ser la primera)
2. Si insertas/eliminas columnas, el `num_columna` se rompe
3. No puede buscar en dos criterios simultáneamente

## INDICE + COINCIDIR (alternativa superior)

### COINCIDIR (MATCH)

Devuelve la **posición** de un valor en un rango:

```
=COINCIDIR(valor, rango, tipo)
```

### INDICE (INDEX)

Devuelve el valor en una posición específica:

```
=INDICE(rango, fila, [columna])
```

### Combinación INDICE + COINCIDIR

```
=INDICE(C1:C100, COINCIDIR(102, A1:A100, 0))
```

**Ventajas sobre BUSCARV**:
- Busca en cualquier dirección
- No depende de números de columna fijos
- Más eficiente en archivos grandes

## BUSCARX (Excel 2021+ / Microsoft 365)

La función moderna que reemplaza a BUSCARV:

```
=BUSCARX(valor_buscado, rango_buscado, rango_resultado, [si_no_encontrado], [modo], [orden])
```

```
=BUSCARX(102, A:A, C:C, "No encontrado")
```

**Ventajas**:
- Busca en cualquier dirección
- Valor por defecto si no encuentra
- Sintaxis más clara
- Búsqueda con comodines

## Resumen

| Función | Cuándo usar |
|---------|-------------|
| BUSCARV | Búsquedas simples, compatibilidad |
| INDICE+COINCIDIR | Flexibilidad total, búsqueda bidireccional |
| BUSCARX | Excel moderno, la mejor opción |
