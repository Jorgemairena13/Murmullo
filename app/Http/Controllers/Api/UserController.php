<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\FollowRequest;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::all(['id', 'nombre', 'username', 'avatar', 'bio', 'is_private']);
        if ($usuarios->isEmpty()) {
            return response()->json(['message' => 'No hay usuarios'], 200);
        }
        return response()->json($usuarios, 200);
    }

    public function show(string $id)
    {
        $usuario = User::withCount(['posts', 'followers', 'following'])
            ->withExists(['followers as is_following' => function ($q) {
                $q->where('seguidor_id', auth()->id());
            }])
            ->find($id);
        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'status' => 404
            ], 404);
        }
        if ($usuario->is_private && auth()->check() && auth()->id() !== $usuario->id) {
            $isFollowing = $usuario->followers()
                ->where('seguidor_id', auth()->id())
                ->exists();
            if ($isFollowing) {
                return response()->json([
                    'usuario' => [
                        'id' => $usuario->id,
                        'nombre' => $usuario->nombre,
                        'username' => $usuario->username,
                        'avatar_url' => $usuario->avatar && str_starts_with($usuario->avatar, 'http') ? $usuario->avatar : null,
                        'bio' => $usuario->bio,
                        'is_private' => true,
                        'is_following' => true,
                        'posts_count' => $usuario->posts_count,
                        'followers_count' => $usuario->followers_count,
                        'following_count' => $usuario->following_count,
                    ],
                    'has_pending_request' => false,
                    'status' => 200
                ], 200);
            }
            $hasPendingRequest = $usuario->hasPendingFollowRequestFrom(auth()->user());
            return response()->json([
                'usuario' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre,
                    'avatar_url' => $usuario->avatar && str_starts_with($usuario->avatar, 'http') ? $usuario->avatar : null,
                    'bio' => $usuario->bio,
                    'is_private' => true,
                    'is_following' => false,
                ],
                'has_pending_request' => $hasPendingRequest,
                'status' => 200
            ], 200);
        }
        return response()->json([
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'username' => $usuario->username,
                'avatar_url' => $usuario->avatar && str_starts_with($usuario->avatar, 'http') ? $usuario->avatar : null,
                'bio' => $usuario->bio,
                'is_private' => $usuario->is_private,
                'is_following' => $usuario->is_following,
                'posts_count' => $usuario->posts_count,
                'followers_count' => $usuario->followers_count,
                'following_count' => $usuario->following_count,
            ],
            'status' => 200
        ], 200);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
        ]);
        $query = $request->query('query');
        $users = User::where(function ($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                  ->orWhere('username', 'like', "%{$query}%");
            })
            ->where('id', '!=', auth()->id())
            ->withExists(['followers as is_following' => function ($q) {
                $q->where('seguidor_id', auth()->id());
            }])
            ->withCount(['posts', 'followers', 'following'])
            ->paginate(15);
        $usersData = collect($users->items())->map(fn($u) => [
            'id' => $u->id,
            'nombre' => $u->nombre,
            'username' => $u->username,
                'avatar_url' => $u->avatar && str_starts_with($u->avatar, 'http') ? $u->avatar : null,
            'bio' => $u->bio,
            'is_private' => $u->is_private,
            'is_following' => $u->is_following,
            'posts_count' => $u->posts_count,
            'followers_count' => $u->followers_count,
            'following_count' => $u->following_count,
        ]);

        return response()->json([
            'users' => $usersData,
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], 200);
    }
}
