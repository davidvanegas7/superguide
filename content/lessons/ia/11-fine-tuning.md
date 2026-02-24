# Fine-tuning y Adaptación de Modelos

Fine-tuning permite adaptar un LLM pre-entrenado a tu dominio específico. Las técnicas modernas como LoRA hacen esto accesible incluso con hardware limitado.

## ¿Cuándo hacer fine-tuning vs usar prompts?

| Criterio | Prompt Engineering | Fine-tuning |
|----------|-------------------|-------------|
| Datos disponibles | Pocos o ninguno | >100 ejemplos de calidad |
| Tarea | General o variada | Específica y repetitiva |
| Formato de salida | Flexible | Consistente y estricto |
| Costo | Bajo (solo API) | Medio (cómputo + datos) |
| Latencia | Normal | Puede reducir tokens del prompt |
| Conocimiento nuevo | RAG es mejor | Estilo/formato, no hechos |

### Regla práctica:
1. Primero intenta **prompt engineering**
2. Si no es suficiente, prueba **RAG**
3. Si necesitas estilo/formato consistente, haz **fine-tuning**
4. Fine-tuning + RAG = la combinación más potente

## Full Fine-Tuning

Actualiza todos los parámetros del modelo. Requiere mucha GPU pero es lo más efectivo:

```python
from transformers import AutoModelForCausalLM, AutoTokenizer, TrainingArguments
from trl import SFTTrainer

model_name = "meta-llama/Llama-3.1-8B"
model = AutoModelForCausalLM.from_pretrained(model_name, torch_dtype="auto")
tokenizer = AutoTokenizer.from_pretrained(model_name)

# Dataset de instrucciones
dataset = [
    {"instruction": "...", "output": "..."},
    # ... más ejemplos
]

training_args = TrainingArguments(
    output_dir="./ft-llama",
    num_train_epochs=3,
    per_device_train_batch_size=4,
    learning_rate=2e-5,
    bf16=True,                     # Mixed precision
    gradient_accumulation_steps=4,
)

trainer = SFTTrainer(
    model=model,
    train_dataset=dataset,
    args=training_args,
    tokenizer=tokenizer,
)

trainer.train()
```

**Costo**: modelo de 8B requiere ~4×A100 (80GB). Para 70B, ~32×A100.

## LoRA: Low-Rank Adaptation

LoRA congela el modelo base y entrena solo matrices pequeñas de bajo rango. Reduce los parámetros entrenables en 100-1000x:

```python
from peft import LoraConfig, get_peft_model, TaskType

# Configuración de LoRA
lora_config = LoraConfig(
    r=16,                          # Rango (4-64, mayor = más capacidad)
    lora_alpha=32,                 # Escala (generalmente 2*r)
    target_modules=["q_proj", "v_proj", "k_proj", "o_proj"],  # Capas a adaptar
    lora_dropout=0.05,
    bias="none",
    task_type=TaskType.CAUSAL_LM,
)

model = AutoModelForCausalLM.from_pretrained(model_name, torch_dtype="auto")
peft_model = get_peft_model(model, lora_config)

# Comparar parámetros
peft_model.print_trainable_parameters()
# trainable params: 33,554,432 || all params: 8,030,261,248
# trainable%: 0.4177%  ← Solo 0.4% de los parámetros
```

### LoRA conceptualmente

```
┌────────────────────────────────────────────────┐
│              LoRA                                │
│                                                  │
│  Modelo Original:  x → W (d×d) → output         │
│                    W está CONGELADO               │
│                                                  │
│  Con LoRA:         x → W + BA → output           │
│                    W congelado                    │
│                    B (d×r) y A (r×d) entrenables  │
│                    r << d (ej: r=16, d=4096)      │
│                                                  │
│  Parámetros nuevos: 2 * d * r vs d * d           │
│  Si d=4096, r=16: 131K vs 16.7M (128x menos)    │
└────────────────────────────────────────────────┘
```

## QLoRA: Quantized LoRA

QLoRA combina cuantización de 4 bits con LoRA, permitiendo fine-tuning en GPUs consumer:

```python
from transformers import BitsAndBytesConfig

# Cuantización a 4 bits
quantization_config = BitsAndBytesConfig(
    load_in_4bit=True,
    bnb_4bit_quant_type="nf4",           # Normal Float 4
    bnb_4bit_compute_dtype="bfloat16",    # Cómputo en bf16
    bnb_4bit_use_double_quant=True,       # Doble cuantización
)

model = AutoModelForCausalLM.from_pretrained(
    "meta-llama/Llama-3.1-8B",
    quantization_config=quantization_config,
    device_map="auto",
)

# Aplicar LoRA sobre modelo cuantizado
peft_model = get_peft_model(model, lora_config)

# Llama 8B con QLoRA cabe en una GPU de 12GB (RTX 4070)
```

| Método | VRAM necesaria (8B modelo) | Calidad |
|--------|--------------------------|---------|
| Full fine-tuning | 80GB+ (A100) | Superior |
| LoRA | 24-40GB (A100/A6000) | Muy buena |
| QLoRA (4-bit) | 12-16GB (RTX 4070) | Buena |
| QLoRA (4-bit, 70B) | 48GB (A100) | Mejor |

## Fine-tuning con la API de OpenAI

```python
from openai import OpenAI
import json

client = OpenAI()

# 1. Preparar datos en formato JSONL
training_data = [
    {
        "messages": [
            {"role": "system", "content": "Eres un asistente legal."},
            {"role": "user", "content": "¿Qué es un contrato bilateral?"},
            {"role": "assistant", "content": "Un contrato bilateral..."}
        ]
    }
    # ... al menos 10 ejemplos, idealmente 50-100+
]

with open("train.jsonl", "w") as f:
    for example in training_data:
        f.write(json.dumps(example) + "\n")

# 2. Subir archivo
file = client.files.create(file=open("train.jsonl", "rb"), purpose="fine-tune")

# 3. Crear fine-tuning job
job = client.fine_tuning.jobs.create(
    training_file=file.id,
    model="gpt-4o-mini-2024-07-18",  # Modelo base
    hyperparameters={"n_epochs": 3},
)

# 4. Monitorear
status = client.fine_tuning.jobs.retrieve(job.id)
print(status.status)  # "running" → "succeeded"

# 5. Usar modelo fine-tuned
response = client.chat.completions.create(
    model=job.fine_tuned_model,  # ft:gpt-4o-mini:org:name:id
    messages=[{"role": "user", "content": "¿Qué es un pagaré?"}]
)
```

## Adaptadores y merging

```python
from peft import PeftModel

# Cargar modelo base + adaptador LoRA
base_model = AutoModelForCausalLM.from_pretrained("meta-llama/Llama-3.1-8B")
model = PeftModel.from_pretrained(base_model, "./lora-adapter")

# Merge: combinar adaptador en el modelo base (para producción)
merged_model = model.merge_and_unload()
merged_model.save_pretrained("./merged-model")

# Ahora tienes un modelo completo sin dependencia de PEFT
```

## Resumen

- Fine-tuning adapta un LLM a tu dominio específico.
- LoRA reduce parámetros entrenables 100x congelando el modelo base.
- QLoRA reduce VRAM 4x con cuantización de 4 bits → fine-tune en GPU consumer.
- La API de OpenAI permite fine-tuning sin infraestructura propia.
- Regla: primero prompts, luego RAG, luego fine-tuning si es necesario.
