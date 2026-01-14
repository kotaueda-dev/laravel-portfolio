<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;

class DeleteArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $article = Article::find($this->route('id'));

        return $article && $this->user()->can('delete', $article);
    }

    /**
     * ルータで形式チェック済みの id を検証済みデータに含める。
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // ルート制約で正の整数が保証されている前提でマージ
        $validated['id'] = (int) $this->route('id');

        return $validated;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
