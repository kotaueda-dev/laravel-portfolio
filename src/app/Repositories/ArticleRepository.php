<?php

namespace App\Repositories;

use App\Data\StoreArticleData;
use App\Data\UpdateArticleData;
use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleRepository
{
    /**
     * ページネーション付きで全記事を取得
     */
    public function getAllPaginated(int $page, int $perPage = 15): LengthAwarePaginator
    {
        return Article::paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * IDで記事を取得（コメント付き）
     */
    public function getWithComments(int $id): ?Article
    {
        return Article::with('comments')->findOrFail($id);

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
    public function create(StoreArticleData $dto): Article
    {
        $article = Article::create([
            'title' => $dto->title,
            'content' => $dto->content,
            'user_id' => $dto->user_id,
        ]);

        return $article;
    }

    /**
     * 記事を更新
     */
    public function update(UpdateArticleData $dto): bool
    {
        $article = Article::findOrFail($dto->id);

        return $article->update([
            'title' => $dto->title,
            'content' => $dto->content,
        ]);
    }

    /**
     * 記事のいいね数をインクリメント
     */
    public function incrementLike(int $id): int
    {
        $article = Article::findOrFail($id);
        $article->increment('like');

        return $article->refresh()->like;
    }

    /**
     * 記事を削除
     */
    public function delete(int $id): bool
    {
        $article = Article::findOrFail($id);

        return $article->delete();
    }
}
