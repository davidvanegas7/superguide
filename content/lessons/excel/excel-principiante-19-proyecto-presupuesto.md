# Proyecto final: presupuesto personal

Apliquemos todo lo aprendido creando un presupuesto personal completo en Excel.

## Estructura del libro

Crearemos 3 hojas:

```
📊 Presupuesto_Personal.xlsx
├── 📋 Resumen (dashboard con totales)
├── 📋 Ingresos (detalle de ingresos)
└── 📋 Gastos (detalle de gastos por categoría)
```

## Hoja de Ingresos

### Estructura de columnas

| Columna | Dato |
|---------|------|
| A | Fecha |
| B | Concepto |
| C | Categoría (Salario, Freelance, Otros) |
| D | Monto |

### Configuración

1. **Formato tabla**: `Ctrl + T` sobre los datos
2. **Validación en C**: Lista desplegable con categorías
3. **Formato de D**: Moneda con 2 decimales
4. **Formato de A**: Fecha DD/MM/AAAA

### Fórmulas de resumen (debajo de la tabla)

```
Total Ingresos:     =SUMA(Tabla_Ingresos[Monto])
Promedio mensual:   =PROMEDIO(Tabla_Ingresos[Monto])
Cantidad registros: =CONTARA(Tabla_Ingresos[Concepto])
```

## Hoja de Gastos

### Estructura

| Columna | Dato |
|---------|------|
| A | Fecha |
| B | Concepto |
| C | Categoría |
| D | Subcategoría |
| E | Monto |
| F | Método de pago |

### Categorías de gastos

- 🏠 **Vivienda**: Renta, servicios, mantenimiento
- 🍕 **Alimentación**: Supermercado, restaurantes
- 🚗 **Transporte**: Gasolina, transporte público
- 💊 **Salud**: Médico, medicinas, gimnasio
- 🎭 **Entretenimiento**: Streaming, salidas, hobbies
- 📚 **Educación**: Cursos, libros
- 👕 **Ropa**: Vestimenta, calzado
- 💰 **Ahorro**: Inversiones, fondo de emergencia

### Fórmulas útiles

```
Total por categoría:
=SUMAR.SI(Tabla_Gastos[Categoría], "Alimentación", Tabla_Gastos[Monto])

Gasto máximo:
=MAX(Tabla_Gastos[Monto])

Contar gastos por método de pago:
=CONTAR.SI(Tabla_Gastos[Método de pago], "Tarjeta")
```

## Hoja de Resumen (Dashboard)

### KPIs principales

```
Total Ingresos:    =SUMA(Ingresos!D:D)
Total Gastos:      =SUMA(Gastos!E:E)
Balance:           =B2-B3
% Ahorro:          =B4/B2
```

### Formato condicional en Balance

- Verde si es positivo (estás ahorrando)
- Rojo si es negativo (gastas más de lo que ganas)

```
Regla: =B4>0 → Fondo verde
Regla: =B4<0 → Fondo rojo
```

### Desglose por categoría

Usa `SUMAR.SI` para totalizar cada categoría y crea un gráfico circular.

### Gráfico de tendencia mensual

Con los totales mensuales, crea un gráfico de líneas para ver la evolución de ingresos vs gastos.

## Toques finales

1. **Protección**: Bloquea las celdas de fórmulas, deja editables solo las de datos
2. **Formato condicional**: Barras de datos en la columna de montos
3. **Validación**: Listas desplegables en categorías y métodos de pago
4. **Encabezados fijos**: Vista → Inmovilizar paneles (fila de encabezados)
5. **Nombre del libro**: Un título profesional en cada hoja

## Conceptos aplicados

- ✅ Navegación y formato de celdas
- ✅ Fórmulas básicas y funciones (SUMA, PROMEDIO, MAX, SI)
- ✅ Referencias absolutas
- ✅ Tablas de Excel
- ✅ Validación de datos con listas
- ✅ Formato condicional
- ✅ Gráficos
- ✅ Múltiples hojas con referencias cruzadas
- ✅ Protección de celdas

## Resumen

Este proyecto integra todas las habilidades del curso principiante. Un presupuesto personal bien construido en Excel demuestra dominio de fórmulas, formato, validación, gráficos y organización de datos.
