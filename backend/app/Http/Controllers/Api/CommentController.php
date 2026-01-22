<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Services\ArticleCacheService;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class CommentController extends Controller
{
    public function __construct(protected ArticleCacheService $articleCacheService) {}

    #[OA\Post(
        path: '/api/articles/{article}/comments',
        summary: 'IDで指定した記事にコメントを投稿する',
        tags: ['Comments'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/PathArticleIdBind'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreCommentRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'コメント投稿成功',
                content: new OA\JsonContent(ref: '#/components/schemas/CommentResource')
            ),
            new OA\Response(response: 404, ref: '#/components/responses/404_NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/422_ValidationError'),
        ]
    )]
    public function store(StoreCommentRequest $request, Article $article)
    {
        Log::info('記事へのコメント投稿を開始します。', ['article_id' => $article->id]);

        $validatedData = $request->validated();

        $comment = $article->comments()->create($validatedData);

        $this->articleCacheService->forgetAllList();
        $this->articleCacheService->forgetDetail($article->id);

        Log::info('記事へのコメント投稿が完了しました。', [
            'article_id' => $article->id,
            'comment_id' => $comment->id,
        ]);

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}
