<?php

use Illuminate\Support\Facades\Config;

const NOT_FOUND_JA = 'リソースが見つかりません。';
const NOT_FOUND_EN = 'Not found.';

test('Accept-Languageがjaの場合、日本語のエラーメッセージを返す', function () {
    Config::set('app.locale', 'ja');

    $response = $this->getJson('/api/articles/99999999', [
        'Accept-Language' => 'ja',
    ]);

    $response->assertStatus(404);
    $response->assertJson(['message' => NOT_FOUND_JA]);
});

test('Accept-Languageがenの場合、英語のエラーメッセージを返す', function () {
    Config::set('app.locale', 'en');

    $response = $this->getJson('/api/articles/99999999', [
        'Accept-Language' => 'en',
    ]);

    $response->assertStatus(404);
    $response->assertJson(['message' => NOT_FOUND_EN]);
});

test('未サポート言語の場合、デフォルトロケールにフォールバックする', function () {
    Config::set('app.locale', 'ja');

    $response = $this->getJson('/api/articles/99999999', [
        'Accept-Language' => 'fr',
    ]);

    $response->assertStatus(404);
    $response->assertJson(['message' => NOT_FOUND_JA]);
});

test('Accept-Languageヘッダーがない場合、デフォルトロケールを使用する', function () {
    Config::set('app.locale', 'ja');

    $response = $this->getJson('/api/articles/99999999');

    $response->assertStatus(404);
    $response->assertJson(['message' => NOT_FOUND_JA]);
});
