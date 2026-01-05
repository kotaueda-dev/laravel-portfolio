<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spectator\Spectator;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('api-docs.json');
    }

    #[Test]
    public function it_deletes_an_authenticated_user_account()
    {
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
    }

    #[Test]
    public function it_returns_404_if_user_not_authenticated()
    {
        $response = $this->deleteJson('/api/user');

        $response
            ->assertValidResponse(401);
    }
}
