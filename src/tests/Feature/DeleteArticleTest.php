<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteArticleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_delete_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson("/api/articles/{$article->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }

    #[Test]
    public function test_unauthorized_delete_article()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->deleteJson("/api/articles/{$article->id}");

        $response->assertStatus(403);
    }
}
