<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('md_file_path')->nullable(); // ruta al archivo .md
            $table->longText('content_md')->nullable(); // contenido markdown directo
            $table->text('excerpt')->nullable();
            $table->boolean('published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();
            $table->unique(['course_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
