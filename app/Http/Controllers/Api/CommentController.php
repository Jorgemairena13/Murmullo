<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Notifications\PostCommented;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    public function index(Post $post)
    {
        $comentarios = $post->comentarios()
            ->with('user')
            ->latest()
            ->get();

        return response()->json(['data' => $comentarios], 200);
    }

    public function store(Request $request, Post $post)
    {
        $request->validate([
            'texto' => 'required|string|max:255'
        ]);

        $comment = $post->comentarios()->create([
            'texto' => $request->texto,
            'user_id' => Auth::id()
        ]);

        if ($post->user_id !== Auth::id()) {
            $post->user->notify(new PostCommented(Auth::user(), $post, $request->texto));
        }

        return response()->json($comment->load('user'), 201);
    }

    public function destroy($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['message' => 'No encontrado'], 404);
        }
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        $comment->delete();
        return response()->json(['message' => 'Comentario eliminado'], 200);
    }
}
