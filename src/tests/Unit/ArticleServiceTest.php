<?php

namespace Tests\Unit;

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

    protected $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheService = $this->createMock(ArticleCacheService::class);
        $this->articleRepository = $this->createMock(ArticleRepository::class);
        $this->articleService = new ArticleService($this->articleRepository, $this->cacheService);
    }

    public function test_create_article_calls_repository_and_clears_cache()
    {
        $data = ['title' => 'Test Title', 'content' => 'Test Content'];
        $this->cacheService->expects($this->once())->method('forgetAllList');
        $this->cacheService->expects($this->never())->method('forgetDetail');
        $this->articleRepository->expects($this->once())->method('create')->with($data)->willReturn(new Article($data));

        $this->articleService->createArticle($data);
    }

    public function test_update_article_calls_repository_and_clears_cache()
    {
        $article = Article::factory()->create();
        $data = ['title' => 'Updated Title'];
        $this->cacheService->expects($this->once())->method('forgetAllList');
        $this->cacheService->expects($this->once())->method('forgetDetail')->with($article->id);
        $this->articleRepository->expects($this->once())->method('update')->with($article, $data)->willReturn(true);

        $this->articleService->updateArticle($article, $data);
    }

    public function test_delete_article_calls_repository_and_clears_cache()
    {
        $article = Article::factory()->create();
        $this->cacheService->expects($this->once())->method('forgetAllList');
        $this->cacheService->expects($this->once())->method('forgetDetail')->with($article->id);
        $this->articleRepository->expects($this->once())->method('delete')->with($article)->willReturn(true);

        $this->articleService->deleteArticle($article);
    }

    public function test_get_article_with_comments_returns_null_if_not_found()
    {
        $this->cacheService->expects($this->once())
            ->method('rememberDetail')
            ->with(999, $this->isType('callable'))
            ->willReturn(null);

        $result = $this->articleService->getArticleWithComments(999);

        $this->assertNull($result);
    }

    // 他のテストケースも追加可能
}
