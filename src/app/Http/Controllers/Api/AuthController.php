<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\DeleteAccountRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
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
            description: 'ユーザー登録情報',
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'ユーザー登録成功',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResource')
            ),
            new OA\Response(response: 422, ref: '#/components/responses/422_ValidationError'),
        ]
    )]
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return new UserResource($user);
    }

    #[OA\Delete(
        path: '/api/user',
        summary: 'ユーザーアカウントを削除する',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/DeleteAccountRequest')
        ),
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
            new OA\Response(response: 401, ref: '#/components/responses/401_Unauthenticated'),
            new OA\Response(response: 422, ref: '#/components/responses/422_ValidationError'),
        ]
    )]
    public function deleteAccount(DeleteAccountRequest $request)
    {
        $user = Auth::user();

        // パスワードチェック
        if (! Hash::check($request->password, $user->password)) {
            abort(422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully.',
        ], 200);
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'ユーザーログインを行う',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
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
                            property: 'access_token',
                            type: 'string',
                            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
                        ),
                        new OA\Property(
                            property: 'user',
                            ref: '#/components/schemas/UserResource'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/401_Unauthenticated'),
            new OA\Response(response: 422, ref: '#/components/responses/422_ValidationError'),
        ]
    )]
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            abort(401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully.',
            'access_token' => $token,
            'user' => new UserResource($user),
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
            new OA\Response(response: 401, ref: '#/components/responses/401_Unauthenticated'),
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
