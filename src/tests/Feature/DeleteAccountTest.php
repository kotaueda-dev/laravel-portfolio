<?php

use App\Models\User;
use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

it('認証済みユーザーのアカウントを削除できる', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = $this->actingAs($user)->deleteJson('/api/user', [
        'password' => 'password123',
    ]);

    $response
        ->assertValidRequest()
        ->assertValidResponse(200);
    $response->assertJson(['message' => 'Account deleted successfully.']);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

it('未認証の場合は401を返す', function () {
    $response = $this->deleteJson('/api/user');

    $response
        ->assertValidResponse(401);
});
