<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/signup',
        summary: 'ユーザー登録を行う',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        description: 'ユーザー名',
                        maxLength: 255,
                        example: 'Taro Yamada'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        description: 'メールアドレス',
                        maxLength: 255,
                        example: 'user@example.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        description: 'パスワード',
                        minLength: 8,
                        example: 'password123'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'ユーザー登録成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'User registered successfully.'
                        ),
                        new OA\Property(
                            property: 'user',
                            ref: '#/components/schemas/User'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'バリデーションエラー',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    #[OA\Delete(
        path: '/api/user',
        summary: 'ユーザーアカウントを削除する',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Account deleted successfully.'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: '認証エラー',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthenticated')
            ),
            new OA\Response(
                response: 404,
                description: 'ユーザーが見つかりません',
                content: new OA\JsonContent(ref: '#/components/schemas/NotFound')
            ),
        ]
    )]
    public function deleteAccount()
    {
        $user = Auth::user();

        if ($user) {
            $user->delete();

            return response()->json(['message' => 'Account deleted successfully.'], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'ユーザーログインを行う',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        description: 'メールアドレス',
                        maxLength: 255,
                        example: 'user@example.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        description: 'パスワード',
                        minLength: 8,
                        example: 'password123'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'ログイン成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Logged in successfully.'
                        ),
                        new OA\Property(
                            property: 'token',
                            type: 'string',
                            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: '認証エラー',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 422,
                description: 'バリデーションエラー',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully.',
            'token' => $token,
        ], 200);
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'ユーザーログアウトを行う',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'ログアウト成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Logged out successfully.'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: '認証エラー',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ], 200);
    }
}
