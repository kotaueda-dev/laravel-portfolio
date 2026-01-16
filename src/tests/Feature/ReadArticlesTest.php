<?php

use App\Models\Article;
use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

it('can fetch paginated articles', function () {
    // Arrange
    Article::factory()->count(config('pagination.default_per_page'))->create();

    // Act
    $response = $this->getJson('/api/articles');

    // Assert
    $response
        ->assertValidRequest()
        ->assertValidResponse(200);
    $response->assertJsonCount(config('pagination.default_per_page'), 'data');
});
