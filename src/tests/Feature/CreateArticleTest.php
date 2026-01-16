<?php

use App\Models\User;
use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

test('authenticated user can create article', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/articles', [
        'title' => 'Test Article',
        'content' => 'This is a test article.',
    ]);

    $response
        ->assertValidRequest()
        ->assertValidResponse(201);
    $response->assertJsonStructure([
        'message',
        'article' => ['id', 'title', 'content', 'user_id', 'created_at', 'updated_at'],
    ]);

    $this->assertDatabaseHas('articles', [
        'title' => 'Test Article',
        'user_id' => $user->id,
    ]);
});

test('guest cannot create article', function () {
    $response = $this->postJson('/api/articles', [
        'title' => 'Test Article',
        'content' => 'This is a test article.',
    ]);

    $response
        ->assertValidRequest()
        ->assertValidResponse(401);
});
