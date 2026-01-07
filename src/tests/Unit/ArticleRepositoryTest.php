<?php

namespace Tests\Unit;

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
        $data = [
            'title' => 'Test Title',
            'content' => 'Test Content',
            'user_id' => $user->id,
        ];

        $article = $this->articleRepository->create($data);

        $this->assertDatabaseHas('articles', $data);
        $this->assertInstanceOf(Article::class, $article);
    }

    public function test_update_article()
    {
        $article = Article::factory()->create();
        $data = ['title' => 'Updated Title'];

        $this->articleRepository->update($article, $data);

        $this->assertDatabaseHas('articles', $data);
    }

    public function test_delete_article()
    {
        $article = Article::factory()->create();

        $this->articleRepository->delete($article);

        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }

    public function test_get_by_id_returns_null_if_not_found()
    {
        $result = $this->articleRepository->getById(999);
        $this->assertNull($result);
    }

    public function test_increment_like()
    {
        $article = Article::factory()->create(['like' => 0]);

        $result = $this->articleRepository->incrementLike($article);

        $this->assertEquals(1, $result);
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'like' => 1,
        ]);
    }

    public function test_increment_like_multiple_times()
    {
        $article = Article::factory()->create(['like' => 5]);

        $this->articleRepository->incrementLike($article);
        $result = $this->articleRepository->incrementLike($article);

        $this->assertEquals(7, $result);
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'like' => 7,
        ]);
    }
}
