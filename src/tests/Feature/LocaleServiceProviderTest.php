<?php

use Illuminate\Support\Facades\Config;

const NOT_FOUND_JA = 'リソースが見つかりません。';
const NOT_FOUND_EN = 'Not found.';

test('accept language ja returns japanese error message', function () {
    Config::set('app.locale', 'ja');

    $response = $this->getJson('/api/articles/99999999', [
        'Accept-Language' => 'ja',
    ]);

    $response->assertStatus(404);
    $response->assertJson(['message' => NOT_FOUND_JA]);
});

test('accept language en returns english error message', function () {
    Config::set('app.locale', 'en');

    $response = $this->getJson('/api/articles/99999999', [
        'Accept-Language' => 'en',
    ]);

    $response->assertStatus(404);
    $response->assertJson(['message' => NOT_FOUND_EN]);
});

test('unsupported language falls back to default locale', function () {
    Config::set('app.locale', 'ja');

    $response = $this->getJson('/api/articles/99999999', [
        'Accept-Language' => 'fr',
    ]);

    $response->assertStatus(404);
    $response->assertJson(['message' => NOT_FOUND_JA]);
});

test('no accept language header uses default locale', function () {
    Config::set('app.locale', 'ja');

    $response = $this->getJson('/api/articles/99999999');

    $response->assertStatus(404);
    $response->assertJson(['message' => NOT_FOUND_JA]);
});
