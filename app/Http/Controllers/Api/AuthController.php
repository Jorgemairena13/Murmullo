<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validar_datos = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'bio' => 'required|string|max:500',
            'is_private' => 'required|boolean',
            'avatar' => 'nullable|string|max:255',
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
        $usuario =  User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'bio' => $request->bio,
            'is_private' => $request->is_private,
            'avatar' => $request->avatar,
            'password' => Hash::make($request->password)
        ]);

        if (!$usuario) {
            $data = [
                'message' => 'Error al crear el usuario',
                'status' => 500
            ];
            return response()->json($data, 500);
        }

        // Generamos el token
        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'bio' => $usuario->bio,
                'is_private' => $usuario->is_private,
                'avatar' => $usuario->avatar,
            ],
            'token' => $token,
            'status' => 201
        ], 201);


    }
    public function login()
    {
        
    }
    public function logout()
    {
        return response()->json([
            'message' => 'Estamos en la función de cerrar sesion'
        ]);
    }
    public function getUser()
    {
        return response()->json([
            'message' => '<h1>Usuario devuelto correctamente</h1>'
        ]);
    }
}
