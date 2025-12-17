<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArticleCacheTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_show_is_cached_and_invalidated_on_update()
    {
        // 記事を作成
        $user = User::factory()->create();
        $this->actingAs($user);
        $article = Article::factory()->create(['user_id' => $user->id]);
        $originalTitle = $article->title;
        $cacheKey = "article:{$article->id}";

        // すべてのキャッシュを削除
        Cache::flush();

        // 1. 詳細記事取得でキャッシュが作成されることを確認
        $response1 = $this->getJson("/api/articles/{$article->id}");
        $response1->assertStatus(200);

        $cached1 = Cache::get($cacheKey);
        $this->assertNotNull($cached1);
        $this->assertEquals($cached1->title, $originalTitle);
        $this->assertEquals($response1->json('title'), $originalTitle);

        // 2. DBを直接更新してキャッシュが古いままであることを確認
        $article->update(['title' => 'DB Direct Update Title']);

        $response2 = $this->getJson("/api/articles/{$article->id}");
        $response2->assertStatus(200);

        $cached2 = Cache::get($cacheKey);
        $this->assertNotNull($cached2);
        $this->assertEquals($cached2->title, $originalTitle);
        $this->assertEquals($response2->json('title'), $originalTitle);

        // 3. 記事の更新でキャッシュが削除されることを確認
        $response3 = $this->putJson("/api/articles/{$article->id}", [
            'title' => 'API Updated Title',
        ]);
        $response3->assertStatus(200);

        $cached3 = Cache::get($cacheKey);
        $this->assertNull($cached3);

        // 4. 再度詳細記事取得で新しいデータがキャッシュされていることを確認
        $response4 = $this->getJson("/api/articles/{$article->id}");
        $response4->assertStatus(200);

        $cached4 = Cache::get($cacheKey);
        $this->assertNotNull($cached4);
        $this->assertEquals($cached4->title, 'API Updated Title');
        $this->assertEquals($response4->json('title'), 'API Updated Title');
    }

    #[Test]
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

    #[Test]
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
