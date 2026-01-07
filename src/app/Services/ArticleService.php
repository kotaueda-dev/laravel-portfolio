<?php

namespace App\Services;

use App\Models\Article;
use App\Repositories\ArticleRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleService
{
    public function __construct(
        private ArticleRepository $repository,
        private ArticleCacheService $cacheService
    ) {}

    /**
     * ページネーション付きで全記事を取得
     */
    public function getAllArticles(int $page, int $perPage = 15): LengthAwarePaginator
    {
        return $this->cacheService->rememberList($page, function () use ($page, $perPage) {
            return $this->repository->getAllPaginated($page, $perPage);
        });
    }

    /**
     * IDで記事を取得（コメント付き）
     */
    public function getArticleWithComments(int $id): ?Article
    {
        return $this->cacheService->rememberDetail($id, function () use ($id) {
            return $this->repository->getWithComments($id);
        });
    }

    /**
     * 新規記事を作成
     */
    public function createArticle(array $data): Article
    {
        $this->cacheService->forgetAllList();

        return $this->repository->create($data);
    }

    /**
     * 記事を更新
     */
    public function updateArticle(Article $article, array $data): bool
    {
        $this->cacheService->forgetAllList();
        $this->cacheService->forgetDetail($article->id);

        return $this->repository->update($article, $data);
    }

    /**
     * 記事にいいねを追加
     */
    public function incrementArticleLike(Article $article): int
    {
        $this->cacheService->forgetAllList();
        $this->cacheService->forgetDetail($article->id);

        return $this->repository->incrementLike($article);
    }

    /**
     * 記事を削除
     */
    public function deleteArticle(Article $article): bool
    {
        $this->cacheService->forgetAllList();
        $this->cacheService->forgetDetail($article->id);

        return $this->repository->delete($article);
    }
}
