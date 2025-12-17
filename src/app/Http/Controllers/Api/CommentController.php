<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CommentController extends Controller
{
    // コメントの投稿
    public function store(Request $request, Article $article)
    {
        $validatedData = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $article->comments()->create($validatedData);

        // invalidate article cache
        Cache::forget("article:{$article->id}");

        return response()->json([
            'message' => 'Comment created successfully.',
        ], 201);
    }
}
