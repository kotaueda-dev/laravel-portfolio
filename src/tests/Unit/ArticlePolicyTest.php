<?php

use App\Models\Article;
use App\Models\User;
use App\Policies\ArticlePolicy;

uses(Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new ArticlePolicy;
});

test('user can update own article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    $result = $this->policy->update($user, $article);

    expect($result)->toBeTrue();
});

test('user cannot update other user article', function () {
    $user = User::factory()->create();
    $user_2 = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user_2->id]);

    $result = $this->policy->update($user, $article);

    expect($result)->toBeFalse();
});

test('user can delete own article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    $result = $this->policy->delete($user, $article);

    expect($result)->toBeTrue();
});

test('user cannot delete other user article', function () {
    $user = User::factory()->create();
    $user_2 = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user_2->id]);

    $result = $this->policy->delete($user, $article);

    expect($result)->toBeFalse();
});
