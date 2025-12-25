<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spectator\Spectator;
use Tests\TestCase;

class SignUpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('api-docs.json');
    }

    #[Test]
    public function it_registers_a_new_user_successfully()
    {
        $response = $this->postJson('/api/signup', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertValidRequest()
            ->assertValidResponse(201);
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id', 'name', 'email', 'created_at', 'updated_at',
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
        ]);
    }

    #[Test]
    public function it_fails_to_register_with_invalid_data()
    {
        $response = $this->postJson('/api/signup', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertValidResponse(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }
}
