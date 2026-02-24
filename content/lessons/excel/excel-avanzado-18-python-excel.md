# Excel y Python: integración moderna

Python en Excel permite usar bibliotecas como pandas y matplotlib directamente en celdas.

## Python en Excel (Microsoft 365)

### Activar

En Excel 365 Insider, la función está disponible en:
Fórmulas → **Python** → o escribir `=PY(` en una celda.

### Sintaxis básica

```python
=PY(
import pandas as pd
df = xl("A1:D100", headers=True)
df.describe()
)
```

### xl() - Leer datos de Excel

```python
# Leer rango con encabezados
datos = xl("Tabla1", headers=True)

# Leer rango específico
precios = xl("B2:B100")

# Leer de otra hoja
ventas = xl("Hoja2!A1:D500", headers=True)
```

## Análisis con pandas

### Estadísticas descriptivas

```python
=PY(
import pandas as pd
df = xl("Tabla1", headers=True)
df.describe()
)
```

### Agrupar y resumir

```python
=PY(
import pandas as pd
df = xl("Tabla1", headers=True)

resumen = df.groupby("Región").agg({
    "Ventas": ["sum", "mean", "count"],
    "Margen": "mean"
}).round(2)

resumen
)
```

### Filtrar datos

```python
=PY(
import pandas as pd
df = xl("Tabla1", headers=True)

# Filtro complejo
resultado = df[
    (df["Ventas"] > 10000) & 
    (df["Región"].isin(["Norte", "Sur"])) &
    (df["Fecha"] >= "2026-01-01")
]

resultado.sort_values("Ventas", ascending=False)
)
```

### Tablas dinámicas con pandas

```python
=PY(
import pandas as pd
df = xl("Tabla1", headers=True)

pivot = pd.pivot_table(df,
    values="Ventas",
    index="Región",
    columns="Categoría",
    aggfunc="sum",
    fill_value=0,
    margins=True
)

pivot
)
```

## Visualización con matplotlib

### Gráfico de barras

```python
=PY(
import pandas as pd
import matplotlib.pyplot as plt

df = xl("Tabla1", headers=True)
resumen = df.groupby("Región")["Ventas"].sum()

fig, ax = plt.subplots(figsize=(10, 6))
resumen.plot(kind="bar", ax=ax, color="#0066CC")
ax.set_title("Ventas por Región", fontsize=16)
ax.set_ylabel("Ventas ($)")
plt.xticks(rotation=45)
plt.tight_layout()
fig
)
```

### Gráfico de dispersión

```python
=PY(
import matplotlib.pyplot as plt
import numpy as np

df = xl("Tabla1", headers=True)

fig, ax = plt.subplots(figsize=(10, 6))
ax.scatter(df["Publicidad"], df["Ventas"], alpha=0.6)

# Línea de tendencia
z = np.polyfit(df["Publicidad"], df["Ventas"], 1)
p = np.poly1d(z)
ax.plot(df["Publicidad"].sort_values(), p(df["Publicidad"].sort_values()), 
        "r--", alpha=0.8, label=f"Tendencia: y={z[0]:.1f}x+{z[1]:.0f}")

ax.legend()
ax.set_xlabel("Inversión en Publicidad")
ax.set_ylabel("Ventas")
ax.set_title("Correlación Publicidad vs Ventas")
fig
)
```

## Machine Learning básico

### Regresión lineal con scikit-learn

```python
=PY(
import pandas as pd
from sklearn.linear_model import LinearRegression
from sklearn.model_selection import train_test_split

df = xl("Tabla1", headers=True)

X = df[["Publicidad", "Precio", "Competidores"]]
y = df["Ventas"]

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2)

modelo = LinearRegression()
modelo.fit(X_train, y_train)

score = modelo.score(X_test, y_test)
coeficientes = pd.DataFrame({
    "Variable": X.columns,
    "Coeficiente": modelo.coef_
})

coeficientes["R²"] = score
coeficientes
)
```

### Clustering

```python
=PY(
import pandas as pd
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler

df = xl("Clientes", headers=True)

features = df[["Frecuencia", "MontoPromedio", "Antigüedad"]]
scaler = StandardScaler()
features_scaled = scaler.fit_transform(features)

kmeans = KMeans(n_clusters=4, random_state=42)
df["Segmento"] = kmeans.fit_predict(features_scaled)

df.groupby("Segmento").agg({
    "Frecuencia": "mean",
    "MontoPromedio": "mean",
    "Antigüedad": "mean",
    "Cliente": "count"
}).round(2)
)
```

## Limpieza de datos con Python

```python
=PY(
import pandas as pd

df = xl("DatosSucios", headers=True)

# Pipeline de limpieza
df["Nombre"] = df["Nombre"].str.strip().str.title()
df["Email"] = df["Email"].str.lower().str.strip()
df["Teléfono"] = df["Teléfono"].str.replace(r"[^\d]", "", regex=True)
df["Fecha"] = pd.to_datetime(df["Fecha"], errors="coerce")
df = df.dropna(subset=["Email"])
df = df.drop_duplicates(subset=["Email"])

df
)
```

## Resumen

Python en Excel combina la interfaz familiar de la hoja de cálculo con la potencia de pandas, matplotlib y scikit-learn. Es ideal para análisis que van más allá de las fórmulas nativas, sin salir de Excel.
