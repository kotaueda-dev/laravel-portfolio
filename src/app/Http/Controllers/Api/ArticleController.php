<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleCacheService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    protected ArticleCacheService $cacheService;

    public function __construct(ArticleCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    // 記事一覧の取得
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
            'article_id' => $id,
            'like' => $article->like,
        ]);
    }

    // 記事の更新
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
