# Funciones de fecha y hora

Excel almacena las fechas como números (días desde el 1 de enero de 1900) y las horas como fracciones del día.

## Funciones de fecha actual

```
=HOY()     → fecha actual (se actualiza automáticamente)
=AHORA()   → fecha y hora actual
```

## Extraer componentes de fecha

| Función | Resultado | Ejemplo con 24/02/2026 |
|---------|-----------|----------------------|
| `AÑO(fecha)` | Año | 2026 |
| `MES(fecha)` | Mes (1-12) | 2 |
| `DIA(fecha)` | Día (1-31) | 24 |
| `DIASEM(fecha)` | Día de la semana (1-7) | 3 (martes) |
| `NUM.DE.SEMANA(fecha)` | Semana del año | 9 |

## Crear una fecha

```
=FECHA(2026, 2, 24)    → 24/02/2026
=FECHA(AÑO(A1), MES(A1)+1, 1)  → primer día del mes siguiente
```

## Calcular diferencias de fecha

### SIFECHA (función oculta)

```
=SIFECHA(fecha_inicio, fecha_fin, unidad)
```

| Unidad | Resultado |
|--------|-----------|
| `"Y"` | Años completos |
| `"M"` | Meses completos |
| `"D"` | Días |
| `"YM"` | Meses restantes (sin contar años) |
| `"MD"` | Días restantes (sin contar meses) |

**Ejemplo**: Calcular la edad:
```
=SIFECHA(A1, HOY(), "Y") & " años"
```

### Resta simple

```
=B1-A1    → diferencia en días
```

## Funciones de hora

```
=HORA(celda)      → horas (0-23)
=MINUTO(celda)    → minutos (0-59)
=SEGUNDO(celda)   → segundos (0-59)
```

### Crear una hora

```
=HORA(14, 30, 0)    → 02:30 PM
```

### Calcular horas trabajadas

```
=B1-A1    → si B1=17:00 y A1=09:00 → 08:00 (8 horas)
```

Para convertir a número decimal de horas:
```
=(B1-A1)*24    → 8.0
```

## DIAS.LAB y DIAS.LAB.INTL

Calcula días laborables entre dos fechas:

```
=DIAS.LAB(fecha_inicio, fecha_fin)
=DIAS.LAB(fecha_inicio, fecha_fin, festivos)
```

Donde `festivos` es un rango con las fechas festivas.

## FIN.MES

Devuelve el último día de un mes:

```
=FIN.MES(HOY(), 0)     → último día del mes actual
=FIN.MES(HOY(), 1)     → último día del próximo mes
=FIN.MES(HOY(), -1)    → último día del mes anterior
```

## Formato de fecha personalizado

| Código | Ejemplo | Resultado |
|--------|---------|-----------|
| `D` | Día sin cero | 5 |
| `DD` | Día con cero | 05 |
| `DDD` | Día abreviado | Lun |
| `DDDD` | Día completo | Lunes |
| `MM` | Mes con cero | 02 |
| `MMM` | Mes abreviado | Feb |
| `MMMM` | Mes completo | Febrero |
| `AA` | Año 2 dígitos | 26 |
| `AAAA` | Año 4 dígitos | 2026 |

## Resumen

Excel maneja fechas como números y horas como fracciones. Las funciones HOY, FECHA, AÑO, MES, DIA y SIFECHA cubren la mayoría de necesidades. Recuerda que el formato visual es independiente del valor almacenado.
