# Trabajar con múltiples hojas

Un libro de Excel puede contener múltiples hojas de trabajo, lo que permite organizar datos relacionados.

## Operaciones básicas con hojas

| Acción | Cómo |
|--------|------|
| **Agregar hoja** | Clic en el ícono `+` junto a las pestañas |
| **Renombrar** | Doble clic en la pestaña |
| **Mover** | Arrastrar la pestaña |
| **Copiar** | `Ctrl` + arrastrar la pestaña |
| **Eliminar** | Clic derecho → Eliminar |
| **Color de pestaña** | Clic derecho → Color de etiqueta |
| **Ocultar hoja** | Clic derecho → Ocultar |

## Navegar entre hojas

| Atajo | Acción |
|-------|--------|
| `Ctrl + AvPág` | Siguiente hoja |
| `Ctrl + RePág` | Hoja anterior |
| Clic derecho en flechas de navegación | Lista de todas las hojas |

## Referencias entre hojas

Para usar datos de otra hoja en una fórmula:

```
=Hoja2!A1
='Ventas Enero'!B5
=SUMA(Enero!A1:A10)
```

### Referencia 3D (entre hojas)

Si tienes las mismas celdas en varias hojas (Enero, Febrero, Marzo):

```
=SUMA(Enero:Marzo!B2)
```

Esto suma la celda B2 de las hojas Enero, Febrero y Marzo.

## Agrupar hojas

Para editar varias hojas simultáneamente:

1. Clic en la primera pestaña
2. `Shift` + clic en la última pestaña (rango continuo)
3. O `Ctrl` + clic en pestañas individuales

Cuando las hojas están agrupadas, aparece `[Grupo]` en el título. **Todo lo que escribas se replicará en todas las hojas del grupo.**

> ⚠️ Desagrupa las hojas cuando termines (clic derecho → Desagrupar hojas).

## Ejemplo: reporte mensual

Estructura típica de un libro con datos mensuales:

```
📊 Libro: Ventas_2026.xlsx
├── 📋 Resumen (totales anuales, fórmulas 3D)
├── 📋 Enero (ventas del mes)
├── 📋 Febrero
├── 📋 Marzo
├── ...
├── 📋 Diciembre
└── 📋 Config (parámetros, listas)
```

En la hoja **Resumen**:
```
=SUMA(Enero:Diciembre!B2)    → total anual de ventas
=PROMEDIO(Enero:Diciembre!B2)  → promedio mensual
```

## Proteger hojas

Para evitar cambios accidentales:

1. Pestaña **Revisar** → **Proteger hoja**
2. Define qué pueden hacer los usuarios (seleccionar, formato, insertar…)
3. Opcionalmente establece una contraseña

### Proteger el libro

**Revisar** → **Proteger libro** evita que se agreguen, eliminen o renombren hojas.

## Resumen

Organizar datos en múltiples hojas mejora la claridad y mantenibilidad de tus libros. Las referencias entre hojas y las fórmulas 3D permiten consolidar información de forma eficiente.
