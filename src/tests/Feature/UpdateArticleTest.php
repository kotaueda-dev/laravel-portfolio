<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spectator\Spectator;
use Tests\TestCase;

class UpdateArticleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('api-docs.json');
    }

    #[Test]
    public function test_update_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson("/api/articles/{$article->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated Content',
        ]);

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Updated Title',
            'content' => 'Updated Content',
        ]);
    }

    #[Test]
    public function test_unauthorized_update_article()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->putJson("/api/articles/{$article->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated Content',
        ]);

        $response
            ->assertValidRequest()
            ->assertValidResponse(403);
    }
}
