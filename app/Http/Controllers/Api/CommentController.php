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
        if ($post->user->is_private && auth()->id() !== $post->user_id) {
            $isFollowing = $post->user->followers()
                ->where('seguidor_id', auth()->id())
                ->exists();
            if (!$isFollowing) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
        }

        $comentarios = $post->comentarios()
            ->with('user')
            ->latest()
            ->paginate(15);
        return response()->json([
            'data' => $comentarios->items(),
            'pagination' => [
                'current_page' => $comentarios->currentPage(),
                'last_page' => $comentarios->lastPage(),
                'per_page' => $comentarios->perPage(),
                'total' => $comentarios->total(),
            ],
        ], 200);
    }

    public function store(Request $request, Post $post)
    {
        if ($post->user->is_private && auth()->id() !== $post->user_id) {
            $isFollowing = $post->user->followers()
                ->where('seguidor_id', auth()->id())
                ->exists();
            if (!$isFollowing) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
        }

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
