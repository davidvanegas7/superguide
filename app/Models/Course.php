<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'language_id', 'title', 'slug', 'description', 'level', 'cover_image', 'published', 'sort_order',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function publishedLessons(): HasMany
    {
        return $this->hasMany(Lesson::class)
            ->where('published', true)
            ->orderBy('sort_order');
    }

    public function getLevelLabelAttribute(): string
    {
        return match($this->level) {
            'beginner'     => 'Principiante',
            'intermediate' => 'Intermedio',
            'advanced'     => 'Avanzado',
            default        => ucfirst($this->level),
        };
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
