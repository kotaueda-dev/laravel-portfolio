<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_article()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/articles', [
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

    public function test_guest_cannot_create_article()
    {
        $response = $this->postJson('/api/articles', [
            'title' => 'Test Article',
            'content' => 'This is a test article.',
        ]);

        $response->assertStatus(401);
    }
}
