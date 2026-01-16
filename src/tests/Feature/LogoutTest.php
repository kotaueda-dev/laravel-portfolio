<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

test('user can logout successfully', function () {
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

test('guest cannot logout', function () {
    $response = $this->postJson('/api/logout');

    $response
        ->assertValidRequest()
        ->assertValidResponse(401);
});
