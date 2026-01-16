<?php

use App\Models\Article;
use App\Models\User;
use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

test('記事を更新できる', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/api/articles/{$article->id}", [
        'title' => 'Updated Title',
        'content' => 'Updated Content',
    ]);

    $response
        ->assertValidRequest()
        ->assertValidResponse(200);
    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'Updated Title',
        'content' => 'Updated Content',
    ]);
});

test('権限がない記事は更新できない', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->putJson("/api/articles/{$article->id}", [
        'title' => 'Updated Title',
        'content' => 'Updated Content',
    ]);

    $response
        ->assertValidRequest()
        ->assertValidResponse(403);
});
