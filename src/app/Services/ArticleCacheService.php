<?php

namespace App\Services;

use App\Helpers\CacheKeyHelper;
use Illuminate\Support\Facades\Cache;

class ArticleCacheService
{
    public function rememberList(string $page, callable $callback, int $ttl = 300)
    {
        $cacheKey = CacheKeyHelper::articleListKey($page);

        return Cache::tags(CacheKeyHelper::articleListTag())->remember($cacheKey, $ttl, $callback);
    }

    public function forgetAllList()
    {
        Cache::tags(CacheKeyHelper::articleListTag())->flush();
    }

    public function rememberDetail(string $id, callable $callback, int $ttl = 300)
    {
        $cacheKey = CacheKeyHelper::articleDetailKey($id);

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    public function forgetDetail(string $id)
    {
        Cache::forget(CacheKeyHelper::articleDetailKey($id));
    }
}
