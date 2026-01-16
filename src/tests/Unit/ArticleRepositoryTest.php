<?php

use App\Data\StoreArticleData;
use App\Data\UpdateArticleData;
use App\Models\Article;
use App\Models\User;
use App\Repositories\ArticleRepository;

uses(Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->articleRepository = new ArticleRepository;
});

test('記事を作成する', function () {
    $user = User::factory()->create();

    $dto = new StoreArticleData(
        title: 'Test Title',
        content: 'Test Content',
        user_id: $user->id,
    );

    $article = $this->articleRepository->create($dto);

    $this->assertDatabaseHas('articles', $dto->toArray());
    expect($article)->toBeInstanceOf(Article::class);
});

test('記事を更新する', function () {
    $article = Article::factory()->create();

    $dto = UpdateArticleData::from([
        'id' => $article->id,
        'title' => 'Updated Title',
    ]);

    $this->articleRepository->update($dto);

    $this->assertDatabaseHas('articles', $dto->toArray());
});

test('記事を削除する', function () {
    $article = Article::factory()->create();

    $this->articleRepository->delete($article->id);

    $this->assertDatabaseMissing('articles', [
        'id' => $article->id,
    ]);
});

test('いいね数を増やす', function () {
    $article = Article::factory()->create(['like' => 0]);

    $result = $this->articleRepository->incrementLike($article->id);

    expect($result)->toEqual(1);
    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'like' => 1,
    ]);
});

test('いいね数を複数回増やす', function () {
    $article = Article::factory()->create(['like' => 5]);

    $this->articleRepository->incrementLike($article->id);
    $result = $this->articleRepository->incrementLike($article->id);

    expect($result)->toEqual(7);
    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'like' => 7,
    ]);
});
