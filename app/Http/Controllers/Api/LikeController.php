<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Notifications\PostLiked;
use Illuminate\Http\Request;


class LikeController extends Controller
{
    public function store(Post $post, Request $request)
    {
        $user = $request->user();
        $isNew = !$user->likes()->where('post_id', $post->id)->exists();
        if ($isNew) {
            $user->likes()->attach($post->id);

            if ($post->user_id !== $user->id) {
                $post->user->notify(new PostLiked($user, $post));
            }
        }
        return response()->json([
            'message' => 'El post tiene un like más'
        ], 201);
    }

    public function destroy(Post $post, Request $request)
    {
        $request->user()->likes()->detach($post->id);
        return response()->json([
            'message' => 'El post tiene un like menos'
        ], 200);
    }
}
