<?php

use App\Data\StoreArticleData;
use App\Data\UpdateArticleData;
use App\Models\Article;
use App\Repositories\ArticleRepository;
use App\Services\ArticleCacheService;
use App\Services\ArticleService;

uses(Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->articleCacheService = $this->createMock(ArticleCacheService::class);
    $this->articleRepository = $this->createMock(ArticleRepository::class);
    $this->articleService = new ArticleService($this->articleRepository, $this->articleCacheService);
});

test('記事作成時にリポジトリを呼び出しキャッシュをクリアする', function () {
    $dto = new StoreArticleData(
        title: 'Test Title',
        content: 'Test Content',
        user_id: 1,
    );

    $this->articleCacheService->expects($this->once())->method('forgetAllList');
    $this->articleCacheService->expects($this->never())->method('forgetDetail');
    $this->articleRepository->expects($this->once())->method('create')->with($dto)->willReturn(new Article($dto->toArray()));

    $this->articleService->create($dto);
});

test('記事更新時にリポジトリを呼び出しキャッシュをクリアする', function () {
    $article = Article::factory()->create();

    $dto = new UpdateArticleData(
        title: 'Test Title',
        content: 'Test Content',
        id: $article->id,
    );

    $this->articleCacheService->expects($this->once())->method('forgetAllList');
    $this->articleCacheService->expects($this->once())->method('forgetDetail')->with($dto->id);
    $this->articleRepository->expects($this->once())->method('update')->with($dto)->willReturn(true);
    $this->articleService->update($dto);
});

test('記事削除時にリポジトリを呼び出しキャッシュをクリアする', function () {
    $article = Article::factory()->create();
    $this->articleCacheService->expects($this->once())->method('forgetAllList');
    $this->articleCacheService->expects($this->once())->method('forgetDetail')->with($article->id);
    $this->articleRepository->expects($this->once())->method('delete')->with($article->id)->willReturn(true);

    $this->articleService->delete($article->id);
});

test('コメント付き記事取得で見つからない場合nullを返す', function () {
    $this->articleCacheService->expects($this->once())
        ->method('rememberDetail')
        ->with(999, $this->callback(fn ($arg) => is_callable($arg)))
        ->willReturn(null);

    $result = $this->articleService->getWithComments(999);

    expect($result)->toBeNull();
});
