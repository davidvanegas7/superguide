<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function show(Language $language, Course $course, Quiz $quiz): View
    {
        abort_unless(
            $quiz->published && $quiz->course_id === $course->id && $course->language_id === $language->id,
            404
        );

        $questions = $quiz->questions()->with('options')->get();

        return view('quizzes.show', compact('language', 'course', 'quiz', 'questions'));
    }

    public function check(Request $request, Language $language, Course $course, Quiz $quiz): JsonResponse
    {
        abort_unless(
            $quiz->published && $quiz->course_id === $course->id && $course->language_id === $language->id,
            404
        );

        $answers  = $request->input('answers', []);  // ['question_id' => 'option_id', ...]
        $questions = $quiz->questions()->with('options')->get();

        $results = [];
        $correct = 0;

        foreach ($questions as $question) {
            $selectedId  = $answers[$question->id] ?? null;
            $correctOpt  = $question->options->firstWhere('is_correct', true);
            $selectedOpt = $selectedId ? $question->options->firstWhere('id', (int) $selectedId) : null;

            $isCorrect = $selectedOpt && $selectedOpt->is_correct;
            if ($isCorrect) $correct++;

            $results[] = [
                'question_id'  => $question->id,
                'is_correct'   => $isCorrect,
                'explanation'  => $question->explanation,
                'correct_option_id' => $correctOpt?->id,
                'selected_option_id' => $selectedId ? (int) $selectedId : null,
            ];
        }

        return response()->json([
            'score'    => $correct,
            'total'    => $questions->count(),
            'percent'  => $questions->count() > 0 ? round($correct / $questions->count() * 100) : 0,
            'results'  => $results,
        ]);
    }
}
