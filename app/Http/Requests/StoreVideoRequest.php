<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
{
    /**
     * Convert the upload form's checkbox values to stable booleans before
     * validation. The companion content type also protects Shorts uploads
     * when a browser has an older cached JavaScript bundle.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_short' => $this->boolean('is_short') || $this->input('content_type') === 'short',
            'is_premium' => $this->boolean('is_premium'),
        ]);
    }

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'status' => [
                'required',
                'in:public,private,unlisted,draft',
            ],

            'license' => ['required', 'in:standard,creative_commons'],

            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['string', 'max:50', 'distinct'],

            'video' => [
                'required',
                'file',
                'extensions:mp4',
                'mimetypes:video/mp4',
                'max:'.config('video.max_upload_kb'),
            ],

            'is_short' => ['nullable', 'boolean'],
            'is_premium' => ['nullable', 'boolean'],
            'content_type' => ['nullable', 'in:video,short'],

        ];
    }

    public function messages(): array
    {
        return [

            'title.required' => 'Video başlığı zorunludur.',

            'video.required' => 'Lütfen bir video seçin.',
            'video.mimetypes' => 'Sadece MP4 formatındaki videolar yüklenebilir.',
            'video.max' => 'Video boyutu en fazla 50 MB olabilir.',
            'category_id.required' => 'Lütfen bir kategori seçin.',
            'category_id.exists' => 'Geçersiz kategori seçildi.',

            'status.required' => 'Lütfen yayın durumunu seçin.',
            'status.in' => 'Geçersiz yayın durumu seçildi.',

        ];
    }
}
