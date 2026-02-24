# Funciones de texto en Excel

Excel no solo trabaja con números. Las funciones de texto te permiten manipular cadenas de texto.

## Funciones básicas de texto

| Función | Descripción | Ejemplo | Resultado |
|---------|-------------|---------|-----------|
| `MAYUSC` | Convierte a mayúsculas | `=MAYUSC("hola")` | HOLA |
| `MINUSC` | Convierte a minúsculas | `=MINUSC("HOLA")` | hola |
| `NOMPROPIO` | Primera letra mayúscula | `=NOMPROPIO("juan pérez")` | Juan Pérez |
| `LARGO` | Cuenta caracteres | `=LARGO("Excel")` | 5 |
| `ESPACIOS` | Quita espacios extra | `=ESPACIOS(" Hola  Mundo ")` | Hola Mundo |

## Extraer partes de texto

```
=IZQUIERDA(texto, n)    → primeros n caracteres
=DERECHA(texto, n)      → últimos n caracteres
=MED(texto, inicio, n)  → n caracteres desde posición inicio
```

**Ejemplos**:
```
=IZQUIERDA("Excel 2026", 5)   → "Excel"
=DERECHA("Excel 2026", 4)     → "2026"
=MED("Excel 2026", 7, 4)      → "2026"
```

## Buscar dentro del texto

```
=ENCONTRAR("texto_buscado", texto)         → posición (distingue mayúsculas)
=HALLAR("texto_buscado", texto)            → posición (no distingue mayúsculas)
```

**Ejemplo**: Extraer el dominio de un email:
```
=MED(A1, ENCONTRAR("@",A1)+1, 100)
```
Si A1 = "usuario@ejemplo.com" → "ejemplo.com"

## Concatenar texto

### Operador &

```
=A1 & " " & B1       → "Juan" & " " & "Pérez" = "Juan Pérez"
```

### Función CONCATENAR

```
=CONCATENAR(A1, " ", B1)
```

### Función UNIRCADENAS (Excel 2019+)

```
=UNIRCADENAS(", ", VERDADERO, A1:A5)
```
Une un rango con un separador, ignorando celdas vacías.

## SUSTITUIR y REEMPLAZAR

```
=SUSTITUIR(A1, "viejo", "nuevo")
=REEMPLAZAR(A1, posición, n_caracteres, "nuevo")
```

**Ejemplos**:
```
=SUSTITUIR("Hola Mundo", "Mundo", "Excel")  → "Hola Excel"
=REEMPLAZAR("ABC123", 4, 3, "XYZ")          → "ABCXYZ"
```

## TEXTO: dar formato a números

Convierte un número a texto con formato específico:

```
=TEXTO(1234.5, "#,##0.00")     → "1,234.50"
=TEXTO(0.85, "0%")             → "85%"
=TEXTO(HOY(), "DD/MM/AAAA")   → "24/02/2026"
```

## Ejemplo práctico: limpiar nombres

Si tienes datos sucios como "  JUAN    PÉREZ  ":

```
=NOMPROPIO(ESPACIOS(A1))  → "Juan Pérez"
```

## Resumen

Las funciones de texto son esenciales para limpiar datos, extraer información y dar formato. Dominar IZQUIERDA, DERECHA, MED, CONCATENAR y SUSTITUIR cubre el 90% de las necesidades.
