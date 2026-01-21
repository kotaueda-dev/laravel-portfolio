<?php

use App\Models\Article;
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
    describe('正常系', function () {});
    describe('異常系', function () {});
});

describe('記事更新API', function () {
    describe('正常系', function () {});
    describe('異常系', function () {});
});

describe('記事削除API', function () {
    describe('正常系', function () {});
    describe('異常系', function () {});
});
