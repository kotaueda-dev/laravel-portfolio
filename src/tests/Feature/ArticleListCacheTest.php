<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ArticleCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArticleListCacheTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index_uses_cache_service_and_returns_paginated_list_for_page_1()
    {
        $perPage = (int) config('pagination.default_per_page');

        Article::factory()->count($perPage + 3)->create();

        $mock = Mockery::mock(ArticleCacheService::class);
        $this->app->instance(ArticleCacheService::class, $mock);

        $mock->shouldReceive('rememberList')
            ->once()
            ->with('1', Mockery::type('callable'))
            ->andReturnUsing(function ($page, $callback) {
                return $callback();
            });

        $response = $this->getJson('/api/articles');
        $response->assertStatus(200);
        $response->assertJsonCount($perPage, 'data');
    }

    #[Test]
    public function test_index_uses_cache_service_with_page_param_and_returns_page_2()
    {
        $perPage = (int) config('pagination.default_per_page');

        $total = $perPage + 5;
        Article::factory()->count($total)->create();

        $mock = Mockery::mock(ArticleCacheService::class);
        $this->app->instance(ArticleCacheService::class, $mock);

        $mock->shouldReceive('rememberList')
            ->once()
            ->with('2', Mockery::type('callable'))
            ->andReturnUsing(function ($page, $callback) {
                return $callback();
            });

        $response = $this->getJson('/api/articles?page=2');
        $response->assertStatus(200);

        $expectedCount = max(0, $total - $perPage);
        $response->assertJsonCount($expectedCount, 'data');
    }

    #[Test]
    public function test_index_returns_400_on_invalid_page_param()
    {
        $response = $this->getJson('/api/articles?page=0');
        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'Invalid parameter.']);
    }
}
