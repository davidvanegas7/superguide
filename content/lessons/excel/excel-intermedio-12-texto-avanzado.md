# Funciones de texto avanzadas

Técnicas avanzadas de manipulación de texto para limpieza y transformación de datos.

## CONCATENAR y operador &

```
=CONCATENAR(A2, " ", B2)
=A2 & " " & B2
```

### UNIRCADENAS (TEXTJOIN) - Excel 2019+

```
=UNIRCADENAS(delimitador, ignorar_vacíos, rango)
```

```
=UNIRCADENAS(", ", VERDADERO, A2:A100)
→ "Ana, Carlos, Diana, ..."
```

## TEXTO: formato como texto

```
=TEXTO(valor, formato)
```

| Formato | Entrada | Resultado |
|---------|---------|-----------|
| `"#,##0.00"` | 1234.5 | 1,234.50 |
| `"$#,##0"` | 5000 | $5,000 |
| `"0%"` | 0.15 | 15% |
| `"dd/mm/yyyy"` | 45678 | 15/01/2025 |
| `"dddd"` | 45678 | miércoles |
| `"mmmm yyyy"` | 45678 | enero 2025 |
| `"000000"` | 42 | 000042 |

## Extraer y modificar texto

### IZQUIERDA, DERECHA, EXTRAE

```
=IZQUIERDA(texto, n)      → primeros n caracteres
=DERECHA(texto, n)         → últimos n caracteres
=EXTRAE(texto, inicio, n)  → n caracteres desde posición inicio
```

### Extraer partes de datos

Email `usuario@empresa.com`:
```
=IZQUIERDA(A2, ENCONTRAR("@", A2)-1)         → "usuario"
=EXTRAE(A2, ENCONTRAR("@", A2)+1, 100)        → "empresa.com"
```

### SUSTITUIR y REEMPLAZAR

```
=SUSTITUIR(texto, viejo, nuevo, [instancia])
=REEMPLAZAR(texto, inicio, num_caracteres, nuevo)
```

```
=SUSTITUIR(A2, " ", "_")           → reemplaza espacios con _
=SUSTITUIR(A2, "Sr.", "Señor")     → solo la primera instancia
```

## Limpieza de datos

```
=RECORTAR(texto)          → elimina espacios extra
=LIMPIAR(texto)            → elimina caracteres no imprimibles
=ESPACIOS(texto)           → alias de RECORTAR en algunas versiones
```

### Quitar saltos de línea
```
=SUSTITUIR(A2, CARACTER(10), " ")
=SUSTITUIR(SUSTITUIR(A2, CARACTER(10), " "), CARACTER(13), " ")
```

### Quitar espacios no estándar (char 160)
```
=SUSTITUIR(A2, CARACTER(160), " ")
```

### Pipeline de limpieza
```
=RECORTAR(LIMPIAR(SUSTITUIR(SUSTITUIR(A2, CARACTER(160), " "), CARACTER(10), " ")))
```

## Funciones de búsqueda en texto

```
=ENCONTRAR(buscar, texto, [inicio])     → case-sensitive
=HALLAR(buscar, texto, [inicio])        → case-insensitive, soporta comodines
```

### Verificar si contiene

```
=ESNUMERO(HALLAR("error", A2))   → VERDADERO si contiene "error"
```

## Separar texto (Text to Columns)

### Con fórmulas

Separar nombre completo "Juan Carlos Pérez":

```
Primer nombre:
=IZQUIERDA(A2, ENCONTRAR(" ", A2)-1)

Resto:
=EXTRAE(A2, ENCONTRAR(" ", A2)+1, 100)
```

### Con Texto en columnas

Pestaña **Datos** → **Texto en columnas** → Delimitado → elige separador

## VALOR y NUMEROVALUE

```
=VALOR("1234")      → 1234 (número)
=VALOR("$1,234")    → 1234
```

## Funciones de Excel 365

### DIVIDIRTEXTO (TEXTSPLIT)
```
=DIVIDIRTEXTO(A2, ",")          → separa por comas
=DIVIDIRTEXTO(A2, ",", CARACTER(10))  → separa por comas y saltos de línea
```

### VALORATEXTO y TEXTOAVALOR
```
=VALORATEXTO(123)    → "123"
=TEXTOAVALOR("123")  → 123
```

## Resumen

Las funciones de texto avanzadas son esenciales para limpiar datos importados, transformar formatos y preparar datos para análisis. UNIRCADENAS, SUSTITUIR y los pipelines de limpieza son las herramientas más frecuentes.
