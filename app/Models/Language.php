<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    protected $fillable = [
        'name', 'slug', 'color', 'icon', 'description', 'active', 'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function publishedCourses(): HasMany
    {
        return $this->hasMany(Course::class)->where('published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
