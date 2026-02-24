<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class Lesson extends Model
{
    use HasFactory;
    protected $fillable = [
        'course_id', 'title', 'slug', 'md_file_path', 'content_md',
        'excerpt', 'published', 'sort_order', 'duration_minutes',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function exercise(): HasOne
    {
        return $this->hasOne(LessonExercise::class);
    }

    /**
     * Devuelve el contenido Markdown: desde archivo .md o desde la BD.
     */
    public function getRawMarkdown(): string
    {
        if ($this->md_file_path && file_exists(base_path($this->md_file_path))) {
            return file_get_contents(base_path($this->md_file_path));
        }
        return $this->content_md ?? '';
    }

    /**
     * Convierte el Markdown a HTML listo para mostrar.
     */
    public function getHtmlContentAttribute(): string
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);
        return $converter->convert($this->getRawMarkdown())->getContent();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
