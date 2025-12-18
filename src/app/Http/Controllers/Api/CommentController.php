<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleCacheService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected ArticleCacheService $cacheService;

    public function __construct(ArticleCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

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
