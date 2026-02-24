# Trabajar con rangos y selección

Dominar la selección de rangos es fundamental para trabajar eficientemente en Excel.

## ¿Qué es un rango?

Un rango es un grupo de celdas. Se escribe como: `CeldaInicio:CeldaFin`

```
A1:A10    → columna A, filas 1 a 10
A1:D1     → fila 1, columnas A a D
A1:D10    → rectángulo de 4 columnas × 10 filas
A:A       → toda la columna A
1:1       → toda la fila 1
```

## Selección con el mouse

| Acción | Resultado |
|--------|-----------|
| Clic + arrastrar | Selecciona un rango rectangular |
| `Ctrl` + clic | Agrega celdas individuales a la selección |
| `Shift` + clic | Extiende la selección hasta esa celda |
| Clic en encabezado de columna | Selecciona toda la columna |
| Clic en número de fila | Selecciona toda la fila |
| `Ctrl + A` | Selecciona todo |

## Selección con teclado

| Atajo | Acción |
|-------|--------|
| `Shift + flechas` | Extiende selección celda por celda |
| `Ctrl + Shift + →` | Selecciona hasta el final del rango |
| `Ctrl + Shift + Fin` | Selecciona hasta la última celda con datos |
| `Ctrl + Shift + Inicio` | Selecciona desde la actual hasta A1 |

## Rangos con nombre

Puedes asignar un nombre a un rango para usarlo en fórmulas:

1. Selecciona el rango
2. Escribe el nombre en el **Cuadro de nombres** (izquierda de la barra de fórmulas)
3. Presiona Enter

```
Nombre: ventas_enero
Rango: B2:B31

=SUMA(ventas_enero)    → más legible que =SUMA(B2:B31)
```

### Administrar nombres

Pestaña **Fórmulas** → **Administrador de nombres** para ver, editar o eliminar rangos con nombre.

## Autorellenar

Excel puede detectar patrones y autocompletar:

1. Escribe los primeros valores (ej: Enero, Febrero)
2. Selecciona ambas celdas
3. Arrastra el controlador de relleno (esquina inferior derecha)

**Patrones que Excel detecta**:
- Meses: Enero, Febrero, Marzo…
- Días: Lunes, Martes, Miércoles…
- Números: 1, 2, 3… o 5, 10, 15…
- Fechas: 01/01, 02/01, 03/01…

## Copiar, cortar y pegar

| Atajo | Acción |
|-------|--------|
| `Ctrl + C` | Copiar |
| `Ctrl + X` | Cortar |
| `Ctrl + V` | Pegar |
| `Ctrl + Shift + V` | Pegado especial |

### Pegado especial

Permite pegar selectivamente:
- **Solo valores**: pega el resultado, no la fórmula
- **Solo formato**: pega colores, bordes, fuente
- **Transponer**: convierte filas en columnas
- **Solo fórmulas**: pega la fórmula sin formato

## Resumen

Los rangos son la unidad de trabajo en Excel. Dominar la selección con mouse y teclado, los rangos con nombre y el autorelleno te harán mucho más productivo.
