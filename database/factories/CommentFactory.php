<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'texto' => $this->faker->sentence(rand(3, 10)), // Frases cortas
            'user_id' => \App\Models\User::factory(),
            'post_id' => \App\Models\Post::factory(),
            'updated_at' => now(),
        ];
    }
}
