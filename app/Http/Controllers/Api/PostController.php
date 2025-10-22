<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    
    public function store(Request $request)
    {
        // Validación
        $validarDatos = Validator::make($request->all(), [
            'texto' => 'nullable|string|max:1000',
            'imagen' => 'nullable|image|max:2048', // imagen opcional
        ]);

        if ($validarDatos->fails()) {
            return response()->json(['errors' => $validarDatos->errors()], 422);
        }

        $imageUrl = null;

        // Procesar imagen si se envía
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');

            if (!$file->isValid()) {
                return response()->json([
                    'errors' => ['imagen' => ['Archivo no válido o fallo al subir.']],
                    'errorCode' => $file->getError()
                ], 422);
            }

            $imageUrl = $file->store('posts', 'public');
        }

        // Crear post
        $post = $request->user()->posts()->create([
            'texto' => $request->texto,
            'imagen' => $imageUrl
        ]);

        return response()->json($post, 201);
    }


    /**
     * Display the specified resource.
     */

    public function getUserPosts(User $user)
    {

        $posts = $user->posts()->latest()->paginate(12);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'avatar' => $user->avatar,
                'bio' => $user->bio,
                'posts_count' => $user->posts()->count()
            ],
            'posts' => $posts
        ]);
    }

    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
