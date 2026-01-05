<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'IndexArticleRequest',
    description: '記事一覧取得用のクエリパラメータ',
    properties: [
        new OA\Property(
            property: 'page',
            description: 'ページ番号（1以上）。未指定の場合は1ページ目を返却。',
            type: 'integer',
            minimum: 1,
            nullable: true,
        ),
    ],
)]
class IndexArticleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'page' => $this->query('page', 1),
        ]);
    }

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
            'page' => ['integer', 'min:1'],
        ];
    }
}
