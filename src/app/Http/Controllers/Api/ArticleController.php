<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleCacheService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ArticleController extends Controller
{
    protected ArticleCacheService $cacheService;

    public function __construct(ArticleCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    // 記事一覧の取得
    #[OA\Get(
        path: '/api/articles',
        summary: '記事一覧を取得する',
        tags: ['Articles'],
        parameters: [
            new OA\Parameter(
                parameter: 'ArticlePage',
                name: 'page',
                in: 'query',
                required: false,
                description: 'ページ番号（デフォルト: 1）',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功',
                content: new OA\JsonContent(ref: '#/components/schemas/ArticlePagination')
            ),
            new OA\Response(
                response: 400,
                description: '不正なパラメータ',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function index(Request $request)
    {
        $page = $request->query('page', 1);

        if (! is_numeric($page) || $page < 1) {
            return response()->json([
                'message' => 'Invalid parameter.',
            ], 400);
        }

        $articles = $this->cacheService->rememberList($page, function () {
            return Article::paginate(config('pagination.default_per_page'));
        });

        return response()->json($articles);
    }

    // 記事の投稿
    #[OA\Post(
        path: '/api/articles',
        summary: '記事を投稿する',
        security: [['sanctum' => []]],
        tags: ['Articles'],
        requestBody: new OA\RequestBody(
            required: true,
            description: '記事情報',
            content: new OA\JsonContent(
                required: ['title', 'content'],
                ref: '#/components/schemas/PostArticleDetailRequest'
            )
        ),
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
                            ref: '#/components/schemas/ArticleDetail'
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
                response: 422,
                description: 'バリデーションエラー',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $article = Article::create([
            'title' => $validatedData['title'],
            'content' => $validatedData['content'],
            'user_id' => $request->user()->id,
        ]);

        $this->cacheService->forgetAllList();

        return response()->json([
            'message' => 'Article created successfully.',
            'article' => $article,
        ], 201);

    }

    // 記事の取得
    #[OA\Get(
        path: '/api/articles/{id}',
        summary: '指定した記事を取得する',
        tags: ['Articles'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: '記事ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功',
                content: new OA\JsonContent(ref: '#/components/schemas/ArticleDetailWithComments')
            ),
            new OA\Response(
                response: 400,
                description: '不正なパラメータ',
                content: new OA\JsonContent(ref: '#/components/schemas/InvalidParameter')
            ),
            new OA\Response(
                response: 404,
                description: '記事が見つかりません',
                content: new OA\JsonContent(ref: '#/components/schemas/NotFound')
            ),
        ]
    )]
    public function show(string $id)
    {
        $article = $this->cacheService->rememberDetail($id, function () use ($id) {
            return Article::with('comments')->find($id);
        });

        if (! $article) {
            return response()->json([
                'message' => 'Article not found.',
            ], 404);
        }

        return response()->json($article);
    }

    // いいねの投稿
    #[OA\Post(
        path: '/api/articles/{id}/likes',
        summary: '記事にいいねを投稿する',
        tags: ['Articles'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: '記事ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
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
            new OA\Response(
                response: 404,
                description: '記事が見つかりません',
                content: new OA\JsonContent(ref: '#/components/schemas/NotFound')
            ),
        ]
    )]
    public function like(string $id)
    {
        $article = Article::find($id);

        if (! $article) {
            return response()->json([
                'message' => 'Not found.',
            ], 404);
        }

        $article->increment('like');

        $this->cacheService->forgetAllList();
        $this->cacheService->forgetDetail($id);

        return response()->json([
            'message' => "Article {$id} liked successfully.",
            'article_id' => (int) $id,
            'like' => $article->like,
        ]);
    }

    // 記事の更新
    #[OA\Put(
        path: '/api/articles/{id}',
        summary: '記事を更新する',
        security: [['sanctum' => []]],
        tags: ['Articles'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: '記事ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: '記事情報',
            content: new OA\JsonContent(
                required: ['title', 'content'],
                ref: '#/components/schemas/PostArticleDetailRequest'
            )
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
                            example: 'Article updated successfully.'
                        ),
                        new OA\Property(
                            property: 'article',
                            ref: '#/components/schemas/ArticleDetail'
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
                response: 403,

                description: '権限がありません',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 422,
                description: 'バリデーションエラー',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function update(Request $request, string $id)
    {
        $article = Article::find($id);

        if (! $article) {
            return response()->json([
                'message' => 'Article not found.',
            ], 404);
        }

        if ($article->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        $article->update($validatedData);

        $this->cacheService->forgetAllList();
        $this->cacheService->forgetDetail($id);

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
            new OA\PathParameter(
                name: 'id',
                description: '記事ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
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
            new OA\Response(
                response: 401,
                description: '認証エラー',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthenticated')
            ),
            new OA\Response(
                response: 403,
                description: '権限がありません',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 404,
                description: '記事が見つかりません',
                content: new OA\JsonContent(ref: '#/components/schemas/ArticleNotFound')
            ),
        ]
    )]
    public function destroy(Request $request, string $id)
    {
        $article = Article::find($id);

        if (! $article) {
            return response()->json([
                'message' => 'Article not found.',
            ], 404);
        }

        if ($article->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $article->delete();

        $this->cacheService->forgetAllList();
        $this->cacheService->forgetDetail($id);

        return response()->json([
            'message' => 'Article deleted successfully.',
        ]);
    }
}
