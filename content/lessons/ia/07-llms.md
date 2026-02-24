# Large Language Models (LLMs)

Los Large Language Models son redes neuronales basadas en Transformers, entrenadas en cantidades masivas de texto para predecir el siguiente token. A pesar de este objetivo simple, emergen capacidades sorprendentes: razonamiento, programación, traducción y más.

## ¿Qué es un LLM?

Un LLM es, en esencia, un modelo que calcula:

```
P(siguiente_token | tokens_anteriores)
```

```python
# Concepto simplificado de cómo genera un LLM
def generate(model, prompt_tokens, max_new_tokens=100):
    tokens = list(prompt_tokens)
    for _ in range(max_new_tokens):
        # El modelo predice probabilidades del siguiente token
        logits = model(tokens)
        next_token_probs = softmax(logits[-1])

        # Sampling: elegir el siguiente token
        next_token = sample(next_token_probs, temperature=0.7)
        tokens.append(next_token)

        if next_token == EOS_TOKEN:
            break
    return tokens
```

## Panorama actual de LLMs (2025-2026)

### Modelos propietarios (closed-source)

| Modelo | Empresa | Fortalezas |
|--------|---------|-----------|
| **GPT-4o** | OpenAI | Multimodal nativo (texto, imagen, audio, video) |
| **o1 / o3** | OpenAI | Razonamiento profundo con chain-of-thought interno |
| **Claude 3.5 Sonnet** | Anthropic | Código, análisis largo, instrucciones complejas |
| **Claude Opus** | Anthropic | Capacidad máxima, razonamiento avanzado |
| **Gemini 2.0** | Google | Multimodal, contexto hasta 2M tokens |
| **Grok** | xAI | Integración con datos en tiempo real |

### Modelos open-source / open-weight

| Modelo | Empresa | Parámetros | Licencia |
|--------|---------|-----------|----------|
| **Llama 3.1** | Meta | 8B, 70B, 405B | Llama Community License |
| **Mistral Large** | Mistral | 123B | Apache 2.0 (variantes) |
| **DeepSeek-V3** | DeepSeek | 671B (MoE, 37B activos) | MIT |
| **DeepSeek-R1** | DeepSeek | 671B | MIT, modelo de razonamiento |
| **Qwen 2.5** | Alibaba | 0.5B-72B | Apache 2.0 |
| **Gemma 2** | Google | 2B, 9B, 27B | Gemma License |
| **Phi-3/4** | Microsoft | 3.8B-14B | MIT |

### Modelos de razonamiento

Una nueva categoría emergida en 2024-2025: modelos que "piensan" antes de responder.

```
Usuario: ¿Cuántas letras "r" hay en "strawberry"?

Modelo estándar: 2 (incorrecto)

Modelo de razonamiento (o1/DeepSeek-R1):
<pensando>
s-t-r-a-w-b-e-r-r-y
Contando las "r": posición 3, 8, 9
Hay 3 letras "r"
</pensando>
Respuesta: 3 (correcto)
```

## Scaling Laws

Las scaling laws (Kaplan et al., 2020) demostraron que el rendimiento de los LLMs mejora predeciblemente al escalar:

- **Más parámetros** (N)
- **Más datos de entrenamiento** (D)
- **Más cómputo** (C)

```python
# Ley de escalado de Chinchilla (Hoffmann et al., 2022)
# Para un presupuesto de cómputo C óptimo:
# N ≈ C^0.5 (parámetros crecen con raíz de cómputo)
# D ≈ C^0.5 (tokens crecen igual)
# Regla: ~20 tokens por parámetro

# Ejemplo: modelo de 7B parámetros
parametros = 7e9
tokens_optimos = parametros * 20  # ~140B tokens
print(f"Tokens óptimos: {tokens_optimos/1e9:.0f}B")
```

## Capacidades emergentes

Al escalar, los LLMs desarrollan capacidades que no fueron explícitamente programadas:

1. **In-context learning**: aprenden de ejemplos en el prompt sin actualizar pesos.
2. **Chain-of-thought**: razonan paso a paso cuando se les pide.
3. **Programación**: escriben y depuran código en múltiples lenguajes.
4. **Traducción**: traducen entre idiomas sin entrenamiento específico.
5. **Razonamiento matemático**: resuelven problemas de lógica y álgebra.
6. **Instrucción-following**: siguen instrucciones complejas en lenguaje natural.

## Mixture of Experts (MoE)

Los modelos MoE activan solo una fracción de sus parámetros por token, reduciendo el costo de inferencia:

```
┌──────────────────────────────────────────┐
│          Mixture of Experts              │
│                                          │
│  Input ──▶ Router ──▶ Expert 1 ✓        │
│                  ├──▶ Expert 2           │
│                  ├──▶ Expert 3 ✓        │
│                  ├──▶ Expert 4           │
│                  ├──▶ Expert 5           │
│                  ├──▶ Expert 6           │
│                  ├──▶ Expert 7           │
│                  └──▶ Expert 8           │
│                                          │
│  Solo 2 de 8 expertos se activan (top-2) │
│  671B parámetros totales                 │
│  37B parámetros activos por token        │
└──────────────────────────────────────────┘
```

Modelos MoE notables: GPT-4 (rumoreado), Mixtral, DeepSeek-V3, Grok.

## Ventanas de contexto

El contexto es cuántos tokens puede "ver" el modelo a la vez:

| Modelo | Contexto |
|--------|---------|
| GPT-3 | 4K tokens |
| GPT-4 | 8K / 128K tokens |
| Claude 3.5 | 200K tokens |
| Gemini 2.0 | 2M tokens |
| Llama 3.1 | 128K tokens |

```python
# Estimar tokens en texto
def estimate_tokens(text):
    """Regla general: ~4 caracteres ≈ 1 token en inglés"""
    return len(text) // 4

text = "Un libro promedio tiene 250 páginas con ~250 palabras por página"
palabras = 250 * 250  # 62,500 palabras
tokens_aprox = palabras * 1.3  # ~81,250 tokens
# Cabe fácilmente en GPT-4 128K o Claude 200K
```

## Usar un LLM con Python

```python
from openai import OpenAI

client = OpenAI(api_key="sk-...")

response = client.chat.completions.create(
    model="gpt-4o",
    messages=[
        {"role": "system", "content": "Eres un asistente útil."},
        {"role": "user", "content": "Explica qué es un LLM en 2 oraciones."}
    ],
    temperature=0.7,
    max_tokens=200,
)

print(response.choices[0].message.content)
```

```python
import anthropic

client = anthropic.Anthropic(api_key="sk-ant-...")

message = client.messages.create(
    model="claude-3-5-sonnet-20241022",
    max_tokens=200,
    messages=[
        {"role": "user", "content": "Explica qué es un LLM en 2 oraciones."}
    ]
)

print(message.content[0].text)
```

## Resumen

- Los LLMs predicen el siguiente token, pero de eso emergen capacidades sorprendentes.
- Las scaling laws predicen que más parámetros + datos = mejor rendimiento.
- Modelos de razonamiento (o1, DeepSeek-R1) representan una nueva frontera.
- MoE permite modelos más eficientes con parámetros activos reducidos.
- El ecosistema incluye modelos propietarios (GPT-4, Claude) y open-source (Llama, DeepSeek).
