<?php

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->postAuthor = User::factory()->create();
    $this->post = Post::factory()->create(['user_id' => $this->postAuthor->id]);
});

test('no autenticado no puede ver comentarios', function () {
    $response = $this->getJson("/api/posts/{$this->post->id}/comments");

    $response->assertStatus(401);
});

test('no autenticado no puede crear comentario', function () {
    $response = $this->postJson("/api/posts/{$this->post->id}/comment", [
        'texto' => 'Mi comentario',
    ]);

    $response->assertStatus(401);
});

test('no puede comentar en post de cuenta privada sin seguir', function () {
    $privateUser = User::factory()->create(['is_private' => true]);
    $privatePost = Post::factory()->create(['user_id' => $privateUser->id]);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/posts/{$privatePost->id}/comment", [
        'texto' => 'Comentario no autorizado',
    ]);

    $response->assertStatus(403);
});

test('no puede ver comentarios de post de cuenta privada sin seguir', function () {
    $privateUser = User::factory()->create(['is_private' => true]);
    $privatePost = Post::factory()->create(['user_id' => $privateUser->id]);
    Comment::factory()->create(['post_id' => $privatePost->id]);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/posts/{$privatePost->id}/comments");

    $response->assertStatus(403);
});

test('no autenticado no puede eliminar comentario', function () {
    $comment = Comment::factory()->create(['post_id' => $this->post->id]);

    $response = $this->deleteJson("/api/comment/{$comment->id}");

    $response->assertStatus(401);
});

test('usuario autenticado puede crear comentario', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/posts/{$this->post->id}/comment", [
        'texto' => 'Mi comentario',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'texto' => 'Mi comentario',
        ]);

    $this->assertDatabaseHas('comentarios', [
        'user_id' => $user->id,
        'post_id' => $this->post->id,
        'texto' => 'Mi comentario',
    ]);
});

test('crear comentario devuelve datos del usuario', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/posts/{$this->post->id}/comment", [
        'texto' => 'Mi comentario',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id', 'texto', 'user_id', 'post_id', 'user' => ['id', 'nombre'],
        ])
        ->assertJsonPath('user.id', $user->id);
});

test('usuario autenticado puede ver comentarios', function () {
    $user = User::factory()->create();
    Comment::factory(3)->create(['post_id' => $this->post->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/posts/{$this->post->id}/comments");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'texto', 'user_id', 'post_id', 'user' => ['id', 'nombre']],
            ],
        ])
        ->assertJsonCount(3, 'data');
});

test('usuario puede eliminar su propio comentario', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $comment = $this->post->comentarios()->create([
        'texto' => 'Comentario a eliminar',
        'user_id' => $user->id,
    ]);

    $response = $this->deleteJson("/api/comment/{$comment->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Comentario eliminado',
        ]);

    $this->assertDatabaseMissing('comentarios', ['id' => $comment->id]);
});

test('usuario no puede eliminar comentario ajeno', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $comment = $this->post->comentarios()->create([
        'texto' => 'Comentario ajeno',
        'user_id' => $owner->id,
    ]);

    Sanctum::actingAs($other);

    $response = $this->deleteJson("/api/comment/{$comment->id}");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'No autorizado',
        ]);

    $this->assertDatabaseHas('comentarios', ['id' => $comment->id]);
});
