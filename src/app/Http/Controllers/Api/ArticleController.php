<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexArticleRequest;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleListResource;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class ArticleController extends Controller
{
    public function __construct(private ArticleService $articleService) {}

    // 記事一覧の取得
    #[OA\Get(
        path: '/api/articles',
        summary: '記事一覧を取得する',
        tags: ['Articles'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/QueryPage'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ArticleListResource')
                        ),
                        new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                    ]
                )
            ),
            new OA\Response(response: 400, ref: '#/components/responses/400_InvalidParameter'),
        ]
    )]
    public function index(IndexArticleRequest $request)
    {
        $page = $request->validated('page');
        $articles = $this->articleService->getAllArticles($page);

        return ArticleListResource::collection($articles);
    }

    // 記事の投稿
    #[OA\Post(
        path: '/api/articles',
        summary: '記事を投稿する',
        security: [['sanctum' => []]],
        tags: ['Articles'],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/StoreArticleRequest'),
        responses: [
            new OA\Response(
                response: 201,
                description: '成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Article created successfully.'
                        ),
                        new OA\Property(
                            property: 'article',
                            type: 'object',
                            ref: '#/components/schemas/ArticleResource'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                ref: '#/components/responses/401_Unauthenticated'
            ),
            new OA\Response(
                response: 422,
                ref: '#/components/responses/422_ValidationError'),
        ]
    )]
    public function store(StoreArticleRequest $request)
    {
        $validatedData = $request->validated();

        $article = $this->articleService->createArticle([
            'title' => $validatedData['title'],
            'content' => $validatedData['content'],
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Article created successfully.',
            'article' => $article,
        ], 201);
    }

    // 記事の取得
    #[OA\Get(
        path: '/api/articles/{article}',
        summary: '指定した記事を取得する',
        tags: ['Articles'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/PathArticleIdBind'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功',
                content: new OA\JsonContent(ref: '#/components/schemas/ArticleWithCommentsResource')
            ),
            new OA\Response(response: 400, ref: '#/components/responses/400_InvalidParameter'),
            new OA\Response(response: 404, ref: '#/components/responses/404_NotFound'),
        ]
    )]
    public function show(Article $article)
    {
        $article = $this->articleService->getArticleWithComments($article->id);

        return new ArticleResource($article);
    }

    // いいねの投稿
    #[OA\Post(
        path: '/api/articles/{id}/likes',
        summary: '記事にいいねを投稿する',
        tags: ['Articles'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/PathArticleId'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Article liked successfully.'
                        ),
                        new OA\Property(
                            property: 'article_id',
                            type: 'integer',
                            example: 1
                        ),
                        new OA\Property(
                            property: 'like',
                            type: 'integer',
                            example: 10
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, ref: '#/components/responses/404_NotFound'),
        ]
    )]
    public function like(Article $article)
    {
        $like = $this->articleService->incrementArticleLike($article);

        return response()->json([
            'message' => "Article {$article->id} liked successfully.",
            'article_id' => (int) $article->id,
            'like' => $like,
        ]);
    }

    // 記事の更新
    #[OA\Put(
        path: '/api/articles/{id}',
        summary: '記事を更新する',
        security: [['sanctum' => []]],
        tags: ['Articles'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/PathArticleId'),
        ],
        requestBody: new OA\RequestBody(ref: '#/components/requestBodies/UpdateArticleRequest'),
        responses: [
            new OA\Response(
                response: 200,
                description: '成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Article updated successfully.'
                        ),
                        new OA\Property(
                            property: 'article',
                            ref: '#/components/schemas/ArticleResource'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/401_Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/403_Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/422_ValidationError'),
        ]
    )]
    public function update(UpdateArticleRequest $request, Article $article)
    {
        Gate::authorize('update', $article);

        $validatedData = $request->validated();
        $this->articleService->updateArticle($article, $validatedData);

        return response()->json([
            'message' => 'Article updated successfully.',
            'article' => $article,
        ]);
    }

    // 記事の削除
    #[OA\Delete(
        path: '/api/articles/{id}',
        summary: '記事を削除する',
        security: [['sanctum' => []]],
        tags: ['Articles'],
        parameters: [
            new OA\PathParameter(ref: '#/components/parameters/PathArticleId'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Article deleted successfully.'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, ref: '#/components/responses/401_Unauthenticated'),
            new OA\Response(response: 403, ref: '#/components/responses/403_Unauthorized'),
            new OA\Response(response: 404, ref: '#/components/responses/404_NotFound'),
        ]
    )]
    public function destroy(Request $request, Article $article)
    {
        Gate::authorize('delete', $article);

        $this->articleService->deleteArticle($article);

        return response()->json([
            'message' => 'Article deleted successfully.',
        ]);
    }
}
