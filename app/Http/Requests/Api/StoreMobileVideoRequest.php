<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreMobileVideoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', $this->input('visibility', 'public')),
            'is_premium' => $this->boolean('is_premium'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user('sanctum') !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'status' => ['required', 'in:public,private,unlisted,draft'],
            'license' => ['nullable', 'in:standard,creative_commons'],
            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['string', 'max:50', 'distinct'],
            'video' => [
                'required',
                'file',
                'extensions:mp4',
                'mimetypes:video/mp4',
                'max:'.config('video.max_upload_kb'),
            ],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_premium' => ['nullable', 'boolean'],
        ];
    }
}
