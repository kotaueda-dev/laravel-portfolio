<?php

use App\Models\User;
use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

it('deletes an authenticated user account', function () {
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

it('returns 404 if user not authenticated', function () {
    $response = $this->deleteJson('/api/user');

    $response
        ->assertValidResponse(401);
});
