<?php

namespace App\Http\Requests;

use App\Models\Article;
use App\Rules\ResourceIdRule;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\RequestBody(
    request: 'UpdateArticleRequest',
    required: true,
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'title', type: 'string', maxLength: 255, example: '更新された記事のタイトル'),
            new OA\Property(property: 'content', type: 'string', example: '更新された記事の本文'),
        ]
    )
)]
class UpdateArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $article = Article::find($this->route('id'));

        return $article && $this->user()->can('update', $article);
    }

    /**
     * ルートパラメータの id をバリデーション対象に含める。
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', new ResourceIdRule],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
        ];
    }
}
