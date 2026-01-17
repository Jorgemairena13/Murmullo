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
            'is_private' =>( bool) $request->is_private,
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

    // Funcion de login
    public function login(Request $request)
    {
        $validar_datos = Validator::make($request->all(), [
            'email'    => 'required|email|',
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

        $user = User::where('email',$email)->first();

        if(!$user){
            $data = [
                'message'=>'Error en la validacion',
                'status'=>400

            ];
            return response()->json($data, 400);
        }

        $userMail = $user->email;
        $userPassword = $user->password;

        if(!$userPassword){
            $data = [
                'message'=>'Error en la validacion',
                'status'=>400

            ];
            return response()->json($data, 400);
        }
        $passwordVerify = Hash::check($password,$userPassword);

        if(!$passwordVerify){
            $data = [
                'message'=>'Error en la validacion',
                'status'=>400

            ];
            return response()->json($data, 400);
        }


        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'=> 'Login correcto',
            'token'=> $token,
            'status'=> 200
        ],200);
    }


    public function logout(Request $request)
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
            'status'=>200
        ]);
    }

    public function getUser()
    {
        return response()->json([
            'message' => '<h1>Usuario devuelto correctamente</h1>'
        ]);
    }
}
