<?php

use App\Models\Article;
use App\Services\ArticleCacheService;
use Illuminate\Support\Facades\Redis;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Redis::connection('cache')->flushdb();

    $this->cache = $this->app->make(ArticleCacheService::class);
});

test('index is cached', function () {
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

test('index cache is cleared on update', function () {
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

test('index cache is cleared on store', function () {
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

test('index cache is cleared on delete', function () {
    $user = \App\Models\User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    $this->getJson('/api/articles');
    expect($this->cache->getList('1'))->not->toBeNull();

    $this->actingAs($user);
    $this->deleteJson("/api/articles/{$article->id}")->assertStatus(200);

    expect($this->cache->getList('1'))->toBeNull();
});

test('index cache is cleared on comment store', function () {
    $user = \App\Models\User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    $this->getJson('/api/articles');
    expect($this->cache->getList('1'))->not->toBeNull();

    $this->postJson("/api/articles/{$article->id}/comments", [
        'message' => 'test comment',
    ])->assertStatus(201);

    expect($this->cache->getList('1'))->toBeNull();
});
