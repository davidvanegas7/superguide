# Preguntas de Entrevista: IA y LLMs

Las preguntas de entrevista sobre IA abarcan desde fundamentos de machine learning hasta conocimiento práctico de LLMs y su despliegue. Esta lección cubre las preguntas más frecuentes con respuestas claras.

## Fundamentos de Machine Learning

### 1. ¿Cuál es la diferencia entre supervised y unsupervised learning?

```python
# Supervised: datos con etiquetas (input → output conocido)
# Ejemplo: clasificar emails como spam/no-spam
X_train = ["Gana dinero fácil", "Reunión mañana a las 10"]
y_train = ["spam", "no-spam"]  # ← etiquetas

# Unsupervised: datos sin etiquetas, buscar patrones
# Ejemplo: agrupar clientes similares (clustering)
customers = [[25, 50000], [45, 120000], [22, 35000]]
# No hay etiquetas, el modelo descubre grupos
```

### 2. ¿Qué es overfitting y cómo se previene?

```python
# Overfitting: el modelo memoriza los datos de entrenamiento
# pero falla con datos nuevos

# Señales: accuracy de training alta, accuracy de test baja

# Prevención:
# 1. Más datos de entrenamiento
# 2. Regularización (L1, L2, Dropout)
# 3. Cross-validation
# 4. Early stopping
# 5. Data augmentation
# 6. Modelos más simples

# Ejemplo con dropout en PyTorch
import torch.nn as nn

class Model(nn.Module):
    def __init__(self):
        super().__init__()
        self.layers = nn.Sequential(
            nn.Linear(100, 64),
            nn.ReLU(),
            nn.Dropout(0.3),      # ← 30% de neuronas se apagan
            nn.Linear(64, 10),
        )
```

### 3. ¿Qué es gradient descent y cómo funciona?

```
Gradient Descent:
- Objetivo: minimizar la función de pérdida (loss)
- Método: mover los pesos en la dirección opuesta al gradiente
- Learning rate: tamaño del paso (muy grande = diverge, muy pequeño = lento)

w_nuevo = w_actual - learning_rate * gradiente

Variantes:
- SGD: un ejemplo a la vez (ruidoso pero rápido)
- Mini-batch: N ejemplos (balance entre SGD y batch)
- Adam: learning rate adaptativo por parámetro (el más usado)
```

## Deep Learning

### 4. Explica la arquitectura Transformer

```
Transformer (Vaswani et al., 2017):
- Reemplazó RNNs/LSTMs para procesamiento de secuencias
- Mecanismo clave: Self-Attention

Self-Attention:
1. Cada token genera Query (Q), Key (K), Value (V)
2. Attention(Q,K,V) = softmax(QK^T / √d_k) · V
3. Cada token "atiende" a todos los demás en paralelo

Multi-Head Attention:
- Múltiples cabezas de atención en paralelo
- Cada una aprende patrones diferentes
- ej: una cabeza aprende sintaxis, otra semántica

Encoder vs Decoder:
- Encoder: BERT, analizar texto (bidireccional)
- Decoder: GPT, generar texto (autoregresivo)
- Encoder-Decoder: T5, traducción
```

### 5. ¿Qué es el attention mechanism y por qué es importante?

```python
import torch
import torch.nn.functional as F

def scaled_dot_product_attention(Q, K, V):
    """
    Q: queries [batch, heads, seq_len, d_k]
    K: keys    [batch, heads, seq_len, d_k]
    V: values  [batch, heads, seq_len, d_v]
    """
    d_k = Q.size(-1)
    scores = torch.matmul(Q, K.transpose(-2, -1)) / (d_k ** 0.5)
    weights = F.softmax(scores, dim=-1)
    return torch.matmul(weights, V)

# Importancia:
# - Permite procesamiento paralelo (vs secuencial en RNNs)
# - Captura dependencias a larga distancia
# - Base de TODOS los LLMs modernos
```

## LLMs

### 6. ¿Cómo se entrenan los LLMs?

```
Fase 1: Pre-training (semanas/meses, millones de $)
- Tarea: predecir el siguiente token
- Datos: internet, libros, código (~15 trillones de tokens)
- Resultado: modelo base con conocimiento general

Fase 2: Supervised Fine-Tuning (SFT)
- Datos: pares instrucción→respuesta curados por humanos
- Resultado: modelo que sigue instrucciones

Fase 3: Alineación (RLHF/DPO)
- RLHF: humanos rankean respuestas → entrenar reward model → PPO
- DPO: directamente en pares preferido/no-preferido (más simple)
- Resultado: modelo alineado con preferencias humanas
```

### 7. ¿Qué es RAG y cuándo lo usarías?

```python
# RAG = Retrieval Augmented Generation
# Combina búsqueda de documentos + generación con LLM

# Cuándo usar RAG:
# ✅ Información que se actualiza frecuentemente
# ✅ Datos privados/corporativos
# ✅ Necesitas citar fuentes
# ✅ Reducir alucinaciones

# Cuándo NO usar RAG:
# ❌ Conocimiento general que el LLM ya sabe
# ❌ Tareas creativas sin base factual
# ❌ Cuando el estilo/formato es más importante que los hechos

# Pipeline RAG básico:
def rag_pipeline(query, vector_db, llm):
    # 1. Recuperar documentos relevantes
    docs = vector_db.similarity_search(query, k=5)
    context = "\n".join(doc.page_content for doc in docs)

    # 2. Generar respuesta con contexto
    prompt = f"""Basándote SOLO en el siguiente contexto, responde la pregunta.
    Contexto: {context}
    Pregunta: {query}"""

    return llm.generate(prompt)
```

