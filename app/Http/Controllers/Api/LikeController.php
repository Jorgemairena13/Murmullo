<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Notifications\PostLiked;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LikeController extends Controller
{
    public function store(Post $post, Request $request)
    {
        if ($post->user->is_private && $request->user()->id !== $post->user_id) {
            $isFollowing = $post->user->followers()
                ->where('seguidor_id', $request->user()->id)
                ->exists();
            if (!$isFollowing) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
        }

        $user = $request->user();

        DB::transaction(function () use ($user, $post) {
            $isNew = !$user->likes()->where('post_id', $post->id)->exists();
            if ($isNew) {
                $user->likes()->attach($post->id);

                if ($post->user_id !== $user->id) {
                    $post->user->notify(new PostLiked($user, $post));
                }
            }
        });

        return response()->json([
            'message' => 'El post tiene un like más'
        ], 201);
    }

    public function destroy(Post $post, Request $request)
    {
        if ($post->user->is_private && $request->user()->id !== $post->user_id) {
            $isFollowing = $post->user->followers()
                ->where('seguidor_id', $request->user()->id)
                ->exists();
            if (!$isFollowing) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
        }

        $request->user()->likes()->detach($post->id);
        return response()->json([
            'message' => 'El post tiene un like menos'
        ], 200);
    }
}
