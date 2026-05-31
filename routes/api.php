<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\FollowRequestController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;
// Registrar usuario
Route::post('/register', [AuthController::class, 'register']);

// Logear usuario
Route::post('/login', [AuthController::class, 'login']);


// Rutas protegidas  login
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [AuthController::class, 'profile']);
    // Cerrar sesion
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/upload', [UploadController::class, 'upload']);

    // Ver un perfil
    Route::get('/search', [UserController::class, 'search']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    // Actualizar perfil
    Route::put('/users/{id}', [AuthController::class, 'updateProfile']);
    // Borrar cuenta
    Route::delete('/users/{id}', [AuthController::class, 'destroyAccount']);


    // Crear editar y todo lo relacionado con posts
    Route::apiResource('/posts', PostController::class)->except(['index']);
    Route::post('/posts/generate-text', [PostController::class, 'generateText']);
    // Ver posts de un usuario concreto
    Route::get('/users/{user}/posts', [PostController::class, 'getUserPosts']);

    // Dar me gusta
    Route::post('/posts/{post}/like', [LikeController::class, 'store']);
    // Quitar me gusta
    Route::delete('/posts/{post}/like', [LikeController::class, 'destroy']);
    // Obtener comentarios de un post
    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
    // Crear comentario
    Route::post('/posts/{post}/comment', [CommentController::class, 'store']);
    // Eliminar comentario
    Route::delete('/comment/{id}', [CommentController::class, 'destroy']);

    // Seguir / dejar de seguir
    Route::post('/users/{user}/follow', [FollowController::class, 'store']);
    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy']);

    // Solicitudes de seguimiento (follow requests)
    Route::get('/follow-requests', [FollowRequestController::class, 'index']);
    Route::post('/follow-requests/{followRequest}/accept', [FollowRequestController::class, 'accept']);
    Route::delete('/follow-requests/{followRequest}/reject', [FollowRequestController::class, 'reject']);
    Route::get('/feed', [PostController::class, 'feed']);
    Route::get('/explorar', [PostController::class, 'explorar']);
});
