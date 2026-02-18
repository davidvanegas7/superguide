@extends('layouts.app')

@section('title', $quiz->title)

@section('content')

<div class="flex items-center gap-2 text-sm text-gray-400 mb-6 flex-wrap">
    <a href="{{ route('home') }}" class="hover:text-indigo-600">Inicio</a>
    <span>/</span>
    <a href="{{ route('languages.show', $language) }}" class="hover:text-indigo-600">{{ $language->name }}</a>
    <span>/</span>
    <a href="{{ route('courses.show', [$language, $course]) }}" class="hover:text-indigo-600">{{ $course->title }}</a>
    <span>/</span>
    <span class="text-gray-700 font-medium">Evaluación</span>
</div>

<div class="max-w-3xl mx-auto">

    {{-- Header del quiz --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-xl">🧠</div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $quiz->title }}</h1>
                <p class="text-sm text-gray-400">{{ $questions->count() }} preguntas · Opción múltiple</p>
            </div>
        </div>
        @if($quiz->description)
            <p class="text-gray-600 text-sm">{{ $quiz->description }}</p>
        @endif

        {{-- Barra de progreso --}}
        <div class="mt-4">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span id="progressText">Pregunta 0 de {{ $questions->count() }}</span>
                <span id="progressPercent">0%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div id="progressBar" class="bg-indigo-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
    </div>

    {{-- Formulario del quiz --}}
    <form id="quizForm">
        <div class="space-y-5">
            @foreach($questions as $i => $question)
                <div class="quiz-card bg-white border border-gray-200 rounded-xl p-5 sm:p-6 transition-all"
                     data-question="{{ $question->id }}"
                     data-index="{{ $i + 1 }}">

                    {{-- Número y pregunta --}}
                    <div class="flex items-start gap-3 mb-4">
                        <span class="shrink-0 w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">
                            {{ $i + 1 }}
                        </span>
                        <p class="font-medium text-gray-900 leading-snug">{{ $question->question }}</p>
                    </div>

                    {{-- Opciones --}}
                    <div class="space-y-2 pl-10">
                        @foreach($question->options as $option)
                            <label class="option-label flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-indigo-300 hover:bg-indigo-50 transition-all"
                                   data-option="{{ $option->id }}"
                                   data-correct="{{ $option->is_correct ? 'true' : 'false' }}">
                                <input type="radio"
                                       name="q{{ $question->id }}"
                                       value="{{ $option->id }}"
                                       class="quiz-radio mt-0.5 shrink-0 accent-indigo-600"
                                       data-question="{{ $question->id }}">
                                <span class="text-sm text-gray-700 leading-snug">{{ $option->text }}</span>
                            </label>
                        @endforeach
                    </div>

                    {{-- Explicación (oculta hasta enviar) --}}
                    @if($question->explanation)
                        <div class="explanation hidden mt-4 pl-10">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-800">
                                <span class="font-semibold">💡 Explicación:</span> {{ $question->explanation }}
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Botón de enviar --}}
        <div class="mt-6 flex flex-col sm:flex-row gap-3 items-center justify-between">
            <a href="{{ route('courses.show', [$language, $course]) }}"
               class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">
                ← Volver al curso
            </a>
            <button type="submit"
                    id="submitBtn"
                    class="w-full sm:w-auto px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Enviar evaluación
            </button>
        </div>
    </form>

    {{-- Panel de resultados (oculto hasta enviar) --}}
    <div id="resultsPanel" class="hidden mt-8 bg-white border border-gray-200 rounded-xl p-6">
        <div class="text-center mb-6">
            <div id="scoreEmoji" class="text-5xl mb-3">🎯</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-1">
                <span id="scoreValue">0</span> / {{ $questions->count() }} correctas
            </h2>
            <p id="scoreMessage" class="text-gray-500"></p>

            <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full text-lg font-bold"
                 id="scoreBadge">
                <span id="scorePercent">0%</span>
            </div>
        </div>

        {{-- Desglose --}}
        <div class="grid grid-cols-3 gap-3 mb-6 text-center">
            <div class="bg-green-50 rounded-xl p-3">
                <p class="text-2xl font-bold text-green-700" id="correctCount">0</p>
                <p class="text-xs text-green-600 mt-1">Correctas</p>
            </div>
            <div class="bg-red-50 rounded-xl p-3">
                <p class="text-2xl font-bold text-red-600" id="wrongCount">0</p>
                <p class="text-xs text-red-500 mt-1">Incorrectas</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-2xl font-bold text-gray-600" id="skippedCount">0</p>
                <p class="text-xs text-gray-500 mt-1">Sin responder</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="resetQuiz()"
                    class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors text-sm">
                🔄 Intentar de nuevo
            </button>
            <a href="{{ route('courses.show', [$language, $course]) }}"
               class="flex-1 text-center px-4 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors text-sm">
                ← Volver al curso
            </a>
        </div>
    </div>

