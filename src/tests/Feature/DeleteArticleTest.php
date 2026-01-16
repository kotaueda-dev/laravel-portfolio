<?php

use App\Models\Article;
use App\Models\User;
use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

test('記事を削除できる', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/api/articles/{$article->id}");

    $response
        ->assertValidRequest()
        ->assertValidResponse(200);
    $this->assertDatabaseMissing('articles', [
        'id' => $article->id,
    ]);
});

test('権限がない記事は削除できない', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->deleteJson("/api/articles/{$article->id}");

    $response
        ->assertValidRequest()
        ->assertValidResponse(403);
    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
    ]);
});
