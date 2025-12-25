<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spectator\Spectator;
use Tests\TestCase;

class CreateArticleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('api-docs.json');
    }

    #[Test]
    public function test_authenticated_user_can_create_article()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/articles', [
            'title' => 'Test Article',
            'content' => 'This is a test article.',
        ]);

        $response
            ->assertValidRequest()
            ->assertValidResponse(201);
        $response->assertJsonStructure([
            'message',
            'article' => ['id', 'title', 'content', 'user_id', 'created_at', 'updated_at'],
        ]);

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article',
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function test_guest_cannot_create_article()
    {
        $response = $this->postJson('/api/articles', [
            'title' => 'Test Article',
            'content' => 'This is a test article.',
        ]);

        $response
            ->assertValidRequest()
            ->assertValidResponse(401);
    }
}
