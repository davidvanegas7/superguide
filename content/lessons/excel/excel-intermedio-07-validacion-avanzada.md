# Validación de datos avanzada

Técnicas avanzadas de validación para crear formularios profesionales y a prueba de errores.

## Listas desplegables dependientes

Una lista que cambia según la selección de otra:

### Paso 1: Definir las listas

| País | Ciudades |
|------|----------|
| México | CDMX, Guadalajara, Monterrey |
| Colombia | Bogotá, Medellín, Cali |
| Argentina | Buenos Aires, Córdoba, Rosario |

### Paso 2: Crear rangos con nombre

Selecciona cada lista de ciudades y nómbrala igual que el país (sin espacios ni acentos):
- `Mexico` → CDMX, Guadalajara, Monterrey
- `Colombia` → Bogotá, Medellín, Cali

### Paso 3: Validación con INDIRECTO

En la celda de ciudad:
```
Validación → Lista → Origen: =INDIRECTO(A2)
```

Donde A2 contiene el país seleccionado. `INDIRECTO` convierte el texto al nombre del rango.

## Validación con fórmulas complejas

### No permitir duplicados

```
=CONTAR.SI($A$2:$A$1000, A2)<=1
```

### Formato de email válido

```
=Y(
    ESNUMERO(ENCONTRAR("@", A2)),
    ESNUMERO(ENCONTRAR(".", A2, ENCONTRAR("@", A2))),
    LARGO(A2) - LARGO(SUSTITUIR(A2, "@", "")) = 1
)
```

### RFC mexicano (13 caracteres)

```
=Y(LARGO(A2)=13, ESNUMERO(A2*1)=FALSO)
```

### Teléfono (10 dígitos)

```
=Y(LARGO(A2)=10, ESNUMERO(A2+0))
```

### Fecha no en fin de semana

```
=Y(
    ESNUMERO(A2),
    DIASEM(A2,2)<6
)
```

## Validación con mensajes dinámicos

Usa `CONCATENAR` o `&` en los mensajes:

```
Título: "Límite de crédito"
Mensaje: "Máximo permitido: $" & TEXTO(B1,"#,##0")
```

> Nota: los mensajes de validación no soportan fórmulas directamente, pero puedes usar macros para lograrlo.

## Celdas dependientes

Habilitar/deshabilitar celdas según otras selecciones:

1. Celda B2 (cantidad): solo editable si A2 tiene un producto
2. Validación personalizada en B2:
```
=A2<>""
```

Si A2 está vacía, no permite ingresar cantidad.

## Protección + Validación

Combina ambas para formularios robustos:

1. Desbloquea solo las celdas de ingreso
2. Aplica validación a cada celda
3. Aplica formato condicional para indicar campos requeridos vacíos
4. Protege la hoja

### Indicador visual de campos requeridos

Formato condicional en celdas obligatorias vacías:
```
=Y(A2="", $Z$1="mostrar_errores")
```
Formato: borde rojo + relleno rosa claro.

## Círculos de datos no válidos

Si ya tienes datos y quieres verificar:

1. Datos → Validación de datos → **Rodear con un círculo datos no válidos**
2. Los datos que violan la regla se marcan con un círculo rojo

Para quitar: **Borrar círculos de validación**

## Resumen

La validación avanzada con listas dependientes, fórmulas personalizadas y combinación con protección permite crear formularios profesionales que guían al usuario y previenen errores de entrada.
