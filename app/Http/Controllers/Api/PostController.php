<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PostResource;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;




class PostController extends Controller
{
    // Feed principal del usuario
    public function feed()
    {
        $user = Auth::user();

        $followedUserIds = $user->following()->pluck('usuarios.id');

        $ids = $followedUserIds->push($user->id);

        $posts = Post::whereIn('user_id', $ids)
            ->with('user')
            ->withCount(['likes', 'comentarios'])
            ->withExists(['likes' => fn($q) => $q->where('user_id', auth()->id())])
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => PostResource::collection($posts),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'next_page_url' => $posts->nextPageUrl(),
                'prev_page_url' => $posts->previousPageUrl(),
            ]
        ], 200);
    }

    // Explorar posts de cuentas públicas
    public function explorar()
    {
        $posts = Post::whereHas('user', fn($q) => $q->where('is_private', false))
            ->with('user')
            ->withCount(['likes', 'comentarios'])
            ->withExists(['likes' => fn($q) => $q->where('user_id', auth()->id())])
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => PostResource::collection($posts),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'next_page_url' => $posts->nextPageUrl(),
                'prev_page_url' => $posts->previousPageUrl(),
            ]
        ], 200);
    }

    // Guarda un post nuevo
    public function store(StorePostRequest $request)
    {
        $imagePath = Cloudinary::uploadApi()->upload(
            $request->file('imagen')->getPathname(),
            ['folder' => 'murmullo/posts']
        )['secure_url'];

        $post = $request->user()->posts()->create([
            'texto' => $request->validated('texto'),
            'imagen' => $imagePath
        ]);

        return (new PostResource($post))
            ->response()
            ->setStatusCode(201);
    }

    // Mostrar post de usuario
    public function getUserPosts(User $user)
    {
        $user->loadCount('posts');

        if ($user->is_private && auth()->check()) {
            $isFollowing = $user->followers()
                ->where('seguidor_id', auth()->id())
                ->exists();

            if (! $isFollowing) {
                return response()->json([
                    'user' => [
                        'id' => $user->id,
                        'nombre' => $user->nombre,
                        'avatar_url' => $user->avatar && str_starts_with($user->avatar, 'http') ? $user->avatar : null,
                        'bio' => $user->bio,
                        'is_private' => true,
                    ],
                    'posts' => [
                        'data' => [],
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 6,
                        'total' => 0,
                    ]
                ]);
            }
        }

        $posts = $user->posts()
            ->with('user')
            ->withCount('likes')
            ->withExists(['likes' => fn($q) => $q->where('user_id', auth()->id())])
            ->latest()
            ->paginate(6);

        $userData = [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'avatar_url' => $user->avatar && str_starts_with($user->avatar, 'http') ? $user->avatar : null,
            'bio' => $user->bio,
            'posts_count' => $user->posts_count
        ];
        // Devolver post paginados
        return response()->json([
            'user' => $userData,
            'posts' => [
                'data' => PostResource::collection($posts),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'next_page_url' => $posts->nextPageUrl(),
            ]
        ]);
    }


    public function show(Post $post)
    {
        $post->load(['user', 'comentarios.user']);
        $post->loadCount(['likes', 'comentarios']);

        return new PostResource($post);
    }


    //   Actualizar texto

    public function update(UpdatePostRequest $request, Post $post)
    {

        $this->authorize('update', $post);


        $post->update($request->validated());


        return new PostResource($post);
    }


    //  Borra un post.

    public function destroy(Post $post)
    {
        // Ver si esta autorizado
        $this->authorize('delete', $post);

        // Borrar imagen de Cloudinary
        if ($post->imagen) {
            $publicId = preg_replace(
                '#\.[a-z]+$#', '',
                preg_replace('#^.+/(?:v\d+/)?#', '', parse_url($post->imagen, PHP_URL_PATH))
            );
            Cloudinary::uploadApi()->destroy($publicId);
        }

        // Borrar de la base de datos
        $post->delete();

        // Devolver respuesta
        return response()->json(null, 204);
    }
}
