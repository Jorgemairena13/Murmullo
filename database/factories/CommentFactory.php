<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    private static array $textos = [
        'Qué bonito!',
        'Me encanta esto',
        'Increíble, sigue así',
        'Gran publicación',
        'Completamente de acuerdo',
        'Espectacular',
        'Me alegra mucho ver esto',
        'Comparto totalmente tu opinión',
        'Qué envidia sana!',
        'Dónde es eso?',
    ];

    private static int $index = 0;

    public function definition(): array
    {
        $texto = self::$textos[self::$index % count(self::$textos)];
        self::$index++;

        return [
            'texto' => $texto,
            'user_id' => \App\Models\User::factory(),
            'post_id' => \App\Models\Post::factory(),
            'updated_at' => now(),
        ];
    }
}
