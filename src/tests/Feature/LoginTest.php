<?php

use App\Models\User;
use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

test('正しい認証情報でログインできる', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertValidRequest()
        ->assertValidResponse(200);
    $response->assertJsonStructure([
        'message',
        'access_token',
        'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
    ]);
});

test('誤った認証情報ではログインできない', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'wrong@example.com',
        'password' => 'wrongpassword',
    ]);

    $response
        ->assertValidRequest()
        ->assertValidResponse(401);
});
