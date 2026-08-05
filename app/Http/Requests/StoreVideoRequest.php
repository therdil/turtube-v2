<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
{
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
                'nullable',
                'exists:categories,id',
            ],

            'status' => [
                'required',
                'in:public,private,unlisted,draft',
            ],

            'license' => ['required', 'in:standard,creative_commons'],

            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['string', 'max:50', 'distinct'],

            'video' => [
                'required',
                'file',
                'extensions:mp4',
                'mimetypes:video/mp4',
                'max:51200', // 50 MB
            ],

            'is_short' => ['nullable', 'boolean'],
            'is_premium' => ['nullable', 'boolean'],

        ];
    }

    public function messages(): array
    {
        return [

            'title.required' => 'Video başlığı zorunludur.',

            'video.required' => 'Lütfen bir video seçin.',
            'video.mimetypes' => 'Sadece MP4 formatındaki videolar yüklenebilir.',
            'video.max' => 'Video boyutu en fazla 50 MB olabilir.',
            'thumbnail.image' => 'Thumbnail bir görsel dosyası olmalıdır.',
            'thumbnail.max' => 'Thumbnail boyutu en fazla 5 MB olabilir.',

            'category_id.exists' => 'Geçersiz kategori seçildi.',

            'status.required' => 'Lütfen yayın durumunu seçin.',
            'status.in' => 'Geçersiz yayın durumu seçildi.',

        ];
    }
}
