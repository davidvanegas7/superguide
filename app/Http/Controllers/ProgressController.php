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

        $sessionId = session()->getId();

        $progress = Progress::firstOrCreate(
            ['session_id' => $sessionId, 'lesson_id' => $request->lesson_id],
            ['completed' => false]
        );

        $progress->completed    = !$progress->completed;
        $progress->completed_at = $progress->completed ? now() : null;
        $progress->save();

        return response()->json(['completed' => $progress->completed]);
    }
}
