<?php

use App\Models\User;
use App\Models\FollowRequest;
use Laravel\Sanctum\Sanctum;

test('no autenticado no puede ver solicitudes', function () {
    $response = $this->getJson('/api/follow-requests');

    $response->assertStatus(401);
});

test('usuario puede ver solicitudes recibidas pendientes', function () {
    $target = User::factory()->create(['is_private' => true]);
    $follower = User::factory()->create();

    FollowRequest::create([
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);

    Sanctum::actingAs($target);

    $response = $this->getJson('/api/follow-requests');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.seguidor.id', $follower->id);
});

test('usuario no ve solicitudes de otros', function () {
    $target = User::factory()->create(['is_private' => true]);
    $follower = User::factory()->create();
    $other = User::factory()->create();

    FollowRequest::create([
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);

    Sanctum::actingAs($other);

    $response = $this->getJson('/api/follow-requests');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});

test('usuario puede aceptar solicitud', function () {
    $target = User::factory()->create(['is_private' => true]);
    $follower = User::factory()->create();

    $request = FollowRequest::create([
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);

    Sanctum::actingAs($target);

    $response = $this->postJson("/api/follow-requests/{$request->id}/accept");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Solicitud aceptada']);

    $this->assertDatabaseHas('follows', [
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);

    $this->assertDatabaseMissing('follow_requests', [
        'id' => $request->id,
    ]);
});

test('usuario puede rechazar solicitud', function () {
    $target = User::factory()->create(['is_private' => true]);
    $follower = User::factory()->create();

    $request = FollowRequest::create([
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);

    Sanctum::actingAs($target);

    $response = $this->deleteJson("/api/follow-requests/{$request->id}/reject");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Solicitud rechazada']);

    $this->assertDatabaseMissing('follow_requests', [
        'id' => $request->id,
    ]);

    $this->assertDatabaseMissing('follows', [
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);
});

test('no puede aceptar solicitud que no es para el', function () {
    $target = User::factory()->create(['is_private' => true]);
    $other = User::factory()->create();
    $follower = User::factory()->create();

    $request = FollowRequest::create([
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);

    Sanctum::actingAs($other);

    $response = $this->postJson("/api/follow-requests/{$request->id}/accept");

    $response->assertStatus(403);
});

test('no puede rechazar solicitud que no es para el', function () {
    $target = User::factory()->create(['is_private' => true]);
    $other = User::factory()->create();
    $follower = User::factory()->create();

    $request = FollowRequest::create([
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);

    Sanctum::actingAs($other);

    $response = $this->deleteJson("/api/follow-requests/{$request->id}/reject");

    $response->assertStatus(403);
});
