<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class User1PostsSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurarse de que el usuario 1 exista
        $user = User::find(1);
        if (!$user) return;

        $numPosts = 50; // número de posts a generar

        for ($i = 1; $i <= $numPosts; $i++) {
            Post::create([
                'user_id' => $user->id,
                'texto' => "Post de prueba #$i",
                'imagen' => 'WuSPE7Xx8zyJHwvZUP6t8xK3ARnTyxfgIW5LqLRU.jpg',
            ]);
        }
    }
}

