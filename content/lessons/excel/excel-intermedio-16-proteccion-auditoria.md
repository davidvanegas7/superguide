# Protección avanzada y auditoría de fórmulas

Técnicas para asegurar la integridad de tus hojas y depurar fórmulas complejas.

## Protección a nivel de celdas

### Desbloquear celdas de entrada

Por defecto, TODAS las celdas están marcadas como "bloqueadas", pero la protección no se activa hasta proteger la hoja.

1. Selecciona las celdas de entrada
2. Clic derecho → Formato de celdas → Protección
3. Desmarca **Bloqueada**
4. Opcionalmente marca **Oculta** para ocultar fórmulas

### Proteger la hoja

Revisar → **Proteger hoja**

Configura qué pueden hacer los usuarios:
- Seleccionar celdas bloqueadas/desbloqueadas
- Dar formato a celdas/columnas/filas
- Insertar/eliminar columnas/filas
- Ordenar y filtrar
- Usar tablas dinámicas

## Protección por rangos

### Permitir edición de rangos (Windows)

Revisar → **Permitir edición de rangos**

Define rangos que ciertos usuarios pueden editar (con o sin contraseña).

## Proteger libro

Revisar → **Proteger libro**

Previene:
- Agregar/eliminar hojas
- Mover hojas
- Ocultar/mostrar hojas
- Renombrar hojas

## Proteger con cifrado

Archivo → Información → **Proteger libro** → **Cifrar con contraseña**

Esto cifra todo el archivo. Sin la contraseña, no se puede abrir.

## Auditoría de fórmulas

### Rastrear precedentes y dependientes

Pestaña **Fórmulas**:

- **Rastrear precedentes**: muestra flechas hacia las celdas que alimentan la fórmula actual
- **Rastrear dependientes**: muestra flechas hacia las celdas que usan el valor actual
- **Quitar flechas**: limpia las flechas

### Evaluar fórmula paso a paso

Fórmulas → **Evaluar fórmula**

Ejecuta la fórmula paso a paso, mostrando cada evaluación intermedia.

### Comprobación de errores

Fórmulas → **Comprobación de errores**

Revisa toda la hoja buscando:
- Errores en fórmulas
- Fórmulas inconsistentes con las vecinas
- Celdas vacías en fórmulas
- Números almacenados como texto

### Ventana de inspección (Watch Window)

Fórmulas → **Ventana de inspección**

Agrega celdas para monitorear sus valores mientras trabajas en otras partes del libro. Ideal para hojas grandes.

## Errores comunes y soluciones

| Error | Causa | Solución |
|-------|-------|----------|
| `#N/A` | BUSCARV no encontró el valor | Verificar datos, usar SI.ND |
| `#REF!` | Referencia rota (fila/columna eliminada) | Reconstruir la fórmula |
| `#VALOR!` | Tipo de dato incorrecto | Verificar que los datos sean del tipo esperado |
| `#DIV/0!` | División por cero | Usar SI.ERROR o verificar denominador |
| `#NOMBRE?` | Nombre no reconocido | Verificar ortografía de funciones/nombres |
| `#NUM!` | Valor numérico no válido | Verificar argumentos |
| `#NULO!` | Intersección nula | Verificar operador de rango |
| `#DERRAME!` | Celda de destino no vacía | Limpiar celdas adyacentes |

## Fórmulas circulares

Ocurren cuando una fórmula se refiere a sí misma directa o indirectamente.

### Detectar

Fórmulas → Comprobación de errores → **Referencias circulares**

Muestra la lista de celdas con circularidad.

### Uso intencional

En algunos modelos financieros se usan intencionalmente:

Archivo → Opciones → Fórmulas → **Habilitar cálculo iterativo**

> ⚠️ Usa con precaución. Las iteraciones pueden no converger.

## Documentar hojas

### Comentarios y notas

- **Comentarios** (modernos): permiten conversaciones con hilos
- **Notas** (clásicas): texto simple anclado a una celda

### Buenas prácticas

1. Usa una hoja de **Instrucciones** o **README**
2. Nombra las hojas descriptivamente
3. Usa nombres definidos en lugar de rangos crípticos
4. Colorea las celdas de entrada (ej: azul claro)
5. Documenta supuestos y fuentes de datos

## Resumen

La protección y auditoría aseguran que tus modelos sean confiables y a prueba de errores del usuario. Combina protección de celdas, auditoría de fórmulas y documentación para crear hojas profesionales.
