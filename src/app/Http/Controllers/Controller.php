<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Laravel Portfolio API',
    version: '1.0.0',
    description: 'Laravel12で構築したWeb APIのドキュメントです'
)]

#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Sanctumで発行されたBearerトークンを入力してください',
)]

// リクエストボディのスキーマ定義
#[OA\Schema(
    schema: 'PostArticleDetailRequest',
    properties: [
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'content', type: 'string'),
    ]
)]

// レスポンスボディのスキーマ定義
#[OA\Schema(
    schema: 'ArticleDetail',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'content', type: 'string'),
        new OA\Property(property: 'like', type: 'integer'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]

// Articles関連のスキーマ定義
#[OA\Schema(
    schema: 'ArticleDetailWithComments',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: '記事タイトル'),
        new OA\Property(property: 'content', type: 'string', example: '記事の内容'),
        new OA\Property(property: 'like', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-12-23T07:54:58.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-12-23T07:54:58.000000Z'),
        // comments部分は配列として定義
        new OA\Property(
            property: 'comments',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'article_id', type: 'integer', example: 1),
                    new OA\Property(property: 'message', type: 'string', example: 'コメントの内容'),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                ]
            )
        ),
    ]
)]

#[OA\Schema(
    schema: 'PaginationLink',
    properties: [
        new OA\Property(property: 'url', type: 'string', nullable: true),
        new OA\Property(property: 'label', type: 'string'),
        new OA\Property(property: 'page', type: 'integer', nullable: true),
        new OA\Property(property: 'active', type: 'boolean'),
    ]
)]

#[OA\Schema(
    schema: 'ArticlePagination',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ArticleDetail')),
        new OA\Property(property: 'first_page_url', type: 'string'),
        new OA\Property(property: 'from', type: 'integer', nullable: true),
        new OA\Property(property: 'last_page', type: 'integer'),
        new OA\Property(property: 'last_page_url', type: 'string'),
        new OA\Property(property: 'links', type: 'array', items: new OA\Items(ref: '#/components/schemas/PaginationLink')),
        new OA\Property(property: 'next_page_url', type: 'string', nullable: true),
        new OA\Property(property: 'path', type: 'string', nullable: true),
        new OA\Property(property: 'per_page', type: 'integer', example: 20),
        new OA\Property(property: 'prev_page_url', type: 'string', nullable: true),
        new OA\Property(property: 'to', type: 'integer', example: 20),
        new OA\Property(property: 'total', type: 'integer', example: 50),
    ]
)]

// エラーレスポンスのスキーマ定義
#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]
)]

#[OA\Schema(
    schema: 'ArticleNotFound',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Article not found.'),
    ]
)]

#[OA\Schema(
    schema: 'Unauthenticated',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
    ]
)]

#[OA\Schema(
    schema: 'Unauthorized',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthorized.'),
    ]
)]

#[OA\Schema(
    schema: 'InvalidParameter',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Invalid parameter.'),
    ]
)]

#[OA\Schema(
    schema: 'NotFound',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Resource not found.'),
    ]
)]

#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'The title field is required. (and 1 more error)'
        ),
        new OA\Property(
            property: 'errors',
            type: 'object',
            description: '各フィールドごとのエラーメッセージ',
            // additionalPropertiesを使うことで、動的なフィールド名（title, content等）を表現できます
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string', example: 'The title field is required.')
            )
        ),
    ]
)]

#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Taro Yamada'),
        new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'id', type: 'integer', example: 1),
    ]
)]

abstract class Controller
{
    //
}
