<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ArticleCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_is_cached_and_invalidated_on_update()
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $article = Article::factory()->create(['user_id' => $user->id]);

        // Ensure cache empty
        Cache::flush();

        // Act - first fetch should populate cache
        $response1 = $this->getJson("/api/articles/{$article->id}");
        $response1->assertStatus(200);

        // Modify directly in DB
        $originalTitle = $article->title;
        $article->update(['title' => 'Updated Title']);

        // Second fetch should return cached (old) title unless cache was invalidated
        $response2 = $this->getJson("/api/articles/{$article->id}");
        $response2->assertStatus(200);
        $this->assertStringContainsString($originalTitle, $response2->getContent());
        $this->assertStringNotContainsString('Updated Title', $response2->getContent(), 'Expected cached response to contain old title');

        // Now call update endpoint to trigger cache invalidation
        $updateResponse = $this->putJson("/api/articles/{$article->id}", [
            'title' => 'Updated Title',
        ]);
        $updateResponse->assertStatus(200);

        // Fetch again - should reflect new title
        $response3 = $this->getJson("/api/articles/{$article->id}");
        $response3->assertStatus(200);
        $this->assertStringContainsString('Updated Title', $response3->getContent());
    }

    public function test_like_invalidates_cache()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $article = Article::factory()->create(['user_id' => $user->id, 'like' => 0]);

        Cache::flush();

        $this->getJson("/api/articles/{$article->id}");

        // Ensure cached
        $cached = Cache::get("article:{$article->id}");
        $this->assertNotNull($cached);

        // Hit like endpoint
        $likeResponse = $this->postJson("/api/articles/{$article->id}/likes");
        $likeResponse->assertStatus(200);

        // Cache should be invalidated
        $cachedAfter = Cache::get("article:{$article->id}");
        $this->assertNull($cachedAfter);
    }

    public function test_comment_creation_invalidates_cache()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $article = Article::factory()->create(['user_id' => $user->id]);

        // Ensure cache empty and prime it
        Cache::flush();
        $this->getJson("/api/articles/{$article->id}");

        // Ensure cached
        $cached = Cache::get("article:{$article->id}");
        $this->assertNotNull($cached, 'Expected article to be cached after first GET');

        // Post a comment to the article
        $commentResponse = $this->postJson("/api/articles/{$article->id}/comments", [
            'message' => 'This is a test comment',
        ]);
        $commentResponse->assertStatus(201);

        // Cache should be invalidated
        $cachedAfter = Cache::get("article:{$article->id}");
        $this->assertNull($cachedAfter, 'Expected cache to be cleared after creating a comment');
    }
}
