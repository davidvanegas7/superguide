# Procesamiento de Lenguaje Natural (NLP)

NLP es la rama de la IA que permite a las máquinas entender, interpretar y generar lenguaje humano. Es la base de los LLMs, chatbots, traductores y motores de búsqueda.

## Tokenización

La tokenización es el primer paso: convertir texto en unidades procesables.

```python
# Tokenización por palabras (simple)
text = "Los LLMs transformaron la IA en 2023"
tokens_words = text.split()
# ['Los', 'LLMs', 'transformaron', 'la', 'IA', 'en', '2023']

# Tokenización con Hugging Face (subword - BPE)
from transformers import AutoTokenizer

tokenizer = AutoTokenizer.from_pretrained("gpt2")
tokens = tokenizer.encode("Los LLMs transformaron la IA")
print(tokenizer.convert_ids_to_tokens(tokens))
# ['Los', ' LL', 'Ms', ' transform', 'aron', ' la', ' IA']

# Los LLMs modernos usan tokenización subword (BPE, SentencePiece)
# Ventajas: vocabulario finito, maneja palabras desconocidas
```

### Tipos de tokenización

| Método | Descripción | Usado por |
|--------|------------|-----------|
| **BPE** (Byte-Pair Encoding) | Fusiona pares de caracteres más frecuentes | GPT-2, GPT-4, Llama |
| **WordPiece** | Similar a BPE, maximiza probabilidad | BERT |
| **SentencePiece** | Tokenización independiente del idioma | T5, Llama |
| **Tiktoken** | BPE optimizado por OpenAI | GPT-3.5, GPT-4 |

```python
# Contar tokens con tiktoken (usado por OpenAI)
import tiktoken

enc = tiktoken.encoding_for_model("gpt-4")
tokens = enc.encode("Hola, ¿cómo estás?")
print(f"Tokens: {len(tokens)}")  # ~7 tokens
print(tokens)  # [39, 10274, 11, 38351, ...]
```

## Word Embeddings

Los embeddings representan palabras como vectores numéricos donde palabras similares están cerca en el espacio vectorial:

```python
# Word2Vec conceptual
# "rey" - "hombre" + "mujer" ≈ "reina"
# "Madrid" - "España" + "Francia" ≈ "París"

from gensim.models import Word2Vec

# Entrenar Word2Vec
sentences = [["el", "gato", "come", "pescado"],
             ["el", "perro", "come", "carne"]]
model = Word2Vec(sentences, vector_size=100, window=5, min_count=1)

# Obtener vector de una palabra
vector = model.wv['gato']  # Vector de 100 dimensiones

# Similitud entre palabras
similarity = model.wv.similarity('gato', 'perro')
```

### Embeddings modernos

```python
from sentence_transformers import SentenceTransformer

# Modelo de embeddings de oraciones completas
model = SentenceTransformer('all-MiniLM-L6-v2')

sentences = [
    "Python es un lenguaje de programación",
    "JavaScript se usa para la web",
    "Me gusta el helado de vainilla"
]

embeddings = model.encode(sentences)
# Cada oración → vector de 384 dimensiones

# Calcular similitud coseno
from sklearn.metrics.pairwise import cosine_similarity
sim = cosine_similarity([embeddings[0]], [embeddings[1]])
print(f"Similitud Python-JS: {sim[0][0]:.3f}")  # ~0.5 (relacionados)

sim = cosine_similarity([embeddings[0]], [embeddings[2]])
print(f"Similitud Python-Helado: {sim[0][0]:.3f}")  # ~0.1 (no relacionados)
```

## Tareas clásicas de NLP

### Clasificación de texto

```python
from transformers import pipeline

classifier = pipeline("sentiment-analysis")
result = classifier("This movie was absolutely fantastic!")
# [{'label': 'POSITIVE', 'score': 0.9998}]
```

### Named Entity Recognition (NER)

```python
ner = pipeline("ner", grouped_entities=True)
result = ner("Elon Musk fundó SpaceX en California en 2002")
# [{'entity_group': 'PER', 'word': 'Elon Musk'},
#  {'entity_group': 'ORG', 'word': 'SpaceX'},
#  {'entity_group': 'LOC', 'word': 'California'}]
```

### Traducción

```python
translator = pipeline("translation", model="Helsinki-NLP/opus-mt-es-en")
result = translator("Hola, ¿cómo estás?")
# [{'translation_text': 'Hello, how are you?'}]
```

## De NLP clásico a LLMs

La evolución del NLP:

1. **Reglas manuales** (1960s-1990s): gramáticas formales, diccionarios.
2. **ML estadístico** (1990s-2010s): n-grams, Naive Bayes, SVM.
3. **Word embeddings** (2013): Word2Vec, GloVe.
4. **RNNs/LSTMs** (2014-2017): seq2seq, attention.
5. **Transformers** (2017+): BERT, GPT, revolucionaron el campo.
6. **LLMs** (2020+): GPT-3, ChatGPT, Claude, Gemini.

El cambio clave fue pasar de entrenar modelos pequeños por tarea a **un solo modelo grande** que puede realizar múltiples tareas con instrucciones en lenguaje natural (prompts).

## Text Preprocessing

```python
import re

def preprocess_text(text):
    """Pipeline básico de preprocesamiento."""
    text = text.lower()                           # Minúsculas
    text = re.sub(r'[^\w\s]', '', text)           # Quitar puntuación
    text = re.sub(r'\d+', '<NUM>', text)           # Normalizar números
    tokens = text.split()                          # Tokenizar
    stopwords = {'el', 'la', 'de', 'en', 'y', 'a'}
    tokens = [t for t in tokens if t not in stopwords]
    return tokens

result = preprocess_text("Los 3 LLMs más usados en 2024")
# ['los', '<NUM>', 'llms', 'más', 'usados', '<NUM>']
```

## Resumen

- La tokenización convierte texto en unidades procesables (BPE es el estándar para LLMs).
- Los embeddings mapean texto a vectores numéricos en un espacio semántico.
- Hugging Face Transformers democratizó el acceso a modelos NLP pre-entrenados.
- El NLP moderno converge en LLMs: un modelo, múltiples tareas.
- Entender tokenización y embeddings es esencial para trabajar con LLMs.
