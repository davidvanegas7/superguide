<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class AIQuizSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'ia-llms')->first();

        if (! $course) {
            $this->command->warn('AI/LLMs course not found. Run CourseSeeder first.');
            return;
        }

        $quiz = Quiz::updateOrCreate(
            ['course_id' => $course->id],
            [
                'title'       => 'Evaluación: Inteligencia Artificial y LLMs',
                'description' => 'Pon a prueba tus conocimientos sobre IA, machine learning, transformers, LLMs, RAG, agentes, prompt engineering y más.',
                'published'   => true,
            ]
        );

        $quiz->questions()->each(fn ($q) => $q->options()->delete());
        $quiz->questions()->delete();

        foreach ($this->questions() as $i => $q) {
            $question = QuizQuestion::create([
                'quiz_id'     => $quiz->id,
                'question'    => $q['question'],
                'explanation' => $q['explanation'],
                'sort_order'  => $i + 1,
            ]);

            foreach ($q['options'] as $j => $opt) {
                QuizOption::create([
                    'quiz_question_id' => $question->id,
                    'text'             => $opt['text'],
                    'is_correct'       => $opt['correct'],
                    'sort_order'       => $j + 1,
                ]);
            }
        }

        $this->command->info("AI/LLMs quiz seeded: {$quiz->questions()->count()} preguntas.");
    }

    private function questions(): array
    {
        return [

            // ── 1 · Introducción a la IA ──────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia fundamental entre ANI (Artificial Narrow Intelligence) y AGI (Artificial General Intelligence)?',
                'explanation' => 'ANI se especializa en una tarea específica (reconocer imágenes, jugar ajedrez, generar texto), mientras que AGI tendría capacidad de aprender y razonar en cualquier dominio como un humano. Todos los modelos actuales (GPT-4o, Claude, Gemini) son ANI, aunque cada vez más capaces. AGI sigue siendo un objetivo teórico no alcanzado.',
                'options'     => [
                    ['text' => 'ANI resuelve tareas específicas; AGI (aún teórica) razonaría en cualquier dominio como un humano. Todo lo actual es ANI', 'correct' => true],
                    ['text' => 'ANI usa redes neuronales y AGI usa algoritmos clásicos', 'correct' => false],
                    ['text' => 'AGI ya fue alcanzada con GPT-4 y Claude 3.5', 'correct' => false],
                    ['text' => 'ANI es más potente que AGI porque está más enfocada', 'correct' => false],
                ],
            ],

            // ── 2 · Fundamentos de ML ─────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre supervised learning y unsupervised learning?',
                'explanation' => 'Supervised learning entrena con datos etiquetados (input → output conocido): clasificación, regresión. Unsupervised learning encuentra patrones en datos sin etiquetas: clustering (K-Means), reducción de dimensionalidad (PCA), detección de anomalías. Existe también semi-supervised y self-supervised (usado en LLMs: predecir next token).',
                'options'     => [
                    ['text' => 'Supervised usa datos etiquetados (input→output); unsupervised encuentra patrones en datos sin etiquetas. LLMs usan self-supervised', 'correct' => true],
                    ['text' => 'Supervised necesita un humano supervisando; unsupervised entrena solo', 'correct' => false],
                    ['text' => 'No hay diferencia real; son nombres distintos para lo mismo', 'correct' => false],
                    ['text' => 'Unsupervised es siempre mejor porque no necesita etiquetas', 'correct' => false],
                ],
            ],

            // ── 3 · Redes Neuronales ──────────────────────────────────
            [
                'question'    => '¿Qué es backpropagation y por qué es esencial para entrenar redes neuronales?',
                'explanation' => 'Backpropagation calcula el gradiente del loss con respecto a cada peso, propagando el error hacia atrás desde la salida. Con gradient descent, ajusta los pesos para minimizar el error. Sin backprop, sería imposible entrenar redes profundas eficientemente. Usa la regla de la cadena del cálculo diferencial.',
                'options'     => [
                    ['text' => 'Calcula gradientes del error propagándolos hacia atrás con la regla de la cadena, permitiendo ajustar pesos con gradient descent', 'correct' => true],
                    ['text' => 'Es un algoritmo que propaga datos de entrada hacia adelante por la red', 'correct' => false],
                    ['text' => 'Es un tipo de función de activación para capas ocultas', 'correct' => false],
                    ['text' => 'Elimina neuronas que no contribuyen al resultado', 'correct' => false],
                ],
            ],

            // ── 4 · Deep Learning ─────────────────────────────────────
            [
                'question'    => '¿Por qué las CNNs son efectivas para visión y las RNNs/LSTMs fueron reemplazadas por transformers en NLP?',
                'explanation' => 'Las CNNs capturan patrones espaciales con filtros locales (bordes, texturas), ideales para imágenes. RNNs procesan secuencias pero sufren de vanishing gradients y no paralelizan. LSTMs mejoraron gradients pero siguen siendo secuenciales. Los transformers resolvieron ambos problemas con self-attention parallelizable y contexto ilimitado.',
                'options'     => [
                    ['text' => 'CNNs capturan patrones espaciales; RNNs eran secuenciales (lentas, vanishing gradients). Transformers paralelizan con self-attention', 'correct' => true],
                    ['text' => 'CNNs y RNNs son igual de efectivas; los transformers solo son marketing', 'correct' => false],
                    ['text' => 'Las CNNs fueron inventadas después de los transformers', 'correct' => false],
                    ['text' => 'Las RNNs son mejores que transformers pero más caras de entrenar', 'correct' => false],
                ],
            ],

            // ── 5 · NLP ───────────────────────────────────────────────
            [
                'question'    => '¿Qué problema resuelven los word embeddings (Word2Vec, GloVe) y cómo evolucionaron hacia embeddings contextuales?',
                'explanation' => 'Word embeddings representan palabras como vectores densos donde la distancia refleja similitud semántica ("rey" - "hombre" + "mujer" ≈ "reina"). Pero cada palabra tiene UN solo vector. Embeddings contextuales (BERT, GPT) generan vectores diferentes según el contexto: "banco" (financiero) vs "banco" (parque).',
                'options'     => [
                    ['text' => 'Embeddings estáticos: un vector fijo por palabra. Contextuales (BERT/GPT): vectores dinámicos según contexto, capturando polisemia', 'correct' => true],
                    ['text' => 'Embeddings solo sirven para traducción automática, no para otros usos', 'correct' => false],
                    ['text' => 'Word2Vec genera una imagen por cada palabra', 'correct' => false],
                    ['text' => 'Los embeddings son obsoletos; LLMs no los usan', 'correct' => false],
                ],
            ],

            // ── 6 · Arquitectura Transformer ──────────────────────────
            [
                'question'    => '¿Cómo funciona el mecanismo de self-attention en un transformer?',
                'explanation' => 'Self-attention calcula relevancia entre todos los pares de tokens. Cada token genera Query (Q), Key (K) y Value (V) mediante matrices aprendidas. Attention = softmax(QK^T / √d_k) × V. Esto permite que cada token "atienda" a cualquier otro directamente, capturando dependencias de largo alcance. Multi-head attention repite esto con diferentes proyecciones.',
                'options'     => [
                    ['text' => 'Cada token genera Q, K, V; calcula scores QK^T/√d_k con softmax; permite atender a cualquier token directamente sin distancia', 'correct' => true],
                    ['text' => 'Procesa tokens uno por uno de izquierda a derecha como una RNN', 'correct' => false],
                    ['text' => 'Usa convoluciones para relacionar tokens cercanos entre sí', 'correct' => false],
                    ['text' => 'Calcula la media de todos los embeddings sin ponderar', 'correct' => false],
                ],
            ],

            // ── 7 · LLMs ─────────────────────────────────────────────
            [
                'question'    => '¿Qué son las scaling laws y por qué fueron determinantes para el desarrollo de LLMs?',
                'explanation' => 'Las scaling laws (Kaplan et al., Chinchilla) demostrararon que la performance de LLMs escala de forma predecible (ley de potencias) con: parámetros del modelo, cantidad de datos, y compute utilizado. Esto permitió planificar entrenamiento: Chinchilla mostró que modelos más pequeños con más datos superan a modelos grandes subentrenados.',
                'options'     => [
                    ['text' => 'La performance escala predeciblemente con parámetros, datos y compute (leyes de potencia). Chinchilla demostró la proporción óptima datos/params', 'correct' => true],
                    ['text' => 'Las scaling laws dicen que el doble de parámetros siempre da el doble de calidad', 'correct' => false],
                    ['text' => 'Son reglas para decidir cuántas GPUs comprar', 'correct' => false],
                    ['text' => 'Solo aplican a modelos de OpenAI, no a open source', 'correct' => false],
                ],
            ],

            // ── 8 · Entrenamiento de LLMs ─────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia entre RLHF y DPO para alinear un LLM?',
                'explanation' => 'RLHF (Reinforcement Learning from Human Feedback) entrena un reward model con preferencias humanas y luego usa PPO para optimizar la policy del LLM. DPO (Direct Preference Optimization) simplifica esto: optimiza directamente con los datos de preferencias, sin reward model separado ni RL. DPO es más estable y simple, usado por Claude y Llama 3.',
                'options'     => [
                    ['text' => 'RLHF necesita reward model + PPO (complejo). DPO optimiza directamente de las preferencias, sin RL; más simple y estable', 'correct' => true],
                    ['text' => 'RLHF es para texto y DPO es para imágenes', 'correct' => false],
                    ['text' => 'Son nombres distintos para el mismo algoritmo', 'correct' => false],
                    ['text' => 'DPO es versión anterior a RLHF y ya no se usa', 'correct' => false],
                ],
            ],

            // ── 9 · Prompt Engineering ────────────────────────────────
            [
                'question'    => '¿Qué es Chain-of-Thought (CoT) prompting y por qué mejora el razonamiento?',
                'explanation' => 'CoT pide al modelo mostrar su proceso de razonamiento paso a paso antes de la respuesta final. "Piensa paso a paso" o ejemplos con razonamiento explícito. Mejora significativamente tareas de matemáticas, lógica y coding. Funciona porque fuerza al modelo a descomponer problemas y usar tokens intermedios como "memoria de trabajo".',
                'options'     => [
                    ['text' => 'Pide razonamiento paso a paso; el modelo descompone el problema y usa tokens intermedios como memoria de trabajo, mejorando lógica y matemáticas', 'correct' => true],
                    ['text' => 'Es una técnica para que el modelo genere respuestas más cortas', 'correct' => false],
                    ['text' => 'Chain-of-Thought solo funciona con GPT-4, no con otros modelos', 'correct' => false],
                    ['text' => 'Es lo mismo que few-shot prompting, solo con otro nombre', 'correct' => false],
                ],
            ],

            // ── 10 · RAG ──────────────────────────────────────────────
            [
                'question'    => '¿Cómo funciona RAG y qué problema resuelve en comparación con solo usar un LLM?',
                'explanation' => 'RAG combina retrieval (búsqueda) con generation (LLM). Primero busca documentos relevantes en un vector store usando embeddings, luego los pasa como contexto al LLM. Resuelve: conocimiento desactualizado, alucinaciones (el LLM puede citar fuentes), y acceso a datos privados sin fine-tuning. Más barato y rápido que re-entrenar.',
                'options'     => [
                    ['text' => 'Busca documentos relevantes con embeddings y los da como contexto al LLM; resuelve datos desactualizados, alucinaciones y acceso a datos privados', 'correct' => true],
                    ['text' => 'RAG re-entrena el modelo cada vez que hay una pregunta nueva', 'correct' => false],
                    ['text' => 'RAG solo funciona con bases de datos SQL, no con documentos', 'correct' => false],
                    ['text' => 'Es una alternativa a los LLMs que no usa redes neuronales', 'correct' => false],
                ],
            ],

            // ── 11 · Fine-tuning ──────────────────────────────────────
            [
                'question'    => '¿Qué es LoRA y por qué revolucionó el fine-tuning de LLMs?',
                'explanation' => 'LoRA (Low-Rank Adaptation) congela los pesos originales y agrega matrices de bajo rango (A×B) en capas de atención. En lugar de actualizar millones de parámetros, entrena solo miles. Reduce memoria GPU de 80GB a 16GB. QLoRA agrega cuantización a 4-bit. Se pueden mergear los adapters o cargarlos dinámicamente por tarea.',
                'options'     => [
                    ['text' => 'Congela pesos originales y entrena matrices de bajo rango; reduce 10-100x la memoria necesaria. QLoRA agrega cuantización a 4-bit', 'correct' => true],
                    ['text' => 'LoRA elimina capas innecesarias del modelo para hacerlo más pequeño', 'correct' => false],
                    ['text' => 'Es un método de entrenamiento que solo funciona con TPUs de Google', 'correct' => false],
                    ['text' => 'LoRA re-entrena todos los parámetros pero de forma más rápida', 'correct' => false],
                ],
            ],

            // ── 12 · APIs de LLMs ─────────────────────────────────────
            [
                'question'    => '¿Cuál es la diferencia principal entre usar una API comercial (OpenAI, Anthropic) y ejecutar un modelo open source con Ollama/vLLM?',
                'explanation' => 'APIs comerciales: sin infraestructura, pago por token, modelos frontier (GPT-4o, Claude 3.5 Sonnet), pero datos salen a terceros. Open source local (Llama 3.1, Mistral via Ollama/vLLM): privacidad total, costo fijo por GPU, latencia predecible, pero requiere hardware (GPUs), mantenimiento, y modelos generalmente menores en capacidad.',
                'options'     => [
                    ['text' => 'API: sin infra, pago por token, modelos frontier, datos a terceros. Local: privacidad total, costo fijo, requiere GPUs, menor capacidad general', 'correct' => true],
                    ['text' => 'No hay diferencia; los modelos open source son igual de buenos que GPT-4o', 'correct' => false],
                    ['text' => 'Las APIs son siempre más baratas que ejecutar modelos localmente', 'correct' => false],
                    ['text' => 'Ollama solo funciona en Linux y no soporta GPUs', 'correct' => false],
                ],
            ],

            // ── 13 · Agentes de IA ────────────────────────────────────
            [
                'question'    => '¿Qué es function calling / tool use en LLMs y cómo habilita agentes de IA?',
                'explanation' => 'Function calling permite al LLM generar llamadas estructuradas a funciones/APIs externas en lugar de solo texto. El LLM decide CUÁNDO y CON QUÉ parámetros llamar a tools (buscar web, ejecutar código, consultar BD). El patrón ReAct (Reason+Act) alterna razonamiento y acciones. MCP (Model Context Protocol) estandariza la conexión con herramientas.',
                'options'     => [
                    ['text' => 'El LLM genera llamadas estructuradas a herramientas externas; con ReAct alterna razonamiento y acciones. MCP estandariza la integración', 'correct' => true],
                    ['text' => 'Function calling es simplemente darle instrucciones al LLM en el prompt', 'correct' => false],
                    ['text' => 'Los agentes no necesitan LLMs; funcionan solo con reglas if/else', 'correct' => false],
                    ['text' => 'Solo GPT-4 soporta function calling; ningún otro modelo lo tiene', 'correct' => false],
                ],
            ],

            // ── 14 · Embeddings y Búsqueda Semántica ──────────────────
            [
                'question'    => '¿Cómo funciona la búsqueda semántica con embeddings y por qué supera a la búsqueda por keywords?',
                'explanation' => 'La búsqueda semántica convierte texto a vectores con modelos de embeddings, luego calcula similitud coseno entre la query y los documentos. Captura significado: "auto" encuentra "vehículo", "coche", "automóvil". Keyword search solo matchea palabras exactas. Búsqueda híbrida combina ambas (BM25 + cosine) para mejor recall.',
                'options'     => [
                    ['text' => 'Convierte texto a vectores, usa similitud coseno para capturar significado; "auto" encuentra "vehículo". Búsqueda híbrida combina con BM25', 'correct' => true],
                    ['text' => 'Busca sinónimos en un diccionario y los agrega a la query de keywords', 'correct' => false],
                    ['text' => 'Semantic search solo funciona en inglés, no en español', 'correct' => false],
                    ['text' => 'Es más lento que keywords y siempre menos preciso', 'correct' => false],
                ],
            ],

            // ── 15 · Evaluación de Modelos ────────────────────────────
            [
                'question'    => '¿Por qué los benchmarks estáticos (MMLU, HumanEval) son cada vez menos confiables para comparar LLMs?',
                'explanation' => 'Los benchmarks estáticos se contaminan: los datos aparecen en el training set (data contamination). Los modelos memorizan respuestas sin entenderlas realmente. Por eso surgen: benchmarks dinámicos (LiveBench, Chatbot Arena con humanos), evaluación con LLM-as-Judge, y benchmarks de dificultad creciente (SWE-Bench Verified, GPQA).',
                'options'     => [
                    ['text' => 'Se contaminan (datos filtrados al training set) y los modelos memorizan. Dinámicos (Arena, LiveBench) y LLM-as-Judge los complementan', 'correct' => true],
                    ['text' => 'Son perfectamente confiables; la industria los usa sin problemas', 'correct' => false],
                    ['text' => 'Los benchmarks se renuevan diariamente y nunca se contaminan', 'correct' => false],
                    ['text' => 'Solo son poco confiables para modelos open source, no para comerciales', 'correct' => false],
                ],
            ],

            // ── 16 · Ética, Seguridad y Regulación ────────────────────
            [
                'question'    => '¿Qué es un prompt injection attack y por qué es difícil de prevenir?',
                'explanation' => 'Prompt injection inyecta instrucciones maliciosas que sobreescriben el system prompt. Indirecto: inyectado en documentos que el LLM procesa (via RAG, email). Es difícil de prevenir porque el modelo no distingue entre instrucciones del desarrollador y contenido del usuario al nivel fundamental. Mitigaciones: sandboxing, validación de outputs, boundaries claras.',
                'options'     => [
                    ['text' => 'Inyecta instrucciones que sobreescriben el system prompt; difícil porque el LLM no distingue instrucciones de contenido a nivel fundamental', 'correct' => true],
                    ['text' => 'Es un ataque de SQL injection adaptado para LLMs', 'correct' => false],
                    ['text' => 'Solo afecta a chatbots, no a aplicaciones con APIs', 'correct' => false],
                    ['text' => 'Se resuelve completamente con un buen system prompt', 'correct' => false],
                ],
            ],

            // ── 17 · IA Multimodal ────────────────────────────────────
            [
                'question'    => '¿Qué significa que un modelo sea "natively multimodal" vs "multimodal via pipeline"?',
                'explanation' => 'Natively multimodal (GPT-4o, Gemini 2.0) procesa texto, imagen, audio y video en un solo modelo con un encoder unificado. Pipeline multimodal conecta modelos separados: OCR → texto, Whisper → texto, CLIP → embeddings. El nativo entiende relaciones cross-modal ("qué dice la persona en este video"), el pipeline pierde contexto entre modalidades.',
                'options'     => [
                    ['text' => 'Nativo: un modelo con encoder unificado para todas las modalidades. Pipeline: modelos separados conectados; pierde contexto cross-modal', 'correct' => true],
                    ['text' => 'No hay diferencia real; todos los modelos multimodales funcionan igual', 'correct' => false],
                    ['text' => 'Pipeline es siempre mejor porque cada modelo se especializa', 'correct' => false],
                    ['text' => 'Natively multimodal solo procesa texto e imágenes, nunca audio', 'correct' => false],
                ],
            ],

            // ── 18 · Deploy y Optimización ────────────────────────────
            [
                'question'    => '¿Qué es la cuantización de modelos y cuáles son los trade-offs?',
                'explanation' => 'Cuantización reduce la precisión de los pesos: de FP32/FP16 a INT8 o INT4. GPTQ, AWQ, GGUF son formatos populares. Reduce tamaño del modelo 2-4x y aumenta velocidad de inferencia. Trade-off: pérdida mínima de calidad en INT8, más notable en INT4. Permite correr Llama 3.1 70B en una GPU de 24GB consumer en lugar de 4x A100.',
                'options'     => [
                    ['text' => 'Reduce precisión de pesos (FP16→INT8/INT4); reduce tamaño 2-4x y acelera inferencia. Pérdida mínima en INT8, más notable en INT4', 'correct' => true],
                    ['text' => 'Elimina parámetros innecesarios del modelo (pruning)', 'correct' => false],
                    ['text' => 'Cuantización siempre destruye la calidad del modelo', 'correct' => false],
                    ['text' => 'Solo funciona con modelos de menos de 7B parámetros', 'correct' => false],
                ],
            ],

            // ── 19 · Preguntas de Entrevista ──────────────────────────
            [
                'question'    => '¿Cuándo elegirías RAG sobre fine-tuning para personalizar un LLM y viceversa?',
                'explanation' => 'RAG: datos que cambian frecuentemente, necesidad de citar fuentes, sin GPU para entrenamiento, datos sensibles que no puedes enviar para entrenar. Fine-tuning: necesitas cambiar el estilo/comportamiento del modelo, dominio muy especializado (médico, legal), formato de output específico, o latencia crítica (sin retrieval). A menudo se combinan.',
                'options'     => [
                    ['text' => 'RAG: datos dinámicos, citar fuentes, sin GPU. Fine-tuning: cambiar estilo/comportamiento, dominio especializado, latencia crítica. Se combinan', 'correct' => true],
                    ['text' => 'Siempre fine-tuning; RAG es solo para prototipos rápidos', 'correct' => false],
                    ['text' => 'Siempre RAG; fine-tuning es obsoleto desde GPT-4', 'correct' => false],
                    ['text' => 'Fine-tuning es para texto, RAG es para imágenes', 'correct' => false],
                ],
            ],

            // ── 20 · Pregunta integradora ─────────────────────────────
            [
                'question'    => '¿Cómo diseñarías un sistema de IA para responder preguntas sobre la documentación interna de una empresa?',
                'explanation' => 'Pipeline completo: 1) Ingesta: procesar PDFs/Notion/Confluence → chunking inteligente. 2) Embeddings: modelo como text-embedding-3-large, almacenar en vector DB (Pinecone/Qdrant). 3) Retrieval: búsqueda híbrida (semantic + BM25) + re-ranking. 4) Generation: LLM con contexto retrieval + system prompt con reglas de la empresa. 5) Evaluación: RAGAS, feedback de usuarios. 6) Guardrails: no revelar datos sensibles, detectar alucinaciones.',
                'options'     => [
                    ['text' => 'RAG: chunking docs → embeddings → vector DB → búsqueda híbrida + re-ranking → LLM con contexto → RAGAS eval → guardrails anti-alucinación', 'correct' => true],
                    ['text' => 'Fine-tunear GPT-4o con toda la documentación copiada al prompt', 'correct' => false],
                    ['text' => 'Usar solo keyword search en la documentación sin IA', 'correct' => false],
                    ['text' => 'Entrenar un modelo desde cero con los documentos de la empresa', 'correct' => false],
                ],
            ],
        ];
    }
}
