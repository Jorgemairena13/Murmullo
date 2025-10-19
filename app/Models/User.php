<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios';
    protected $fillable = [
        'nombre',
        'email',
        'bio',
        'is_private',
        'avatar',
        'password',
    ];


    protected $hidden = [
        'password',
    ];

    // Registro de usuario
    public  function registerUser(array $data) {}

    // Login de usuario
    public static function loginUser(string $email, string $password) {}
}
