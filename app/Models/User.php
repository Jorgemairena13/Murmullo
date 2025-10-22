<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $fillable = [
        'nombre',
        'email',
        'bio',
        'is_private',
        'avatar',
        'password',
    ];
    protected $appends = [
        'avatar_url', 
    ];


    protected $hidden = [
        'password',
    ];
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }


    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->avatar ? Storage::url($this->avatar) : null,
        );
    }
}
