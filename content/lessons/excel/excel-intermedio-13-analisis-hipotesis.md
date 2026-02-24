# Análisis de hipótesis y escenarios

Herramientas de Excel para modelar situaciones y tomar decisiones basadas en datos.

## Tablas de datos (What-If)

### Tabla de una variable

Analiza cómo cambia un resultado al variar UN parámetro.

**Ejemplo**: ¿Cómo cambia la cuota mensual con diferentes tasas de interés?

1. Fórmula del préstamo en una celda (ej: B1)
2. Lista de tasas en una columna (A4:A14)
3. En B3, referencia a la fórmula: `=B1`
4. Selecciona A3:B14
5. Datos → Análisis Y si → **Tabla de datos**
6. Celda de entrada (columna): la celda de la tasa original

### Tabla de dos variables

Analiza variando DOS parámetros simultáneamente.

1. Fórmula en la esquina superior izquierda
2. Valores de variable 1 en la columna
3. Valores de variable 2 en la fila
4. Selecciona todo el rango
5. Datos → Tabla de datos → define ambas celdas de entrada

**Ejemplo**: cuota mensual variando tasa Y plazo.

## Buscar objetivo (Goal Seek)

Encuentra el valor de entrada necesario para obtener un resultado deseado.

1. Datos → Análisis Y si → **Buscar objetivo**
2. Define:
   - Definir la celda: celda con la fórmula
   - Con el valor: resultado deseado
   - Para cambiar la celda: la variable de entrada

**Ejemplo**: ¿Qué precio debo cobrar para obtener $50,000 de ganancia?

## Administrador de escenarios

Guarda y compara diferentes conjuntos de valores.

### Crear escenarios

1. Datos → Análisis Y si → **Administrador de escenarios**
2. Agregar escenario:
   - Nombre: "Optimista"
   - Celdas cambiantes: las variables
   - Valores: los valores del escenario
3. Repite para "Pesimista" y "Base"

### Resumen de escenarios

1. En el Administrador → **Resumen**
2. Define las celdas de resultado
3. Excel crea una tabla comparativa automática

## Solver

Herramienta de optimización para encontrar la mejor solución:

### Activar Solver

Archivo → Opciones → Complementos → Complementos de Excel → Solver

### Configurar

1. Datos → Solver
2. Define:
   - **Objetivo**: celda a optimizar
   - **Para**: Máximo, Mínimo o Valor específico
   - **Cambiando**: celdas variables
   - **Restricciones**: límites y condiciones

### Ejemplo: maximizar ganancias

- Objetivo: maximizar celda de ganancia total
- Cambiando: cantidades de producción
- Restricciones:
  - Cantidades >= 0
  - Material total <= 1000 kg
  - Horas trabajo <= 160

## Funciones financieras

### Valor presente y futuro

```
=VA(tasa, nper, pago, [vf])       → Valor Actual
=VF(tasa, nper, pago, [va])       → Valor Futuro
=PAGO(tasa, nper, va, [vf])       → Cuota/Pago periódico
=NPER(tasa, pago, va, [vf])       → Número de periodos
=TASA(nper, pago, va, [vf])       → Tasa de interés
```

### Ejemplo: préstamo

Préstamo de $100,000 a 12% anual, 36 meses:
```
=PAGO(0.12/12, 36, -100000)  → $3,321.43 mensual
```

### Análisis de inversiones

```
=VNA(tasa, flujos)            → Valor Neto Actual
=TIR(flujos, [estimación])    → Tasa Interna de Retorno
```

```
=VNA(0.10, B2:B6) + B1
=TIR(B1:B6)
```

## Sensibilidad con formato condicional

Combina tablas de datos con formato condicional para visualizar:
- Verde: escenarios favorables
- Rojo: escenarios desfavorables
- Amarillo: punto de equilibrio

## Resumen

Las herramientas de análisis de hipótesis permiten explorar escenarios, optimizar decisiones y modelar situaciones financieras. Buscar objetivo para problemas simples, tablas de datos para sensibilidad, escenarios para comparar, y Solver para optimización compleja.
