<?php

use App\Models\Article;
use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

it('can create a comment for an article', function () {
    // Arrange
    $article = Article::factory()->create();
    $data = [
        'message' => 'This is a test comment.',
    ];

    // Act
    $response = $this->postJson("/api/articles/{$article->id}/comments", $data);

    // Assert
    $response
        ->assertValidRequest()
        ->assertValidResponse(201);
    $this->assertDatabaseHas('comments', [
        'article_id' => $article->id,
        'message' => $data['message'],
    ]);
});
