<?php

namespace Tests\Unit;

use App\Data\StoreArticleData;
use App\Data\UpdateArticleData;
use App\Models\Article;
use App\Models\User;
use App\Repositories\ArticleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $articleRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->articleRepository = new ArticleRepository;
    }

    public function test_create_article()
    {
        $user = User::factory()->create();

        $dto = new StoreArticleData(
            title: 'Test Title',
            content: 'Test Content',
            user_id: $user->id,
        );

        $article = $this->articleRepository->create($dto);

        $this->assertDatabaseHas('articles', $dto->toArray());
        $this->assertInstanceOf(Article::class, $article);
    }

    public function test_update_article()
    {
        $article = Article::factory()->create();

        $dto = UpdateArticleData::from([
            'id' => $article->id,
            'title' => 'Updated Title',
        ]);

        $this->articleRepository->update($dto);

        $this->assertDatabaseHas('articles', $dto->toArray());
    }

    public function test_delete_article()
    {
        $article = Article::factory()->create();

        $this->articleRepository->delete($article->id);

        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }

    public function test_increment_like()
    {
        $article = Article::factory()->create(['like' => 0]);

        $result = $this->articleRepository->incrementLike($article->id);

        $this->assertEquals(1, $result);
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'like' => 1,
        ]);
    }

    public function test_increment_like_multiple_times()
    {
        $article = Article::factory()->create(['like' => 5]);

        $this->articleRepository->incrementLike($article->id);
        $result = $this->articleRepository->incrementLike($article->id);

        $this->assertEquals(7, $result);
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'like' => 7,
        ]);
    }
}
