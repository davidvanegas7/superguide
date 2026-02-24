# Función SI (IF): decisiones en Excel

La función SI es la más importante para tomar decisiones lógicas en tus hojas de cálculo.

## Sintaxis

```
=SI(condición, valor_si_verdadero, valor_si_falso)
```

## Ejemplos básicos

```
=SI(A1>60, "Aprobado", "Reprobado")
=SI(B1>=1000, "VIP", "Regular")
=SI(C1="", "Sin dato", C1)
```

## Operadores de comparación

| Operador | Significado |
|----------|-------------|
| `=` | Igual a |
| `<>` | Diferente de |
| `>` | Mayor que |
| `<` | Menor que |
| `>=` | Mayor o igual |
| `<=` | Menor o igual |

## SI anidado

Puedes anidar hasta 64 funciones SI:

```
=SI(A1>=90, "Excelente",
   SI(A1>=80, "Bueno",
      SI(A1>=70, "Regular",
         SI(A1>=60, "Suficiente", "Insuficiente"))))
```

> **Tip**: Para muchas condiciones, considera usar `SI.CONJUNTO` (Excel 2019+) que es más legible.

## Y, O (AND, OR)

Combina condiciones:

```
=SI(Y(A1>0, A1<100), "Válido", "Fuera de rango")
=SI(O(A1="Lunes", A1="Viernes"), "Día especial", "Normal")
```

| Función | Verdadero cuando… |
|---------|-------------------|
| `Y(cond1, cond2)` | TODAS las condiciones se cumplen |
| `O(cond1, cond2)` | AL MENOS UNA condición se cumple |
| `NO(condición)` | La condición es falsa |

## SI.ERROR

Maneja errores elegantemente:

```
=SI.ERROR(A1/B1, "Error: división entre cero")
=SI.ERROR(BUSCARV(...), "No encontrado")
```

## SI.CONJUNTO (Excel 2019+)

Evalúa múltiples condiciones sin anidamiento:

```
=SI.CONJUNTO(
    A1>=90, "Excelente",
    A1>=80, "Bueno",
    A1>=70, "Regular",
    A1>=60, "Suficiente",
    VERDADERO, "Insuficiente"
)
```

El último `VERDADERO` actúa como "en todos los demás casos".

## Ejemplo práctico: comisiones

| Ventas | Fórmula comisión |
|--------|-----------------|
| < $5,000 | 3% |
| $5,000 - $10,000 | 5% |
| > $10,000 | 8% |

```
=SI(A2>10000, A2*0.08, SI(A2>=5000, A2*0.05, A2*0.03))
```

## Resumen

SI es la función de decisión por excelencia. Combínala con Y, O y SI.ERROR para cubrir cualquier lógica condicional. Para múltiples condiciones, prefiere SI.CONJUNTO.
