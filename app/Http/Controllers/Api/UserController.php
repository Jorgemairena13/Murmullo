<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

class UserController extends Controller
{


    // Sacar todos los usuarios
    public function index()
    {
        $usuarios = User::all();
        if ($usuarios->isEmpty()) {
            return response()->json(['message' => 'No hay usuarios'], 200);
        }
        return response()->json($usuarios, 200);
    }

    public function show(string $id)
    {
        $usuario = User::withCount(['posts', 'followers', 'following'])->find($id);
        if (!$usuario) {
            $data = [
                'mesage' => 'Usuario no encontrado',
                'status' => 404
            ];
            return response()->json($data, 404);
        }

        $data = [
            'usuario' => $usuario,
            'status' => 200
        ];
        return response()->json($data, 200);
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

        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], 200);
    }
}
