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

test('usuario puede seguir a usuario publico', function () {
    $follower = User::factory()->create();
    $target = User::factory()->create(['is_private' => false]);

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

test('seguir a usuario publico dos veces es idempotente', function () {
    $follower = User::factory()->create();
    $target = User::factory()->create(['is_private' => false]);

    Sanctum::actingAs($follower);

    $this->postJson("/api/users/{$target->id}/follow")->assertStatus(200);
    $this->postJson("/api/users/{$target->id}/follow")->assertStatus(200);

    $this->assertDatabaseCount('follows', 1);
    $this->assertDatabaseHas('follows', [
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);
});

test('seguir a usuario privado crea solicitud', function () {
    $follower = User::factory()->create();
    $target = User::factory()->create(['is_private' => true]);

    Sanctum::actingAs($follower);

    $response = $this->postJson("/api/users/{$target->id}/follow");

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Solicitud enviada a ' . $target->nombre,
        ]);

    $this->assertDatabaseHas('follow_requests', [
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);

    $this->assertDatabaseMissing('follows', [
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);
});

test('seguir a usuario privado dos veces es idempotente', function () {
    $follower = User::factory()->create();
    $target = User::factory()->create(['is_private' => true]);

    Sanctum::actingAs($follower);

    $this->postJson("/api/users/{$target->id}/follow")->assertStatus(201);
    $this->postJson("/api/users/{$target->id}/follow")->assertStatus(200)
        ->assertJson([
            'message' => 'Ya enviaste una solicitud a ' . $target->nombre,
        ]);

    $this->assertDatabaseCount('follow_requests', 1);
});

test('dejar de seguir elimina tambien solicitud pendiente', function () {
    $follower = User::factory()->create();
    $target = User::factory()->create(['is_private' => true]);

    \App\Models\FollowRequest::create([
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);

    Sanctum::actingAs($follower);

    $response = $this->deleteJson("/api/users/{$target->id}/follow");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('follow_requests', [
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);
});
