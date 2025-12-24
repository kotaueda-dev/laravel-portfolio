<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleCacheService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CommentController extends Controller
{
    protected ArticleCacheService $cacheService;

    public function __construct(ArticleCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    #[OA\Post(
        path: '/api/articles/{article}/comments',
        summary: 'IDで指定した記事にコメントを投稿する',
        tags: ['Comments'],
        parameters: [
            new OA\PathParameter(
                name: 'article',
                description: '記事ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['message'],
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        description: 'コメント内容',
                        maxLength: 500,
                        example: 'コメントの内容です。'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'コメント投稿成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Comment created successfully.'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: '記事が見つかりません',
                content: new OA\JsonContent(ref: '#/components/schemas/NotFound')
            ),
            new OA\Response(
                response: 422,
                description: 'バリデーションエラー',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function store(Request $request, Article $article)
    {
        $validatedData = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $article->comments()->create($validatedData);

        $this->cacheService->forgetAllList();
        $this->cacheService->forgetDetail($article->id);

        return response()->json([
            'message' => 'Comment created successfully.',
        ], 201);
    }
}
