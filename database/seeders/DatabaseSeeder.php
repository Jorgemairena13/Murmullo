<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $me = User::updateOrCreate(
            ['email' => 'jorgemairena13@gmail.com'],
            [
                'nombre' => 'Jorge Mairena',
                'username' => 'jorgemairena',
                'bio' => 'Creador de Murmullo',
                'is_private' => false,
                'avatar' => 'https://i.pravatar.cc/200?u=jorgemairena',
                'password' => bcrypt('Mairena13'),
            ]
        );

        $usersData = [
            ['nombre' => 'Ana García', 'username' => 'anagarcia', 'bio' => 'Fotógrafa y viajera. Capturando el mundo un click a la vez.'],
            ['nombre' => 'Carlos López', 'username' => 'carloslopez', 'bio' => 'Desarrollador web y café adicto.'],
            ['nombre' => 'María Rodríguez', 'username' => 'mariarodriguez', 'bio' => 'Diseñadora gráfica. Amante del arte y la tipografía.'],
            ['nombre' => 'Pedro Sánchez', 'username' => 'pedrosanchez', 'bio' => 'Chef y foodie. La cocina es mi pasión.'],
            ['nombre' => 'Laura Martínez', 'username' => 'lauramartinez', 'bio' => 'Escritora y lectora compulsiva.'],
            ['nombre' => 'David Fernández', 'username' => 'davidfernandez', 'bio' => 'Músico y productor. El ruido también es música.'],
            ['nombre' => 'Sara Torres', 'username' => 'saratorres', 'bio' => 'Yoga y meditación. En busca del equilibrio.'],
            ['nombre' => 'Miguel Ángel Ruiz', 'username' => 'miguelruiz', 'bio' => 'Arquitecto. Construyendo sueños desde los cimientos.'],
            ['nombre' => 'Elena Gómez', 'username' => 'elenagomez', 'bio' => 'Médica y runner. Mente sana en cuerpo sano.'],
            ['nombre' => 'Javier Morales', 'username' => 'javimorales', 'bio' => 'Ingeniero de software. Resolviendo problemas complejos.'],
            ['nombre' => 'Paula Castillo', 'username' => 'paulacastillo', 'bio' => 'Periodista y podcast host. Contando historias que importan.'],
            ['nombre' => 'Adrián Navarro', 'username' => 'adriannavarro', 'bio' => 'Videógrafo y editor. La magia está en el montaje.'],
            ['nombre' => 'Lucía Jiménez', 'username' => 'luciajimenez', 'bio' => 'Ilustradora digital. Dibujando mundos imposibles.'],
            ['nombre' => 'Raúl Herrera', 'username' => 'raulherrera', 'bio' => 'Entrenador personal. Transformando vidas desde el gym.'],
        ];

        $users = collect([$me]);

        foreach ($usersData as $data) {
            $email = $data['username'] . '@murmullo.app';
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'nombre' => $data['nombre'],
                    'username' => $data['username'],
                    'email' => $email,
                    'bio' => $data['bio'],
                    'is_private' => false,
                    'avatar' => 'https://i.pravatar.cc/200?u=' . $data['username'],
                    'password' => bcrypt('password'),
                ]
            );
            $users->push($user);
        }

        $this->command->info('Creados/actualizados ' . $users->count() . ' usuarios');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Comment::truncate();
        Like::truncate();
        Post::truncate();
        DB::table('follows')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($users as $user) {
            try {
                $posts = Post::factory(rand(3, 6))->create([
                    'user_id' => $user->id,
                ]);
            } catch (\Exception $e) {
                $this->command->error("Error creando posts para {$user->nombre}: " . $e->getMessage());
                continue;
            }

            foreach ($posts as $post) {
                $numberOfComments = rand(0, 4);
                if ($numberOfComments > 0) {
                    try {
                        Comment::factory($numberOfComments)->create([
                            'post_id' => $post->id,
                            'user_id' => $users->where('id', '!=', $user->id)->random()->id,
                        ]);
                    } catch (\Exception $e) {
                        $this->command->error("Error creando comment: " . $e->getMessage());
                    }
                }

                $randomLikers = $users->where('id', '!=', $user->id)->random(rand(1, 8));
                foreach ($randomLikers as $liker) {
                    try {
                        Like::create([
                            'user_id' => $liker->id,
                            'post_id' => $post->id,
                        ]);
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        $this->command->info('Posts creados: ' . Post::count());
        $this->command->info('Comentarios creados: ' . Comment::count());
        $this->command->info('Likes creados: ' . Like::count());

        foreach ($users as $user) {
            $usersToFollow = $users
                ->where('id', '!=', $user->id)
                ->random(rand(3, 8));

            try {
                $user->following()->attach($usersToFollow);
            } catch (\Exception $e) {
            }
        }

        $this->command->info('Relaciones de follow recreadas');
        $this->command->info("✅ Base de datos sembrada correctamente con usuario 'jorgemairena13@gmail.com'");
    }
}
