<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_an_authenticated_user_account()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/user');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Account deleted successfully']);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    #[Test]
    public function it_returns_404_if_user_not_authenticated()
    {
        $response = $this->deleteJson('/api/user');

        $response->assertStatus(401);
    }
}