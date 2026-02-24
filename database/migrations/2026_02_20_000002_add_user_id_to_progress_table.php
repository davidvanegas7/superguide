<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progress', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Eliminar la restricción única anónima
            $table->dropUnique('progress_session_id_lesson_id_unique');

            // Nueva restricción única para usuarios autenticados (NULL != NULL en MySQL, por eso no hay conflicto para anónimos)
            $table->unique(['user_id', 'lesson_id'], 'progress_user_lesson_unique');
        });
    }

    public function down(): void
    {
        Schema::table('progress', function (Blueprint $table) {
            $table->dropUnique('progress_user_lesson_unique');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->unique(['session_id', 'lesson_id']);
        });
    }
};
