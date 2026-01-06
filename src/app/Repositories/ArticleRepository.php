<?php

namespace App\Repositories;

use App\Models\Article;
use App\Services\ArticleCacheService;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleRepository
{
    protected $cacheService;

    public function __construct(ArticleCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * ページネーション付きで全記事を取得
     */
    public function getAllPaginated(int $page, int $perPage = 15): LengthAwarePaginator
    {
        return $this->cacheService->rememberList($page, function () use ($page, $perPage) {
            return Article::paginate($perPage, ['*'], 'page', $page);
        });
    }

    /**
     * IDで記事を取得（コメント付き）
     */
    public function getWithComments(int $id): ?Article
    {
        return $this->cacheService->rememberDetail($id, function () use ($id) {
            return Article::with('comments')->find($id);
        });
    }

    /**
     * IDで記事を取得
     */
    public function getById(int $id): ?Article
    {
        return Article::find($id);
    }

    /**
     * 記事を作成
     */
    public function create(array $data): Article
    {
        $article = Article::create($data);

        return $article;
    }

    /**
     * 記事を更新
     */
    public function update(Article $article, array $data): bool
    {
        $result = $article->update($data);

        return $result;
    }

    /**
     * 記事のいいね数をインクリメント
     */
    public function incrementLike(Article $article): int
    {
        $article->increment('like');

        return $article->like;
    }

    /**
     * 記事を削除
     */
    public function delete(Article $article): bool
    {
        $result = $article->delete();

        return $result;
    }
}
