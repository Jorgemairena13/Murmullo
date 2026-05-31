<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    private static array $textos = [
        'Un día increíble para compartir esto con todos vosotros.',
        'Mirando atrás, cada paso ha valido la pena.',
        'La vida está hecha de pequeños momentos como este.',
        'Nuevo proyecto en marcha, muy pronto más detalles.',
        'Atardecer desde mi lugar favorito del mundo.',
        'A veces solo necesitas una pausa para apreciar lo que tienes.',
        'Trabajando duro para conseguir lo que quiero.',
        'Nada como empezar el día con buena energía.',
        'Descubriendo rincones nuevos de la ciudad.',
        'Momento de reflexión y gratitud.',
    ];

    public function definition(): array
    {
        return [
            'texto' => self::$textos[array_rand(self::$textos)],
            'imagen' => 'https://picsum.photos/seed/' . Str::random(10) . '/600/600',
        ];
    }
}
