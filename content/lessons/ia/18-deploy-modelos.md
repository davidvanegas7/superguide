# Deploy y Optimización de Modelos de IA

Llevar un modelo a producción requiere optimizar latencia, costo y escalabilidad. Esta lección cubre las técnicas clave para servir modelos de IA de forma eficiente.

## Cuantización

Reducir la precisión numérica de los pesos para acelerar inferencia y reducir memoria:

```python
# Cuantización con llama.cpp (GGUF format)
# Convierte modelos de 16-bit a 4-bit o 8-bit

# Descargar modelo cuantizado
# Q4_K_M = 4 bits, buen balance calidad/velocidad
# Q5_K_M = 5 bits, mejor calidad
# Q8_0 = 8 bits, casi sin pérdida

# Con Ollama (ya usa modelos cuantizados)
# ollama run llama3.1  → Q4_K_M por defecto
```

### Cuantización con transformers

```python
from transformers import AutoModelForCausalLM, BitsAndBytesConfig

# INT8 cuantización
model_8bit = AutoModelForCausalLM.from_pretrained(
    "meta-llama/Llama-3.1-8B",
    load_in_8bit=True,
    device_map="auto",
)
# VRAM: ~8GB (vs ~16GB en fp16)

# INT4 cuantización (NF4)
bnb_config = BitsAndBytesConfig(
    load_in_4bit=True,
    bnb_4bit_quant_type="nf4",
    bnb_4bit_compute_dtype="bfloat16",
)

model_4bit = AutoModelForCausalLM.from_pretrained(
    "meta-llama/Llama-3.1-8B",
    quantization_config=bnb_config,
    device_map="auto",
)
# VRAM: ~5GB (vs ~16GB en fp16)
```

### Comparativa de cuantización

| Precisión | Tamaño (8B modelo) | VRAM | Calidad | Velocidad |
|-----------|-------------------|------|---------|-----------|
| FP32 | 32GB | 32GB | Referencia | 1x |
| FP16/BF16 | 16GB | 16GB | ~100% | 2x |
| INT8 (Q8) | 8GB | 8GB | ~99% | 2.5x |
| INT4 (Q4) | 4GB | 5GB | ~95% | 3x |
| GPTQ/AWQ | 4GB | 5GB | ~97% | 3.5x |

## ONNX Runtime

Framework de inferencia optimizado que soporta múltiples plataformas:

```python
from optimum.onnxruntime import ORTModelForSequenceClassification
from transformers import AutoTokenizer

# Exportar modelo a ONNX
model = ORTModelForSequenceClassification.from_pretrained(
    "distilbert-base-uncased-finetuned-sst-2-english",
    export=True,
)
tokenizer = AutoTokenizer.from_pretrained(
    "distilbert-base-uncased-finetuned-sst-2-english"
)

# Inferencia optimizada
inputs = tokenizer("This movie is great!", return_tensors="pt")
outputs = model(**inputs)

# ONNX puede ser 2-5x más rápido que PyTorch nativo
```

## vLLM: Serving de alta performance

```python
# vLLM usa PagedAttention para servir LLMs eficientemente
# Soporta continuous batching, speculative decoding y más

# Iniciar servidor compatible con OpenAI
# python -m vllm.entrypoints.openai.api_server \
#     --model meta-llama/Llama-3.1-8B-Instruct \
#     --dtype bfloat16 \
#     --max-model-len 4096 \
#     --gpu-memory-utilization 0.9 \
#     --port 8000

# Desde tu app, usa la API estándar de OpenAI
from openai import OpenAI

client = OpenAI(base_url="http://localhost:8000/v1", api_key="none")

response = client.chat.completions.create(
    model="meta-llama/Llama-3.1-8B-Instruct",
    messages=[{"role": "user", "content": "Hola"}],
)
```

### vLLM vs otras soluciones

| Feature | vLLM | Ollama | TGI | llama.cpp |
|---------|------|--------|-----|-----------|
| Throughput | Muy alto | Medio | Alto | Medio |
| Batching | Continuous | No | Continuous | No |
| GPU support | NVIDIA, AMD | NVIDIA, Apple | NVIDIA | CPU, GPU |
| Cuantización | AWQ, GPTQ | GGUF | GPTQ, AWQ | GGUF |
| Uso ideal | Producción | Desarrollo | Producción | Edge/Local |

## Deploy en la nube

### Con Docker

