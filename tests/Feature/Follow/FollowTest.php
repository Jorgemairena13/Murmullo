<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('no autenticado no puede seguir usuario', function () {
    $user = User::factory()->create();

    $response = $this->postJson("/api/users/{$user->id}/follow");

    $response->assertStatus(401);
});

test('no autenticado no puede dejar de seguir', function () {
    $user = User::factory()->create();

    $response = $this->deleteJson("/api/users/{$user->id}/follow");

    $response->assertStatus(401);
});

test('usuario puede seguir a otro usuario', function () {
    $follower = User::factory()->create();
    $target = User::factory()->create();

    Sanctum::actingAs($follower);

    $response = $this->postJson("/api/users/{$target->id}/follow");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Siguiendo a ' . $target->nombre,
        ]);

    $this->assertDatabaseHas('follows', [
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);
});

test('usuario puede dejar de seguir a otro usuario', function () {
    $follower = User::factory()->create();
    $target = User::factory()->create();

    $follower->following()->attach($target);

    Sanctum::actingAs($follower);

    $response = $this->deleteJson("/api/users/{$target->id}/follow");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Dejaste de seguir a ' . $target->nombre,
        ]);

    $this->assertDatabaseMissing('follows', [
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);
});

test('usuario no puede seguirse a si mismo', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/users/{$user->id}/follow");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'No puedes seguirte a ti mismo',
        ]);
});

test('usuario no puede dejar de seguirse a si mismo', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/users/{$user->id}/follow");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'No puedes dejar seguirte a ti mismo',
        ]);
});

test('seguir dos veces es idempotente', function () {
    $follower = User::factory()->create();
    $target = User::factory()->create();

    Sanctum::actingAs($follower);

    $this->postJson("/api/users/{$target->id}/follow")->assertStatus(200);
    $this->postJson("/api/users/{$target->id}/follow")->assertStatus(200);

    $this->assertDatabaseCount('follows', 1);
    $this->assertDatabaseHas('follows', [
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);
});
