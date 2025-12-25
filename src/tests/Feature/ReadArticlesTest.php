<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spectator\Spectator;
use Tests\TestCase;

class ReadArticlesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('api-docs.json');
    }

    #[Test]
    public function it_can_fetch_paginated_articles()
    {
        // Arrange
        Article::factory()->count(config('pagination.default_per_page'))->create();

        // Act
        $response = $this->getJson('/api/articles');

        // Assert
        $response
            ->assertValidRequest()
            ->assertValidResponse(200);
        $response->assertJsonCount(config('pagination.default_per_page'), 'data');
    }
}
