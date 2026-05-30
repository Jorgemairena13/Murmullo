<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('usuario puede registrarse', function () {
    $response = $this->postJson('/api/register', [
        'nombre' => 'Test User',
        'email' => 'test@example.com',
        'password' => '123456',
        'password_confirmation' => '123456',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'usuario' => ['id', 'nombre', 'email', 'avatar', 'bio'],
            'token',
            'status',
        ]);

    $this->assertDatabaseHas('usuarios', [
        'email' => 'test@example.com',
    ]);
});

test('usuario puede iniciar sesion', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user',
            'token',
            'status',
        ]);
});

test('usuario puede cerrar sesion', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/logout');

    $response->assertStatus(200);
});

test('login falla con credenciales incorrectas', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'noexiste@test.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(400);
});

test('registro falla con datos invalidos', function () {
    $response = $this->postJson('/api/register', [
        'nombre' => '',
        'email' => 'no-es-email',
        'password' => '123',
    ]);

    $response->assertStatus(400);
});
