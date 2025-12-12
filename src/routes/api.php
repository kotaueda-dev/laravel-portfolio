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
    Route::get('/{id}', [ArticleController::class, 'show'])->where('id', '[0-9]+');
    Route::post('/{id}/likes', [ArticleController::class, 'like'])->where('id', '[0-9]+');

    // 認証済みユーザーのみ
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [ArticleController::class, 'store']);
        Route::put('/{id}', [ArticleController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [ArticleController::class, 'destroy'])->where('id', '[0-9]+');
    });
});

// // コメント
Route::post('/articles/{article}/comments', [CommentController::class, 'store'])->where('id', '[0-9]+');
