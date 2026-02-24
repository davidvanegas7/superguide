# IA Multimodal

Los modelos multimodales procesan y generan múltiples tipos de datos: texto, imágenes, audio y video. Representan la convergencia de la IA hacia sistemas que perciben el mundo como los humanos.

## ¿Qué es la IA multimodal?

```
┌────────────────────────────────────────────────┐
│              MODELO MULTIMODAL                  │
│                                                 │
│  ENTRADAS:              SALIDAS:                │
│  📝 Texto        ──┐   ┌──▶  📝 Texto          │
│  🖼️ Imágenes     ──┤   │                       │
│  🔊 Audio        ──┼──▶├──▶  🖼️ Imágenes       │
│  🎬 Video        ──┤   │                       │
│  📊 Datos        ──┘   └──▶  🔊 Audio          │
│                                                 │
│  Ejemplos:                                      │
│  - GPT-4o: texto + imagen + audio → texto       │
│  - Gemini 2.0: texto + imagen + video → texto   │
│  - DALL-E 3: texto → imagen                     │
│  - Sora: texto → video                          │
│  - Whisper: audio → texto                       │
└────────────────────────────────────────────────┘
```

## Visión: análisis de imágenes

### Con GPT-4o

```python
from openai import OpenAI
import base64

client = OpenAI()

# Opción 1: imagen desde URL
response = client.chat.completions.create(
    model="gpt-4o",
    messages=[{
        "role": "user",
        "content": [
            {"type": "text", "text": "¿Qué hay en esta imagen? Describe con detalle."},
            {"type": "image_url", "image_url": {
                "url": "https://example.com/foto.jpg",
                "detail": "high"  # "low", "high", o "auto"
            }}
        ]
    }],
    max_tokens=500,
)

print(response.choices[0].message.content)

# Opción 2: imagen en base64
with open("diagram.png", "rb") as f:
    base64_image = base64.b64encode(f.read()).decode()

response = client.chat.completions.create(
    model="gpt-4o",
    messages=[{
        "role": "user",
        "content": [
            {"type": "text", "text": "Explica este diagrama de arquitectura."},
            {"type": "image_url", "image_url": {
                "url": f"data:image/png;base64,{base64_image}"
            }}
        ]
    }],
)
```

### Casos de uso de vision

```python
# OCR avanzado: extraer texto de documentos
def extract_text_from_document(image_path):
    with open(image_path, "rb") as f:
        b64 = base64.b64encode(f.read()).decode()

    response = client.chat.completions.create(
        model="gpt-4o",
        messages=[{
            "role": "user",
            "content": [
                {"type": "text", "text": """Extrae TODO el texto de esta imagen.
                Mantén el formato y estructura original.
                Devuelve solo el texto extraído."""},
                {"type": "image_url", "image_url": {
                    "url": f"data:image/png;base64,{b64}"
                }}
            ]
        }]
    )
    return response.choices[0].message.content

# Análisis de gráficos y charts
def analyze_chart(chart_image_path):
    """Analiza un gráfico y extrae insights."""
    # GPT-4o puede leer barras, líneas, pie charts, etc.
    response = extract_from_image(
        chart_image_path,
        "Analiza este gráfico. ¿Cuáles son las tendencias principales? "
        "¿Qué datos específicos puedes extraer?"
    )
    return response
```

## Generación de imágenes

### DALL-E 3

```python
response = client.images.generate(
    model="dall-e-3",
    prompt="Un robot programador trabajando en una laptop, estilo acuarela",
    size="1024x1024",
    quality="hd",
    n=1,
)

image_url = response.data[0].url
revised_prompt = response.data[0].revised_prompt  # DALL-E mejora tu prompt
print(f"Prompt usado: {revised_prompt}")
```

### Stable Diffusion (open source)

```python
from diffusers import StableDiffusionXLPipeline
import torch

pipe = StableDiffusionXLPipeline.from_pretrained(
    "stabilityai/stable-diffusion-xl-base-1.0",
    torch_dtype=torch.float16,
)
pipe = pipe.to("cuda")

image = pipe(
    prompt="A futuristic city at sunset, cyberpunk style, 4k",
    negative_prompt="blurry, low quality, distorted",
    num_inference_steps=30,
    guidance_scale=7.5,
).images[0]

image.save("city.png")
```

## Audio: Speech-to-Text y Text-to-Speech

### Whisper (transcripción)

```python
# Whisper de OpenAI: transcripción multilingüe
audio_file = open("podcast.mp3", "rb")

transcript = client.audio.transcriptions.create(
    model="whisper-1",
    file=audio_file,
    language="es",
    response_format="verbose_json",
    timestamp_granularities=["segment"],
)

for segment in transcript.segments:
    print(f"[{segment['start']:.1f}s] {segment['text']}")

# Whisper local (open source)
import whisper

model = whisper.load_model("large-v3")
result = model.transcribe("audio.mp3", language="es")
print(result["text"])
```

### Text-to-Speech

```python
# OpenAI TTS
response = client.audio.speech.create(
    model="tts-1-hd",
    voice="nova",        # alloy, echo, fable, onyx, nova, shimmer
    input="Hola, soy una voz generada por inteligencia artificial.",
    speed=1.0,
)

response.stream_to_file("output.mp3")

# ElevenLabs (alta calidad, clonación de voz)
from elevenlabs import generate, play

audio = generate(
    text="Este es un ejemplo de voz clonada.",
    voice="tu_voz_clonada",
    model="eleven_multilingual_v2",
)
play(audio)
```

## Video: Generación y análisis

```python
# Análisis de video con Gemini 2.0
import google.generativeai as genai

model = genai.GenerativeModel("gemini-2.0-flash")

# Subir video
video_file = genai.upload_file("demo.mp4")

# Analizar
response = model.generate_content([
    video_file,
    "Describe lo que ocurre en este video paso a paso."
])
print(response.text)

# Generación de video (Sora y similares)
# OpenAI Sora: genera videos de hasta 60s desde texto
# RunwayML Gen-3: genera clips de 10s
# Nota: estas APIs son de acceso limitado (2025)
```

## Modelos multimodales nativos

```python
# GPT-4o: nativo multimodal (texto + imagen + audio)
# Una sola llamada puede procesar múltiples modalidades

response = client.chat.completions.create(
    model="gpt-4o-audio-preview",
    modalities=["text", "audio"],
    audio={"voice": "alloy", "format": "wav"},
    messages=[{
        "role": "user",
        "content": "Explica qué es la fotosíntesis en una oración."
    }]
)

# Respuesta en texto Y audio simultáneamente
print(response.choices[0].message.content)       # Texto
audio_data = response.choices[0].message.audio    # Audio WAV

# Gemini 2.0: procesa texto, images, audio, video, código
# en una sola API unificada
```

## Resumen

- Los modelos multimodales procesan texto, imágenes, audio y video.
- GPT-4o y Gemini 2.0 son nativamente multimodales.
- Vision: OCR, análisis de diagramas, descripción de imágenes.
- Audio: Whisper para transcripción, TTS para generación de voz.
- Generación de imágenes: DALL-E 3, Stable Diffusion (open source).
- Video: análisis con Gemini, generación temprana con Sora.
- La tendencia es hacia modelos unificados que manejan todas las modalidades.
