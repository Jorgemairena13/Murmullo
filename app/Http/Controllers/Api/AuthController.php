<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(){
        return response()->json([
            'message' => 'Estamos en la función de registrar'
        ]);
    }
    public function login(){
        return response()->json([
            'message' => 'Estamos en la función de login'
        ]);
    }
    public function logout(){
        return response()->json([
            'message' => 'Estamos en la función de cerrar sesion'
        ]);
    }
    public function getUser(){
        return response()->json([
            'message' => '<h1>Usuario devuelto correctamente</h1>'
        ]);
    }
}
