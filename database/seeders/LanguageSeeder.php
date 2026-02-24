<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['name' => 'PHP',        'color' => '#777bb4', 'icon' => '🐘', 'description' => 'Lenguaje de scripting del lado del servidor, ideal para web.'],
            ['name' => 'JavaScript', 'color' => '#f7df1e', 'icon' => '⚡', 'description' => 'El lenguaje del navegador. Esencial para el frontend moderno.'],
            ['name' => 'Python',     'color' => '#3776ab', 'icon' => '🐍', 'description' => 'Versátil y legible. Usado en IA, data science y scripting.'],
            ['name' => 'TypeScript', 'color' => '#3178c6', 'icon' => '🔷', 'description' => 'JavaScript con tipos estáticos. Más robusto y mantenible.'],
            ['name' => 'SQL',        'color' => '#e38c00', 'icon' => '🗄️', 'description' => 'El lenguaje estándar para consultar bases de datos relacionales.'],
            ['name' => 'Ruby',       'color' => '#cc342d', 'icon' => '💎', 'description' => 'Lenguaje elegante y expresivo. Diseñado para la felicidad del programador.'],
            ['name' => 'Elixir',    'color' => '#6e4a7e', 'icon' => '💧', 'description' => 'Lenguaje funcional sobre la BEAM. Concurrencia masiva y tolerancia a fallos.'],
            ['name' => 'Excel',     'color' => '#217346', 'icon' => '📊', 'description' => 'La herramienta de hojas de cálculo más usada del mundo. Fórmulas, tablas dinámicas y automatización.'],
        ];

        foreach ($languages as $i => $lang) {
            Language::firstOrCreate(
                ['slug' => Str::slug($lang['name'])],
                array_merge($lang, ['sort_order' => $i, 'active' => true])
            );
        }
    }
}
