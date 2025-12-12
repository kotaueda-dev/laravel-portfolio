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
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{id}', [ArticleController::class, 'show'])
    ->where('id', '[0-9]+');
Route::post('/articles/{id}/likes', [ArticleController::class, 'like'])
    ->where('id', '[0-9]+');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/articles', [ArticleController::class, 'store']);
});

// // コメント
Route::post('/articles/{article}/comments', [CommentController::class, 'store'])
    ->where('id', '[0-9]+');
