<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spectator\Spectator;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('api-docs.json');
    }

    #[Test]
    public function test_user_can_logout_successfully()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);
        $response->assertJson([
            'message' => 'Logged out successfully.',
        ]);
    }

    #[Test]
    public function test_guest_cannot_logout()
    {
        $response = $this->postJson('/api/logout');

        $response
            ->assertValidRequest()
            ->assertValidResponse(401);
    }
}
