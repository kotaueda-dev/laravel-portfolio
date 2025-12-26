<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ArticleCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArticleListCacheTest extends TestCase
{
    use RefreshDatabase;

    protected ArticleCacheService $cache;

    protected function setUp(): void
    {
        parent::setUp();
        Redis::connection('cache')->flushdb();

        $this->cache = $this->app->make(ArticleCacheService::class);
    }

    // 一覧ページを取得した際にキャッシュされることを確認するテスト
    #[Test]
    public function test_index_is_cached()
    {
        $perPage = (int) config('pagination.default_per_page');
        $totalArticles = $perPage + 2;
        Article::factory()->count($totalArticles)->create();

        $response1 = $this->getJson('/api/articles?page=1');
        $response1->assertStatus(200);

        $cached = $this->cache->getList('1');
        $this->assertNotNull($cached);
        $this->assertEquals($cached->total(), $totalArticles);
        $this->assertEquals($response1->json('meta.total'), $totalArticles);
    }

    // 記事を更新した際に記事一覧のキャッシュがクリアされることを確認するテスト
    #[Test]
    public function test_index_cache_is_cleared_on_update()
    {
        $user = \App\Models\User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id]);

        $this->getJson('/api/articles');
        $this->assertNotNull($this->cache->getList('1'));

        $this->actingAs($user);
        $this->putJson("/api/articles/{$article->id}", [
            'title' => 'updated title',
        ])->assertStatus(200);

        $this->assertNull($this->cache->getList('1'));
    }

    // 記事を投稿した際に記事一覧のキャッシュがクリアされることを確認するテスト
    #[Test]
    public function test_index_cache_is_cleared_on_store()
    {
        $user = \App\Models\User::factory()->create();
        Article::factory()->count(3)->create();

        $this->getJson('/api/articles');
        $this->assertNotNull($this->cache->getList('1'));

        $this->actingAs($user);
        $this->postJson('/api/articles', [
            'title' => 'new article',
            'content' => 'body',
        ])->assertStatus(201);

        $this->assertNull($this->cache->getList('1'));
    }

    // 記事を削除した際に記事一覧のキャッシュがクリアされることを確認するテスト
    #[Test]
    public function test_index_cache_is_cleared_on_delete()
    {
        $user = \App\Models\User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id]);

        $this->getJson('/api/articles');
        $this->assertNotNull($this->cache->getList('1'));

        $this->actingAs($user);
        $this->deleteJson("/api/articles/{$article->id}")->assertStatus(200);

        $this->assertNull($this->cache->getList('1'));
    }

    // コメントが投稿された際に記事一覧のキャッシュがクリアされることを確認するテスト
    #[Test]
    public function test_index_cache_is_cleared_on_comment_store()
    {
        $user = \App\Models\User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id]);

        $this->getJson('/api/articles');
        $this->assertNotNull($this->cache->getList('1'));

        $this->postJson("/api/articles/{$article->id}/comments", [
            'message' => 'test comment',
        ])->assertStatus(201);

        $this->assertNull($this->cache->getList('1'));
    }
}
