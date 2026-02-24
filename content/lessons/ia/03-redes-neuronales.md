# Redes Neuronales

Las redes neuronales artificiales son el pilar del deep learning. Inspiradas vagamente en el cerebro humano, son funciones matemáticas que aprenden a mapear entradas a salidas ajustando millones (o miles de millones) de parámetros.

## El Perceptrón

La unidad más simple de una red neuronal:

```python
import numpy as np

class Perceptron:
    def __init__(self, n_inputs):
        self.weights = np.random.randn(n_inputs)
        self.bias = 0.0

    def forward(self, x):
        # Suma ponderada + bias → activación
        z = np.dot(x, self.weights) + self.bias
        return 1 if z > 0 else 0  # Función escalón

    def train(self, X, y, lr=0.01, epochs=100):
        for _ in range(epochs):
            for xi, yi in zip(X, y):
                pred = self.forward(xi)
                error = yi - pred
                self.weights += lr * error * xi
                self.bias += lr * error
```

Un solo perceptrón puede resolver problemas linealmente separables (AND, OR) pero NO puede resolver XOR. Para eso necesitamos redes multicapa.

## Red Neuronal Multicapa (MLP)

```python
import torch
import torch.nn as nn

class MLP(nn.Module):
    def __init__(self, input_size, hidden_size, output_size):
        super().__init__()
        self.network = nn.Sequential(
            nn.Linear(input_size, hidden_size),    # Capa oculta 1
            nn.ReLU(),                              # Activación
            nn.Linear(hidden_size, hidden_size),   # Capa oculta 2
            nn.ReLU(),
            nn.Dropout(0.2),                        # Regularización
            nn.Linear(hidden_size, output_size),   # Capa de salida
        )

    def forward(self, x):
        return self.network(x)

# Crear red con 784 entradas, 256 neuronas ocultas, 10 salidas
model = MLP(784, 256, 10)
print(f"Parámetros: {sum(p.numel() for p in model.parameters()):,}")
```

## Funciones de Activación

Las funciones de activación introducen no-linealidad, permitiendo que la red aprenda patrones complejos:

```python
import torch.nn.functional as F

# ReLU: max(0, x) — La más usada en capas ocultas
relu = F.relu(torch.tensor([-2.0, -1.0, 0.0, 1.0, 2.0]))
# tensor([0., 0., 0., 1., 2.])

# Sigmoid: 1/(1+e^-x) — Usada para probabilidades (0-1)
sigmoid = torch.sigmoid(torch.tensor([-2.0, 0.0, 2.0]))
# tensor([0.1192, 0.5000, 0.8808])

# Softmax: convierte logits a probabilidades que suman 1
logits = torch.tensor([2.0, 1.0, 0.1])
probs = F.softmax(logits, dim=0)
# tensor([0.6590, 0.2424, 0.0986]) — suman 1.0

# GELU: usada en Transformers (GPT, BERT)
gelu = F.gelu(torch.tensor([-1.0, 0.0, 1.0]))
```

| Función | Uso principal | Rango |
|---------|--------------|-------|
| ReLU | Capas ocultas de CNNs y MLPs | [0, ∞) |
| Sigmoid | Clasificación binaria | (0, 1) |
| Softmax | Clasificación multiclase | (0, 1), suma = 1 |
| GELU | Transformers | (-0.17, ∞) |
| Tanh | RNNs, normalización | (-1, 1) |

## Backpropagation

El algoritmo que permite a las redes neuronales aprender. Calcula el gradiente del error respecto a cada peso usando la regla de la cadena:

```python
# PyTorch maneja backpropagation automáticamente
model = MLP(784, 256, 10)
criterion = nn.CrossEntropyLoss()
optimizer = torch.optim.Adam(model.parameters(), lr=0.001)

# Ciclo de entrenamiento
for epoch in range(10):
    for batch_x, batch_y in dataloader:
        # Forward pass
        output = model(batch_x)
        loss = criterion(output, batch_y)

        # Backward pass (backpropagation)
        optimizer.zero_grad()  # Limpiar gradientes
        loss.backward()        # Calcular gradientes
        optimizer.step()       # Actualizar pesos

    print(f"Epoch {epoch}, Loss: {loss.item():.4f}")
```

El proceso:
1. **Forward pass**: los datos fluyen de entrada a salida, generando una predicción.
2. **Loss**: se calcula el error entre predicción y valor real.
3. **Backward pass**: se propagan los gradientes hacia atrás.
4. **Update**: se ajustan los pesos en la dirección que reduce el error.

## Optimizadores

```python
# SGD: simple pero puede ser lento
optimizer = torch.optim.SGD(model.parameters(), lr=0.01, momentum=0.9)

# Adam: el más popular, adapta el learning rate por parámetro
optimizer = torch.optim.Adam(model.parameters(), lr=0.001)

# AdamW: Adam con weight decay correcto (usado en LLMs)
optimizer = torch.optim.AdamW(model.parameters(), lr=3e-4, weight_decay=0.01)
```

## Loss Functions comunes

```python
# Clasificación binaria
loss_fn = nn.BCEWithLogitsLoss()

# Clasificación multiclase (la más común en LLMs para next-token prediction)
loss_fn = nn.CrossEntropyLoss()

# Regresión
loss_fn = nn.MSELoss()
```

## Ejemplo completo: clasificador de dígitos

```python
import torch
import torch.nn as nn
from torchvision import datasets, transforms
from torch.utils.data import DataLoader

# Datos
transform = transforms.Compose([
    transforms.ToTensor(),
    transforms.Normalize((0.1307,), (0.3081,))
])

train_data = datasets.MNIST('.', train=True, download=True, transform=transform)
train_loader = DataLoader(train_data, batch_size=64, shuffle=True)

# Modelo
model = nn.Sequential(
    nn.Flatten(),
    nn.Linear(784, 128),
    nn.ReLU(),
    nn.Linear(128, 10)
)

# Entrenamiento
optimizer = torch.optim.Adam(model.parameters())
criterion = nn.CrossEntropyLoss()

for epoch in range(5):
    for images, labels in train_loader:
        loss = criterion(model(images), labels)
        optimizer.zero_grad()
        loss.backward()
        optimizer.step()
    print(f"Epoch {epoch+1} completado")
```

## Resumen

- Las redes neuronales son funciones con parámetros ajustables (pesos y biases).
- Las funciones de activación (ReLU, GELU) introducen no-linealidad.
- Backpropagation calcula gradientes; el optimizador actualiza pesos.
- PyTorch es el framework dominante para deep learning e investigación.
- Estos conceptos son la base de Transformers y LLMs.
