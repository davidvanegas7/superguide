# Análisis estadístico y regresión en Excel

Herramientas de Excel para análisis estadístico profesional y modelado predictivo.

## Analysis ToolPak

### Activar

Archivo → Opciones → Complementos → **Herramientas de análisis**

### Herramientas disponibles

- Estadística descriptiva
- Histograma
- Correlación
- Regresión
- ANOVA
- Prueba t
- Prueba F
- Análisis de Fourier
- Media móvil
- Muestreo

## Estadística descriptiva

Datos → Análisis de datos → **Estadística descriptiva**

Genera automáticamente:
- Media, mediana, moda
- Desviación estándar, varianza
- Curtosis, asimetría
- Rango, mínimo, máximo
- Error estándar
- Nivel de confianza

### Con fórmulas

```
Media:          =PROMEDIO(datos)
Mediana:        =MEDIANA(datos)
Moda:           =MODA.UNO(datos)
DesvEst:        =DESVEST(datos)          → muestra
                =DESVEST.P(datos)        → población
Varianza:       =VAR(datos)
Asimetría:      =COEFICIENTE.ASIMETRIA(datos)
Curtosis:       =CURTOSIS(datos)
Error estándar: =DESVEST(datos)/RAIZ(CONTARA(datos))
```

## Correlación

Mide la relación lineal entre dos variables (-1 a 1):

```
=COEF.DE.CORREL(rango_x, rango_y)
```

### Matriz de correlación

Datos → Análisis de datos → **Correlación**

Genera una matriz con la correlación entre todas las variables.

### Interpretación

| Valor | Interpretación |
|-------|---------------|
| 0.9 a 1.0 | Correlación muy fuerte positiva |
| 0.7 a 0.9 | Fuerte positiva |
| 0.4 a 0.7 | Moderada positiva |
| 0.0 a 0.4 | Débil o ninguna |
| -0.4 a 0.0 | Débil negativa |
| -1.0 a -0.7 | Fuerte negativa |

## Regresión lineal simple

### Con Analysis ToolPak

Datos → Análisis de datos → **Regresión**

- Rango Y: variable dependiente
- Rango X: variable(s) independiente(s)
- Nivel de confianza: 95%
- Gráficos de residuos: Sí

### Interpretar resultados

| Métrica | Significado |
|---------|-------------|
| R² | % de variación explicada (0.85 = 85%) |
| R² ajustado | R² corregido por número de variables |
| Error estándar | Precisión de las predicciones |
| Coeficientes | Pendiente e intercepto de la recta |
| Valor P | Significancia (< 0.05 = significativo) |
| F | Significancia global del modelo |

### Ecuación del modelo

```
Y = intercepto + (coeficiente × X)
```

Si intercepto = 1000 y coeficiente = 50:
```
Ventas = 1000 + 50 × Publicidad
```

### Con fórmulas

```
Pendiente:    =PENDIENTE(Y, X)
Intercepto:   =INTERSECCION.EJE(Y, X)
R²:           =COEF.DE.CORREL(X, Y)^2
Predicción:   =PRONOSTICO(nuevo_X, Y, X)
```

## Regresión múltiple

Múltiples variables independientes:

```
Ventas = β0 + β1×Publicidad + β2×Precio + β3×Competidores
```

En Analysis ToolPak, simplemente incluye múltiples columnas en el Rango X.

### Multicolinealidad

Si las variables independientes están muy correlacionadas entre sí, el modelo es inestable. Verifica con la matriz de correlación.

## Pruebas de hipótesis

### Prueba t (2 muestras)

¿Son diferentes las medias de dos grupos?

Datos → Análisis de datos → **Prueba t para dos muestras**

- Si valor P < 0.05 → diferencia significativa
- Si valor P ≥ 0.05 → no hay evidencia de diferencia

### ANOVA (3+ grupos)

¿Son diferentes las medias de tres o más grupos?

Datos → Análisis de datos → **ANOVA de un factor**

### Con fórmulas

```
=PRUEBA.T(rango1, rango2, colas, tipo)
=PRUEBA.F(rango1, rango2)
=PRUEBA.CHI(observados, esperados)
```

## Intervalos de confianza

```
=INTERVALO.CONFIANZA.NORM(alfa, desvest, tamaño)
=INTERVALO.CONFIANZA.T(alfa, desvest, tamaño)
```

```
Media ± Intervalo = rango donde está el valor real con X% de confianza
```

## Pronósticos

### PRONOSTICO.LINEAL

```
=PRONOSTICO.LINEAL(x_nuevo, rango_y, rango_x)
```

### TENDENCIA (múltiples predicciones)

```
=TENDENCIA(rango_y, rango_x, nuevos_x)
```

### PRONOSTICO.ETS (series temporales)

```
=PRONOSTICO.ETS(fecha_objetivo, valores, fechas)
```

Incluye detección automática de estacionalidad.

## Resumen

Excel es una herramienta estadística competente con Analysis ToolPak, funciones integradas y capacidades de regresión. Para análisis exploratorio y modelos básicos es ideal; para análisis avanzado considera R o Python como complemento.
