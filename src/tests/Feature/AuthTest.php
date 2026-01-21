<?php

use App\Models\User;

describe('ユーザー登録API', function () {
    describe('正常系', function () {
        test('201:有効なデータでユーザーを登録できる', function () {
            $validData = signupData();

            $response = $this->postJson('/api/signup', $validData);

            $response
                ->assertValidRequest()
                ->assertValidResponse(201);

            $this->assertDatabaseHas('users', [
                'email' => $validData['email'],
            ]);
        });
    });

    describe('異常系', function () {
        test('422:不正なデータでは登録に失敗する', function () {
            $response = $this->postJson('/api/signup', [
                'name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
            ]);

            $response
                ->assertValidResponse(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);
        });

        test('422:既に存在するメールアドレスでは登録に失敗する', function () {
            $requestData = signupData();
            User::factory()->create([
                'email' => $requestData['email'],
            ]);

            $response = $this->postJson('/api/signup', $requestData);

            $response
                ->assertValidResponse(422)
                ->assertJsonValidationErrors(['email']);
        });
    });
});

describe('ユーザー退会API', function () {
    describe('正常系', function () {
        test('200:アカウントを削除できる', function () {
            $user = User::factory()->create([
                'password' => bcrypt('password123'),
            ]);

            $response = $this->actingAs($user)->deleteJson('/api/user', [
                'password' => 'password123',
            ]);

            $response
                ->assertValidRequest()
                ->assertValidResponse(200);

            $this->assertDatabaseMissing('users', [
                'id' => $user->id,
            ]);
        });
    });

    describe('異常系', function () {
        test('401:未認証の場合はアカウント削除に失敗する', function () {
            $response = $this->deleteJson('/api/user');

            $response
                ->assertValidResponse(401);
        });

        test('422:誤ったパスワードではアカウント削除に失敗する', function () {
            $user = User::factory()->create([
                'password' => bcrypt('password123'),
            ]);

            $response = $this->actingAs($user)->deleteJson('/api/user', [
                'password' => 'wrongpassword',
            ]);

            $response
                ->assertValidResponse(422)
                ->assertJsonValidationErrors(['password']);

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
            ]);
        });
    });

});

describe('ユーザーログインAPI', function () {

    describe('正常系', function () {
        test('200:正しい認証情報でログインできる', function () {
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

            $this->assertDatabaseHas('personal_access_tokens', [
                'tokenable_id' => $user->id,
            ]);
        });
    });

    describe('異常系', function () {
        test('401:誤った認証情報ではログインできない', function () {
            $user = User::factory()->create([
                'email' => 'test@example.com',
                'password' => bcrypt('password123'),
            ]);

            $response = $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);

            $response
                ->assertValidRequest()
                ->assertValidResponse(401);
        });

        test('422:不正なデータではログインに失敗する', function () {
            $response = $this->postJson('/api/login', [
                'email' => 'not-an-email',
                'password' => 'short',
            ]);

            $response
                ->assertValidResponse(422)
                ->assertJsonValidationErrors(['email', 'password']);
        });
    });

});

describe('ユーザーログアウトAPI', function () {

    describe('正常系', function () {
        test('200:ログインしたユーザーがログアウトできる', function () {
            $user = User::factory()->create([
                'email' => 'logout-test@example.com',
                'password' => bcrypt('password123'),
            ]);

            $loginResponse = $this->postJson('/api/login', [
                'email' => 'logout-test@example.com',
                'password' => 'password123',
            ]);

            $loginResponse->assertValidResponse(200);

            $token = $loginResponse->json('access_token');

            expect($token)->not->toBeNull();

            $this->assertDatabaseHas('personal_access_tokens', [
                'tokenable_id' => $user->id,
            ]);

            $response = $this->withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->postJson('/api/logout');

            $response
                ->assertValidRequest()
                ->assertValidResponse(200);

            // トークンが削除されたことを確認
            $this->assertDatabaseMissing('personal_access_tokens', [
                'tokenable_id' => $user->id,
            ]);
        });
    });

    describe('異常系', function () {
        test('401:未認証ユーザーはログアウトできない', function () {
            $response = $this->postJson('/api/logout');

            $response
                ->assertValidRequest()
                ->assertValidResponse(401);
        });

        test('401:無効なトークンではログアウトできない', function () {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer invalid-token',
            ])->postJson('/api/logout');

            $response
                ->assertValidRequest()
                ->assertValidResponse(401);
        });
    });

});
