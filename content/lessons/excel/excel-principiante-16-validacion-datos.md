# Validación de datos básica

La validación evita que los usuarios ingresen datos incorrectos, manteniendo la integridad de tu hoja.

## Aplicar validación

1. Selecciona las celdas
2. Pestaña **Datos** → **Validación de datos**

## Tipos de validación

| Tipo | Restricción |
|------|-------------|
| **Número entero** | Solo enteros, con condiciones (entre, mayor que, etc.) |
| **Decimal** | Números con decimales |
| **Lista** | Desplegable con opciones definidas |
| **Fecha** | Solo fechas en un rango |
| **Hora** | Solo horas en un rango |
| **Longitud del texto** | Limita la cantidad de caracteres |
| **Personalizada** | Fórmula que devuelve VERDADERO/FALSO |

## Lista desplegable

La validación más usada:

1. Validación de datos → Permitir: **Lista**
2. En **Origen**, escribe las opciones separadas por coma:
   ```
   Alto,Medio,Bajo
   ```
3. O selecciona un rango que contenga las opciones

### Lista desde un rango

Si tus opciones están en las celdas E1:E5:
```
Origen: =$E$1:$E$5
```

Ventaja: si agregas opciones al rango, el desplegable se actualiza.

## Mensaje de entrada

En la pestaña **Mensaje de entrada** del diálogo de validación:

- **Título**: "Seleccione prioridad"
- **Mensaje**: "Elija entre Alto, Medio o Bajo"

Se muestra como un tooltip cuando el usuario selecciona la celda.

## Mensaje de error

En la pestaña **Mensaje de error**:

| Estilo | Comportamiento |
|--------|---------------|
| **Detener** 🛑 | No permite el dato incorrecto |
| **Advertencia** ⚠️ | Advierte pero permite continuar |
| **Información** ℹ️ | Solo informa |

## Validación con fórmula

Para reglas personalizadas, usa una fórmula que devuelva VERDADERO:

**Ejemplo**: Solo permitir emails (que contengan @):
```
=ESNUMERO(ENCONTRAR("@", A1))
```

**Ejemplo**: No permitir fechas futuras:
```
=A1<=HOY()
```

**Ejemplo**: Solo números positivos:
```
=A1>0
```

## Resaltar celdas no válidas

Para encontrar datos que violan las reglas:

**Datos** → **Validación de datos** → **Rodear con un círculo datos no válidos**

## Eliminar validación

1. Selecciona las celdas
2. Datos → Validación de datos → **Borrar todos**

## Resumen

La validación de datos previene errores antes de que ocurran. Las listas desplegables son la forma más práctica, y las fórmulas personalizadas cubren cualquier regla que necesites.
