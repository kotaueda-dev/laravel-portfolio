<?php

use App\Models\Article;
use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

it('単一の記事を取得できる', function () {
    // Arrange
    $article = Article::factory()->create();

    // Act
    $response = $this->getJson("/api/articles/{$article->id}");

    // Assert
    $response
        ->assertValidRequest()
        ->assertValidResponse(200);
    $response->assertJsonPath('id', $article->id);
});
