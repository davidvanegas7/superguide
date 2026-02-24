# Fórmulas básicas: suma, resta, multiplicación y división

Las fórmulas son el corazón de Excel. Toda fórmula comienza con el signo `=`.

## Operadores aritméticos

| Operador | Operación | Ejemplo | Resultado |
|----------|-----------|---------|-----------|
| `+` | Suma | `=5+3` | 8 |
| `-` | Resta | `=10-4` | 6 |
| `*` | Multiplicación | `=6*7` | 42 |
| `/` | División | `=20/4` | 5 |
| `^` | Potencia | `=2^3` | 8 |
| `%` | Porcentaje | `=50%` | 0.5 |

## Fórmulas con referencias

En lugar de números fijos, usa referencias a celdas:

```
=A1+B1      → suma el valor de A1 más B1
=A1*B1      → multiplica A1 por B1
=A1/B1      → divide A1 entre B1
```

**Ventaja**: Si cambias el valor de A1 o B1, el resultado se actualiza automáticamente.

## Orden de operaciones (PEMDAS)

Excel respeta el orden matemático estándar:

1. **P**aréntesis `()`
2. **E**xponentes `^`
3. **M**ultiplicación y **D**ivisión `*` `/`
4. **A**dición y **S**ustracción `+` `-`

```
=2+3*4      → 14 (no 20)
=(2+3)*4    → 20
=10-2^2     → 6 (10-4)
```

## La función SUMA

La forma más eficiente de sumar rangos:

```
=SUMA(A1:A10)       → suma de A1 a A10
=SUMA(A1:A5,C1:C5)  → suma dos rangos
=SUMA(A1,B1,C1)     → suma celdas individuales
```

## Autosuma

El atajo más útil de Excel:

1. Selecciona la celda debajo de tu columna de números
2. Presiona `Alt + =`
3. Excel detecta el rango automáticamente
4. Presiona **Enter** para confirmar

## Errores comunes

| Error | Causa |
|-------|-------|
| `#¡DIV/0!` | División entre cero |
| `#¡VALOR!` | Tipo de dato incorrecto en la fórmula |
| `#¡REF!` | Referencia a una celda eliminada |
| `#¡NOMBRE?` | Nombre de función mal escrito |

## Resumen

Las fórmulas transforman Excel de una simple tabla a una calculadora poderosa. Usa `=` para iniciar, referencias de celdas para flexibilidad, y `SUMA()` como tu primera función.
