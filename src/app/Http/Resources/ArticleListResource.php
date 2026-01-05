<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ArticleListResource',
    description: '記事一覧用の軽量リソース',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: '記事のタイトル'),
        new OA\Property(property: 'like', type: 'integer', example: 10),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-12-23T07:54:58.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-12-23T07:54:58.000000Z'),
    ]
)]

#[OA\Schema(
    schema: 'ArticlePagination',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ArticleListResource')),
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

#[OA\Schema(
    schema: 'PaginationLink',
    properties: [
        new OA\Property(property: 'url', type: 'string', nullable: true),
        new OA\Property(property: 'label', type: 'string'),
        new OA\Property(property: 'page', type: 'integer', nullable: true),
        new OA\Property(property: 'active', type: 'boolean'),
    ]
)]
class ArticleListResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'like' => $this->like,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
