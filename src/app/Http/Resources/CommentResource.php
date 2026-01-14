<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @property int $id
 * @property int $article_id
 * @property string $message
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class CommentResource extends JsonResource
{
    public static $wrap = null;

    #[OA\Schema(
        schema: 'CommentResource',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'article_id', type: 'integer', example: 1),
            new OA\Property(property: 'message', type: 'string', example: 'コメントの内容'),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-12-23T07:54:58.000000Z'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-12-23T07:54:58.000000Z'),
        ]
    )]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'article_id' => $this->article_id,
            'message' => $this->message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
