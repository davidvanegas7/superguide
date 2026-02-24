# Colaboración y Excel en la nube

Trabaja en equipo con Excel usando OneDrive, SharePoint y Microsoft 365.

## Excel Online vs Excel Desktop

| Característica | Online | Desktop |
|---------------|--------|---------|
| Co-autoría en tiempo real | ✅ Nativo | ✅ Con OneDrive |
| Macros/VBA | ❌ | ✅ |
| Power Query | Limitado | ✅ Completo |
| Power Pivot | ❌ | ✅ |
| Tablas dinámicas | ✅ Básicas | ✅ Completas |
| Formato condicional | ✅ Básico | ✅ Completo |
| Fórmulas | ✅ Mayoría | ✅ Todas |
| Office Scripts | ✅ | ❌ |

## Co-autoría en tiempo real

### Configurar

1. Guarda el archivo en **OneDrive** o **SharePoint**
2. Comparte con los colaboradores (Archivo → Compartir)
3. Varios usuarios pueden editar simultáneamente

### Funcionalidades

- Ves los cursores de otros usuarios en tiempo real
- Cambios se sincronizan automáticamente
- Historial de versiones disponible
- Comentarios con @menciones

## Compartir libros

### Niveles de acceso

| Nivel | Permisos |
|-------|----------|
| Puede editar | Lectura y escritura |
| Puede ver | Solo lectura |
| Específico | Por hoja o rango |

### Proteger hojas compartidas

1. Revisar → Proteger hoja → define permisos
2. Permitir edición solo en rangos específicos
3. Diferentes contraseñas por rango

## Office Scripts (alternativa a macros en la nube)

### ¿Qué es?

TypeScript/JavaScript para Excel Online. Reemplazo moderno de VBA para la nube.

### Crear un script

Pestaña **Automatizar** → **Nuevo script**

```typescript
function main(workbook: ExcelScript.Workbook) {
    // Obtener hoja activa
    let sheet = workbook.getActiveWorksheet();
    
    // Leer datos
    let range = sheet.getRange("A1:D100");
    let values = range.getValues();
    
    // Procesar
    for (let i = 1; i < values.length; i++) {
        if (values[i][2] as number > 10000) {
            sheet.getRange(`E${i + 1}`).setValue("Alto");
        } else {
            sheet.getRange(`E${i + 1}`).setValue("Normal");
        }
    }
    
    // Formato
    let headerRange = sheet.getRange("A1:E1");
    headerRange.getFormat().getFill().setColor("#0066CC");
    headerRange.getFormat().getFont().setColor("#FFFFFF");
    headerRange.getFormat().getFont().setBold(true);
}
```

### Ventajas sobre VBA

- Funciona en la nube (Excel Online)
- Se puede ejecutar desde Power Automate
- TypeScript moderno
- Versionable y compartible
- Sin problemas de seguridad de macros

## Power Automate + Excel

### Flujos automáticos

Automatiza acciones cuando:
- Se agrega una fila a una tabla de Excel
- Se modifica un archivo en SharePoint
- Se recibe un email con adjunto
- Según horario (diario, semanal)

### Ejemplo: procesar emails

1. Trigger: "Cuando llega un email con adjunto"
2. Guardar adjunto en OneDrive
3. Leer datos del Excel
4. Enviar resumen por Teams
5. Actualizar dashboard

### Ejemplo: aprobaciones

1. Usuario llena formulario en Excel Online
2. Power Automate detecta nueva fila
3. Envía solicitud de aprobación al gerente
4. Actualiza el estado en Excel
5. Notifica al solicitante

## Versionamiento

### Historial de versiones

1. Archivo → Información → Historial de versiones
2. O clic derecho en OneDrive/SharePoint → Historial de versiones

### Restaurar versión

1. Abre la versión deseada
2. "Restaurar" reemplaza la actual
3. O "Guardar copia" para mantener ambas

## Microsoft Lists vs Excel

| Aspecto | Excel | Lists |
|---------|-------|-------|
| Datos tabulares simples | ✅ | ✅ |
| Cálculos complejos | ✅ | ❌ |
| Formularios de entrada | VBA/Office Scripts | ✅ Nativo |
| Vistas múltiples | ❌ | ✅ (calendario, galería) |
| Reglas de automatización | Power Automate | ✅ Nativo |
| Capacidad | ~1M filas | 30M items |

## Resumen

Excel en la nube combina la potencia del desktop con la colaboración en tiempo real. Office Scripts reemplaza a VBA en la nube, Power Automate conecta Excel con cientos de servicios, y el versionamiento garantiza que nunca pierdas trabajo.
