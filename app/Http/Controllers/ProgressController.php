<?php

namespace App\Http\Controllers;

use App\Models\Progress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['lesson_id' => 'required|exists:lessons,id']);

        if (auth()->check()) {
            // Usuario autenticado: guarda el progreso vinculado a su cuenta
            $progress = Progress::firstOrCreate(
                ['user_id' => auth()->id(), 'lesson_id' => $request->lesson_id],
                ['session_id' => session()->getId(), 'completed' => false]
            );
        } else {
            // Visitante anónimo: guarda el progreso por sesión
            $progress = Progress::firstOrCreate(
                ['session_id' => session()->getId(), 'lesson_id' => $request->lesson_id],
                ['completed' => false]
            );
        }

        $progress->completed    = !$progress->completed;
        $progress->completed_at = $progress->completed ? now() : null;
        $progress->save();

        return response()->json(['completed' => $progress->completed]);
    }
}
