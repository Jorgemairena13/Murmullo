<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Models\Post;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;



// Conseguir todos los usuarios
Route::get('/users', [UserController::class, 'index']);

// Conseguir un usuario
Route::get('/users/{id}', [UserController::class, 'show']);

// Crear un usuario
Route::post('/users', [UserController::class, 'store']);

// Actualizar un usuario
Route::put('users/{id}', [UserController::class, 'update']);

// Actualizar parte de un usuario
Route::patch('users/{id}', [UserController::class, 'updatePartial']);

// Borrar un usuario
Route::delete('users/{id}', [UserController::class, 'destroy']);


// Registar usuario
Route::post('register', [AuthController::class, 'register']);

// Logear usuario
Route::post('login', [AuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {
    // Cerra sesion
    Route::delete('/logout', [AuthController::class, 'logout']);
    // Posts
    Route::apiResource('/posts', PostController::class);
    // Sacar post del usuario
    Route::get('/users/{user}/posts', [PostController::class, 'getUserPosts']);
    // Dar me gusta
    Route::post('/posts/{post}/like',[LikeController::class,'store']);
    // Quitar me gusta
    Route::delete('/posts/{post}/like',[LikeController::class,'destroy']);
});
