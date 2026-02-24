# Introducción a la Inteligencia Artificial

La Inteligencia Artificial (IA) es el campo de la informática dedicado a crear sistemas capaces de realizar tareas que normalmente requieren inteligencia humana. Desde asistentes virtuales hasta vehículos autónomos, la IA ha pasado de ser ciencia ficción a transformar cada industria.

## ¿Qué es la IA?

La IA abarca cualquier sistema que percibe su entorno y toma acciones para maximizar sus posibilidades de éxito en algún objetivo. Se clasifica en tres niveles:

- **IA Estrecha (ANI)**: especializada en una tarea concreta. Es la única que existe hoy. Ejemplos: GPT-4o, reconocimiento facial, AlphaGo.
- **IA General (AGI)**: capaz de realizar cualquier tarea intelectual humana. Aún no se ha logrado, aunque es el objetivo de empresas como OpenAI y Anthropic.
- **IA Superinteligente (ASI)**: superaría la inteligencia humana en todos los aspectos. Es teórica.

## Historia breve de la IA

| Época | Hito |
|-------|------|
| 1950 | Alan Turing propone el Test de Turing |
| 1956 | Conferencia de Dartmouth: nace el término "IA" |
| 1966-1974 | Primer "invierno de IA" |
| 1997 | Deep Blue vence a Kasparov en ajedrez |
| 2012 | AlexNet revoluciona visión por computadora (deep learning) |
| 2017 | Google publica "Attention Is All You Need" (Transformers) |
| 2020 | GPT-3 demuestra capacidades emergentes |
| 2022 | ChatGPT populariza los LLMs masivamente |
| 2023 | GPT-4, Claude 2, Llama 2: la era multimodal |
| 2024 | Modelos de razonamiento (o1, DeepSeek-R1), agentes de IA |
| 2025 | Claude 3.5, Gemini 2.0, agentes autónomos, MCP, IA en código |

## Ramas principales de la IA

```
Inteligencia Artificial
├── Machine Learning (ML)
│   ├── Supervisado
│   ├── No Supervisado
│   └── Por Refuerzo
├── Deep Learning (DL)
│   ├── CNNs (Visión)
│   ├── RNNs/LSTMs (Secuencias)
│   └── Transformers (LLMs)
├── Procesamiento de Lenguaje Natural (NLP)
├── Visión por Computadora
├── Robótica
└── Sistemas Expertos
```

## ML vs DL vs IA Tradicional

```python
# IA Tradicional: Reglas escritas manualmente
def clasificar_spam_tradicional(email):
    palabras_spam = ["gratis", "oferta", "premio"]
    return any(p in email.lower() for p in palabras_spam)

# Machine Learning: Aprende patrones de datos
from sklearn.naive_bayes import MultinomialNB
model = MultinomialNB()
model.fit(X_train, y_train)  # Aprende de ejemplos
prediction = model.predict(X_test)

# Deep Learning: Aprende representaciones jerárquicas
import torch.nn as nn
class SpamClassifier(nn.Module):
    def __init__(self):
        super().__init__()
        self.embedding = nn.Embedding(vocab_size, 128)
        self.lstm = nn.LSTM(128, 64, batch_first=True)
        self.fc = nn.Linear(64, 2)
```

La diferencia fundamental:
- **IA Tradicional**: el programador define las reglas.
- **Machine Learning**: el modelo aprende reglas a partir de datos.
- **Deep Learning**: el modelo aprende representaciones a partir de datos crudos, sin feature engineering manual.

## El ecosistema actual de IA (2025-2026)

El panorama actual está dominado por los **Large Language Models (LLMs)**:

- **OpenAI**: GPT-4o, o1, o3 (modelos de razonamiento)
- **Anthropic**: Claude 3.5 Sonnet, Claude Opus
- **Google**: Gemini 2.0 Pro/Flash
- **Meta**: Llama 3.1, Llama 4
- **Mistral**: Mistral Large, Mixtral
- **DeepSeek**: DeepSeek-V3, DeepSeek-R1

Herramientas clave:
- **Python** como lenguaje dominante
- **PyTorch** como framework de deep learning principal
- **Hugging Face** como hub de modelos y datasets
- **LangChain/LlamaIndex** para aplicaciones con LLMs
- **Vector databases** (Pinecone, Weaviate, ChromaDB) para RAG

## ¿Por qué aprender IA ahora?

1. **Demanda laboral**: los roles de IA/ML son los mejor pagados en tech.
2. **Transformación de industrias**: salud, finanzas, educación, legal.
3. **Herramientas accesibles**: APIs de LLMs, modelos open source, GPUs en la nube.
4. **Impacto personal**: potencia tu productividad 10x como desarrollador.

## Resumen

- La IA es un campo amplio que incluye ML, DL y más.
- Los LLMs (como GPT-4o y Claude) son la revolución actual.
- Python es el lenguaje dominante para IA.
- El ecosistema incluye frameworks (PyTorch), hubs (Hugging Face) y APIs (OpenAI, Anthropic).
- No necesitas un PhD para empezar: las APIs de LLMs democratizaron el acceso.
