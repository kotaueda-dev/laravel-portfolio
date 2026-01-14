<?php

namespace App\Services;

use App\Helpers\CacheKeyHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArticleCacheService
{
    public function rememberList(int $page, callable $callback, int $ttl = 300): mixed
    {
        $cacheKey = CacheKeyHelper::articleListKey($page);

        return Cache::tags(CacheKeyHelper::articleListTag())->remember($cacheKey, $ttl, $callback);
    }

    public function getList(int $page): mixed
    {
        $cacheKey = CacheKeyHelper::articleListKey($page);

        return Cache::tags(CacheKeyHelper::articleListTag())->get($cacheKey);
    }

    public function forgetAllList(): void
    {
        Cache::tags(CacheKeyHelper::articleListTag())->flush();

        Log::debug('記事一覧のキャッシュを削除しました。');
    }

    public function rememberDetail(int $id, callable $callback, int $ttl = 300): mixed
    {
        $cacheKey = CacheKeyHelper::articleDetailKey($id);

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    public function getDetail(int $id): mixed
    {
        $cacheKey = CacheKeyHelper::articleDetailKey($id);

        return Cache::get($cacheKey);
    }

    public function forgetDetail(int $id): void
    {
        Cache::forget(CacheKeyHelper::articleDetailKey($id));

        Log::debug('記事詳細のキャッシュを削除しました。', ['article_id' => $id]);
    }
}
