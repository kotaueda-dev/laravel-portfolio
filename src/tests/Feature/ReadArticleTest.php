<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spectator\Spectator;
use Tests\TestCase;

class ReadArticleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('api-docs.json');
    }

    #[Test]
    public function it_can_fetch_a_single_article()
    {

        // Arrange
        $article = Article::factory()->create();

        // Act
        $response = $this->getJson("/api/articles/{$article->id}");

        // Assert
        $response
            ->assertValidRequest()
            ->assertValidResponse(200);
    }
}
