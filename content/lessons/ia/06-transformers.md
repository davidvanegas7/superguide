# La Arquitectura Transformer

El paper "Attention Is All You Need" (Vaswani et al., 2017) introdujo los Transformers, la arquitectura que fundamenta todos los LLMs modernos. Reemplazó a las RNNs al procesar secuencias completas en paralelo usando el mecanismo de atención.

## ¿Por qué Transformers?

Las RNNs procesan tokens uno por uno (secuencialmente). Los Transformers procesan **todos los tokens a la vez** gracias a la atención, lo que permite:

- **Paralelización masiva**: se aprovechan GPUs/TPUs al máximo.
- **Captura de dependencias largas**: un token puede "atender" directamente a cualquier otro, sin importar la distancia.
- **Escalabilidad**: escalar a billones de parámetros es viable.

## Self-Attention: el corazón del Transformer

La self-attention permite que cada token calcule cuánta "atención" debe prestar a cada otro token de la secuencia:

```python
import torch
import torch.nn.functional as F

def self_attention(Q, K, V, mask=None):
    """
    Q, K, V: [batch, seq_len, d_k]
    Attention(Q,K,V) = softmax(QK^T / √d_k) V
    """
    d_k = Q.size(-1)
    scores = torch.matmul(Q, K.transpose(-2, -1)) / (d_k ** 0.5)

    if mask is not None:
        scores = scores.masked_fill(mask == 0, float('-inf'))

    attention_weights = F.softmax(scores, dim=-1)
    output = torch.matmul(attention_weights, V)
    return output, attention_weights

# Ejemplo: secuencia de 4 tokens, dimensión 8
Q = K = V = torch.randn(1, 4, 8)
output, weights = self_attention(Q, K, V)
print(f"Output shape: {output.shape}")      # [1, 4, 8]
print(f"Weights shape: {weights.shape}")    # [1, 4, 4]
```

Intuitivamente: en la frase "El gato se sentó en la alfombra porque **estaba** cansado", la atención conectaría "estaba" con "gato" para entender que el gato es quien estaba cansado.

## Multi-Head Attention

En lugar de una sola atención, usamos múltiples "cabezas" que capturan diferentes tipos de relaciones:

```python
class MultiHeadAttention(torch.nn.Module):
    def __init__(self, d_model=512, n_heads=8):
        super().__init__()
        self.n_heads = n_heads
        self.d_k = d_model // n_heads

        self.W_q = torch.nn.Linear(d_model, d_model)
        self.W_k = torch.nn.Linear(d_model, d_model)
        self.W_v = torch.nn.Linear(d_model, d_model)
        self.W_o = torch.nn.Linear(d_model, d_model)

    def forward(self, Q, K, V, mask=None):
        batch_size = Q.size(0)

        # Proyecciones lineales y reshape a múltiples cabezas
        Q = self.W_q(Q).view(batch_size, -1, self.n_heads, self.d_k).transpose(1, 2)
        K = self.W_k(K).view(batch_size, -1, self.n_heads, self.d_k).transpose(1, 2)
        V = self.W_v(V).view(batch_size, -1, self.n_heads, self.d_k).transpose(1, 2)

        # Atención por cabeza
        output, _ = self_attention(Q, K, V, mask)

        # Concatenar cabezas y proyectar
        output = output.transpose(1, 2).contiguous().view(batch_size, -1, self.n_heads * self.d_k)
        return self.W_o(output)
```

Cada cabeza puede enfocarse en diferentes aspectos: sintaxis, semántica, correferencia, posición.

## Positional Encoding

Como los Transformers procesan todos los tokens a la vez (sin noción de orden), necesitan codificación posicional:

```python
class PositionalEncoding(torch.nn.Module):
    def __init__(self, d_model, max_len=5000):
        super().__init__()
        pe = torch.zeros(max_len, d_model)
        position = torch.arange(0, max_len).unsqueeze(1).float()
        div_term = torch.exp(
            torch.arange(0, d_model, 2).float() * -(torch.log(torch.tensor(10000.0)) / d_model)
        )
        pe[:, 0::2] = torch.sin(position * div_term)  # Posiciones pares
        pe[:, 1::2] = torch.cos(position * div_term)  # Posiciones impares
        self.register_buffer('pe', pe.unsqueeze(0))

    def forward(self, x):
        return x + self.pe[:, :x.size(1)]
```

Los LLMs modernos usan variantes como **RoPE** (Rotary Position Embedding) que permite extender el contexto de manera más eficiente.

## Encoder vs Decoder

El Transformer original tiene dos componentes:

```
┌─────────────────────────────────────────────────────┐
│                  TRANSFORMER                         │
│                                                      │
│  ┌──────────────┐          ┌──────────────────┐     │
│  │   ENCODER    │          │    DECODER        │     │
│  │              │          │                   │     │
│  │ Self-Attn    │──cross──▶│ Masked Self-Attn  │     │
│  │ Feed-Forward │  attn    │ Cross-Attention   │     │
│  │ × N capas    │          │ Feed-Forward      │     │
│  │              │          │ × N capas         │     │
│  └──────────────┘          └──────────────────┘     │
│                                                      │
│  Entrada: "Hola mundo"    Salida: "Hello world"      │
└──────────────────────────────────────────────────────┘
```

### Variantes de arquitectura

| Tipo | Descripción | Modelos |
|------|------------|---------|
| **Encoder-only** | Bidireccional, entiende contexto completo | BERT, RoBERTa |
| **Decoder-only** | Autoregresivo, genera token por token | GPT-4, Claude, Llama |
| **Encoder-Decoder** | Seq2seq, entrada → salida | T5, BART, Whisper |

Los LLMs modernos (GPT-4, Claude, Llama, Gemini) usan **decoder-only** porque escala mejor y las tareas de comprensión pueden reformularse como generación.

## Transformer Decoder Block

```python
class TransformerBlock(torch.nn.Module):
    def __init__(self, d_model=512, n_heads=8, d_ff=2048, dropout=0.1):
        super().__init__()
        self.attention = MultiHeadAttention(d_model, n_heads)
        self.norm1 = torch.nn.LayerNorm(d_model)
        self.norm2 = torch.nn.LayerNorm(d_model)
        self.ffn = torch.nn.Sequential(
            torch.nn.Linear(d_model, d_ff),
            torch.nn.GELU(),
            torch.nn.Linear(d_ff, d_model),
            torch.nn.Dropout(dropout),
        )
        self.dropout = torch.nn.Dropout(dropout)

    def forward(self, x, mask=None):
        # Self-attention con residual connection + layer norm
        attn_output = self.attention(x, x, x, mask)
        x = self.norm1(x + self.dropout(attn_output))

        # Feed-forward con residual + norm
        ff_output = self.ffn(x)
        x = self.norm2(x + ff_output)
        return x
```

## Escala de los Transformers modernos

| Modelo | Parámetros | Capas | d_model | Cabezas |
|--------|-----------|-------|---------|---------|
| GPT-2 | 1.5B | 48 | 1600 | 25 |
| GPT-3 | 175B | 96 | 12288 | 96 |
| GPT-4 (estimado) | ~1.8T (MoE) | ~120 | ~12288 | ~128 |
| Llama 3.1 405B | 405B | 126 | 16384 | 128 |
| Claude 3.5 Sonnet | No público | - | - | - |

## Resumen

- Self-attention permite que cada token "vea" toda la secuencia simultáneamente.
- Multi-head attention captura diferentes tipos de relaciones.
- Positional encoding inyecta información de posición.
- Los LLMs modernos usan decoder-only Transformers.
- La escala (más parámetros + más datos) produce capacidades emergentes.
