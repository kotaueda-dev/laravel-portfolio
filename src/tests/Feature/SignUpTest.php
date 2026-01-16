<?php

use Spectator\Spectator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Spectator::using('api-docs.json');
});

it('registers a new user successfully', function () {
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

it('fails to register with invalid data', function () {
    $response = $this->postJson('/api/signup', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
    ]);

    $response->assertValidResponse(422);
    $response->assertJsonValidationErrors(['name', 'email', 'password']);
});
