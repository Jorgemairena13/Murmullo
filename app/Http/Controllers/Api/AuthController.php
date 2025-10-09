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
}
