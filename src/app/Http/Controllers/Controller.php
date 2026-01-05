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

// 共通で使用するスキーマやパラメータを定義
#[OA\Components(
    schemas: [
        new OA\Schema(
            schema: 'PaginationLinks',
            properties: [
                new OA\Property(property: 'first', type: 'string', example: 'http://localhost:8000/api/articles?page=1'),
                new OA\Property(property: 'last', type: 'string', example: 'http://localhost:8000/api/articles?page=3'),
                new OA\Property(property: 'prev', type: 'string', nullable: true, example: null),
                new OA\Property(property: 'next', type: 'string', nullable: true, example: 'http://localhost:8000/api/articles?page=2'),
            ]
        ),
        new OA\Schema(
            schema: 'PaginationMeta',
            properties: [
                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                new OA\Property(property: 'from', type: 'integer', example: 1),
                new OA\Property(property: 'last_page', type: 'integer', example: 3),
                new OA\Property(property: 'path', type: 'string', example: 'http://localhost:8000/api/articles'),
                new OA\Property(property: 'per_page', type: 'integer', example: 20),
                new OA\Property(property: 'to', type: 'integer', example: 20),
                new OA\Property(property: 'total', type: 'integer', example: 52),
            ]
        ),
        new OA\Schema(
            schema: 'User',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Taro Yamada'),
                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-12-23T07:54:58.000000Z'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-12-23T07:54:58.000000Z'),
            ]
        ),
        new OA\Schema(
            schema: 'ValidationError',
            required: ['message'],
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
                    additionalProperties: new OA\AdditionalProperties(
                        type: 'array',
                        items: new OA\Items(type: 'string', example: 'The title field is required.')
                    )
                ),
            ]
        ),
    ],
    // パラメータ定義
    parameters: [
        new OA\Parameter(
            parameter: 'QueryPage',
            name: 'page',
            in: 'query',
            description: 'ページ番号（省略時は1）',
            required: false,
            schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
        ),
        new OA\PathParameter(
            parameter: 'PathArticleId',
            name: 'id',
            description: '記事ID',
            required: true,
            schema: new OA\Schema(type: 'integer', example: 1)
        ),
        new OA\PathParameter(
            parameter: 'PathArticleIdBind',
            name: 'article',
            description: '記事ID',
            required: true,
            schema: new OA\Schema(type: 'integer', example: 1)
        ),
    ],
    // リクエストボディ定義
    requestBodies: [],

    // レスポンス定義
    responses: [
        new OA\Response(
            response: '400_InvalidParameter',
            description: '不正なパラメータ',
            content: new OA\JsonContent(
                required: ['message'],
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Invalid parameter.'),
                ]
            )
        ),
        new OA\Response(
            response: '401_Unauthenticated',
            description: '認証エラー',
            content: new OA\JsonContent(
                required: ['message'],
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                ]
            )
        ),
        new OA\Response(
            response: '403_Unauthorized',
            description: '権限エラー',
            content: new OA\JsonContent(
                required: ['message'],
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Unauthorized.'),
                ]
            )
        ),
        new OA\Response(
            response: '404_NotFound',
            description: 'リソースが見つからない',
            content: new OA\JsonContent(
                required: ['message'],
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Not found.'),
                ]
            )
        ),
        new OA\Response(
            response: '422_ValidationError',
            description: 'バリデーションエラー',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
        ),
    ]
)]

abstract class Controller
{
    //
}
