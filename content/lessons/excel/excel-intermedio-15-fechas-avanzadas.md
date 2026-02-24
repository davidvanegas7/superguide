# Funciones de fecha y hora avanzadas

Domina el cálculo de fechas para reportes, plazos y análisis temporal.

## Cómo Excel almacena fechas

Internamente, las fechas son números enteros:
- 1 = 1 de enero de 1900
- 45658 = 26 de diciembre de 2024

Las horas son fracciones del día:
- 0.5 = 12:00 PM (mediodía)
- 0.75 = 6:00 PM

## Funciones avanzadas

### DIAS.LAB (NETWORKDAYS)

Calcula días laborables entre dos fechas (excluye fines de semana):

```
=DIAS.LAB(fecha_inicio, fecha_fin, [festivos])
```

```
=DIAS.LAB(A2, B2)                  → días laborables sin festivos
=DIAS.LAB(A2, B2, festivos)        → con lista de festivos
```

### DIAS.LAB.INTL

Permite definir qué días son no laborables:

```
=DIAS.LAB.INTL(inicio, fin, fin_de_semana, festivos)
```

| Código | Días no laborables |
|--------|-------------------|
| 1 | Sábado, Domingo |
| 2 | Domingo, Lunes |
| 11 | Solo Domingo |
| 7 | Solo Viernes |

### DIA.LAB (WORKDAY)

Calcula una fecha futura/pasada saltando solo días laborables:

```
=DIA.LAB(fecha_inicio, dias, [festivos])
```

```
=DIA.LAB(HOY(), 10)  → 10 días laborables desde hoy
```

## Agrupar fechas

### Por trimestre

```
=REDONDEAR.MAS(MES(A2)/3, 0)
="Q" & REDONDEAR.MAS(MES(A2)/3, 0)
```

### Por semana

```
=A2 - DIASEM(A2, 2) + 1    → lunes de esa semana
```

### Por mes-año

```
=TEXTO(A2, "yyyy-mm")
=FECHA(AÑO(A2), MES(A2), 1)    → primer día del mes
```

## Funciones SIFECHA (DATEDIF)

Función oculta (no aparece en autocompletado):

```
=SIFECHA(fecha_inicio, fecha_fin, unidad)
```

| Unidad | Devuelve |
|--------|----------|
| "Y" | Años completos |
| "M" | Meses completos |
| "D" | Días |
| "YM" | Meses después de restar años |
| "MD" | Días después de restar meses |

### Calcular edad

```
=SIFECHA(fecha_nacimiento, HOY(), "Y") & " años, " &
 SIFECHA(fecha_nacimiento, HOY(), "YM") & " meses"
```

## FRAC.AÑO (YEARFRAC)

Devuelve la fracción del año entre dos fechas:

```
=FRAC.AÑO(inicio, fin, [base])
```

Útil para cálculos financieros donde se necesita la proporción exacta del año.

## Fechas dinámicas

### Primer y último día del mes

```
=FECHA(AÑO(A2), MES(A2), 1)                  → primer día
=FIN.MES(A2, 0)                                → último día
```

### Primer y último día del año

```
=FECHA(AÑO(HOY()), 1, 1)                      → 1 de enero
=FECHA(AÑO(HOY()), 12, 31)                    → 31 de diciembre
```

### Siguiente viernes

```
=A2 + RESIDUO(6 - DIASEM(A2, 2), 7)
```

## Horas y tiempo

### Sumar horas que pasen de 24

Formato personalizado: `[h]:mm:ss`

```
=SUMA(A2:A10)   → con formato [h]:mm:ss muestra "36:45:00"
```

### Calcular horas trabajadas con horario nocturno

```
=SI(B2>A2, B2-A2, 1-A2+B2)
```

### Convertir horas decimales a horas y minutos

```
=ENTERO(A2) & "h " & REDONDEAR((A2-ENTERO(A2))*60, 0) & "m"
```

## SECUENCIA de fechas (Excel 365)

```
=SECUENCIA(12, 1, FECHA(2026,1,1), 30)   → 12 fechas cada 30 días
```

## Resumen

Las funciones de fecha avanzadas como DIAS.LAB, SIFECHA y FIN.MES son fundamentales para cálculos de plazos, antigüedad y reportes temporales. Recuerda que las fechas son números, lo que permite operaciones aritméticas directas.
