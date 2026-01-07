<?php

namespace App\Services;

use App\Models\Article;
use App\Repositories\ArticleRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

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
        Log::info('記事一覧の取得を開始します。', ['page' => $page]);

        $articles = $this->cacheService->rememberList($page, function () use ($page, $perPage) {
            return $this->repository->getAllPaginated($page, $perPage);
        });

        Log::info('記事一覧の取得が完了しました。', [
            'page' => $page,
            'total' => $articles->total(),
        ]);

        return $articles;
    }

    /**
     * IDで記事を取得（コメント付き）
     */
    public function getArticleWithComments(int $id): ?Article
    {
        Log::info('記事詳細の取得を開始します。', ['target_id' => $id]);

        $article = $this->cacheService->rememberDetail($id, function () use ($id) {
            return $this->repository->getWithComments($id);
        });

        Log::info('記事詳細の取得が完了しました。', ['target_id' => $id]);

        return $article;
    }

    /**
     * 新規記事を作成
     */
    public function createArticle(array $data): Article
    {
        Log::info('記事の作成を開始します。');

        $this->cacheService->forgetAllList();

        $article = $this->repository->create($data);

        Log::info('記事の作成が完了しました。', [
            'article_id' => $article->id,
            'title' => $article->title,
        ]);

        return $article;
    }

    /**
     * 記事を更新
     */
    public function updateArticle(Article $article, array $data): bool
    {
        Log::info('記事の更新を開始します。', [
            'article_id' => $article->id,
            'title' => $data['title'] ?? $article->title,
        ]);

        $this->cacheService->forgetAllList();
        $this->cacheService->forgetDetail($article->id);

        $result = $this->repository->update($article, $data);

        Log::info('記事の更新が完了しました。', [
            'article_id' => $article->id,
            'success' => $result,
        ]);

        return $result;
    }

    /**
     * 記事にいいねを追加
     */
    public function incrementArticleLike(Article $article): int
    {
        Log::info('いいねの追加を開始します。', [
            'article_id' => $article->id,
            'current_likes' => $article->like,
        ]);

        $this->cacheService->forgetAllList();
        $this->cacheService->forgetDetail($article->id);

        $newLikeCount = $this->repository->incrementLike($article);

        Log::info('いいねの追加が完了しました。', [
            'article_id' => $article->id,
            'new_likes' => $newLikeCount,
        ]);

        return $newLikeCount;
    }

    /**
     * 記事を削除
     */
    public function deleteArticle(Article $article): bool
    {
        Log::info('記事の削除を開始します。', ['article_id' => $article->id]);

        $this->cacheService->forgetAllList();
        $this->cacheService->forgetDetail($article->id);

        $result = $this->repository->delete($article);

        Log::info('記事の削除が完了しました。', ['article_id' => $article->id]);

        return $result;
    }
}
