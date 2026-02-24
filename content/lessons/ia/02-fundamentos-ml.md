# Fundamentos de Machine Learning

Machine Learning (ML) es la rama de la IA donde los sistemas aprenden patrones a partir de datos, sin ser programados explícitamente para cada caso. Es la base sobre la que se construyen los LLMs y toda la IA moderna.

## Tipos de aprendizaje

### Aprendizaje Supervisado

El modelo aprende de ejemplos etiquetados (input → output conocido):

```python
from sklearn.linear_model import LinearRegression
import numpy as np

# Datos: metros cuadrados → precio
X = np.array([[50], [80], [100], [120], [150]])
y = np.array([150000, 240000, 300000, 360000, 450000])

model = LinearRegression()
model.fit(X, y)

# Predecir precio de casa de 90m²
precio = model.predict([[90]])
print(f"Precio estimado: ${precio[0]:,.0f}")  # ~$270,000
```

Tareas comunes:
- **Clasificación**: spam/no-spam, sentimiento positivo/negativo
- **Regresión**: predecir precios, temperaturas, ventas

### Aprendizaje No Supervisado

El modelo encuentra patrones sin etiquetas previas:

```python
from sklearn.cluster import KMeans

# Segmentar clientes por comportamiento de compra
clientes = [[25, 50000], [30, 60000], [45, 90000], [50, 95000], [22, 30000]]

kmeans = KMeans(n_clusters=2)
kmeans.fit(clientes)

print(kmeans.labels_)  # [0, 0, 1, 1, 0] → 2 segmentos
```

Tareas comunes:
- **Clustering**: agrupar datos similares
- **Reducción de dimensionalidad**: PCA, t-SNE
- **Detección de anomalías**: fraude, fallos

### Aprendizaje por Refuerzo (RL)

El agente aprende por prueba y error, maximizando recompensas:

```python
# Pseudocódigo de Q-Learning
Q = {}  # Tabla estado → acción → valor

def choose_action(state, epsilon=0.1):
    if random() < epsilon:
        return random_action()  # Explorar
    return max(Q[state], key=Q[state].get)  # Explotar

def update_q(state, action, reward, next_state, alpha=0.1, gamma=0.99):
    old_q = Q[state][action]
    next_max = max(Q[next_state].values())
    Q[state][action] = old_q + alpha * (reward + gamma * next_max - old_q)
```

Usado en: juegos (AlphaGo), robótica, RLHF para alinear LLMs.

## Conceptos fundamentales

### Features y Labels

```python
# Features (X): características de entrada
# Labels (y): lo que queremos predecir

import pandas as pd

datos = pd.DataFrame({
    'metros': [50, 80, 100],
    'habitaciones': [1, 2, 3],
    'precio': [150000, 240000, 300000]  # Label
})

X = datos[['metros', 'habitaciones']]  # Features
y = datos['precio']                      # Labels
```

### Train/Test Split

```python
from sklearn.model_selection import train_test_split

X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42
)

# 80% para entrenar, 20% para evaluar
# Evita overfitting: evaluar con datos que el modelo NO vio
```

### Overfitting vs Underfitting

- **Overfitting**: el modelo memoriza los datos de entrenamiento, falla con datos nuevos.
- **Underfitting**: el modelo es demasiado simple, no captura los patrones.
- **Bias-Variance Tradeoff**: encontrar el equilibrio correcto.

```python
from sklearn.tree import DecisionTreeClassifier

# Underfitting: árbol muy simple
model_under = DecisionTreeClassifier(max_depth=1)

# Overfitting: árbol sin límite
model_over = DecisionTreeClassifier(max_depth=None)

# Equilibrado
model_balanced = DecisionTreeClassifier(max_depth=5)
```

## Métricas de evaluación

```python
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score

y_true = [1, 0, 1, 1, 0, 1, 0, 0]
y_pred = [1, 0, 1, 0, 0, 1, 1, 0]

print(f"Accuracy:  {accuracy_score(y_true, y_pred):.2f}")   # 0.75
print(f"Precision: {precision_score(y_true, y_pred):.2f}")  # 0.75
print(f"Recall:    {recall_score(y_true, y_pred):.2f}")     # 0.75
print(f"F1-Score:  {f1_score(y_true, y_pred):.2f}")         # 0.75
```

- **Accuracy**: porcentaje de predicciones correctas.
- **Precision**: de los que predijo positivo, cuántos son correctos.
- **Recall**: de los realmente positivos, cuántos detectó.
- **F1-Score**: media armónica de precision y recall.

## Pipeline de ML

```python
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import StandardScaler
from sklearn.svm import SVC

pipeline = Pipeline([
    ('scaler', StandardScaler()),      # 1. Normalizar datos
    ('classifier', SVC(kernel='rbf'))  # 2. Clasificar
])

pipeline.fit(X_train, y_train)
score = pipeline.score(X_test, y_test)
```

El flujo típico de un proyecto ML:
1. **Recolección de datos**
2. **Exploración y limpieza (EDA)**
3. **Feature engineering**
4. **Selección de modelo**
5. **Entrenamiento**
6. **Evaluación**
7. **Deploy y monitoreo**

## Resumen

- ML supervisado necesita datos etiquetados; no supervisado encuentra patrones sin etiquetas.
- Train/test split previene overfitting.
- Las métricas (accuracy, precision, recall, F1) evalúan el rendimiento.
- Scikit-learn es la librería estándar para ML clásico en Python.
- ML clásico es la base para entender deep learning y LLMs.
