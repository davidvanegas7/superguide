# APIs de LLMs y Modelos Open Source

Integrar LLMs en aplicaciones requiere conocer las APIs disponibles y cómo elegir entre modelos propietarios y open source.

## OpenAI API

La API más utilizada. Modelo base para muchas apps:

```python
from openai import OpenAI

client = OpenAI(api_key="sk-...")

# Chat completion básico
response = client.chat.completions.create(
    model="gpt-4o",
    messages=[
        {"role": "system", "content": "Eres un experto en Python."},
        {"role": "user", "content": "¿Cómo funciona un decorador?"}
    ],
    temperature=0.7,
    max_tokens=500,
)

print(response.choices[0].message.content)
print(f"Tokens: {response.usage.total_tokens}")
print(f"Costo aprox: ${response.usage.total_tokens * 0.000005:.4f}")

# Streaming (respuesta token por token)
stream = client.chat.completions.create(
    model="gpt-4o",
    messages=[{"role": "user", "content": "Cuenta hasta 10"}],
    stream=True,
)

for chunk in stream:
    if chunk.choices[0].delta.content:
        print(chunk.choices[0].delta.content, end="", flush=True)
```

## Anthropic API (Claude)

```python
import anthropic

client = anthropic.Anthropic(api_key="sk-ant-...")

message = client.messages.create(
    model="claude-sonnet-4-20250514",
    max_tokens=1024,
    system="Eres un asistente técnico conciso.",
    messages=[
        {"role": "user", "content": "Explica Docker en 3 oraciones."}
    ]
)

print(message.content[0].text)
print(f"Input tokens: {message.usage.input_tokens}")
print(f"Output tokens: {message.usage.output_tokens}")

# Streaming con Anthropic
with client.messages.stream(
    model="claude-sonnet-4-20250514",
    max_tokens=1024,
    messages=[{"role": "user", "content": "Hola"}],
) as stream:
    for text in stream.text_stream:
        print(text, end="", flush=True)
```

## Google Gemini API

```python
import google.generativeai as genai

genai.configure(api_key="...")

model = genai.GenerativeModel("gemini-2.0-flash")
response = model.generate_content("Explica qué es Kubernetes")
print(response.text)

# Multimodal: imagen + texto
import PIL.Image
img = PIL.Image.open("diagram.png")
response = model.generate_content(["Describe esta imagen", img])
print(response.text)
```

## Modelos Open Source con Hugging Face

```python
from transformers import pipeline, AutoModelForCausalLM, AutoTokenizer
import torch

# Cargar modelo open source
model_id = "meta-llama/Llama-3.1-8B-Instruct"
tokenizer = AutoTokenizer.from_pretrained(model_id)
model = AutoModelForCausalLM.from_pretrained(
    model_id,
    torch_dtype=torch.bfloat16,
    device_map="auto",  # Distribuye en GPUs disponibles
)

# Generar
messages = [
    {"role": "system", "content": "Eres un asistente útil."},
    {"role": "user", "content": "¿Qué es un API REST?"},
]

input_ids = tokenizer.apply_chat_template(
    messages, return_tensors="pt"
).to(model.device)

output = model.generate(
    input_ids,
    max_new_tokens=256,
    temperature=0.7,
    do_sample=True,
)

print(tokenizer.decode(output[0][input_ids.shape[-1]:], skip_special_tokens=True))
```

## Servir modelos locales con Ollama

```bash
# Instalar Ollama (Linux/macOS)
curl -fsSL https://ollama.ai/install.sh | sh

# Descargar y ejecutar un modelo
ollama pull llama3.1
ollama run llama3.1

# API compatible con OpenAI
curl http://localhost:11434/v1/chat/completions \
  -d '{"model": "llama3.1", "messages": [{"role": "user", "content": "Hola"}]}'
```

```python
# Usar Ollama desde Python con la API de OpenAI
from openai import OpenAI

client = OpenAI(
    base_url="http://localhost:11434/v1",
    api_key="ollama",  # Ollama no requiere API key
)

response = client.chat.completions.create(
    model="llama3.1",
    messages=[{"role": "user", "content": "Explica recursión en 2 oraciones"}],
)
print(response.choices[0].message.content)
```

## vLLM: Serving eficiente

```python
# vLLM ofrece serving de alto rendimiento
# pip install vllm

from vllm import LLM, SamplingParams

llm = LLM(model="meta-llama/Llama-3.1-8B-Instruct")
params = SamplingParams(temperature=0.7, max_tokens=256)

outputs = llm.generate(["¿Qué es Python?"], params)
print(outputs[0].outputs[0].text)

# vLLM como servidor API
# python -m vllm.entrypoints.openai.api_server \
#     --model meta-llama/Llama-3.1-8B-Instruct \
#     --port 8000
```

## Comparativa de costos (2025-2026)

| Modelo | Input (1M tokens) | Output (1M tokens) |
|--------|-------------------|---------------------|
| GPT-4o | $2.50 | $10.00 |
| GPT-4o-mini | $0.15 | $0.60 |
| Claude 3.5 Sonnet | $3.00 | $15.00 |
| Claude Haiku | $0.25 | $1.25 |
| Gemini 2.0 Flash | $0.10 | $0.40 |
| Llama 3.1 (self-hosted) | Costo de GPU | Costo de GPU |
| Ollama (local) | Gratis | Gratis |

## Cuándo usar cada opción

```python
# Árbol de decisión simplificado
def elegir_modelo(caso):
    if caso == "prototipo_rapido":
        return "gpt-4o-mini o Claude Haiku (barato y rápido)"

    if caso == "produccion_calidad":
        return "gpt-4o o Claude 3.5 Sonnet"

    if caso == "privacidad_total":
        return "Llama 3.1 self-hosted con vLLM"

    if caso == "desarrollo_local":
        return "Ollama + Llama 3.1, Mistral o Phi-3"

    if caso == "alto_volumen_bajo_costo":
        return "GPT-4o-mini, Gemini Flash, o self-hosted"

    if caso == "razonamiento_complejo":
        return "o1/o3 o DeepSeek-R1"
```

## Resumen

- OpenAI y Anthropic ofrecen las APIs más maduras y con mejor calidad.
- Modelos open-source (Llama 3.1, DeepSeek) compiten en calidad.
- Ollama para uso local, vLLM para serving en producción.
- All APIs convergen en un formato similar (chat completions).
- Elige según: calidad necesaria, privacidad, costo y volumen.
