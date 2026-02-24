<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('starter_code')->nullable();
            $table->string('language')->default('php');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_exercises');
    }
};
