<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use App\Models\Comment;
use Dom\Comment as DomComment;

class CommentController extends Controller
{
    // Guardar comentario
    public function store(Request $request,Post $post){
        $request->validate([
            'texto'=>'required|string|max:255'
        ]);
        // Crear el comentario en el post
        $comment = $post->comentarios()->create([
            'texto'=>$request->texto,
            'user_id'=>Auth::id()
        ]);

        // Devolver comentario
        return response()->json($comment->load('user'),201);
    }

    public function destroy($id){
        $comment = Comment::find($id);
        if(!$comment){
            return response()->json(['message' => 'No encontrado'], 404);
        }
        if($comment->user_id !== Auth::id()){
            return response()->json(['message'=>'No autorizado']);
        }
        $comment->delete();
        return response()->json(['message' => 'Comentarion eliminado'], 200);
    }
}
