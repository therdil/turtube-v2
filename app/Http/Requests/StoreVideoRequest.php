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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',

            'video' => [
                'required',
                'file',
                'mimetypes:video/mp4',
                'max:51200', // 50 MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'video.required' => 'Lütfen bir video seçin.',
            'video.mimetypes' => 'Sadece MP4 formatındaki videolar yüklenebilir.',
            'video.max' => 'Video boyutu en fazla 50 MB olabilir.',
        ];
    }
}