<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;

class ArticleController extends Controller
{
    // 記事一覧の取得
    public function index(Request $request)
    {
        $page = $request->query('page', 1);

        if (!is_numeric($page) || $page < 1) {
            return response()->json([
                "message" => "Invalid parameter."
            ], 400);
        }

        $articles = Article::paginate(2);

        return response()->json($articles);
    }

    // 記事の投稿
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            "title"    => "required|string|max:255",
            "content"  => "required|string",
            "username" => "required|string|max:50",
        ]);

        $article = Article::create($validatedData);

        return response()->json([
            "message" => "Article created successfully.",
        ]);
    }

    // 記事の取得
    public function show(string $id)
    {
        $article = Article::with('comments')->find($id);

        if (!$article) {
            return response()->json([
                "message" => "Article not found."
            ], 404);
        }

        return response()->json($article);
    }

    // いいねの投稿
    public function like(string $id)
    {
        $article = Article::find($id);

        if(!$article) {
            return response()->json([
                "message" => "Not found."
            ], 404);
        }

        $article->increment("like");

        return response()->json([
            "message" => "Article {$id} liked successfully.",
            "article_id" => $id,
            "like" => $article->like,
        ]);
    }
}
