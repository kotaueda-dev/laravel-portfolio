<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $content
 * @property int $like
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
#[OA\Schema(
    schema: 'ArticleResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: '記事タイトル'),
        new OA\Property(property: 'content', type: 'string', example: '記事の内容'),
        new OA\Property(property: 'like', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-12-23T07:54:58.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-12-23T07:54:58.000000Z'),
    ]
)]

#[OA\Schema(
    schema: 'ArticleWithCommentsResource',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/ArticleResource'),
        new OA\Schema(
            properties: [
                new OA\Property(
                    property: 'comments',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/CommentResource')
                ),
            ]
        ),
    ]
)]
class ArticleDetailResource extends JsonResource
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
            'content' => $this->content,
            'like' => $this->like,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'comments' => $this->whenLoaded('comments'),
        ];
    }
}
