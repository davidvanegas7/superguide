# RAG: Retrieval Augmented Generation

RAG es el patrón más importante para aplicaciones con LLMs en producción. Permite a los modelos acceder a información actualizada y específica que no está en sus datos de entrenamiento.

## El problema que resuelve RAG

Los LLMs tienen limitaciones fundamentales:
- **Knowledge cutoff**: no conocen información posterior a su entrenamiento.
- **Alucinaciones**: inventan datos con confianza cuando no saben.
- **Sin datos privados**: no conocen documentación interna de tu empresa.

RAG resuelve esto inyectando información relevante en el prompt.

## Arquitectura de RAG

```
┌─────────────────────────────────────────────────────────┐
│                    Pipeline RAG                          │
│                                                          │
│  1. INDEXACIÓN (offline)                                 │
│     Documentos → Chunks → Embeddings → Vector DB        │
│                                                          │
│  2. RETRIEVAL (en runtime)                               │
│     Query → Embedding → Búsqueda similar → Top-K docs   │
│                                                          │
│  3. GENERATION                                           │
│     Prompt = Query + Documentos relevantes → LLM → Resp │
└─────────────────────────────────────────────────────────┘
```

## Paso 1: Chunking

Dividir documentos en fragmentos procesables:

```python
from langchain.text_splitter import RecursiveCharacterTextSplitter

text_splitter = RecursiveCharacterTextSplitter(
    chunk_size=1000,          # Caracteres por chunk
    chunk_overlap=200,         # Solapamiento entre chunks
    separators=["\n\n", "\n", ". ", " ", ""]
)

documents = [
    "Tu documentación larga aquí...",
    "Otro documento largo..."
]

chunks = text_splitter.create_documents(documents)
print(f"Documentos: {len(documents)} → Chunks: {len(chunks)}")
```

### Estrategias de chunking

| Estrategia | Cuándo usar |
|-----------|------------|
| **Por caracteres fijos** | Documentos homogéneos |
| **Recursivo** | General, respeta estructura (párrafos > oraciones) |
| **Por oraciones** | Textos narrativos |
| **Semántico** | Agrupa por significado, más preciso pero lento |
| **Por documentos** | Docs cortos (emails, tickets) |

## Paso 2: Embeddings y Vector Store

```python
from openai import OpenAI
import chromadb

client = OpenAI()

# Generar embeddings
def get_embedding(text, model="text-embedding-3-small"):
    response = client.embeddings.create(input=text, model=model)
    return response.data[0].embedding

# Crear vector store con ChromaDB
chroma_client = chromadb.PersistentClient(path="./chroma_db")
collection = chroma_client.create_collection(
    name="documentacion",
    metadata={"hnsw:space": "cosine"}  # Similitud coseno
)

# Indexar chunks
for i, chunk in enumerate(chunks):
    embedding = get_embedding(chunk.page_content)
    collection.add(
        ids=[f"chunk_{i}"],
        embeddings=[embedding],
        documents=[chunk.page_content],
        metadatas=[{"source": "docs.md", "chunk_index": i}]
    )

print(f"Indexados: {collection.count()} chunks")
```

### Vector Databases populares

| Base de datos | Tipo | Fortaleza |
|--------------|------|-----------|
| **ChromaDB** | Embebida | Simple, ideal para prototipos |
| **Pinecone** | Cloud | Serverless, escalable |
| **Weaviate** | Self-hosted/Cloud | Híbrido (vector + keyword) |
| **Qdrant** | Self-hosted/Cloud | Alto rendimiento, filtros |
| **pgvector** | Extensión PostgreSQL | Si ya usas Postgres |
| **FAISS** | Librería (Meta) | Muy rápido, sin servidor |

## Paso 3: Retrieval y Generation

```python
def rag_query(question, collection, n_results=5):
    # 1. Buscar documentos relevantes
    query_embedding = get_embedding(question)
    results = collection.query(
        query_embeddings=[query_embedding],
        n_results=n_results
    )

    # 2. Construir contexto
    context = "\n\n---\n\n".join(results['documents'][0])

    # 3. Generar respuesta con LLM
    response = client.chat.completions.create(
        model="gpt-4o",
        messages=[
            {"role": "system", "content": f"""Responde basándote SOLO en el contexto proporcionado.
Si la información no está en el contexto, di "No tengo información sobre eso."

CONTEXTO:
{context}"""},
            {"role": "user", "content": question}
        ],
        temperature=0.3,
    )

    return {
        "answer": response.choices[0].message.content,
        "sources": results['metadatas'][0],
    }

# Usar
result = rag_query("¿Cuál es la política de vacaciones?", collection)
print(result["answer"])
print(f"Fuentes: {result['sources']}")
```

## RAG avanzado

### Hybrid Search (Vector + Keyword)

```python
# Combinar búsqueda semántica con keyword (BM25)
from rank_bm25 import BM25Okapi

class HybridSearch:
    def __init__(self, documents, collection):
        self.documents = documents
        self.collection = collection
        # BM25 para keyword search
        tokenized = [doc.split() for doc in documents]
        self.bm25 = BM25Okapi(tokenized)

    def search(self, query, n_results=5, alpha=0.5):
        # Búsqueda semántica
        vector_results = self.collection.query(
            query_embeddings=[get_embedding(query)],
            n_results=n_results * 2
        )

        # Búsqueda keyword
        bm25_scores = self.bm25.get_scores(query.split())

        # Combinar scores (Reciprocal Rank Fusion)
        combined = self._reciprocal_rank_fusion(
            vector_results, bm25_scores, alpha
        )
        return combined[:n_results]
```

### Re-ranking

```python
# Usar un modelo de re-ranking para mejorar la relevancia
from sentence_transformers import CrossEncoder

reranker = CrossEncoder('cross-encoder/ms-marco-MiniLM-L-12-v2')

def rerank_results(query, documents, top_k=5):
    pairs = [[query, doc] for doc in documents]
    scores = reranker.predict(pairs)

    ranked = sorted(
        zip(documents, scores),
        key=lambda x: x[1],
        reverse=True
    )
    return [doc for doc, score in ranked[:top_k]]
```

### Metadata Filtering

```python
# Filtrar por metadatos antes de la búsqueda vectorial
results = collection.query(
    query_embeddings=[get_embedding(question)],
    where={
        "$and": [
            {"department": "engineering"},
            {"date": {"$gte": "2025-01-01"}},
        ]
    },
    n_results=5
)
```

## Evaluación de RAG

```python
# Métricas clave de RAG
def evaluate_rag(questions, expected_answers, rag_system):
    metrics = {
        "retrieval_precision": 0,  # ¿Los docs recuperados son relevantes?
        "answer_relevance": 0,     # ¿La respuesta es relevante a la pregunta?
        "faithfulness": 0,         # ¿La respuesta se basa en los docs?
        "answer_correctness": 0,   # ¿La respuesta es correcta?
    }

    for q, expected in zip(questions, expected_answers):
        result = rag_system.query(q)
        # Evaluar cada métrica...

    return metrics
```

Herramientas de evaluación: **RAGAS**, **DeepEval**, **LangSmith**.

## Resumen

- RAG resuelve alucinaciones y knowledge cutoff inyectando contexto relevante.
- Pipeline: Documentos → Chunks → Embeddings → Vector DB → Retrieve → Generate.
- ChromaDB/Pinecone para vector store, BM25 para keyword, combinados = hybrid search.
- Re-ranking mejora la calidad de los resultados recuperados.
- Evalúa con métricas de retrieval (precision) y generation (faithfulness, correctness).
