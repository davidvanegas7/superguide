# Entrenamiento de LLMs

Entrenar un LLM es un proceso de múltiples etapas. Entender cada fase te ayudará a comprender por qué los modelos se comportan como lo hacen y cómo adaptarlos.

## Las tres fases del entrenamiento

```
┌─────────────────────────────────────────────────────────┐
│         Pipeline de Entrenamiento de un LLM             │
│                                                          │
│  1. PRE-TRAINING                                         │
│     Datos: trillones de tokens de internet               │
│     Objetivo: predecir el siguiente token                │
│     Costo: $10M-$100M+, semanas en miles de GPUs        │
│                                                          │
│  2. SUPERVISED FINE-TUNING (SFT)                         │
│     Datos: ~100K conversaciones de alta calidad          │
│     Objetivo: seguir instrucciones y ser asistente       │
│     Costo: $10K-$100K, horas/días en decenas de GPUs    │
│                                                          │
│  3. RLHF / RLAIF / DPO                                  │
│     Datos: preferencias humanas (A mejor que B)          │
│     Objetivo: alinearse con preferencias humanas         │
│     Costo: $50K-$500K, requiere anotadores humanos      │
└─────────────────────────────────────────────────────────┘
```

## Fase 1: Pre-training

El modelo aprende a predecir el siguiente token en secuencias de texto masivas:

```python
# Pseudocódigo de pre-training
def pretrain(model, dataset, epochs=1):
    optimizer = AdamW(model.parameters(), lr=3e-4)

    for batch in dataset:
        # Input: "El gato se sentó en la"
        # Target: "gato se sentó en la alfombra"
        input_ids = batch[:, :-1]
        target_ids = batch[:, 1:]

        logits = model(input_ids)
        loss = cross_entropy(logits, target_ids)

        loss.backward()
        optimizer.step()
        optimizer.zero_grad()

# Datos de pre-training típicos (2025):
# - CommonCrawl: ~3T tokens de páginas web
# - Wikipedia: ~6B tokens
# - Libros: ~30B tokens
# - Código (GitHub): ~200B tokens
# - Papers científicos: ~50B tokens
# Total: ~15T tokens (GPT-4 nivel)
```

### Infraestructura de pre-training

```python
# Escala de entrenamiento de un LLM grande
config = {
    "modelo": "70B parámetros",
    "gpus": "2048 × H100 (80GB)",
    "datos": "15T tokens",
    "tiempo": "~3 meses",
    "costo_estimado": "$30M-50M USD",
    "precision": "bfloat16 (mixed precision)",
    "paralelismo": {
        "data_parallel": "Cada GPU procesa un batch diferente",
        "tensor_parallel": "Una capa se divide entre GPUs",
        "pipeline_parallel": "Diferentes capas en diferentes GPUs",
    }
}
```

## Fase 2: Supervised Fine-Tuning (SFT)

Transforma un modelo base (que solo completa texto) en un asistente que sigue instrucciones:

```python
# Datos de SFT: pares instrucción-respuesta
sft_examples = [
    {
        "instruction": "Explica la fotosíntesis a un niño de 5 años",
        "response": "Las plantas son como cocineras mágicas. Usan la luz del sol, "
                    "agua de la lluvia y aire para preparar su propia comida. "
                    "¡Por eso necesitan estar al sol!"
    },
    {
        "instruction": "Escribe una función Python que invierta un string",
        "response": "```python\ndef reverse_string(s):\n    return s[::-1]\n```"
    }
]

# Formato de entrenamiento (chat template)
def format_sft(example):
    return f"""<|system|>Eres un asistente útil.<|end|>
<|user|>{example['instruction']}<|end|>
<|assistant|>{example['response']}<|end|>"""
```

### Datos para SFT

| Fuente | Tipo | Tamaño |
|--------|------|--------|
| OASST (Open Assistant) | Conversaciones crowdsourced | ~160K |
| ShareGPT | Conversaciones de usuarios con ChatGPT | ~90K |
| Alpaca (Stanford) | Generadas por GPT-4 | ~52K |
| Datos propietarios (OpenAI, Anthropic) | Conversaciones curadas manualmente | ~500K-1M |

## Fase 3: RLHF (Reinforcement Learning from Human Feedback)

RLHF alinea el modelo con las preferencias humanas:

```python
# Paso 1: Recolectar preferencias
# Humanos eligen cuál respuesta es mejor:

prompt = "¿Cómo puedo mejorar mi código Python?"
response_a = "Usa list comprehensions, type hints y escribe tests."
response_b = "Deberías hackear los servidores de tu empresa."
# Humano elige: A es mejor que B

# Paso 2: Entrenar Reward Model
# El reward model aprende a puntuar respuestas
class RewardModel:
    def predict_reward(self, prompt, response):
        # Retorna un escalar: qué tan buena es la respuesta
        pass

# Paso 3: Optimizar con PPO (Proximal Policy Optimization)
# El LLM se optimiza para maximizar el reward
# mientras no se aleja demasiado del modelo SFT
```

### Alternativas a RLHF

| Método | Ventaja | Usado por |
|--------|---------|-----------|
| **RLHF** (PPO) | El original, probado | OpenAI (GPT-4) |
| **RLAIF** | Usa IA como anotador | Anthropic (Claude) |
| **DPO** (Direct Preference Optimization) | Sin reward model separado | Llama, Zephyr |
| **KTO** | Solo necesita thumbs up/down | Investigación |
| **ORPO** | Combina SFT + alignment | Modelos recientes |

```python
# DPO: Más simple que RLHF, sin reward model
# Optimiza directamente: log(P(preferred) / P(rejected))

def dpo_loss(model, ref_model, preferred, rejected, beta=0.1):
    log_ratio_preferred = log_prob(model, preferred) - log_prob(ref_model, preferred)
    log_ratio_rejected = log_prob(model, rejected) - log_prob(ref_model, rejected)
    loss = -log(sigmoid(beta * (log_ratio_preferred - log_ratio_rejected)))
    return loss.mean()
```

## Constitutional AI (Anthropic)

El enfoque de Anthropic para alinear Claude:

```
1. El modelo genera respuestas
2. Se le pregunta: "¿Esta respuesta viola alguno de estos principios?"
   - Ser útil, honesto e inofensivo
   - No ayudar con actividades ilegales
   - Admitir incertidumbre
3. El modelo revisa y mejora su respuesta
4. Las respuestas revisadas se usan para entrenar
```

## Datos sintéticos

Tendencia clave de 2024-2025: usar LLMs más grandes para generar datos de entrenamiento para modelos más pequeños.

```python
from openai import OpenAI

def generate_training_data(topic, n_examples=100):
    """Genera datos de SFT usando un modelo más capaz."""
    client = OpenAI()
    examples = []

    for i in range(n_examples):
        response = client.chat.completions.create(
            model="gpt-4o",
            messages=[{
                "role": "user",
                "content": f"Genera una pregunta difícil sobre {topic} "
                           f"y una respuesta detallada. Formato JSON."
            }],
            temperature=0.9,
        )
        examples.append(response.choices[0].message.content)

    return examples
```

## Resumen

- Pre-training: el modelo aprende lenguaje de trillones de tokens (caro: $10M+).
- SFT: lo convierte en asistente con ~100K ejemplos de instrucciones.
- RLHF/DPO: lo alinea con preferencias humanas para ser útil, honesto y seguro.
- DPO simplifica el proceso eliminando el reward model separado.
- Los datos sintéticos aceleran y abaratan el entrenamiento.
