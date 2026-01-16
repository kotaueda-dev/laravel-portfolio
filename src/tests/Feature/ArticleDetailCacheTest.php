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

test('show is cached and invalidated on update', function () {
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

test('like invalidates cache', function () {
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

test('comment creation invalidates cache', function () {
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
