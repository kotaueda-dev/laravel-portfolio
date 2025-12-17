<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReadArticlesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_fetch_paginated_articles()
    {
        // Arrange
        Article::factory()->count(15)->create();

        // Act
        $response = $this->getJson('/api/articles');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data'); // Ensure only 10 articles are returned
    }
}
