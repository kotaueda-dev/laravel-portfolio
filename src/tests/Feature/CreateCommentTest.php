<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Article;
use App\Models\Comment;

class CreateCommentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
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
        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'message' => $data['message'],
        ]);
    }
}