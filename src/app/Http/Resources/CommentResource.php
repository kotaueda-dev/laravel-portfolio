<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class CommentResource extends JsonResource
{
    public static $wrap = null;

    #[OA\Schema(
        schema: 'CommentResource',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'message', type: 'string', example: 'This is a comment.'),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01T12:00:00Z'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-01T12:00:00Z'),
        ]
    )]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode(201);
    }
}
