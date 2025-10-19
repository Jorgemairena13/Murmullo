<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;



// Conseguir todos los usuarios
Route::get('/users',[UserController::class,'index']);

// Conseguir un usuario
Route::get('/users/{id}',[UserController::class,'show']);

// Crear un usuario
Route::post('/users',[UserController::class,'store']);

// Actualizar un usuario
Route::put('users/{id}', [UserController::class,'update']);

// Actualizar parte de un usuario
Route::patch('users/{id}',[UserController::class,'updatePartial']);

// Borrar un usuario
Route::delete('users/{id}', [UserController::class,'destroy']);



