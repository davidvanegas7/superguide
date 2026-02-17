<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Principiante',  'color' => '#10b981'],
            ['name' => 'Intermedio',    'color' => '#f59e0b'],
            ['name' => 'Avanzado',      'color' => '#ef4444'],
            ['name' => 'POO',           'color' => '#6366f1'],
            ['name' => 'Funciones',     'color' => '#8b5cf6'],
            ['name' => 'Arrays',        'color' => '#ec4899'],
            ['name' => 'Bases de datos','color' => '#0ea5e9'],
            ['name' => 'Web',           'color' => '#14b8a6'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(
                ['slug' => Str::slug($tag['name'])],
                $tag
            );
        }
    }
}
