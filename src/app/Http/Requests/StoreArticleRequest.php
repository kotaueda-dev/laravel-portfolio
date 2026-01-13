<?php

namespace App\Http\Requests;

use App\Data\StoreArticleData;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\RequestBody(
    request: 'StoreArticleRequest',
    required: true,
    content: new OA\JsonContent(
        required: ['title', 'content'],
        properties: [
            new OA\Property(property: 'title', type: 'string', maxLength: 255, example: '新しい記事のタイトル'),
            new OA\Property(property: 'content', type: 'string', example: '新しい記事の本文'),
        ]
    )
)]
class StoreArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ];
    }

    public function toDto(): StoreArticleData
    {
        return new StoreArticleData(
            title: $this->validated('title'),
            content: $this->validated('content'),
            user_id: $this->validated('user_id'),  // 認証から取得
        );
    }
}
