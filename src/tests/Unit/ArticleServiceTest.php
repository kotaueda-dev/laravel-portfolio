<?php

namespace Tests\Unit;

use App\Data\StoreArticleData;
use App\Models\Article;
use App\Repositories\ArticleRepository;
use App\Services\ArticleCacheService;
use App\Services\ArticleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $articleService;

    protected $articleRepository;

    protected $articleCacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->articleCacheService = $this->createMock(ArticleCacheService::class);
        $this->articleRepository = $this->createMock(ArticleRepository::class);
        $this->articleService = new ArticleService($this->articleRepository, $this->articleCacheService);
    }

    public function test_create_article_calls_repository_and_clears_cache()
    {
        $data = ['title' => 'Test Title', 'content' => 'Test Content'];
        $dto = new StoreArticleData(
            title: 'Test Title',
            content: 'Test Content',
            user_id: 1,
        );
        $this->articleCacheService->expects($this->once())->method('forgetAllList');
        $this->articleCacheService->expects($this->never())->method('forgetDetail');
        $this->articleRepository->expects($this->once())->method('create')->with($dto)->willReturn(new Article((array) $dto));

        $this->articleService->create($dto);
    }

    public function test_update_article_calls_repository_and_clears_cache()
    {
        $article = Article::factory()->create();
        $data = ['title' => 'Updated Title'];
        $this->articleCacheService->expects($this->once())->method('forgetAllList');
        $this->articleCacheService->expects($this->once())->method('forgetDetail')->with($article->id);
        $this->articleRepository->expects($this->once())->method('update')->with($article->id, $data)->willReturn(true);

        $this->articleService->update($article->id, $data);
    }

    public function test_delete_article_calls_repository_and_clears_cache()
    {
        $article = Article::factory()->create();
        $this->articleCacheService->expects($this->once())->method('forgetAllList');
        $this->articleCacheService->expects($this->once())->method('forgetDetail')->with($article->id);
        $this->articleRepository->expects($this->once())->method('delete')->with($article->id)->willReturn(true);

        $this->articleService->delete($article->id);
    }

    public function test_get_article_with_comments_returns_null_if_not_found()
    {
        $this->articleCacheService->expects($this->once())
            ->method('rememberDetail')
            ->with(999, $this->isType('callable'))
            ->willReturn(null);

        $result = $this->articleService->getWithComments(999);

        $this->assertNull($result);
    }

    // 他のテストケースも追加可能
}
