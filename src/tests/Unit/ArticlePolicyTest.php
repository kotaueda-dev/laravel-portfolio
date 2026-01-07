<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\User;
use App\Policies\ArticlePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticlePolicyTest extends TestCase
{
    use RefreshDatabase;

    private ArticlePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ArticlePolicy;
    }

    /**
     * ユーザーが自分の記事を更新できることをテスト
     */
    public function test_user_can_update_own_article(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->update($user, $article);

        $this->assertTrue($result);
    }

    /**
     * ユーザーが他人の記事を更新できないことをテスト
     */
    public function test_user_cannot_update_other_user_article(): void
    {
        $user = User::factory()->create();
        $user_2 = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user_2->id]);

        $result = $this->policy->update($user, $article);

        $this->assertFalse($result);
    }

    /**
     * ユーザーが自分の記事を削除できることをテスト
     */
    public function test_user_can_delete_own_article(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id]);

        $result = $this->policy->delete($user, $article);

        $this->assertTrue($result);
    }

    /**
     * ユーザーが他人の記事を削除できないことをテスト
     */
    public function test_user_cannot_delete_other_user_article(): void
    {
        $user = User::factory()->create();
        $user_2 = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user_2->id]);

        $result = $this->policy->delete($user, $article);

        $this->assertFalse($result);
    }
}
