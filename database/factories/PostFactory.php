<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'texto' => $this->faker->paragraph(rand(1, 3)),
            'imagen' => 'https://picsum.photos/seed/' . Str::random(10) . '/600/600',
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
