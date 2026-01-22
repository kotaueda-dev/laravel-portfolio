<?php

use App\Models\Article;
use App\Models\User;
use App\Policies\ArticlePolicy;

beforeEach(function () {
    $this->policy = new ArticlePolicy;
});

test('ユーザーは自分の記事を更新できる', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    $result = $this->policy->update($user, $article);

    expect($result)->toBeTrue();
});

test('ユーザーは他人の記事を更新できない', function () {
    $user = User::factory()->create();
    $user_2 = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user_2->id]);

    $result = $this->policy->update($user, $article);

    expect($result)->toBeFalse();
});

test('ユーザーは自分の記事を削除できる', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    $result = $this->policy->delete($user, $article);

    expect($result)->toBeTrue();
});

test('ユーザーは他人の記事を削除できない', function () {
    $user = User::factory()->create();
    $user_2 = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user_2->id]);

    $result = $this->policy->delete($user, $article);

    expect($result)->toBeFalse();
});