</div>

<script>
(function () {
    const form          = document.getElementById('quizForm');
    const submitBtn     = document.getElementById('submitBtn');
    const resultsPanel  = document.getElementById('resultsPanel');
    const progressBar   = document.getElementById('progressBar');
    const progressText  = document.getElementById('progressText');
    const progressPct   = document.getElementById('progressPercent');
    const totalQ        = {{ $questions->count() }};
    const checkUrl      = "{{ route('quizzes.check', [$language, $course, $quiz]) }}";
    const csrfToken     = document.querySelector('meta[name="csrf-token"]').content;

    let answered = new Set();

    // Actualizar barra de progreso
    document.querySelectorAll('.quiz-radio').forEach(radio => {
        radio.addEventListener('change', function () {
            answered.add(this.dataset.question);
            const pct = Math.round(answered.size / totalQ * 100);
            progressBar.style.width = pct + '%';
            progressText.textContent = `Pregunta ${answered.size} de ${totalQ}`;
            progressPct.textContent = pct + '%';
        });
    });

    // Enviar
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Recoger respuestas
        const answers = {};
        document.querySelectorAll('.quiz-radio:checked').forEach(r => {
            answers[r.dataset.question] = r.value;
        });

        submitBtn.disabled = true;
        submitBtn.textContent = 'Evaluando…';

        try {
            const resp = await fetch(checkUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ answers }),
            });
            const data = await resp.json();
            showResults(data, answers);
        } catch (err) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Enviar evaluación';
            alert('Error al enviar. Intenta de nuevo.');
        }
    });

    function showResults(data, answers) {
        // Colorear cada opción
        data.results.forEach(r => {
            const card = document.querySelector(`.quiz-card[data-question="${r.question_id}"]`);
            if (!card) return;

            card.querySelectorAll('.option-label').forEach(label => {
                const optId      = parseInt(label.dataset.option);
                const isCorrect  = label.dataset.correct === 'true';
                const isSelected = parseInt(answers[r.question_id]) === optId;

                label.classList.remove('hover:border-indigo-300', 'hover:bg-indigo-50', 'cursor-pointer');

                if (isCorrect) {
                    label.classList.add('bg-green-50', 'border-green-400', 'text-green-800');
                } else if (isSelected && !isCorrect) {
                    label.classList.add('bg-red-50', 'border-red-400', 'text-red-800');
                } else {
                    label.classList.add('opacity-50');
                }
            });

            // Mostrar explicación
            const explanation = card.querySelector('.explanation');
            if (explanation) explanation.classList.remove('hidden');

            // Icono en el número de la pregunta
            const numBadge = card.querySelector('span.shrink-0');
            if (numBadge) {
                if (r.is_correct) {
                    numBadge.classList.remove('bg-indigo-100', 'text-indigo-700');
                    numBadge.classList.add('bg-green-100', 'text-green-700');
                    numBadge.textContent = '✓';
                } else {
                    numBadge.classList.remove('bg-indigo-100', 'text-indigo-700');
                    numBadge.classList.add('bg-red-100', 'text-red-600');
                    numBadge.textContent = '✗';
                }
            }
        });

        // Deshabilitar todos los radios
        document.querySelectorAll('.quiz-radio').forEach(r => r.disabled = true);

        // Calcular skipped
        const skipped = totalQ - Object.keys(answers).length;
        const wrong   = data.total - data.score - skipped;

        // Panel de resultados
        document.getElementById('scoreValue').textContent  = data.score;
        document.getElementById('scorePercent').textContent = data.percent + '%';
        document.getElementById('correctCount').textContent = data.score;
        document.getElementById('wrongCount').textContent  = wrong;
        document.getElementById('skippedCount').textContent = skipped;

        const badge = document.getElementById('scoreBadge');
        const emoji = document.getElementById('scoreEmoji');
        const msg   = document.getElementById('scoreMessage');

        if (data.percent >= 80) {
            badge.className = 'mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full text-lg font-bold bg-green-100 text-green-700';
            emoji.textContent = '🏆';
            msg.textContent = '¡Excelente resultado! Estás listo para la entrevista.';
        } else if (data.percent >= 60) {
            badge.className = 'mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full text-lg font-bold bg-yellow-100 text-yellow-700';
            emoji.textContent = '📚';
            msg.textContent = 'Buen intento. Revisa las respuestas incorrectas y vuelve a intentarlo.';
        } else {
            badge.className = 'mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full text-lg font-bold bg-red-100 text-red-700';
            emoji.textContent = '💪';
            msg.textContent = 'Necesitas repasar el material. ¡Cada intento cuenta!';
        }

        // Ocultar botón, mostrar resultados y hacer scroll
        submitBtn.closest('div').classList.add('hidden');
        resultsPanel.classList.remove('hidden');
        resultsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    window.resetQuiz = function () {
        location.reload();
    };
})();
</script>

@endsection
