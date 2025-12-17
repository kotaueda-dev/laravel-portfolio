<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateLikeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
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
