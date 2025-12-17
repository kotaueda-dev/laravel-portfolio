<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateArticleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_authenticated_user_can_create_article()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/articles', [
            'title' => 'Test Article',
            'content' => 'This is a test article.',
        ]);

        $response->assertStatus(201);
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

        $response->assertStatus(401);
    }
}