```dockerfile
FROM python:3.11-slim

WORKDIR /app
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

# Modelo se carga al iniciar
ENV MODEL_PATH="./models/llama-3.1-8b-q4"

EXPOSE 8000
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]
```

```python
# main.py - API con FastAPI
from fastapi import FastAPI
from pydantic import BaseModel
from vllm import LLM, SamplingParams
import os

app = FastAPI()
model = LLM(model=os.environ["MODEL_PATH"])

class ChatRequest(BaseModel):
    message: str
    max_tokens: int = 256
    temperature: float = 0.7

@app.post("/chat")
async def chat(request: ChatRequest):
    params = SamplingParams(
        temperature=request.temperature,
        max_tokens=request.max_tokens,
    )
    output = model.generate([request.message], params)
    return {"response": output[0].outputs[0].text}
```

### GPU en la nube

```python
# Opciones de GPU cloud (2025-2026 pricing aprox.)
cloud_gpus = {
    "AWS": {
        "p4d.24xlarge": "8x A100 80GB → ~$32/hr",
        "g5.xlarge": "1x A10G 24GB → ~$1/hr",
        "inf2.xlarge": "Inferentia2 → ~$0.75/hr",
    },
    "GCP": {
        "a2-highgpu-1g": "1x A100 40GB → ~$3.6/hr",
        "g2-standard-4": "1x L4 24GB → ~$0.7/hr",
    },
    "Serverless": {
        "Modal": "Pay per second, auto-scaling",
        "Replicate": "Pay per prediction",
        "Together AI": "API compatible con OpenAI",
        "Groq": "Inferencia ultra-rápida (LPU)",
    },
}
```

## Optimización de inferencia

### KV-Cache
```python
# KV-Cache evita recalcular tokens anteriores
# La mayoría de frameworks lo implementan automáticamente

# Sin KV-Cache: cada nuevo token recalcula TODA la secuencia
# Con KV-Cache: solo calcula el nuevo token + cache previo

# Impacto: O(n²) → O(n) por token generado
# Trade-off: usa más VRAM pero es mucho más rápido
```

### Speculative Decoding

```python
# Usa un modelo pequeño (draft) para proponer tokens
# y el modelo grande solo verifica

# Ejemplo conceptual
def speculative_decode(big_model, small_model, prompt, n_speculate=4):
    """
    1. Modelo pequeño genera n tokens rápidamente
    2. Modelo grande verifica todos a la vez (en paralelo)
    3. Si coinciden: aceptar (ganancia de velocidad)
    4. Si difieren: usar el token del modelo grande
    """
    draft_tokens = small_model.generate(prompt, n=n_speculate)
    verified = big_model.verify(prompt + draft_tokens)
    return verified

# Resultado: 2-3x speedup sin pérdida de calidad
```

### Batching

```python
# Continuous batching: procesar múltiples requests simultáneamente
# vLLM lo hace automáticamente

# Sin batching: 1 request a la vez → GPU subutilizada
# Con batching: N requests a la vez → GPU al máximo

# Impacto en throughput (tokens/segundo):
# - Sin batching: ~50 tok/s
# - Con static batching (bs=8): ~300 tok/s
# - Con continuous batching: ~500 tok/s
```

## Edge deployment

```python
# Modelos pequeños para dispositivos edge

# Opciones para edge (2025-2026):
edge_models = {
    "Phi-3-mini (3.8B)": "Corre en laptops, ~4GB VRAM",
    "Gemma 2 2B": "Ultra ligero, ~2GB VRAM",
    "Llama 3.2 1B/3B": "Diseñado para mobile/edge",
    "TinyLlama 1.1B": "Mínimo, para IoT",
}

# Con llama.cpp en CPU
# ./llama-cli -m phi-3-mini-Q4.gguf -p "Hola" -n 128
# Funciona en Raspberry Pi, laptops sin GPU, etc.

# Con ONNX para mobile
# Exportar modelo → ONNX → ONNX Runtime Mobile → Android/iOS
```

## Resumen

- Cuantización (4-bit, 8-bit) reduce VRAM 4-8x con pérdida mínima de calidad.
- vLLM con continuous batching es el estándar para serving en producción.
- ONNX Runtime optimiza inferencia cross-platform.
- Docker + FastAPI es el stack común para APIs de IA.
- KV-Cache y speculative decoding aceleran la generación.
- Modelos <4B parámetros permiten edge deployment en dispositivos limitados.
- Serverless (Modal, Replicate) elimina la gestión de infraestructura GPU.
