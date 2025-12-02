<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FollowController;
use Illuminate\Support\Facades\Route;

// Registrar usuario
Route::post('/register', [AuthController::class, 'register']);

// Logear usuario
Route::post('/login', [AuthController::class, 'login']);


// Rutas protegidas  login
Route::middleware('auth:sanctum')->group(function () {

    // Cerrar sesion
    Route::post('/logout', [AuthController::class, 'logout']);


    // Ver un perfil
    Route::get('/users/{id}', [UserController::class, 'show']);
    // Actualizar perfil
    Route::put('/users/{id}', [UserController::class, 'update']);
    // Borrar cuenta
    Route::delete('/users/{id}', [UserController::class, 'destroy']);


    // Crear editar y rodo
    Route::apiResource('/posts', PostController::class);
    // Ver posts de un usuario concreto
    Route::get('/users/{user}/posts', [PostController::class, 'getUserPosts']);



    // Dar me gusta
    Route::post('/posts/{post}/like', [LikeController::class, 'store']);
    // Quitar me gusta
    Route::delete('/posts/{post}/like', [LikeController::class, 'destroy']);
    // Crear comentario
    Route::post('/posts/{post}/comment', [CommentController::class,'store']);
    // Eliminar comentario
    Route::delete('/comment/{id}', [CommentController::class,'destroy']);

    // Seguir usuario sin crear
    Route::post('/users/{user}/follow',[FollowController::class,'store']);
    // Dejar de seguir usuario sin crear

    Route::delete('/users/{user}/follow',[FollowController::class,'destroy']);
    Route::get('/feed',[PostController::class,'feed']);
});
