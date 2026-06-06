<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validar_datos = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:usuarios,username',
            'email' => 'required|email|unique:usuarios,email',
            'bio' => 'nullable|string|max:500',
            'is_private' => 'nullable|boolean',
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
        if ($request->hasFile('avatar')) {
            $rutaAvatar = Cloudinary::uploadApi()->upload(
                $request->file('avatar')->getPathname(),
                ['folder' => 'murmullo/avatars']
            )['secure_url'];
        } else {
            $rutaAvatar = null;
        }
        $usuario = User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'username' => $request->username,
            'bio' => $request->bio,
            'is_private' => (bool) $request->is_private,
            'avatar' => $rutaAvatar,
            'password' => Hash::make($request->password)
        ]);
        if (!$usuario) {
            $data = [
                'message' => 'Error al crear el usuario',
                'status' => 500
            ];
            return response()->json($data, 500);
        }
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

    public function login(Request $request)
    {
        $validar_datos = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);
        if ($validar_datos->fails()) {
            $data = [
                'message' => 'Error en la validacion de datos',
                'errors' => $validar_datos->errors(),
                'status' => 400
            ];
            return response()->json($data, 400);
        }
        $email = $request->email;
        $password = $request->password;
        $user = User::where('email', $email)->first();
        if (! $user) {
            $data = [
                'message' => 'Error en la validacion',
                'status' => 400
            ];
            return response()->json($data, 400);
        }
        $userPassword = $user->password;
        if (! $userPassword) {
            $data = [
                'message' => 'Error en la validacion',
                'status' => 400
            ];
            return response()->json($data, 400);
        }
        $passwordVerify = Hash::check($password, $userPassword);
        if (! $passwordVerify) {
            $data = [
                'message' => 'Error en la validacion',
                'status' => 400
            ];
            return response()->json($data, 400);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Login correcto',
            'user' => $user,
            'token' => $token,
            'status' => 200
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Sesión cerrada correctamente',
            'status' => 200
        ]);
    }

    public function profile(Request $request)
    {
        $usuario = $request->user()->loadCount(['posts', 'followers', 'following']);

        return response()->json([
            'usuario' => $usuario,
            'status' => 200
        ], 200);
    }

    public function updateProfile(Request $request, string $id)
    {
        $user = $request->user();
        if ((int) $id !== $user->id) {
            return response()->json(['message' => 'No autorizado', 'status' => 403], 403);
        }
        $validar_datos = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:usuarios,username,' . $user->id,
            'email' => 'required|email|unique:usuarios,email,' . $user->id,
            'bio' => 'required|string|max:500',
            'is_private' => 'required|boolean',
            'avatar' => 'nullable|image|max:51200',
            'password' => 'nullable|string|min:6|confirmed',
        ]);
        if ($validar_datos->fails()) {
            return response()->json([
                'message' => 'Error en la validacion de datos',
                'errors' => $validar_datos->errors(),
                'status' => 400
            ], 400);
        }
        $user->nombre = $request->nombre;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->bio = $request->bio;
        $user->is_private = $request->is_private;
        if ($request->hasFile('avatar')) {
            $rutaAvatar = Cloudinary::uploadApi()->upload(
                $request->file('avatar')->getPathname(),
                ['folder' => 'murmullo/avatars']
            )['secure_url'];
            $user->avatar = $rutaAvatar;
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();
        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'users' => $user,
            'status' => 200
        ], 200);
    }

    public function destroyAccount(Request $request, string $id)
    {
        $user = $request->user();
        if ((int) $id !== $user->id) {
            return response()->json(['message' => 'No autorizado', 'status' => 403], 403);
        }
        $user->delete();
        return response()->json([
            'message' => 'Usuario eliminado',
            'status' => 200
        ], 200);
    }
}
