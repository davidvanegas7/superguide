# Prompt Engineering

Prompt engineering es el arte de comunicarse efectivamente con LLMs para obtener los mejores resultados. Es la habilidad más importante para trabajar con IA en 2025-2026.

## Anatomía de un prompt

```python
# Estructura básica de un prompt
prompt = {
    "system": "Contexto y rol del asistente",
    "user": "Instrucción específica del usuario",
    "examples": "Ejemplos de entrada/salida deseada (opcional)",
    "constraints": "Restricciones y formato deseado"
}
```

## Técnicas fundamentales

### Zero-Shot Prompting

Sin ejemplos previos, el modelo usa su conocimiento general:

```python
messages = [
    {"role": "system", "content": "Eres un clasificador de sentimientos."},
    {"role": "user", "content": """
Clasifica el sentimiento del siguiente texto como POSITIVO, NEGATIVO o NEUTRO.

Texto: "La película fue aburrida pero la actuación estuvo genial."
Sentimiento:"""}
]
# Respuesta: NEUTRO (o MIXTO)
```

### Few-Shot Prompting

Proporcionas ejemplos para guiar el formato y estilo:

```python
messages = [
    {"role": "user", "content": """
Clasifica el sentimiento:

Texto: "Me encantó la comida"
Sentimiento: POSITIVO

Texto: "El servicio fue terrible"
Sentimiento: NEGATIVO

Texto: "La película fue aburrida pero la actuación estuvo genial"
Sentimiento:"""}
]
# Respuesta: MIXTO
```

### Chain-of-Thought (CoT)

Pedir al modelo que razone paso a paso mejora dramáticamente la precisión en tareas de lógica:

```python
# Sin CoT
prompt_sin_cot = "¿Cuántos números primos hay entre 20 y 40?"
# Respuesta: "5" (posible error)

# Con CoT
prompt_con_cot = """¿Cuántos números primos hay entre 20 y 40?

Piensa paso a paso:
1. Lista los números del 20 al 40
2. Para cada uno, verifica si es primo
3. Cuenta los primos encontrados"""
# Respuesta: 23, 29, 31, 37 → 4 primos (correcto)
```

### Tree-of-Thought (ToT)

Explorar múltiples caminos de razonamiento:

```python
prompt_tot = """
Resuelve este problema explorando tres enfoques diferentes.
Para cada enfoque, evalúa si lleva a la solución correcta.
Elige el mejor enfoque y presenta la respuesta final.

Problema: [tu problema aquí]

Enfoque 1: ...
Evaluación: ...

Enfoque 2: ...
Evaluación: ...

Enfoque 3: ...
Evaluación: ...

Mejor enfoque y respuesta final: ...
"""
```

## System Prompts efectivos

```python
# System prompt para asistente de código
system_prompt = """Eres un programador senior experto en Python.

REGLAS:
1. Escribe código limpio, bien documentado y con type hints.
2. Incluye docstrings en todas las funciones.
3. Sugiere tests cuando sea apropiado.
4. Si no estás seguro, dilo explícitamente.
5. Explica tu razonamiento antes del código.

FORMATO DE RESPUESTA:
- Explicación breve del problema
- Solución con código
- Ejemplo de uso
- Posibles mejoras
"""

# System prompt para extracción de datos
system_prompt_extraction = """Eres un extractor de datos estructurados.
Responde SOLO con JSON válido. Sin texto adicional.
Si un campo no se puede determinar, usa null.

Schema:
{
    "nombre": string,
    "email": string | null,
    "empresa": string | null,
    "cargo": string | null
}"""
```

## Parámetros de generación

```python
response = client.chat.completions.create(
    model="gpt-4o",
    messages=messages,
    temperature=0.7,      # Creatividad: 0=determinístico, 2=muy aleatorio
    max_tokens=1000,       # Máximo de tokens a generar
    top_p=0.9,            # Nucleus sampling: considera top 90% de probabilidad
    frequency_penalty=0.5, # Penaliza repetición de tokens
    presence_penalty=0.3,  # Fomenta hablar de temas nuevos
    stop=["\n\n"],        # Tokens de parada personalizados
)
```

| Parámetro | Valor bajo | Valor alto |
|-----------|-----------|-----------|
| **temperature** | Respuestas consistentes, factuales | Creativo, diverso, posible alucinación |
| **top_p** | Vocabulario restringido | Vocabulario amplio |
| **frequency_penalty** | Permite repetición | Evita repetición |

Recomendaciones:
- **Código / datos**: temperature=0, top_p=1
- **Escritura creativa**: temperature=0.8-1.0
- **Conversación general**: temperature=0.7

## Structured Output

Forzar al modelo a responder en un formato específico:

```python
# Con OpenAI Structured Outputs
from pydantic import BaseModel

class MovieReview(BaseModel):
    title: str
    rating: float
    sentiment: str
    summary: str

response = client.beta.chat.completions.parse(
    model="gpt-4o",
    messages=[
        {"role": "user", "content": "Analiza esta reseña: 'Inception es una obra maestra de Nolan'"}
    ],
    response_format=MovieReview,
)

review = response.choices[0].message.parsed
print(review.title)      # "Inception"
print(review.rating)     # 9.5
print(review.sentiment)  # "positivo"
```

## Prompts para programación

```python
# Prompt para generar código con tests
coding_prompt = """
Implementa una clase `LRUCache` en Python con las siguientes especificaciones:

REQUISITOS:
- Constructor recibe `capacity: int`
- Método `get(key) -> int | -1` si no existe
- Método `put(key, value)` agrega o actualiza
- Si la capacidad se excede, elimina el elemento menos recientemente usado
- Operaciones en O(1)

RESTRICCIONES:
- Usa `OrderedDict` de collections
- Incluye type hints
- Incluye docstring
- Escribe al menos 3 tests con assert

Responde solo con el código Python.
"""
```

## Técnicas avanzadas

### Prompt Chaining

Dividir tareas complejas en pasos encadenados:

```python
# Paso 1: Extraer información
extract_result = llm("Extrae los puntos clave de este artículo: ...")

# Paso 2: Analizar
analysis = llm(f"Analiza estos puntos clave: {extract_result}")

# Paso 3: Generar resumen
summary = llm(f"Genera un resumen ejecutivo basado en: {analysis}")
```

### Self-Consistency

Generar múltiples respuestas y elegir la más frecuente:

```python
def self_consistent_answer(prompt, n=5, temperature=0.7):
    responses = []
    for _ in range(n):
        response = llm(prompt, temperature=temperature)
        responses.append(response)

    # Voto mayoritario
    from collections import Counter
    return Counter(responses).most_common(1)[0][0]
```

## Resumen

- Zero-shot para tareas simples, few-shot cuando necesitas formato específico.
- Chain-of-thought mejora razonamiento: pide "piensa paso a paso".
- Temperature baja para precisión (código, datos), alta para creatividad.
- System prompts definen el comportamiento del modelo.
- Structured outputs (Pydantic, JSON Schema) para respuestas parseables.
- Prompt chaining divide problemas complejos en pasos manejables.
