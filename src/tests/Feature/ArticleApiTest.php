<?php

use App\Models\Article;
use App\Models\User;
use Spectator\Spectator;

beforeEach(function () {
    Spectator::using('api-docs.json');
});

// describe('API', function () {
//     describe('正常系', function () {});
//     describe('異常系', function () {});
// });

describe('記事一覧取得API', function () {
    describe('正常系', function () {
        test('ページネーション付き記事一覧を取得できる', function () {
            // Arrange
            Article::factory()->count(config('pagination.default_per_page'))->create();

            // Act
            $response = $this->getJson('/api/articles');

            // Assert
            $response
                ->assertValidRequest()
                ->assertValidResponse(200);
            $response->assertJsonCount(config('pagination.default_per_page'), 'data');
        });
    });

    describe('異常系', function () {});
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
    });

    describe('異常系', function () {});
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
        });
    });

    describe('異常系', function () {});
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
    });
});
