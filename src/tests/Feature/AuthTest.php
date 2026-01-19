<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('ユーザー新規登録', function () {

    test('新規ユーザーを正常に登録できる', function () {
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

    test('不正なデータでは登録に失敗する', function () {
        $response = $this->postJson('/api/signup', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertValidResponse(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    });

});

describe('ユーザー退会', function () {
    test('認証済みユーザーのアカウントを削除できる', function () {
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

    test('未認証の場合は401を返す', function () {
        $response = $this->deleteJson('/api/user');

        $response
            ->assertValidResponse(401);
    });
});

describe('ユーザーログイン', function () {
    test('正しい認証情報でログインできる', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);
        $response->assertJsonStructure([
            'message',
            'access_token',
            'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
        ]);
    });

    test('誤った認証情報ではログインできない', function () {
        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response
            ->assertValidRequest()
            ->assertValidResponse(401);
    });
});

describe('ユーザーログアウト', function () {
    test('ログアウトが正常に実行できる', function () {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response
            ->assertValidRequest()
            ->assertValidResponse(200);
        $response->assertJson([
            'message' => 'Logged out successfully.',
        ]);
    });

    test('ゲストユーザーはログアウトできない', function () {
        $response = $this->postJson('/api/logout');

        $response
            ->assertValidRequest()
            ->assertValidResponse(401);
    });
});
