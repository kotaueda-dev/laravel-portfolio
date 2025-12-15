<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Article;

class ReadArticleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_fetch_a_single_article()
    {
        // Arrange
        $article = Article::factory()->create();

        // Act
        $response = $this->getJson("/api/articles/{$article->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('id', $article->id);
    }
}