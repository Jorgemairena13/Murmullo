<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    private static array $nombres = [
        'Ana García', 'Carlos López', 'María Rodríguez', 'Pedro Sánchez',
        'Laura Martínez', 'David Fernández', 'Sara Torres', 'Miguel Ruiz',
        'Elena Gómez', 'Javier Morales',
    ];

    private static int $index = 0;

    public function definition(): array
    {
        $nombre = self::$nombres[self::$index % count(self::$nombres)];
        $username = Str::slug($nombre) . self::$index;
        self::$index++;

        return [
            'nombre' => $nombre,
            'username' => $username,
            'email' => $username . '@example.com',
            'bio' => 'Bio de ' . $nombre,
            'is_private' => false,
            'avatar' => "https://i.pravatar.cc/200?u={$username}",
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
