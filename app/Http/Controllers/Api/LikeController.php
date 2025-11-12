<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{

    public function store(Post $post,Request $request)
    {
        // Guardar like
        $request->user()->likes()->attach($post->id);
        return response()->json([
            'message'=> 'El post tiene un like mas'],201);
    }





    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post,Request $request)
    {
        // Borrar el like
        $request->user()->likes()->attach($post->id);

        return response()->json([
            'message'=> 'El post tiene un like menos'],200);

    }
}
