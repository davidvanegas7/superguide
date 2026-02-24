# Proteger y compartir libros

La protección y el trabajo colaborativo son fundamentales en entornos profesionales.

## Proteger una hoja

1. Pestaña **Revisar** → **Proteger hoja**
2. Opcional: establece una contraseña
3. Selecciona qué acciones se permiten:

| Permiso | Descripción |
|---------|-------------|
| Seleccionar celdas bloqueadas | Pueden hacer clic pero no editar |
| Seleccionar celdas desbloqueadas | Solo celdas editables |
| Formato de celdas | Cambiar formato |
| Formato de columnas/filas | Cambiar anchos/altos |
| Insertar/Eliminar columnas/filas | Modificar estructura |
| Ordenar | Usar ordenación |
| Usar Autofiltro | Filtrar datos |

## Bloquear y desbloquear celdas

Por defecto, **todas las celdas están bloqueadas** pero la protección no está activada.

Para crear una hoja donde solo algunas celdas sean editables:

1. Selecciona las celdas que SÍ deben ser editables
2. `Ctrl + 1` → pestaña **Protección** → desmarca **Bloqueada**
3. Activa la protección de la hoja

**Tip**: Aplica un color diferente a las celdas editables para que el usuario las identifique.

## Proteger el libro

**Revisar** → **Proteger libro**

Esto previene:
- Agregar o eliminar hojas
- Renombrar hojas
- Mover hojas
- Ocultar/mostrar hojas

## Cifrar con contraseña

**Archivo** → **Información** → **Proteger libro** → **Cifrar con contraseña**

> ⚠️ Si olvidas la contraseña, no podrás recuperar el archivo. Excel usa cifrado AES de 256 bits.

## Marcar como final

**Archivo** → **Información** → **Proteger libro** → **Marcar como final**

El libro se pone en modo de solo lectura. Es informativo, no es una protección fuerte.

## Coautoría (Excel Online / Microsoft 365)

Con OneDrive o SharePoint, varios usuarios pueden editar simultáneamente:

1. Guarda el archivo en OneDrive
2. **Compartir** → ingresa los correos
3. Elige permisos: editar o solo ver

### Indicadores de coautoría

- Ves los cursores de otros usuarios con sus nombres
- Los cambios se sincronizan automáticamente
- El botón **Autoguardado** está activo

## Comentarios y notas

| Función | Uso |
|---------|-----|
| **Comentarios** | Conversaciones (mencionas con @nombre) |
| **Notas** | Anotaciones simples en celdas |

### Insertar comentario/nota
- Clic derecho → **Nuevo comentario** o **Nueva nota**
- Atajo: `Shift + F2` (nota)

## Exportar y compartir

| Formato | Uso |
|---------|-----|
| `.xlsx` | Formato estándar de Excel |
| `.xlsm` | Con macros VBA |
| `.csv` | Datos planos (compatible con todo) |
| `.pdf` | Para distribución (no editable) |
| `.xls` | Formato legacy (Excel 97-2003) |

## Resumen

La protección en Excel tiene múltiples niveles: desde celdas individuales hasta cifrado del archivo completo. Para trabajo colaborativo, OneDrive y coautoría son la solución moderna.
