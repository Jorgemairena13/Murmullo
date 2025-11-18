<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PostResource;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;




class PostController extends Controller
{
    // Feed principal del usuario
    public function feed()
    {

        $authenticatedUser = Auth::user();

        $followedUserIds = $authenticatedUser->following()->pluck('id');
        $posts = Post::whereIn('user_id', $followedUserIds)
            ->latest()
            ->with('user')
            ->paginate(10);

        return response()->json($posts, 200);
    }

    // Guarda un post nuevo
    public function store(StorePostRequest $request)
    {

        $imagePath = $request->file('imagen')->store('posts', 'public');


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


        $posts = $user->posts()
            ->with('user')
            ->withCount('likes')
            ->with('likes')
            ->latest()
            ->paginate(6);


        $userData = [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'avatar_url' => $user->avatar ? config('app.url') . Storage::url($user->avatar) : null,
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
        $posts = Post::with('user')->withCount('likes')->with('likes')->latest()->paginate(15);
        $post->load(['user', 'comments.user']);
        $post->loadCount(['likes', 'comments']);

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

        // Borrar imagen
        Storage::disk('public')->delete($post->imagen);

        // Borrar de la base de datos
        $post->delete();

        // Devolver respuesta
        return response()->json(null, 204);
    }
}
