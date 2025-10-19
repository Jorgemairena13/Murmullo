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
        if($usuarios->isEmpty()){
            return response()->json(['message'=>'No hay usuarios' ],200);
        }
        return response()->json($usuarios,200);

    }

    // Guardar
    public function store(Request $request)
    {
        $validar_datos = Validator::make($request->all(),[
            'nombre'=> 'required|string|max:255',
            'email'=> 'required|email|unique:usuarios,email',
            'bio'=> 'required|string|max:500',
            'is_private'=> 'required|boolean',
            'avatar'=> 'required',
            'password'=> 'required|string|min:6|confirmed',
        ]);

        if($validar_datos->fails()){
            $data = [
                'message' => 'Error en la validacion de datos',
                'errors' => $validar_datos->errors(),
                'status' => 400
            ];
            return response()->json($data,400);
        }
        $usuario =  User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'bio' => $request->bio,
            'is_private' => $request->is_private,
            'avatar' => $request->avatar,
            'password' => bcrypt($request->password)
        ]);

        if(!$usuario){
            $data = [
                'message' => 'Error al crear el usuario',
                'status' => 500
            ];
            return response()->json($data,500);
        }

        $data =[
            'usuario' => $usuario,
            'status' => 200
        ];

        return response()->json($data,201);

    }


    public function show(string $id)
    {
        $usuario = User::find($id);
        if(!$usuario){
            $data = [
                'mesage'=> 'Usuario no encontrado',
                'status'=>404
            ];
            return response()->json($data,404);
        }

        $data = [
            'usuario'=> $usuario,
            'status' =>200
        ];
        return response()->json($data,200);
    }


    public function update(Request $request, string $id)
    {
        $usuario = User::find($id);

        if(!$usuario){
            $data = [
                'mesage'=> 'Usuario no encontrado',
                'status'=>404
            ];
            return response()->json($data,404);
             }
        $validar_datos = Validator::make($request->all(),[
            'nombre'=> 'required|string|max:255',
            'email'=> 'required|email|unique:usuarios,email',
            'bio'=> 'required|string|max:500',
            'is_private'=> 'required|boolean',
            'avatar'=> 'required',
            'password'=> 'required|string|min:6|confirmed',
        ]);
        if($validar_datos->fails()){
            $data = [
                'message'=>'Error en la validacion de datos',
                ''=>$validar_datos->errors(),
                'status'=>400
            ];
            return response()->json($data,400);
        }

        $usuario->nombre = $request->nombre;
        $usuario->email = $request->email;
        $usuario->bio = $request->bio;
        $usuario->is_private = $request->is_private;
        $usuario->avatar = $request->avatar;
        $usuario->password = $request->password;
        $usuario->save();

        $data = [
            'message'=>'Usuario actualizado correctamente',
            'users'=> $usuario,
            'status'=>400
        ];
        return response()->json($data,200);
    }


    public function destroy(string $id)
    {
        $usuario = User::find($id);
        if(!$usuario){
            $data = [
                'mesage'=> 'Usuario no encontrado',
                'status'=>404
            ];
            return response()->json($data,404);
        }
        $usuario->delete();

        $data = [
            'message'=> 'Usuario eliminado',
            'status'=> 200
        ];
        return response()->json($data,200);


    }

    public function follow($id) {}

    public function unFollow($id) {}
}
