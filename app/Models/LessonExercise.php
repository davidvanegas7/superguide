<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class LessonExercise extends Model
{
    protected $fillable = ['lesson_id', 'title', 'description', 'starter_code', 'language'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function getDescriptionHtmlAttribute(): string
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);
        return $converter->convert($this->description)->getContent();
    }
}
