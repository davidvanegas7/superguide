# Referencias relativas, absolutas y mixtas

Entender las referencias es clave para copiar fórmulas correctamente.

## Referencia relativa (A1)

Es el tipo por defecto. Cuando copias la fórmula, la referencia se **ajusta** automáticamente.

**Ejemplo**: Si en C1 escribes `=A1+B1` y la copias a C2:
- C1: `=A1+B1`
- C2: `=A2+B2` (se ajustó automáticamente)

## Referencia absoluta ($A$1)

El signo `$` **fija** la referencia. No cambia al copiar.

**Ejemplo**: Si en C1 escribes `=A1*$B$1` y la copias a C2:
- C1: `=A1*$B$1`
- C2: `=A2*$B$1` (A1 cambió a A2, pero $B$1 quedó fijo)

### ¿Cuándo usar absoluta?

Cuando una celda contiene un valor constante que todas las fórmulas deben usar:

| A | B | C |
|---|---|---|
| Precio | IVA → | 0.16 |
| 100 | | =A2*$C$1 |
| 250 | | =A3*$C$1 |
| 500 | | =A4*$C$1 |

## Referencia mixta ($A1 o A$1)

Fija solo la columna o solo la fila:

| Tipo | Ejemplo | Se fija |
|------|---------|---------|
| `$A1` | Columna fija, fila se ajusta | Columna A |
| `A$1` | Columna se ajusta, fila fija | Fila 1 |

## El atajo F4

Coloca el cursor en la referencia dentro de la barra de fórmulas y presiona `F4` para ciclar:

```
A1 → $A$1 → A$1 → $A1 → A1
```

## Ejemplo práctico: tabla de multiplicar

Para crear una tabla de multiplicar con una sola fórmula:

1. En A2:A10 coloca los números 1-9
2. En B1:J1 coloca los números 1-9
3. En B2 escribe: `=$A2*B$1`
4. Copia B2 a todo el rango B2:J10

La referencia `$A2` fija la columna A (siempre lee de A), y `B$1` fija la fila 1 (siempre lee de fila 1).

## Referencia a otras hojas

Puedes referenciar celdas de otras hojas:

```
=Hoja2!A1
='Datos Ventas'!B5
=SUMA(Enero!A1:A10)
```

Si el nombre de la hoja tiene espacios, usa comillas simples.

## Resumen

- **Relativa** (`A1`): se ajusta al copiar
- **Absoluta** (`$A$1`): nunca cambia
- **Mixta** (`$A1` o `A$1`): fija una dimensión
- Usa `F4` para alternar rápidamente
