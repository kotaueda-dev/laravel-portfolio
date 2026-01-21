<?php

use App\Models\Article;
use App\Models\User;
use App\Services\ArticleCacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Redis::connection('cache')->flushdb();

    $this->cache = $this->app->make(ArticleCacheService::class);
});

test('記事一覧がキャッシュされる', function () {
    $perPage = (int) config('pagination.default_per_page');
    $totalArticles = $perPage + 2;
    Article::factory()->count($totalArticles)->create();

    $response1 = $this->getJson('/api/articles?page=1');
    $response1->assertStatus(200);

    $cached = $this->cache->getList('1');
    expect($cached)->not->toBeNull();
    expect($totalArticles)->toEqual($cached->total());
    expect($totalArticles)->toEqual($response1->json('meta.total'));
});

test('記事更新時に一覧キャッシュがクリアされる', function () {
    $user = \App\Models\User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    $this->getJson('/api/articles');
    expect($this->cache->getList('1'))->not->toBeNull();

    $this->actingAs($user);
    $this->putJson("/api/articles/{$article->id}", [
        'title' => 'updated title',
    ])->assertStatus(200);

    expect($this->cache->getList('1'))->toBeNull();
});

test('記事作成時に一覧キャッシュがクリアされる', function () {
    $user = \App\Models\User::factory()->create();
    Article::factory()->count(3)->create();

    $this->getJson('/api/articles');
    expect($this->cache->getList('1'))->not->toBeNull();

    $this->actingAs($user);
    $this->postJson('/api/articles', [
        'title' => 'new article',
        'content' => 'body',
    ])->assertStatus(201);

    expect($this->cache->getList('1'))->toBeNull();
});

test('記事削除時に一覧キャッシュがクリアされる', function () {
    $user = \App\Models\User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    $this->getJson('/api/articles');
    expect($this->cache->getList('1'))->not->toBeNull();

    $this->actingAs($user);
    $this->deleteJson("/api/articles/{$article->id}")->assertStatus(200);

    expect($this->cache->getList('1'))->toBeNull();
});

test('コメント作成時に一覧キャッシュがクリアされる', function () {
    $user = \App\Models\User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    $this->getJson('/api/articles');
    expect($this->cache->getList('1'))->not->toBeNull();

    $this->postJson("/api/articles/{$article->id}/comments", [
        'message' => 'test comment',
    ])->assertStatus(201);

    expect($this->cache->getList('1'))->toBeNull();
});

test('記事詳細がキャッシュされ、更新時に無効化される', function () {
    // 記事を作成
    $user = User::factory()->create();
    $this->actingAs($user);
    $article = Article::factory()->create(['user_id' => $user->id]);
    $originalTitle = $article->title;

    // 1. 詳細記事取得でキャッシュが作成されることを確認
    $response1 = $this->getJson("/api/articles/{$article->id}");
    $response1->assertStatus(200);

    $cached1 = $this->cache->getDetail($article->id);
    expect($cached1)->not->toBeNull();
    expect($originalTitle)->toEqual($cached1->title);
    expect($originalTitle)->toEqual($response1->json('title'));

    // 2. DBを直接更新してキャッシュが古いままであることを確認
    $article->update(['title' => 'DB Direct Update Title']);

    $response2 = $this->getJson("/api/articles/{$article->id}");
    $response2->assertStatus(200);

    $cached2 = $this->cache->getDetail($article->id);
    expect($cached2)->not->toBeNull();
    expect($originalTitle)->toEqual($cached2->title);
    expect($originalTitle)->toEqual($response2->json('title'));

    // 3. 記事の更新でキャッシュが削除されることを確認
    $response3 = $this->putJson("/api/articles/{$article->id}", [
        'title' => 'API Updated Title',
    ]);
    $response3->assertStatus(200);

    $cached3 = $this->cache->getDetail($article->id);
    expect($cached3)->toBeNull();

    // 4. 再度詳細記事取得で新しいデータがキャッシュされていることを確認
    $response4 = $this->getJson("/api/articles/{$article->id}");
    $response4->assertStatus(200);

    $cached4 = $this->cache->getDetail($article->id);
    expect($cached4)->not->toBeNull();
    expect('API Updated Title')->toEqual($cached4->title);
    expect('API Updated Title')->toEqual($response4->json('title'));
});

test('いいねがキャッシュを無効化する', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $article = Article::factory()->create(['user_id' => $user->id, 'like' => 0]);

    $this->getJson("/api/articles/{$article->id}");

    // Ensure cached
    $cached = $this->cache->getDetail($article->id);
    expect($cached)->not->toBeNull();

    // Hit like endpoint
    $likeResponse = $this->postJson("/api/articles/{$article->id}/likes");
    $likeResponse->assertStatus(200);

    // Cache should be invalidated
    $cachedAfter = $this->cache->getDetail($article->id);
    expect($cachedAfter)->toBeNull();
});

test('コメント作成がキャッシュを無効化する', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $article = Article::factory()->create(['user_id' => $user->id]);

    $this->getJson("/api/articles/{$article->id}");

    // Ensure cached
    $cached = $this->cache->getDetail($article->id);
    expect($cached)->not->toBeNull();

    // Post a comment to the article
    $commentResponse = $this->postJson("/api/articles/{$article->id}/comments", [
        'message' => 'This is a test comment',
    ]);
    $commentResponse->assertStatus(201);

    // Cache should be invalidated
    $cachedAfter = $this->cache->getDetail($article->id);
    expect($cachedAfter)->toBeNull();
});
