# Nombres definidos y rangos con nombre

Los nombres definidos hacen las fórmulas más legibles y fáciles de mantener.

## ¿Qué es un nombre definido?

Un alias para un rango, constante o fórmula. En lugar de escribir `$B$2:$B$500`, escribes `Ventas`.

## Crear nombres

### Método 1: Cuadro de nombres

1. Selecciona el rango
2. Haz clic en el **Cuadro de nombres** (esquina superior izquierda)
3. Escribe el nombre y presiona Enter

### Método 2: Pestaña Fórmulas

1. Fórmulas → **Definir nombre**
2. Completa:
   - Nombre
   - Ámbito (Libro o Hoja específica)
   - Comentario
   - Se refiere a: `=Hoja1!$A$2:$A$100`

### Método 3: Crear desde selección

1. Selecciona datos con encabezados
2. Fórmulas → **Crear desde la selección**
3. Elige: Fila superior, Columna izquierda, etc.

Los encabezados se usan como nombres automáticamente.

## Usar nombres en fórmulas

```
=SUMA(Ventas)
=PROMEDIO(Calificaciones)
=BUSCARV(A2, TablaPrecios, 3, FALSO)
```

## Administrar nombres

Fórmulas → **Administrador de nombres**:

- Ver todos los nombres definidos
- Editar rangos
- Eliminar nombres no usados
- Filtrar por ámbito

## Constantes con nombre

Define valores frecuentes:

```
Nombre: IVA
Se refiere a: =0.16

Nombre: TipoCambio
Se refiere a: =17.50
```

Uso: `=Subtotal * (1 + IVA)`

## Fórmulas con nombre

Define fórmulas reutilizables:

```
Nombre: UltimaFila
Se refiere a: =COINCIDIR(9.99E+307, Hoja1!$A:$A)

Nombre: RangoDinamico
Se refiere a: =DESREF(Hoja1!$A$1, 0, 0, CONTARA(Hoja1!$A:$A), 1)
```

## Rangos dinámicos con nombre

### Método clásico con DESREF

```
=DESREF(Hoja1!$A$1, 0, 0, CONTARA(Hoja1!$A:$A), 1)
```

Este rango crece automáticamente cuando agregas datos.

### Método moderno con tablas Excel

Convierte tus datos en tabla (Ctrl+T) y usa:
```
=Tabla1[Ventas]
```

Las referencias estructuradas se ajustan automáticamente.

## Ámbito de nombres

- **Libro**: disponible en todas las hojas
- **Hoja**: solo en la hoja específica (permite nombres repetidos en diferentes hojas)

```
Hoja1!Total   → nombre "Total" en Hoja1
Hoja2!Total   → nombre "Total" diferente en Hoja2
```

## Reglas de nombres

- Primer carácter: letra, guion bajo o barra invertida
- Sin espacios (usa guion bajo: `Precio_Unitario`)
- Máximo 255 caracteres
- No puede ser una referencia de celda (no usar "A1" como nombre)
- Case-insensitive: `Ventas` = `VENTAS` = `ventas`

## INDIRECTO con nombres

```
=SUMA(INDIRECTO(A1))
```

Si A1 contiene "Ventas", calcula `=SUMA(Ventas)`.

Útil para selección dinámica de rangos con listas desplegables.

## Resumen

Los nombres definidos mejoran la legibilidad, mantenibilidad y robustez de las hojas de cálculo. Son esenciales para fórmulas complejas y archivos compartidos.
