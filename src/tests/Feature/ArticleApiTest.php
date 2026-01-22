<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Spectator\Spectator;

beforeEach(function () {
    Spectator::using('api-docs.json');
    Redis::connection('cache')->flushdb();
});

describe('記事一覧取得API', function () {
    describe('正常系', function () {
        test('200:ページネーション付き記事一覧を取得できる', function () {
            Article::factory()->count(config('pagination.default_per_page'))->create();

            $response = $this->getJson('/api/articles');

            $response
                ->assertValidRequest()
                ->assertValidResponse(200);
            $response->assertJsonCount(config('pagination.default_per_page'), 'data');
        });

        test('200:記事が0件のとき空配列と0件メタを返す', function () {
            $response = $this->getJson('/api/articles');

            $response
                ->assertValidRequest()
                ->assertValidResponse(200);

            $response->assertJsonCount(0, 'data');
            $response->assertJsonPath('meta.total', 0);
            $response->assertJsonPath('meta.current_page', 1);
            $response->assertJsonPath('meta.per_page', config('pagination.default_per_page'));
        });

        test('200:2ページ目でページネーション情報を返す', function () {
            $perPage = config('pagination.default_per_page');
            $total = $perPage + 5;
            Article::factory()->count($total)->create();

            $response = $this->getJson('/api/articles?page=2');

            $response
                ->assertValidRequest()
                ->assertValidResponse(200);

            $response->assertJsonCount($total - $perPage, 'data');
            $response->assertJsonPath('meta.current_page', 2);
            $response->assertJsonPath('meta.per_page', $perPage);
            $response->assertJsonPath('meta.total', $total);
            $response->assertJsonPath('meta.last_page', 2);
        });
    });

    describe('異常系', function () {
        test('422:pageが負数の場合はエラーを返す', function () {
            $response = $this->getJson('/api/articles?page=-1');

            $response->assertStatus(422);
        });

        test('422:pageが数値以外の場合はエラーを返す', function () {
            $response = $this->getJson('/api/articles?page=abc');

            $response->assertStatus(422);
        });
    });
});

describe('記事取得API', function () {
    describe('正常系', function () {
        test('単一の記事を取得できる', function () {
            // Arrange
            $article = Article::factory()->create();

            // Act
            $response = $this->getJson("/api/articles/{$article->id}");

            // Assert
            $response
                ->assertValidRequest()
                ->assertValidResponse(200);
            $response->assertJsonPath('id', $article->id);
        });

        test('コメント付きで取得できる', function () {
            $article = Article::factory()->hasComments(2)->create();

            $response = $this->getJson("/api/articles/{$article->id}");

            $response
                ->assertValidRequest()
                ->assertValidResponse(200);
            $response->assertJsonCount(2, 'comments');
            $response->assertJsonPath('comments.0.article_id', $article->id);
        });
    });

    describe('異常系', function () {
        test('存在しないIDでは404を返す', function () {
            $response = $this->getJson('/api/articles/999999');

            $response
                ->assertValidRequest()
                ->assertValidResponse(404);
        });
    });
});

describe('記事作成API', function () {
    describe('正常系', function () {
        test('認証済みユーザーが記事を作成できる', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/articles', [
                'title' => 'Test Article',
                'content' => 'This is a test article.',
            ]);

            $response
                ->assertValidRequest()
                ->assertValidResponse(201);
            $response->assertJsonStructure([
                'message',
                'article' => ['id', 'title', 'content', 'user_id', 'created_at', 'updated_at'],
            ]);

            $this->assertDatabaseHas('articles', [
                'title' => 'Test Article',
                'user_id' => $user->id,
            ]);
        });
    });

    describe('異常系', function () {
        test('ゲストユーザーは記事を作成できない', function () {
            $response = $this->postJson('/api/articles', [
                'title' => 'Test Article',
                'content' => 'This is a test article.',
            ]);

            $response
                ->assertValidRequest()
                ->assertValidResponse(401);
        });

        test('タイトル必須バリデーションに失敗する', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/articles', [
                'title' => '',
                'content' => 'content',
            ]);

            $response
                ->assertValidRequest()
                ->assertValidResponse(422);
        });

        test('コンテンツ必須バリデーションに失敗する', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->postJson('/api/articles', [
                'title' => 'title',
            ]);

            $response->assertValidResponse(422);
        });
    });
});

