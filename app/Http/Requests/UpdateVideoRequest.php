<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $video = $this->route('video');

        return $this->user()?->can('update', $video) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'status' => ['required', 'in:public,private,unlisted,draft'],
            'license' => ['required', 'in:standard,creative_commons'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['string', 'max:50', 'distinct'],
            'is_short' => ['nullable', 'boolean'],
            'is_premium' => ['nullable', 'boolean'],
        ];
    }
}
