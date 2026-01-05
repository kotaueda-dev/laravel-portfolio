<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterRequest',
    required: ['name', 'email', 'password'],
    properties: [
        new OA\Property(
            property: 'name',
            type: 'string',
            description: 'ユーザー名',
            maxLength: 50,
            example: 'Taro Yamada'
        ),
        new OA\Property(
            property: 'email',
            type: 'string',
            format: 'email',
            description: 'メールアドレス',
            maxLength: 255,
            example: 'user@example.com'
        ),
        new OA\Property(
            property: 'password',
            type: 'string',
            description: 'パスワード',
            minLength: 8,
            example: 'password123'
        ),
    ]
)]
class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
