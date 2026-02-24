# Funciones lógicas avanzadas

Más allá de SI, Excel ofrece funciones lógicas potentes para escenarios complejos.

## SI.CONJUNTO (IFS)

Evalúa múltiples condiciones en orden:

```
=SI.CONJUNTO(
    condición1, resultado1,
    condición2, resultado2,
    ...
    VERDADERO, resultado_por_defecto
)
```

```
=SI.CONJUNTO(
    A2>=90, "A",
    A2>=80, "B",
    A2>=70, "C",
    A2>=60, "D",
    VERDADERO, "F"
)
```

## ELEGIR (CHOOSE)

Devuelve un valor de una lista basándose en un índice:

```
=ELEGIR(índice, valor1, valor2, valor3, ...)
```

```
=ELEGIR(DIASEM(A2), "Dom","Lun","Mar","Mié","Jue","Vie","Sáb")
=ELEGIR(B2, "Bajo","Medio","Alto","Crítico")
```

## CAMBIAR (SWITCH)

Compara un valor contra una lista de opciones:

```
=CAMBIAR(valor,
    opción1, resultado1,
    opción2, resultado2,
    ...,
    resultado_por_defecto
)
```

```
=CAMBIAR(A2,
    "MX", "México",
    "US", "Estados Unidos",
    "ES", "España",
    "País desconocido"
)
```

## Funciones de información

| Función | Devuelve VERDADERO si |
|---------|----------------------|
| `ESNUMERO(A1)` | A1 contiene un número |
| `ESTEXTO(A1)` | A1 contiene texto |
| `ESBLANCO(A1)` | A1 está vacía |
| `ESERROR(A1)` | A1 contiene cualquier error |
| `ESERR(A1)` | A1 contiene error (excepto #N/A) |
| `ESNOD(A1)` | A1 contiene #N/A |
| `ESLOGICO(A1)` | A1 es VERDADERO o FALSO |
| `ESPAR(A1)` | A1 es par |
| `ESIMPAR(A1)` | A1 es impar |

## SI.ERROR y SI.ND

```
=SI.ERROR(fórmula, valor_si_error)
=SI.ND(fórmula, valor_si_nd)
```

`SI.ND` solo captura errores `#N/A`, dejando pasar otros errores que podrían indicar problemas reales.

```
=SI.ND(BUSCARV(A2, datos, 2, 0), "No encontrado")
```

## Combinar lógica con otras funciones

### Suma condicional con múltiples OR

```
=SUMAPRODUCTO((B2:B100="Ventas")+(B2:B100="Marketing"), C2:C100)
```

El `+` actúa como OR dentro de SUMAPRODUCTO.

### Contar con múltiples condiciones AND

```
=SUMAPRODUCTO((B2:B100="Activo")*(C2:C100>1000))
```

El `*` actúa como AND.

### Promedio condicional complejo

```
=SUMAPRODUCTO((B2:B100="Ventas")*(C2:C100>1000)*D2:D100)
/ SUMAPRODUCTO((B2:B100="Ventas")*(C2:C100>1000)*1)
```

## LET (Excel 365)

Define variables dentro de una fórmula:

```
=LET(
    impuesto, 0.16,
    subtotal, A2*B2,
    total, subtotal * (1 + impuesto),
    total
)
```

Ventajas:
- Fórmulas más legibles
- Evita calcular lo mismo varias veces
- Mejor rendimiento

## LAMBDA (Excel 365)

Crea funciones personalizadas sin VBA:

```
=LAMBDA(x, y, x^2 + y^2)
```

Asigna a un nombre y úsala como cualquier función:
```
=MiFuncion(3, 4)  → 25
```

## Resumen

Las funciones lógicas avanzadas como SI.CONJUNTO, CAMBIAR, LET y LAMBDA simplifican fórmulas complejas y mejoran la legibilidad. SUMAPRODUCTO es la navaja suiza para condiciones múltiples.
