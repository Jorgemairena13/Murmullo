<?php

use App\Models\User;

test('usuario puede registrarse', function () {
    $response = $this->postJson('/api/register', [
        'nombre' => 'Test User',
        'username' => 'testuser',
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

test('registro falla con email duplicado', function () {
    User::factory()->create(['email' => 'duplicado@test.com']);

    $response = $this->postJson('/api/register', [
        'nombre' => 'Test User',
        'username' => 'nuevousuario',
        'email' => 'duplicado@test.com',
        'password' => '123456',
        'password_confirmation' => '123456',
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrorFor('email');
});

test('registro falla con username duplicado', function () {
    User::factory()->create(['username' => 'testuser']);

    $response = $this->postJson('/api/register', [
        'nombre' => 'Test User',
        'username' => 'testuser',
        'email' => 'otro@test.com',
        'password' => '123456',
        'password_confirmation' => '123456',
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrorFor('username');
});

test('registro falla con email invalido', function () {
    $response = $this->postJson('/api/register', [
        'nombre' => 'Test User',
        'username' => 'testuser',
        'email' => 'no-es-email',
        'password' => '123456',
        'password_confirmation' => '123456',
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrorFor('email');
});

test('registro falla con password demasiado corto', function () {
    $response = $this->postJson('/api/register', [
        'nombre' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@test.com',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrorFor('password');
});

test('registro falla con password que no coincide', function () {
    $response = $this->postJson('/api/register', [
        'nombre' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@test.com',
        'password' => '123456',
        'password_confirmation' => '654321',
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrorFor('password');
});

test('registro falla con nombre vacio', function () {
    $response = $this->postJson('/api/register', [
        'nombre' => '',
        'username' => 'testuser',
        'email' => 'test@test.com',
        'password' => '123456',
        'password_confirmation' => '123456',
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrorFor('nombre');
});

test('registro falla con username vacio', function () {
    $response = $this->postJson('/api/register', [
        'nombre' => 'Test User',
        'username' => '',
        'email' => 'test@test.com',
        'password' => '123456',
        'password_confirmation' => '123456',
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrorFor('username');
});

test('registro devuelve token de acceso', function () {
    $response = $this->postJson('/api/register', [
        'nombre' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => '123456',
        'password_confirmation' => '123456',
    ]);

    $response->assertStatus(201);
    $token = $response->json('token');
    expect($token)->not->toBeNull()
        ->and(strlen($token))->toBeGreaterThan(10);

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $response->json('usuario.id'),
    ]);
});

test('registro devuelve datos correctos del usuario', function () {
    $response = $this->postJson('/api/register', [
        'nombre' => 'María García',
        'username' => 'mariagarcia',
        'email' => 'maria@example.com',
        'bio' => 'Hola, soy María',
        'password' => '123456',
        'password_confirmation' => '123456',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'usuario' => [
                'nombre' => 'María García',
                'email' => 'maria@example.com',
                'bio' => 'Hola, soy María',
            ],
            'status' => 201,
        ]);
});

test('registro crea usuario en base de datos', function () {
    $this->postJson('/api/register', [
        'nombre' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => '123456',
        'password_confirmation' => '123456',
    ]);

    $this->assertDatabaseHas('usuarios', [
        'nombre' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
    ]);
});
