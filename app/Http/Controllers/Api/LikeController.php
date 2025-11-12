<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{

    public function store(Post $post, Request $request)
    {
        $user = $request->user();

        // Revisar si ya existe
        if (!$user->likes()->where('post_id', $post->id)->exists()) {
            $user->likes()->attach($post->id);
        }

        return response()->json([
            'message' => 'El post tiene un like más'
        ], 201);
    }



    public function destroy(Post $post, Request $request)
    {
        // Borrar el like
        $request->user()->likes()->detach($post->id);


        return response()->json([
            'message' => 'El post tiene un like menos'
        ], 200);
    }
}
