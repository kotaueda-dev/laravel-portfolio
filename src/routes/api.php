<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use Illuminate\Support\Facades\Route;

// サインアップ・退会
Route::post('/signup', [AuthController::class, 'register']);
Route::delete('/user', [AuthController::class, 'deleteAccount'])->middleware('auth:sanctum');

// ログイン・ログアウト
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// 記事
Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::get('/{id}', [ArticleController::class, 'show']);
    Route::post('/{id}/likes', [ArticleController::class, 'like']);

    // 認証済みユーザーのみ
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [ArticleController::class, 'store']);
        Route::put('/{id}', [ArticleController::class, 'update']);
        Route::delete('/{id}', [ArticleController::class, 'destroy']);
    });
});

// // コメント
Route::post('/articles/{article}/comments', [CommentController::class, 'store']);
