<?php

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

test('like a post crea notificacion al dueño', function () {
    $owner = User::factory()->create();
    $liker = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id]);

    Sanctum::actingAs($liker);

    $this->postJson("/api/posts/{$post->id}/like")->assertStatus(201);

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $owner->id,
        'type' => 'App\Notifications\PostLiked',
    ]);
});

test('like propio no crea notificacion', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->postJson("/api/posts/{$post->id}/like")->assertStatus(201);

    $this->assertDatabaseCount('notifications', 0);
});

test('comentar en post crea notificacion al dueño', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id]);

    Sanctum::actingAs($commenter);

    $this->postJson("/api/posts/{$post->id}/comment", [
        'texto' => 'Qué bonito!',
    ])->assertStatus(201);

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $owner->id,
        'type' => 'App\Notifications\PostCommented',
    ]);
});

test('comentario propio no crea notificacion', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $this->postJson("/api/posts/{$post->id}/comment", [
        'texto' => 'Mi propio comentario',
    ])->assertStatus(201);

    $this->assertDatabaseCount('notifications', 0);
});

test('seguir usuario publico crea notificacion al seguido', function () {
    $follower = User::factory()->create();
    $target = User::factory()->create(['is_private' => false]);

    Sanctum::actingAs($follower);

    $this->postJson("/api/users/{$target->id}/follow")->assertStatus(200);

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $target->id,
        'type' => 'App\Notifications\UserFollowed',
    ]);
});

test('seguir usuario privado crea notificacion de solicitud', function () {
    $follower = User::factory()->create();
    $target = User::factory()->create(['is_private' => true]);

    Sanctum::actingAs($follower);

    $this->postJson("/api/users/{$target->id}/follow")->assertStatus(201);

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $target->id,
        'type' => 'App\Notifications\FollowRequestSent',
    ]);
});

test('aceptar solicitud crea notificacion al solicitante', function () {
    $target = User::factory()->create(['is_private' => true]);
    $follower = User::factory()->create();

    $followRequest = \App\Models\FollowRequest::create([
        'seguidor_id' => $follower->id,
        'seguido_id' => $target->id,
    ]);

    Sanctum::actingAs($target);

    $this->postJson("/api/follow-requests/{$followRequest->id}/accept")->assertStatus(200);

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $follower->id,
        'type' => 'App\Notifications\FollowRequestAccepted',
    ]);
});

test('usuario puede ver sus notificaciones', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $user->notify(new \App\Notifications\UserFollowed($other));

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/notifications');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'user_followed');
});

test('no autenticado no puede ver notificaciones', function () {
    $response = $this->getJson('/api/notifications');

    $response->assertStatus(401);
});

test('marcar notificaciones como leidas', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $user->notify(new \App\Notifications\UserFollowed($other));

    Sanctum::actingAs($user);

    $this->postJson('/api/notifications/read-all')->assertStatus(200);

    $this->assertDatabaseMissing('notifications', [
        'notifiable_id' => $user->id,
        'read_at' => null,
    ]);
});

test('unread_count refleja notificaciones no leidas', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $user->notify(new \App\Notifications\UserFollowed($other));

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/notifications');

    $response->assertStatus(200)
        ->assertJsonPath('unread_count', 1);
});
