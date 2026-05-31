<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

test('login devuelve token valido', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $token = $response->json('token');
    expect($token)->not->toBeNull()
        ->and(strlen($token))->toBeGreaterThan(10);

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
    ]);
});

test('login devuelve datos del usuario', function () {
    $user = User::factory()->create([
        'nombre' => 'Ana García',
        'email' => 'ana@test.com',
        'bio' => 'Bio de prueba',
        'is_private' => false,
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'ana@test.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Login correcto',
            'status' => 200,
        ])
        ->assertJsonPath('user.email', 'ana@test.com')
        ->assertJsonPath('user.nombre', 'Ana García');
});

test('login falla con email que no existe', function () {
    User::factory()->create([
        'email' => 'existe@test.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'noexiste@test.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Error en la validacion',
            'status' => 400,
        ]);
});

test('login falla con contraseña incorrecta', function () {
    $user = User::factory()->create([
        'email' => 'test@test.com',
        'password' => Hash::make('passwordcorrecta'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@test.com',
        'password' => 'passwordincorrecta',
    ]);

    $response->assertStatus(400);
});

test('login falla con email vacio', function () {
    $response = $this->postJson('/api/login', [
        'email' => '',
        'password' => '123456',
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrorFor('email');
});

test('login falla con password vacio', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'test@test.com',
        'password' => '',
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrorFor('password');
});

test('login falla con email invalido', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'no-es-email',
        'password' => '123456',
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrorFor('email');
});
