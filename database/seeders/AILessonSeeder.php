<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class AILessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::where('slug', 'ia-llms')->first();

        if (! $course) {
            $this->command->warn('AI/LLMs course not found. Run CourseSeeder first.');
            return;
        }

        $tagPrincipiante = Tag::where('slug', 'principiante')->first();
        $tagIntermedio    = Tag::where('slug', 'intermedio')->first();
        $tagAvanzado      = Tag::where('slug', 'avanzado')->first();
        $tagFunciones     = Tag::where('slug', 'funciones')->first();
        $tagBackend       = Tag::where('slug', 'backend')->first();
        $tagApi           = Tag::where('slug', 'api')->first();

        $lessons = [
            ['slug' => 'introduccion-ia',           'title' => 'Introducción a la Inteligencia Artificial',     'md_file_path' => 'content/lessons/ia/01-introduccion-ia.md',        'excerpt' => 'Historia de la IA, tipos (ANI/AGI/ASI), ML vs DL y ecosistema actual.',                'published' => true, 'sort_order' => 1,  'duration_minutes' => 20],
            ['slug' => 'fundamentos-ml',            'title' => 'Fundamentos de Machine Learning',              'md_file_path' => 'content/lessons/ia/02-fundamentos-ml.md',         'excerpt' => 'Supervised, unsupervised, reinforcement learning, métricas y pipeline.',              'published' => true, 'sort_order' => 2,  'duration_minutes' => 25],
            ['slug' => 'redes-neuronales',          'title' => 'Redes Neuronales',                             'md_file_path' => 'content/lessons/ia/03-redes-neuronales.md',       'excerpt' => 'Perceptrón, MLP, funciones de activación, backpropagation y optimización.',            'published' => true, 'sort_order' => 3,  'duration_minutes' => 30],
            ['slug' => 'deep-learning',             'title' => 'Deep Learning',                                'md_file_path' => 'content/lessons/ia/04-deep-learning.md',          'excerpt' => 'CNNs, RNNs, LSTMs, transfer learning, GPUs y mixed precision.',                       'published' => true, 'sort_order' => 4,  'duration_minutes' => 30],
            ['slug' => 'nlp-procesamiento-lenguaje','title' => 'Procesamiento de Lenguaje Natural (NLP)',      'md_file_path' => 'content/lessons/ia/05-nlp.md',                   'excerpt' => 'Tokenización, embeddings, Word2Vec, NER, clasificación de texto.',                     'published' => true, 'sort_order' => 5,  'duration_minutes' => 25],
            ['slug' => 'transformers',              'title' => 'Arquitectura Transformer',                     'md_file_path' => 'content/lessons/ia/06-transformers.md',           'excerpt' => 'Self-attention, multi-head attention, positional encoding, encoder vs decoder.',        'published' => true, 'sort_order' => 6,  'duration_minutes' => 30],
            ['slug' => 'llms-modelos-lenguaje',     'title' => 'Modelos de Lenguaje (LLMs)',                   'md_file_path' => 'content/lessons/ia/07-llms.md',                  'excerpt' => 'GPT-4o, Claude 3.5, Gemini 2.0, Llama 3.1, scaling laws y MoE.',                      'published' => true, 'sort_order' => 7,  'duration_minutes' => 30],
            ['slug' => 'entrenamiento-llms',        'title' => 'Entrenamiento de LLMs',                       'md_file_path' => 'content/lessons/ia/08-entrenamiento-llms.md',     'excerpt' => 'Pre-training, SFT, RLHF, DPO, Constitutional AI y datos sintéticos.',                 'published' => true, 'sort_order' => 8,  'duration_minutes' => 30],
            ['slug' => 'prompt-engineering',         'title' => 'Prompt Engineering',                           'md_file_path' => 'content/lessons/ia/09-prompt-engineering.md',     'excerpt' => 'Zero-shot, few-shot, Chain-of-Thought, system prompts y salida estructurada.',          'published' => true, 'sort_order' => 9,  'duration_minutes' => 25],
            ['slug' => 'rag-retrieval-augmented',   'title' => 'RAG: Retrieval Augmented Generation',          'md_file_path' => 'content/lessons/ia/10-rag.md',                   'excerpt' => 'Chunking, embeddings, vector stores, búsqueda híbrida y re-ranking.',                  'published' => true, 'sort_order' => 10, 'duration_minutes' => 30],
            ['slug' => 'fine-tuning-adaptacion',    'title' => 'Fine-tuning y Adaptación de Modelos',          'md_file_path' => 'content/lessons/ia/11-fine-tuning.md',            'excerpt' => 'LoRA, QLoRA, PEFT, adaptadores, merge y fine-tuning via API.',                         'published' => true, 'sort_order' => 11, 'duration_minutes' => 30],
            ['slug' => 'apis-llms',                 'title' => 'APIs de LLMs y Modelos Open Source',           'md_file_path' => 'content/lessons/ia/12-apis-llms.md',             'excerpt' => 'OpenAI, Anthropic, Gemini, Hugging Face, Ollama y vLLM.',                              'published' => true, 'sort_order' => 12, 'duration_minutes' => 25],
            ['slug' => 'agentes-ia',                'title' => 'Agentes de IA',                                'md_file_path' => 'content/lessons/ia/13-agentes-ia.md',            'excerpt' => 'Function calling, tool use, MCP, LangChain, CrewAI y patrones ReAct.',                'published' => true, 'sort_order' => 13, 'duration_minutes' => 30],
            ['slug' => 'embeddings-busqueda',       'title' => 'Embeddings y Búsqueda Semántica',              'md_file_path' => 'content/lessons/ia/14-embeddings.md',            'excerpt' => 'Similitud coseno, modelos de embeddings, clustering y CLIP multimodal.',               'published' => true, 'sort_order' => 14, 'duration_minutes' => 25],
            ['slug' => 'evaluacion-modelos',        'title' => 'Evaluación de Modelos de IA',                  'md_file_path' => 'content/lessons/ia/15-evaluacion-modelos.md',     'excerpt' => 'MMLU, HumanEval, SWE-Bench, Arena ELO, LLM-as-Judge y RAGAS.',                        'published' => true, 'sort_order' => 15, 'duration_minutes' => 25],
            ['slug' => 'etica-seguridad-ia',        'title' => 'Ética, Seguridad y Regulación en IA',          'md_file_path' => 'content/lessons/ia/16-etica-seguridad.md',       'excerpt' => 'Sesgos, alineación, prompt injection, EU AI Act y IA responsable.',                    'published' => true, 'sort_order' => 16, 'duration_minutes' => 25],
            ['slug' => 'ia-multimodal',             'title' => 'IA Multimodal',                                'md_file_path' => 'content/lessons/ia/17-ia-multimodal.md',         'excerpt' => 'Visión, audio, video, DALL-E, Whisper, Stable Diffusion y modelos unificados.',         'published' => true, 'sort_order' => 17, 'duration_minutes' => 30],
            ['slug' => 'deploy-modelos-ia',         'title' => 'Deploy y Optimización de Modelos de IA',       'md_file_path' => 'content/lessons/ia/18-deploy-modelos.md',        'excerpt' => 'Cuantización, vLLM, ONNX, Docker, speculative decoding y edge.',                       'published' => true, 'sort_order' => 18, 'duration_minutes' => 30],
            ['slug' => 'entrevista-ia-llms',        'title' => 'Preguntas de Entrevista: IA y LLMs',           'md_file_path' => 'content/lessons/ia/19-preguntas-entrevista.md',  'excerpt' => 'ML, deep learning, transformers, RAG, agentes y diseño de sistemas IA.',               'published' => true, 'sort_order' => 19, 'duration_minutes' => 25],
        ];

        foreach ($lessons as $data) {
            $lesson = Lesson::firstOrCreate(
                ['course_id' => $course->id, 'slug' => $data['slug']],
                $data + ['course_id' => $course->id]
            );

            $sort = $data['sort_order'];

            if ($tagPrincipiante && $sort <= 4) {
                $lesson->tags()->syncWithoutDetaching([$tagPrincipiante->id]);
            }
            if ($tagIntermedio && $sort >= 5 && $sort <= 12) {
                $lesson->tags()->syncWithoutDetaching([$tagIntermedio->id]);
            }
            if ($tagAvanzado && $sort >= 13) {
                $lesson->tags()->syncWithoutDetaching([$tagAvanzado->id]);
            }
            if ($tagFunciones && in_array($sort, [3, 5, 6, 9])) {
                $lesson->tags()->syncWithoutDetaching([$tagFunciones->id]);
            }
            if ($tagApi && in_array($sort, [12, 13])) {
                $lesson->tags()->syncWithoutDetaching([$tagApi->id]);
            }
            if ($tagBackend && in_array($sort, [10, 11, 18])) {
                $lesson->tags()->syncWithoutDetaching([$tagBackend->id]);
            }
        }
    }
}
