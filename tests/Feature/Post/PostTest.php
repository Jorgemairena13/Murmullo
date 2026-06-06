<?php

use App\Models\User;
use App\Models\Post;
use App\Ai\Agents\PostGenerator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

beforeEach(function () {
    Storage::fake('public');
});

// ─── AUTENTICACIÓN ───────────────────────────────────────────

test('no autenticado no puede crear post', function () {
    $response = $this->postJson('/api/posts', [
        'texto' => 'Mi primer post',
    ]);

    $response->assertStatus(401);
});

test('no autenticado no puede ver posts', function () {
    $response = $this->getJson('/api/posts/1');

    $response->assertStatus(401);
});

// ─── GENERAR TEXTO CON IA ─────────────────────────────────────

test('no autenticado no puede generar texto con IA', function () {
    $file = UploadedFile::fake()->image('test.jpg');

    $response = $this->postJson('/api/posts/generate-text', [
        'imagen' => $file,
    ]);

    $response->assertStatus(401);
});

test('autenticado puede generar texto desde imagen', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $responseText = 'Atardecer increíble en la playa 🌅 #atardecer #playa #verano';

    PostGenerator::fake(function ($prompt, $attachments) use ($responseText) {
        expect($prompt)->toBe('Genera una publicación para esta imagen.');
        expect($attachments)->toHaveCount(1);

        return $responseText;
    });

    $file = UploadedFile::fake()->image('playa.jpg', 800, 600);

    $response = $this->postJson('/api/posts/generate-text', [
        'imagen' => $file,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'texto' => $responseText,
        ]);
});

test('generate-text requiere archivo de imagen valido', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/posts/generate-text', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['imagen']);
});

test('generate-text rechaza archivo que no es imagen', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $file = UploadedFile::fake()->create('documento.pdf', 100);

    $response = $this->postJson('/api/posts/generate-text', [
        'imagen' => $file,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['imagen']);
});

// ─── CREAR POST ──────────────────────────────────────────────

test('usuario autenticado puede crear un post', function () {
    $uploadApi = Mockery::mock(\Cloudinary\Api\Upload\UploadApi::class);
    $uploadApi->shouldReceive('upload')
        ->once()
        ->andReturn(new \Cloudinary\Api\ApiResponse(
            ['secure_url' => 'https://res.cloudinary.com/test.jpg'],
            []
        ));
    $cloudinaryMock = Mockery::mock(\Cloudinary\Cloudinary::class);
    $cloudinaryMock->shouldReceive('uploadApi')->once()->andReturn($uploadApi);
    app()->instance(\Cloudinary\Cloudinary::class, $cloudinaryMock);

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/posts', [
        'texto' => 'Mi primer post',
        'imagen' => UploadedFile::fake()->image('post.jpg'),
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'texto', 'imagen_url', 'created_at', 'user', 'likes_count', 'comments_count', 'is_liked'],
        ]);

    $this->assertDatabaseHas('posts', [
        'user_id' => $user->id,
        'texto' => 'Mi primer post',
    ]);
});

// ─── VER POST ────────────────────────────────────────────────

test('usuario puede ver un post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/posts/{$post->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $post->id)
        ->assertJsonPath('data.texto', $post->texto);
});

test('usuario puede ver posts de un usuario', function () {
    $owner = User::factory()->create();
    Post::factory(3)->create(['user_id' => $owner->id]);

    $viewer = User::factory()->create();
    Sanctum::actingAs($viewer);

    $response = $this->getJson("/api/users/{$owner->id}/posts");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => ['id', 'nombre', 'avatar_url', 'bio', 'posts_count'],
            'posts' => ['data', 'current_page', 'last_page', 'per_page', 'total'],
        ])
        ->assertJsonPath('posts.total', 3);
});

// ─── EDITAR POST ─────────────────────────────────────────────

test('usuario puede editar su propio post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $user->id,
        'texto' => 'Texto original',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/posts/{$post->id}", [
        'texto' => 'Texto actualizado',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.texto', 'Texto actualizado');

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'texto' => 'Texto actualizado',
    ]);
});

test('usuario no puede editar post ajeno', function () {
    $owner = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id]);

    $other = User::factory()->create();
    Sanctum::actingAs($other);

    $response = $this->putJson("/api/posts/{$post->id}", [
        'texto' => 'Intento de edicion',
    ]);

    $response->assertStatus(403);
});

// ─── BORRAR POST ─────────────────────────────────────────────

test('usuario puede borrar su propio post', function () {
    $uploadApi = Mockery::mock(\Cloudinary\Api\Upload\UploadApi::class);
    $uploadApi->shouldReceive('destroy')->once()->andReturn(new \Cloudinary\Api\ApiResponse(
        ['result' => 'ok'],
        []
    ));
    $cloudinaryMock = Mockery::mock(\Cloudinary\Cloudinary::class);
    $cloudinaryMock->shouldReceive('uploadApi')->once()->andReturn($uploadApi);
    app()->instance(\Cloudinary\Cloudinary::class, $cloudinaryMock);

    $user = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $user->id,
        'imagen' => 'https://res.cloudinary.com/dta0ricxm/image/upload/v1/test.jpg',
    ]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/posts/{$post->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

test('usuario no puede borrar post ajeno', function () {
    $owner = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id]);

    $other = User::factory()->create();
    Sanctum::actingAs($other);

    $response = $this->deleteJson("/api/posts/{$post->id}");

    $response->assertStatus(403);
});

// ─── FEED Y EXPLORAR ─────────────────────────────────────────

test('feed muestra posts de usuarios seguidos', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $userC = User::factory()->create();

    $userA->following()->attach($userB);

    $postB = Post::factory()->create(['user_id' => $userB->id, 'texto' => 'Post de B']);
    Post::factory()->create(['user_id' => $userC->id, 'texto' => 'Post de C']);

    Sanctum::actingAs($userA);

    $response = $this->getJson('/api/feed');

    $response->assertStatus(200)
        ->assertJsonStructure(['data', 'pagination'])
        ->assertJsonFragment(['texto' => 'Post de B'])
        ->assertJsonMissing(['texto' => 'Post de C']);
});

test('explorar muestra posts de cuentas publicas', function () {
    $publicUser = User::factory()->create(['is_private' => false]);
    $privateUser = User::factory()->create(['is_private' => true]);

    Post::factory()->create(['user_id' => $publicUser->id, 'texto' => 'Post publico']);
    Post::factory()->create(['user_id' => $privateUser->id, 'texto' => 'Post privado']);

    $viewer = User::factory()->create();
    Sanctum::actingAs($viewer);

    $response = $this->getJson('/api/explorar');

    $response->assertStatus(200)
        ->assertJsonFragment(['texto' => 'Post publico'])
        ->assertJsonMissing(['texto' => 'Post privado']);
});

test('usuario privado oculta posts a no seguidores', function () {
    $privateUser = User::factory()->create(['is_private' => true]);
    Post::factory(2)->create(['user_id' => $privateUser->id]);

    $viewer = User::factory()->create();
    Sanctum::actingAs($viewer);

    $response = $this->getJson("/api/users/{$privateUser->id}/posts");

    $response->assertStatus(200)
        ->assertJsonPath('posts.total', 0)
        ->assertJsonPath('posts.data', []);
});
