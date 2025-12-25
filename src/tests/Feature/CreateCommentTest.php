<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spectator\Spectator;
use Tests\TestCase;

class CreateCommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('api-docs.json');
    }

    #[Test]
    public function it_can_create_a_comment_for_an_article()
    {
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
    }
}
