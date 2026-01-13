<?php

namespace App\Services;

use App\Models\Article;
use App\Repositories\ArticleRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ArticleService
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private ArticleCacheService $articleCacheService
    ) {}

    /**
     * ページネーション付きで全記事を取得
     */
    public function getAllPaginated(int $page, int $perPage = 15): LengthAwarePaginator
    {
        Log::info('記事一覧の取得を開始します。', ['page' => $page]);

        $articles = $this->articleCacheService->rememberList($page, function () use ($page, $perPage) {
            return $this->articleRepository->getAllPaginated($page, $perPage);
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
    public function getWithComments(int $id): ?Article
    {
        Log::info('記事詳細の取得を開始します。', ['target_id' => $id]);

        $article = $this->articleCacheService->rememberDetail($id, function () use ($id) {
            return $this->articleRepository->getWithComments($id);
        });

        Log::info('記事詳細の取得が完了しました。', [
            'target_id' => $id,
            'found' => $article ? true : false,
        ]);

        return $article;
    }

    /**
     * 新規記事を作成
     */
    public function create(array $data): Article
    {
        Log::info('記事の作成を開始します。');

        $this->articleCacheService->forgetAllList();

        $article = $this->articleRepository->create($data);

        Log::info('記事の作成が完了しました。', ['article_id' => $article->id]);

        return $article;
    }

    /**
     * 記事を更新
     */
    public function update(int $id, array $data): bool
    {
        Log::info('記事の更新を開始します。', ['article_id' => $id]);

        $this->articleCacheService->forgetAllList();
        $this->articleCacheService->forgetDetail($id);

        $result = $this->articleRepository->update($id, $data);

        Log::info('記事の更新が完了しました。', ['article_id' => $id]);

        return $result;
    }

    /**
     * 記事にいいねを追加
     */
    public function incrementLike(int $id): int
    {
        Log::info('いいねの追加を開始します。', [
            'article_id' => $id,
        ]);

        $this->articleCacheService->forgetAllList();
        $this->articleCacheService->forgetDetail($id);
        $newLikeCount = $this->articleRepository->incrementLike($id);

        Log::info('いいねの追加が完了しました。', [
            'article_id' => $id,
            'new_likes' => $newLikeCount,
        ]);

        return $newLikeCount;
    }

    /**
     * 記事を削除
     */
    public function delete(Article $article): bool
    {
        Log::info('記事の削除を開始します。', ['article_id' => $article->id]);

        $this->articleCacheService->forgetAllList();
        $this->articleCacheService->forgetDetail($article->id);

        $result = $this->articleRepository->delete($article);

        Log::info('記事の削除が完了しました。', ['article_id' => $article->id]);

        return $result;
    }
}
