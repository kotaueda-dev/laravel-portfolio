<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Article;

class UpdateLikeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_increment_article_likes()
    {
        // Arrange
        $article = Article::factory()->create(['like' => 0]);

        // Act
        $response = $this->postJson("/api/articles/{$article->id}/likes");

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'like' => 1,
        ]);
    }
}