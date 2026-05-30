<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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


    public function update(Request $request, string $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            $data = [
                'mesage' => 'Usuario no encontrado',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $validar_datos = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email,' . $id,
            'bio' => 'required|string|max:500',
            'is_private' => 'required|boolean',
            'avatar' => 'nullable|image|max:51200',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validar_datos->fails()) {
            $data = [
                'message' => 'Error en la validacion de datos',
                'errors' => $validar_datos->errors(),
                'status' => 400
            ];
            return response()->json($data, 400);
        }

        $usuario->nombre = $request->nombre;
        $usuario->email = $request->email;
        $usuario->bio = $request->bio;
        $usuario->is_private = $request->is_private;
        if ($request->hasFile('avatar')) {
            $rutaAvatar = Cloudinary::uploadApi()->upload(
                $request->file('avatar')->getPathname(),
                ['folder' => 'murmullo/avatars']
            )['secure_url'];
            $usuario->avatar = $rutaAvatar;
        }
        $usuario->password = Hash::make($request->password);
        $usuario->save();

        $data = [
            'message' => 'Usuario actualizado correctamente',
            'users' => $usuario,
            'status' => 200
        ];
        return response()->json($data, 200);
    }


    public function destroy(string $id)
    {
        $usuario = User::find($id);
        if (!$usuario) {
            $data = [
                'mesage' => 'Usuario no encontrado',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $usuario->delete();

        $data = [
            'message' => 'Usuario eliminado',
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

    public function follow($id) {}

    public function unFollow($id) {}
}
