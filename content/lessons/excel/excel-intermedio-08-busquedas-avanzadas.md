# Funciones de búsqueda avanzadas

Más allá de BUSCARV: técnicas de búsqueda poderosas para escenarios complejos.

## INDICE + COINCIDIR en detalle

### Búsqueda hacia la izquierda

BUSCARV solo busca a la derecha. INDICE+COINCIDIR busca en cualquier dirección:

```
=INDICE(A2:A100, COINCIDIR("Laptop", C2:C100, 0))
```

Busca "Laptop" en columna C y devuelve el valor de columna A.

### Búsqueda bidimensional

Encontrar un valor en la intersección de fila y columna:

```
=INDICE(B2:M13, COINCIDIR("Marzo", A2:A13, 0), COINCIDIR("Ventas", B1:M1, 0))
```

## BUSCARX avanzado

### Búsqueda con comodines

```
=BUSCARX("*laptop*", B:B, C:C, , 2)
```

El modo `2` habilita coincidencia con comodines.

### Búsqueda del último match

```
=BUSCARX(valor, rango_buscar, rango_resultado, , 0, -1)
```

El último argumento `-1` busca desde el final.

### Devolver múltiples columnas

```
=BUSCARX("A001", A:A, B:D)
```

Devuelve las columnas B, C y D en una sola fórmula.

## FILTRAR (Excel 365)

Filtra un rango según criterios y devuelve los resultados:

```
=FILTRAR(A2:D100, C2:C100="Activo")
=FILTRAR(A2:D100, (C2:C100="Activo")*(D2:D100>1000))
=FILTRAR(A2:D100, C2:C100="Activo", "Sin resultados")
```

## ORDENAR (Excel 365)

Ordena un rango dinámicamente:

```
=ORDENAR(A2:D100, 3, -1)     → ordena por columna 3 descendente
=ORDENAR(A2:D100, {3,1}, {-1,1})  → por columna 3 desc, luego col 1 asc
```

### ORDENAR + FILTRAR

```
=ORDENAR(FILTRAR(A2:D100, C2:C100>1000), 4, -1)
```

Filtra valores >1000 y ordena por la columna 4 descendente.

## UNICOS (Excel 365)

Extrae valores únicos:

```
=UNICOS(A2:A100)             → valores únicos
=UNICOS(A2:A100, , VERDADERO)  → valores que aparecen exactamente una vez
```

## TRANSPONER

Convierte filas en columnas:

```
=TRANSPONER(A1:D1)
```

## BUSCARV con múltiples criterios

Combina columnas para crear una clave compuesta:

### Columna auxiliar

En columna E: `=A2&"-"&B2` (concatena Departamento + Mes)

Luego: `=BUSCARV("Ventas-Marzo", E:F, 2, FALSO)`

### Con INDICE + COINCIDIR

```
=INDICE(D2:D100, COINCIDIR(1, (A2:A100="Ventas")*(B2:B100="Marzo"), 0))
```

> Esta es una fórmula matricial. En Excel 365 funciona directamente. En versiones anteriores, confirma con `Ctrl+Shift+Enter`.

## BUSCARV aproximado: tablas de rangos

Para comisiones, impuestos, calificaciones:

| Desde | Hasta | Comisión |
|-------|-------|----------|
| 0 | 4999 | 3% |
| 5000 | 9999 | 5% |
| 10000 | ∞ | 8% |

```
=BUSCARV(monto, tabla_comisiones, 3, VERDADERO)
```

Con VERDADERO, BUSCARV encuentra el valor más cercano *menor o igual*. La tabla DEBE estar ordenada.

## Resumen

| Necesidad | Función recomendada |
|-----------|-------------------|
| Búsqueda simple | BUSCARX o BUSCARV |
| Búsqueda hacia la izquierda | INDICE+COINCIDIR o BUSCARX |
| Búsqueda bidimensional | INDICE + 2 COINCIDIR |
| Filtrar datos dinámicamente | FILTRAR |
| Valores únicos | UNICOS |
| Múltiples criterios | BUSCARX o INDICE+COINCIDIR con arrays |
