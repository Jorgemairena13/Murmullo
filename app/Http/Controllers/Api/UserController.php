<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    // Guardar
    public function store(Request $request)
    {
        $validar_datos = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'bio' => 'required|string|max:500',
            'is_private' => 'required|boolean',
            'avatar' => 'required |image|max:51200',
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
            $rutaAvatar = $request->file('avatar')->store('avatars', 'public');
        } else {
            $rutaAvatar = null;
        }

        $usuario =  User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'bio' => $request->bio,
            'is_private' => $request->is_private,
            'avatar' => $rutaAvatar,
            'password' => bcrypt($request->password)
        ]);

        if (!$usuario) {
            $data = [
                'message' => 'Error al crear el usuario',
                'status' => 500
            ];
            return response()->json($data, 500);
        }

        $data = [
            'usuario' => $usuario,
            'status' => 200
        ];

        return response()->json($data, 201);
    }


    public function show(string $id)
    {
        $usuario = User::find($id);
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
            'email' => 'required|email|unique:usuarios,email',
            'bio' => 'required|string|max:500',
            'is_private' => 'required|boolean',
            'avatar' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validar_datos->fails()) {
            $data = [
                'message' => 'Error en la validacion de datos',
                '' => $validar_datos->errors(),
                'status' => 400
            ];
            return response()->json($data, 400);
        }

        $usuario->nombre = $request->nombre;
        $usuario->email = $request->email;
        $usuario->bio = $request->bio;
        $usuario->is_private = $request->is_private;
        $usuario->avatar = $request->avatar;
        $usuario->password = bcrypt($request->password);
        $usuario->save();

        $data = [
            'message' => 'Usuario actualizado correctamente',
            'users' => $usuario,
            'status' => 400
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

    public function updatePartial(Request $request, string $id)
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
            'nombre' => 'string|max:255',
            'email' => 'email|unique:usuarios,email',
            'bio' => 'string|max:500',
            'is_private' => 'boolean',
            'avatar' => 'string',
            'password' => 'string|min:6',
        ]);

        if ($validar_datos->fails()) {
            $data = [
                'message' => 'Error en la validacion de datos',
                'errors' => $validar_datos->errors(),
                'status' => 400
            ];
            return response()->json($data,400);
        }
        if ($request->has('nombre')){
            $usuario->nombre = $request->nombre;
        }
        if ($request->has('email')){
            $usuario->email = $request->email;
        }
        if ($request->has('bio')){
            $usuario->bio = $request->bio;
        }
        if ($request->has('is_private')){
            $usuario->is_private = $request->is_private;
        }
        if ($request->has('avatar')){
            $usuario->avatar = $request->avatar;
        }
        if ($request->has('password')){
            $usuario->password = bcrypt($request->password);
        }

        $usuario->save();

        $data = [
            'message'=>'Estudiante actualizado',
            'usuario'=>$usuario,
            'status'=>200
        ];
        return response()->json($data,200);
    }

    public function follow($id) {}

    public function unFollow($id) {}
}
