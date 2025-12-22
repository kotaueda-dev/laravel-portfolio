<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\ArticleCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArticleDetailCacheTest extends TestCase
{
    use RefreshDatabase;

    protected ArticleCacheService $cache;

    protected function setUp(): void
    {
        parent::setUp();
        Redis::connection('cache')->flushdb();

        $this->cache = $this->app->make(ArticleCacheService::class);
    }

    #[Test]
    public function test_show_is_cached_and_invalidated_on_update()
    {
        // 記事を作成
        $user = User::factory()->create();
        $this->actingAs($user);
        $article = Article::factory()->create(['user_id' => $user->id]);
        $originalTitle = $article->title;

        // 1. 詳細記事取得でキャッシュが作成されることを確認
        $response1 = $this->getJson("/api/articles/{$article->id}");
        $response1->assertStatus(200);

        $cached1 = $this->cache->getDetail($article->id);
        $this->assertNotNull($cached1);
        $this->assertEquals($cached1->title, $originalTitle);
        $this->assertEquals($response1->json('title'), $originalTitle);

        // 2. DBを直接更新してキャッシュが古いままであることを確認
        $article->update(['title' => 'DB Direct Update Title']);

        $response2 = $this->getJson("/api/articles/{$article->id}");
        $response2->assertStatus(200);

        $cached2 = $this->cache->getDetail($article->id);
        $this->assertNotNull($cached2);
        $this->assertEquals($cached2->title, $originalTitle);
        $this->assertEquals($response2->json('title'), $originalTitle);

        // 3. 記事の更新でキャッシュが削除されることを確認
        $response3 = $this->putJson("/api/articles/{$article->id}", [
            'title' => 'API Updated Title',
        ]);
        $response3->assertStatus(200);

        $cached3 = $this->cache->getDetail($article->id);
        $this->assertNull($cached3);

        // 4. 再度詳細記事取得で新しいデータがキャッシュされていることを確認
        $response4 = $this->getJson("/api/articles/{$article->id}");
        $response4->assertStatus(200);

        $cached4 = $this->cache->getDetail($article->id);
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

        $this->getJson("/api/articles/{$article->id}");

        // Ensure cached
        $cached = $this->cache->getDetail($article->id);
        $this->assertNotNull($cached);

        // Hit like endpoint
        $likeResponse = $this->postJson("/api/articles/{$article->id}/likes");
        $likeResponse->assertStatus(200);

        // Cache should be invalidated
        $cachedAfter = $this->cache->getDetail($article->id);
        $this->assertNull($cachedAfter);
    }

    #[Test]
    public function test_comment_creation_invalidates_cache()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $article = Article::factory()->create(['user_id' => $user->id]);

        $this->getJson("/api/articles/{$article->id}");

        // Ensure cached
        $cached = $this->cache->getDetail($article->id);
        $this->assertNotNull($cached);

        // Post a comment to the article
        $commentResponse = $this->postJson("/api/articles/{$article->id}/comments", [
            'message' => 'This is a test comment',
        ]);
        $commentResponse->assertStatus(201);

        // Cache should be invalidated
        $cachedAfter = $this->cache->getDetail($article->id);
        $this->assertNull($cachedAfter);
    }
}
