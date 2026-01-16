<?php

use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

it('新規ユーザーを正常に登録できる', function () {
    $response = $this->postJson('/api/signup', [
        'name' => 'Test User',
        'email' => 'testuser@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertValidRequest()
        ->assertValidResponse(201);
    $response->assertJsonStructure([
        'id', 'name', 'email', 'created_at', 'updated_at',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'testuser@example.com',
    ]);
});

it('不正なデータでは登録に失敗する', function () {
    $response = $this->postJson('/api/signup', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
    ]);

    $response->assertValidResponse(422);
    $response->assertJsonValidationErrors(['name', 'email', 'password']);
});
