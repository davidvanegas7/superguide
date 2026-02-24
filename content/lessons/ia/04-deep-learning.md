# Deep Learning: CNNs, RNNs y más allá

Deep Learning es la rama del ML que usa redes neuronales con múltiples capas para aprender representaciones jerárquicas. Ha revolucionado la visión por computadora, el procesamiento de lenguaje natural y la generación de contenido.

## Redes Convolucionales (CNNs)

Las CNNs son la arquitectura dominante para procesamiento de imágenes. Aplican filtros (kernels) que detectan patrones locales:

```python
import torch.nn as nn

class SimpleCNN(nn.Module):
    def __init__(self, num_classes=10):
        super().__init__()
        self.features = nn.Sequential(
            # Conv2d(canales_entrada, canales_salida, kernel_size)
            nn.Conv2d(3, 32, 3, padding=1),   # 32 filtros 3x3
            nn.BatchNorm2d(32),
            nn.ReLU(),
            nn.MaxPool2d(2),                    # Reduce tamaño 50%

            nn.Conv2d(32, 64, 3, padding=1),
            nn.BatchNorm2d(64),
            nn.ReLU(),
            nn.MaxPool2d(2),

            nn.Conv2d(64, 128, 3, padding=1),
            nn.BatchNorm2d(128),
            nn.ReLU(),
            nn.AdaptiveAvgPool2d(1),            # Global average pooling
        )
        self.classifier = nn.Linear(128, num_classes)

    def forward(self, x):
        x = self.features(x)
        x = x.view(x.size(0), -1)  # Flatten
        return self.classifier(x)
```

Arquitecturas importantes de CNNs:
- **AlexNet (2012)**: inicio del boom de deep learning
- **ResNet (2015)**: conexiones residuales, redes de 150+ capas
- **EfficientNet (2019)**: escalado eficiente
- **Vision Transformer (ViT, 2020)**: Transformers para imágenes

## Redes Recurrentes (RNNs)

Las RNNs procesan secuencias manteniendo un estado oculto. Fueron la base del NLP antes de los Transformers:

```python
class SimpleRNN(nn.Module):
    def __init__(self, vocab_size, embed_dim, hidden_dim, output_dim):
        super().__init__()
        self.embedding = nn.Embedding(vocab_size, embed_dim)
        self.rnn = nn.RNN(embed_dim, hidden_dim, batch_first=True)
        self.fc = nn.Linear(hidden_dim, output_dim)

    def forward(self, x):
        embedded = self.embedding(x)        # [batch, seq, embed]
        output, hidden = self.rnn(embedded) # output: [batch, seq, hidden]
        return self.fc(hidden.squeeze(0))   # Usamos último hidden state
```

### LSTM: Long Short-Term Memory

LSTMs resuelven el problema del gradiente desvaneciente de RNNs básicas:

```python
class SentimentLSTM(nn.Module):
    def __init__(self, vocab_size, embed_dim=128, hidden_dim=256):
        super().__init__()
        self.embedding = nn.Embedding(vocab_size, embed_dim)
        self.lstm = nn.LSTM(
            embed_dim, hidden_dim,
            num_layers=2,           # 2 capas apiladas
            bidirectional=True,     # Procesa en ambas direcciones
            dropout=0.3,
            batch_first=True
        )
        self.fc = nn.Linear(hidden_dim * 2, 1)  # *2 por bidireccional

    def forward(self, x):
        embedded = self.embedding(x)
        lstm_out, (hidden, cell) = self.lstm(embedded)
        # Concatenar último hidden de forward y backward
        hidden = torch.cat([hidden[-2], hidden[-1]], dim=1)
        return torch.sigmoid(self.fc(hidden))
```

## De RNNs a Transformers

Las RNNs tienen limitaciones importantes:
- **Procesamiento secuencial**: no se pueden paralelizar
- **Memoria a largo plazo**: pierden información en secuencias largas
- **Entrenamiento lento**: cada paso depende del anterior

Los Transformers (siguiente lección) resuelven todo esto con el mecanismo de **atención**.

## Transfer Learning

Usar un modelo pre-entrenado y adaptarlo a tu tarea específica:

```python
from torchvision import models

# Cargar ResNet pre-entrenada en ImageNet
model = models.resnet50(weights=models.ResNet50_Weights.DEFAULT)

# Congelar parámetros del backbone
for param in model.parameters():
    param.requires_grad = False

# Reemplazar la última capa para nuestra tarea (5 clases)
model.fc = nn.Linear(model.fc.in_features, 5)

# Solo se entrenan los pesos de la nueva capa
optimizer = torch.optim.Adam(model.fc.parameters(), lr=0.001)
```

Este concepto es **fundamental** para los LLMs: modelos como GPT-4 se pre-entrenan en trillones de tokens y luego se adaptan (fine-tune) para tareas específicas.

## GPUs y entrenamiento eficiente

```python
# Mover modelo y datos a GPU
device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
model = model.to(device)

# En el loop de entrenamiento
for batch_x, batch_y in dataloader:
    batch_x = batch_x.to(device)
    batch_y = batch_y.to(device)
    output = model(batch_x)

# Mixed Precision Training (más rápido, menos memoria)
from torch.amp import autocast, GradScaler
scaler = GradScaler()

with autocast(device_type='cuda'):
    output = model(input)
    loss = criterion(output, target)

scaler.scale(loss).backward()
scaler.step(optimizer)
scaler.update()
```

## Resumen

- **CNNs**: dominan visión por computadora con filtros convolucionales.
- **RNNs/LSTMs**: procesan secuencias pero son lentas y tienen memoria limitada.
- **Transfer learning**: reutiliza modelos pre-entrenados (concepto clave para LLMs).
- **GPUs**: esenciales para entrenar redes profundas.
- Los **Transformers** superaron a RNNs/LSTMs en casi todas las tareas de secuencias.
