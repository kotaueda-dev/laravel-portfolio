<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_registers_a_new_user_successfully()
    {
        $response = $this->postJson('/api/signup', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id', 'name', 'email', 'created_at', 'updated_at'
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
        ]);
    }

    /** @test */
    public function it_fails_to_register_with_invalid_data()
    {
        $response = $this->postJson('/api/signup', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }
}