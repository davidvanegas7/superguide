# Embeddings y Búsqueda Semántica

Los embeddings son representaciones numéricas de texto (o imágenes, audio) que capturan significado semántico. Son fundamentales para RAG, búsqueda, recomendaciones y clasificación.

## ¿Qué son los embeddings?

Un embedding convierte texto en un vector de números donde textos similares están cerca en el espacio vectorial:

```python
from openai import OpenAI

client = OpenAI()

def get_embedding(text, model="text-embedding-3-small"):
    response = client.embeddings.create(input=text, model=model)
    return response.data[0].embedding

# Obtener embeddings
vec_python = get_embedding("Python es un lenguaje de programación")
vec_java   = get_embedding("Java es un lenguaje de programación")
vec_gato   = get_embedding("Los gatos son mascotas populares")

print(f"Dimensiones: {len(vec_python)}")  # 1536
```

## Similitud Coseno

La métrica estándar para comparar embeddings:

```python
import numpy as np

def cosine_similarity(a, b):
    """Calcula similitud coseno entre dos vectores."""
    a, b = np.array(a), np.array(b)
    return np.dot(a, b) / (np.linalg.norm(a) * np.linalg.norm(b))

# Python vs Java: alta similitud (ambos son lenguajes)
sim1 = cosine_similarity(vec_python, vec_java)
print(f"Python vs Java: {sim1:.4f}")    # ~0.85

# Python vs Gato: baja similitud (temas diferentes)
sim2 = cosine_similarity(vec_python, vec_gato)
print(f"Python vs Gato: {sim2:.4f}")    # ~0.15

# Rango: -1 (opuestos) a 1 (idénticos)
```

## Modelos de embeddings

| Modelo | Dims | Contexto | Proveedor |
|--------|------|---------|-----------|
| text-embedding-3-small | 1536 | 8K tokens | OpenAI |
| text-embedding-3-large | 3072 | 8K tokens | OpenAI |
| voyage-3 | 1024 | 32K tokens | Voyage AI |
| all-MiniLM-L6-v2 | 384 | 512 tokens | Open source |
| BGE-large-en | 1024 | 512 tokens | Open source |
| nomic-embed-text | 768 | 8K tokens | Open source |
| E5-Mistral-7B | 4096 | 32K tokens | Open source |

```python
# Modelo open source local (sin API)
from sentence_transformers import SentenceTransformer

model = SentenceTransformer('all-MiniLM-L6-v2')

sentences = [
    "Machine learning es parte de la inteligencia artificial",
    "El aprendizaje automático es una rama de la IA",
    "Me gusta cocinar pasta con tomate",
]

embeddings = model.encode(sentences)
print(f"Shape: {embeddings.shape}")  # (3, 384)

# Similitud: las dos primeras son semánticamente iguales
from sklearn.metrics.pairwise import cosine_similarity as cos_sim
sims = cos_sim(embeddings)
print(f"ML vs IA (paráfrasis): {sims[0][1]:.3f}")  # ~0.90
print(f"ML vs Pasta: {sims[0][2]:.3f}")             # ~0.10
```

## Búsqueda semántica

A diferencia de la búsqueda por keywords, la búsqueda semántica entiende el significado:

```python
import chromadb

# Crear colección
client = chromadb.PersistentClient(path="./search_db")
collection = client.create_collection("knowledge_base")

# Indexar documentos
documents = [
    "Python es ideal para ciencia de datos y machine learning",
    "JavaScript domina el desarrollo web frontend y backend",
    "Rust ofrece seguridad de memoria sin garbage collector",
    "Go es excelente para microservicios y concurrencia",
    "Elixir maneja millones de conexiones simultáneas",
]

collection.add(
    documents=documents,
    ids=[f"doc_{i}" for i in range(len(documents))]
)

# Búsqueda semántica
results = collection.query(
    query_texts=["¿Qué lenguaje es bueno para análisis de datos?"],
    n_results=3
)

for doc, distance in zip(results['documents'][0], results['distances'][0]):
    print(f"[{1-distance:.3f}] {doc}")
# [0.852] Python es ideal para ciencia de datos y machine learning
# [0.421] JavaScript domina el desarrollo web...
# [0.380] Go es excelente para microservicios...
```

## Clustering con embeddings

```python
from sklearn.cluster import KMeans
import numpy as np

# Agrupar documentos similares automáticamente
texts = [
    "Cómo entrenar un modelo de ML", "Tutorial de redes neuronales",
    "Receta de paella valenciana", "Mejores restaurantes de Madrid",
    "Guía de inversión en bolsa", "Cómo diversificar tu portafolio",
]

embeddings = model.encode(texts)

# Clustering con K-Means
kmeans = KMeans(n_clusters=3, random_state=42)
labels = kmeans.fit_predict(embeddings)

for text, label in zip(texts, labels):
    print(f"Cluster {label}: {text}")
# Cluster 0: Cómo entrenar un modelo de ML
# Cluster 0: Tutorial de redes neuronales
# Cluster 1: Receta de paella valenciana
# Cluster 1: Mejores restaurantes de Madrid
# Cluster 2: Guía de inversión en bolsa
# Cluster 2: Cómo diversificar tu portafolio
```

## Visualización de embeddings

```python
from sklearn.manifold import TSNE
import matplotlib.pyplot as plt

# Reducir de N dimensiones a 2 con t-SNE
tsne = TSNE(n_components=2, random_state=42, perplexity=5)
embeddings_2d = tsne.fit_transform(np.array(embeddings))

plt.figure(figsize=(10, 8))
colors = ['red', 'blue', 'green']
for i, (point, text) in enumerate(zip(embeddings_2d, texts)):
    color = colors[labels[i]]
    plt.scatter(point[0], point[1], c=color, s=100)
    plt.annotate(text[:30], (point[0], point[1]), fontsize=8)

plt.title("Embeddings visualizados con t-SNE")
plt.savefig("embeddings_viz.png")
```

## Embeddings multimodales

```python
# CLIP: embeddings que conectan texto e imágenes
from transformers import CLIPModel, CLIPProcessor
from PIL import Image

model = CLIPModel.from_pretrained("openai/clip-vit-base-patch32")
processor = CLIPProcessor.from_pretrained("openai/clip-vit-base-patch32")

# Embeddings de texto e imagen en el MISMO espacio vectorial
texts = ["un gato naranja", "un coche deportivo rojo"]
image = Image.open("cat.jpg")

inputs = processor(text=texts, images=image, return_tensors="pt", padding=True)
outputs = model(**inputs)

# Similitud texto-imagen
text_embeds = outputs.text_embeds   # [2, 512]
image_embeds = outputs.image_embeds  # [1, 512]

# ¿El gato naranja es más similar a la imagen que el coche?
sims = cosine_similarity(image_embeds.detach(), text_embeds.detach())
print(sims)  # [[0.85, 0.12]] → La imagen se parece más a "gato naranja"
```

## Resumen

- Los embeddings convierten texto en vectores numéricos que capturan significado.
- Similitud coseno mide cuán similares son dos textos semánticamente.
- Modelos: OpenAI (text-embedding-3), Sentence Transformers (open source).
- Vector databases (ChromaDB, Pinecone) permiten búsqueda eficiente.
- Los embeddings son la base de RAG, búsqueda semántica y recomendaciones.
- CLIP conecta embeddings de texto e imagen en el mismo espacio.
