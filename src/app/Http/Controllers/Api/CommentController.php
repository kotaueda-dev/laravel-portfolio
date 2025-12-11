<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Comment;

class CommentController extends Controller
{
    // コメントの投稿
    public function store(Request $request, Article $article)
    {
        $validatedData = $request->validate([
            "message" => "required|string|max:500",
        ]);

        $article->comments()->create($validatedData);

        return response()->json([
            "message" => "Comment created successfully.",
        ]);
    }
}
