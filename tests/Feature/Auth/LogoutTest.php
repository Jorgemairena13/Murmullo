<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('usuario puede cerrar sesion', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/logout');

    $response->assertStatus(200);
});

test('logout requiere autenticacion', function () {
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401);
});
