# Dashboards profesionales y visualización avanzada

Técnicas para construir dashboards de nivel ejecutivo directamente en Excel.

## Principios de diseño

### 1. Jerarquía visual

Los elementos más importantes deben captar la atención primero:
- KPIs grandes y prominentes arriba
- Gráficos detallados en el medio
- Tablas de datos abajo

### 2. Regla de los 5 segundos

El usuario debe entender el mensaje principal en 5 segundos.

### 3. Consistencia

- Misma paleta de colores en todo el dashboard
- Fuentes consistentes (máximo 2 familias)
- Alineación uniforme

### 4. Ratio datos-tinta

Maximiza la información, minimiza la decoración. Elimina:
- Líneas de cuadrícula innecesarias
- Bordes 3D
- Sombras decorativas
- Fondos de gráfico

## Estructura del archivo

```
📊 Dashboard.xlsm
├── 📄 Dashboard (vista principal)
├── 📄 Datos (datos crudos / conexiones)
├── 📄 Cálculos (fórmulas intermedias)
├── 📄 Catálogos (listas, parámetros)
└── 📄 Config (colores, settings)
```

## Tarjetas KPI

### Diseño con celdas combinadas

1. Combina celdas para crear una "tarjeta" (ej: B2:D5)
2. Formato:
   - Fondo blanco o color de acento
   - Borde fino o sombra sutil
   - Etiqueta (fuente 10pt, gris)
   - Valor (fuente 28pt, negrita)
   - Indicador (▲▼ con color verde/rojo)

### Indicador de tendencia

```
=SI(actual>anterior, "▲ " & TEXTO((actual-anterior)/anterior, "0.0%"),
    "▼ " & TEXTO((anterior-actual)/anterior, "0.0%"))
```

### Sparkline en la tarjeta

Agrega un minigráfico debajo del KPI para mostrar tendencia.

## Gráficos para dashboards

### Gráfico de bala (Bullet Chart)

Muestra valor actual vs. meta:

1. Crea gráfico de barras apiladas
2. Primera serie: valor actual (barra delgada)
3. Segunda serie: meta (línea/marca)
4. Tercera serie: rangos de rendimiento (barras anchas, grises)

### Gráfico de dona con KPI central

1. Gráfico de dona estándar
2. Reduce el tamaño del agujero a 70%
3. Coloca un cuadro de texto centrado con el porcentaje
4. Solo 2 series: completado (color) y restante (gris claro)

### Gráfico de termómetro

Para mostrar progreso hacia una meta:

1. Gráfico de barras con una sola barra
2. Color degradado de rojo a verde
3. Línea de meta superpuesta

### Gráfico de semáforo

Usando formato condicional con iconos o formas:

```
🟢 > 90% del objetivo
🟡 70-90% del objetivo
🔴 < 70% del objetivo
```

## Controles interactivos

### Segmentaciones estilizadas

1. Inserta segmentaciones desde tabla dinámica
2. Pestaña Segmentación → Estilos
3. Personaliza colores y tamaño de botones
4. Organiza horizontalmente arriba del dashboard

### Escala de tiempo

Para filtrar por períodos de forma visual.

### ComboBox de formulario

1. Desarrollador → Insertar → Cuadro combinado
2. Vincula a una celda
3. Usa la celda vinculada en fórmulas INDICE:

```
=INDICE(lista_regiones, celda_combo)
```

## Imágenes vinculadas

Para mover gráficos y tablas sin romper la referencia:

1. Selecciona el rango o gráfico original
2. Copiar
3. En el dashboard: **Pegado especial → Imagen vinculada**

La imagen se actualiza automáticamente cuando los datos cambian.

## Diseño responsivo

### Configurar la vista

1. Oculta encabezados de fila/columna
2. Oculta líneas de cuadrícula
3. Oculta hojas de soporte (clic derecho → Ocultar)
4. Congela paneles en la fila correcta
5. Establece el zoom adecuado (85-100%)

### Protección del dashboard

1. Desbloquea solo las celdas de filtro
2. Protege la hoja con contraseña
3. Oculta la barra de fórmulas

## Paletas de colores profesionales

| Paleta | Colores |
|--------|---------|
| Corporativa | #003366, #0066CC, #339966, #FF9900 |
| Moderna | #2C3E50, #3498DB, #2ECC71, #E74C3C |
| Neutra | #34495E, #7F8C8D, #BDC3C7, #ECF0F1 |

## Resumen

Un dashboard profesional combina diseño limpio, KPIs prominentes, gráficos informativos y filtros interactivos. La clave es contar una historia con los datos, no simplemente mostrarlos.
