<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('sanctum') !== null;
    }

    public function rules(): array
    {
        return [
            'role' => ['sometimes', 'string', 'in:user,moderator,admin'],
            'banned' => ['sometimes', 'boolean'],
            'ban_reason' => ['nullable', 'string', 'max:500'],
            'premium_duration' => ['sometimes', 'string', 'in:1,3,12,revoke'],
        ];
    }
}
