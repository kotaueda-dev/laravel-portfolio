<?php

use App\Models\Article;
use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

it('can increment article likes', function () {
    // Arrange
    $article = Article::factory()->create(['like' => 0]);

    // Act
    $response = $this->postJson("/api/articles/{$article->id}/likes");

    // Assert
    $response
        ->assertValidRequest()
        ->assertValidResponse(200);
    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'like' => 1,
    ]);
});
