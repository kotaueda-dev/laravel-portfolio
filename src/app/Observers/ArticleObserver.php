<?php

namespace App\Observers;

use App\Models\Article;
use App\Services\ArticleCacheService;

class ArticleObserver
{
    public function __construct(
        private ArticleCacheService $cacheService
    ) {}

    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        // 作成時にリスト全体のキャッシュをクリア
        $this->cacheService->forgetAllList();
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        // 更新時はリスト全体とこの記事のキャッシュをクリア
        $this->cacheService->forgetAllList();
        $this->cacheService->forgetDetail($article->id);
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        // 削除時はリスト全体とこの記事のキャッシュをクリア
        $this->cacheService->forgetAllList();
        $this->cacheService->forgetDetail($article->id);
    }
}