### 8. ¿Cuál es la diferencia entre fine-tuning y RAG?

```
Fine-tuning:
- Modifica los pesos del modelo
- Ideal para: estilo, formato, tareas específicas
- No ideal para: hechos actualizados (se "congela")
- Costo: medio-alto (GPU + datos curados)
- Ejemplo: adaptar modelo a jerga legal

RAG:
- No modifica el modelo, añade contexto externo
- Ideal para: información actualizada, datos privados
- No ideal para: cambiar el comportamiento del modelo
- Costo: bajo (solo vectorizar documentos)
- Ejemplo: chatbot sobre documentación que cambia semanalmente

Combinación (lo mejor):
- Fine-tune para estilo/formato + RAG para hechos actualizados
```

### 9. ¿Qué son los embeddings y para qué se utilizan?

```
Embeddings = representación vectorial de datos (texto, imágenes) que
captura significado semántico.

Textos similares → vectores cercanos en el espacio

Usos:
1. Búsqueda semántica (RAG)
2. Clustering de documentos
3. Detección de duplicados
4. Recomendaciones
5. Clasificación de texto

Modelo popular: text-embedding-3-small (OpenAI), 1536 dims
Open source: all-MiniLM-L6-v2 (384 dims)
```

### 10. ¿Qué es prompt engineering?

```python
# Técnicas principales:

# 1. Zero-shot: sin ejemplos
"Clasifica este email como spam o no-spam: ..."

# 2. Few-shot: con ejemplos
"""Clasifica:
Email: "Gana $1000 ahora" → spam
Email: "Reunión a las 3pm" → no-spam
Email: "Oferta exclusiva!!!" → """

# 3. Chain-of-Thought (CoT): razonamiento paso a paso
"Piensa paso a paso antes de responder..."

# 4. System prompts: definir rol y comportamiento
{"role": "system", "content": "Eres un experto en derecho..."}
```

## Preguntas prácticas

### 11. ¿Cómo reducirías la latencia de un LLM en producción?

```
1. Cuantización: FP16 → INT4 (3-4x más rápido)
2. KV-Cache: evitar recalcular tokens previos
3. Speculative decoding: modelo draft + verificación
4. Batching: procesar múltiples requests juntos
5. Streaming: enviar tokens mientras se generan
6. Modelo más pequeño: usar 8B en vez de 70B si la calidad es suficiente
7. Caching: cachear respuestas frecuentes
8. Prompt optimization: prompts más cortos = menos tokens
```

### 12. ¿Cómo manejas las alucinaciones de un LLM?

```python
# Alucinaciones: el modelo genera información incorrecta con confianza

# Estrategias:
# 1. RAG: fundamentar respuestas en documentos reales
# 2. Temperature baja (0.0-0.3): menos creatividad, más precisión
# 3. Instrucciones explícitas:
prompt = """Responde basándote SOLO en el contexto proporcionado.
Si no tienes suficiente información, di 'No tengo información suficiente'.
NO inventes datos."""

# 4. Verificación automática:
def verify_response(response, sources):
    """Verificar que los claims estén en las fuentes."""
    verification_prompt = f"""Verifica si la respuesta se basa en las fuentes.
    Respuesta: {response}
    Fuentes: {sources}
    ¿Hay información inventada? Responde JSON: {{"hallucination": bool}}"""
    return llm.generate(verification_prompt)

# 5. Citas y fuentes
prompt = "Responde citando el número de fuente [1], [2], etc."
```

### 13. ¿Qué es un agente de IA?

```
Un agente = LLM + herramientas + loop de razonamiento

Componentes:
1. LLM como "cerebro" (razona y planifica)
2. Herramientas (APIs, bases de datos, búsqueda web)
3. Memoria (historial de conversación, resultados previos)
4. Loop: Pensar → Actuar → Observar → Repetir

Ejemplo: "Reserva un vuelo barato a Madrid"
- Pensar: necesito buscar vuelos
- Actuar: search_flights("Madrid", "2025-06-15")
- Observar: hay vuelos desde $200
- Pensar: filtrar los más baratos
- Actuar: filter_results(max_price=300)
- Observar: 3 opciones disponibles
- Actuar: book_flight(flight_id="...")
```

### 14. Diseña un sistema de chatbot para documentación técnica

```
Arquitectura:
1. Ingesta: parsear docs → chunks → embeddings → vector DB
2. Retrieval: query → embedding → búsqueda semántica → top-k docs
3. Generation: contexto + pregunta → LLM → respuesta con citas
4. Evaluación: feedback del usuario + métricas automáticas

Stack recomendado:
- Vector DB: ChromaDB (simple) o Pinecone (escalable)
- Embeddings: text-embedding-3-small o BGE-large
- LLM: GPT-4o-mini (costo) o Claude 3.5 Sonnet (calidad)
- Framework: LangChain o LlamaIndex
- Frontend: React + streaming

Consideraciones:
- Chunking: ~500 tokens con 50 de overlap
- Re-ranking: usar Cohere rerank para mejorar retrieval
- Cache: Redis para queries frecuentes
- Feedback loop: thumbs up/down → mejorar prompts y retrieval
```

## Resumen

- Las entrevistas de IA cubren ML fundamentals, deep learning y LLMs prácticos.
- Diferencia clave: supervised vs unsupervised, fine-tuning vs RAG.
- La arquitectura Transformer y el attention mechanism son preguntas obligatorias.
- Las preguntas de diseño de sistemas (chatbot, RAG) son las más valoradas.
- Conocer trade-offs (costo, latencia, calidad) demuestra experiencia real.
- Mantente actualizado: el campo de LLMs cambia cada pocos meses.
