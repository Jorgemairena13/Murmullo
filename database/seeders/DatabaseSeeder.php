<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $me = User::factory()->create([
            'nombre' => 'Jorge Mairena',
            'email' => 'jorgemairena13@gmail.com',
            'password' => bcrypt('Mairena13'),
        ]);


        $users = User::factory(10)->create();


        $users->push($me);


        foreach ($users as $user) {


            $posts = Post::factory(rand(1, 5))->create([
                'user_id' => $user->id
            ]);

            foreach ($posts as $post) {

                $numberOfComments = rand(0, 3);

                if ($numberOfComments > 0) {
                    Comment::factory($numberOfComments)->create([
                        'post_id' => $post->id,

                        'user_id' => $users->random()->id
                    ]);
                }


                $randomLikers = $users->random(rand(0, 5));
                foreach ($randomLikers as $liker) {

                    try {
                        Like::create([
                            'user_id' => $liker->id,
                            'post_id' => $post->id
                        ]);
                    } catch (\Exception $e) {

                    }
                }
            }
            foreach ($users as $user) {
            // Cada usuario seguirá a entre 1 y 5 personas al azar de la lista
            // (excluyéndose a sí mismo para no auto-seguirse)
            $usersToFollow = $users->where('id', '!=', $user->id)->random(rand(1, 5));

            // Usamos 'attach' para crear la relación en la tabla pivote
            // OJO: Esto requiere que tengas la relación 'following()' en tu modelo User
            try {
                $user->following()->attach($usersToFollow);
            } catch (\Exception $e) {
                // Si ya lo seguía, no pasa nada
            }
        }
        }

        echo "✅ Base de datos sembrada correctamente con usuario 'jorgemairena13@gmail.com' \n";
    }
}