describe('記事更新API', function () {
    describe('正常系', function () {
        test('記事を更新できる', function () {
            $user = User::factory()->create();
            $article = Article::factory()->create(['user_id' => $user->id]);

            $response = $this->actingAs($user)->putJson("/api/articles/{$article->id}", [
                'title' => 'Updated Title',
                'content' => 'Updated Content',
            ]);

            $response
                ->assertValidRequest()
                ->assertValidResponse(200);
            $this->assertDatabaseHas('articles', [
                'id' => $article->id,
                'title' => 'Updated Title',
                'content' => 'Updated Content',
            ]);
        });
    });

    describe('異常系', function () {
        test('権限がない記事は更新できない', function () {
            $user = User::factory()->create();
            $otherUser = User::factory()->create();
            $article = Article::factory()->create(['user_id' => $otherUser->id]);

            $response = $this->actingAs($user)->putJson("/api/articles/{$article->id}", [
                'title' => 'Updated Title',
                'content' => 'Updated Content',
            ]);

            $response
                ->assertValidRequest()
                ->assertValidResponse(403);
        });

        test('未認証ユーザーは記事を更新できない', function () {
            $article = Article::factory()->create();

            $response = $this->putJson("/api/articles/{$article->id}", [
                'title' => 'Updated Title',
            ]);

            $response
                ->assertValidRequest()
                ->assertValidResponse(401);
        });

        test('存在しない記事は404を返す', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->putJson('/api/articles/999999', [
                'title' => 'Updated Title',
            ]);

            $response
                ->assertValidRequest()
                ->assertValidResponse(403);
        });

        test('タイトル必須バリデーションで422を返す', function () {
            $user = User::factory()->create();
            $article = Article::factory()->create(['user_id' => $user->id]);

            $response = $this->actingAs($user)->putJson("/api/articles/{$article->id}", [
                'title' => '',
            ]);

            $response
                ->assertValidRequest()
                ->assertValidResponse(422);
        });
    });
});

describe('記事いいねAPI', function () {
    describe('正常系', function () {
        test('記事のいいね数を増やせる', function () {
            // Arrange
            $article = Article::factory()->create(['like' => 0]);

            // Act
            $response = $this->postJson("/api/articles/{$article->id}/likes");

            // Assert
            $response
                ->assertValidRequest()
                ->assertValidResponse(200);
            $this->assertDatabaseHas('articles', [
                'id' => $article->id,
                'like' => 1,
            ]);
            $response->assertJsonPath('article_id', $article->id);
            $response->assertJsonPath('like', 1);
        });
    });

    describe('異常系', function () {
        test('存在しない記事へのいいねは404を返す', function () {
            $response = $this->postJson('/api/articles/999999/likes');

            $response
                ->assertValidRequest()
                ->assertValidResponse(404);
        });
    });
});

describe('記事削除API', function () {
    describe('正常系', function () {
        test('記事を削除できる', function () {
            $user = User::factory()->create();
            $article = Article::factory()->create(['user_id' => $user->id]);

            $response = $this->actingAs($user)->deleteJson("/api/articles/{$article->id}");

            $response
                ->assertValidRequest()
                ->assertValidResponse(200);
            $this->assertDatabaseMissing('articles', [
                'id' => $article->id,
            ]);
        });
    });

    describe('異常系', function () {
        test('権限がない記事は削除できない', function () {
            $user = User::factory()->create();
            $otherUser = User::factory()->create();
            $article = Article::factory()->create(['user_id' => $otherUser->id]);

            $response = $this->actingAs($user)->deleteJson("/api/articles/{$article->id}");

            $response
                ->assertValidRequest()
                ->assertValidResponse(403);
            $this->assertDatabaseHas('articles', [
                'id' => $article->id,
            ]);
        });

        test('未認証ユーザーは記事を削除できない', function () {
            $article = Article::factory()->create();

            $response = $this->deleteJson("/api/articles/{$article->id}");

            $response
                ->assertValidRequest()
                ->assertValidResponse(401);
            $this->assertDatabaseHas('articles', [
                'id' => $article->id,
            ]);
        });

        test('存在しない記事削除は404を返す', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->deleteJson('/api/articles/999999');

            $response
                ->assertValidRequest()
                ->assertValidResponse(403);
        });
    });
});
