<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

test('ログアウトが正常に実行できる', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/logout');

    $response
        ->assertValidRequest()
        ->assertValidResponse(200);
    $response->assertJson([
        'message' => 'Logged out successfully.',
    ]);
});

test('ゲストユーザーはログアウトできない', function () {
    $response = $this->postJson('/api/logout');

    $response
        ->assertValidRequest()
        ->assertValidResponse(401);
});
