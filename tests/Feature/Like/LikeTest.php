<?php

use App\Models\User;
use App\Models\Post;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->postAuthor = User::factory()->create();
});

test('no autenticado no puede dar like', function () {
    $post = Post::factory()->create(['user_id' => $this->postAuthor->id]);

    $response = $this->postJson("/api/posts/{$post->id}/like");

    $response->assertStatus(401);
});

test('no puede dar like a post de cuenta privada sin seguir', function () {
    $privateUser = User::factory()->create(['is_private' => true]);
    $privatePost = Post::factory()->create(['user_id' => $privateUser->id]);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/posts/{$privatePost->id}/like");

    $response->assertStatus(403);
});

test('no puede quitar like de post de cuenta privada sin seguir', function () {
    $privateUser = User::factory()->create(['is_private' => true]);
    $privatePost = Post::factory()->create(['user_id' => $privateUser->id]);
    $user = User::factory()->create();

    $user->likes()->attach($privatePost);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/posts/{$privatePost->id}/like");

    $response->assertStatus(403);
});

test('no autenticado no puede quitar like', function () {
    $post = Post::factory()->create(['user_id' => $this->postAuthor->id]);

    $response = $this->deleteJson("/api/posts/{$post->id}/like");

    $response->assertStatus(401);
});

test('usuario puede dar like a un post', function () {
    $post = Post::factory()->create(['user_id' => $this->postAuthor->id]);
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/posts/{$post->id}/like");

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'El post tiene un like más',
        ]);

    $this->assertDatabaseHas('likes', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
});

test('usuario puede quitar like de un post', function () {
    $post = Post::factory()->create(['user_id' => $this->postAuthor->id]);
    $user = User::factory()->create();

    $user->likes()->attach($post);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/posts/{$post->id}/like");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'El post tiene un like menos',
        ]);

    $this->assertDatabaseMissing('likes', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
});

test('dar like dos veces es idempotente', function () {
    $post = Post::factory()->create(['user_id' => $this->postAuthor->id]);
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson("/api/posts/{$post->id}/like")->assertStatus(201);
    $this->postJson("/api/posts/{$post->id}/like")->assertStatus(201);

    $this->assertDatabaseCount('likes', 1);
    $this->assertDatabaseHas('likes', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
});
