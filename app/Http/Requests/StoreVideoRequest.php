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
            ],

            'category_id' => [
                'nullable',
                'exists:categories,id',
            ],

            'status' => [
                'required',
                'in:public,private,unlisted,draft',
            ],

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

            'title.required' => 'Video başlığı zorunludur.',

            'video.required' => 'Lütfen bir video seçin.',
            'video.mimetypes' => 'Sadece MP4 formatındaki videolar yüklenebilir.',
            'video.max' => 'Video boyutu en fazla 50 MB olabilir.',

            'category_id.exists' => 'Geçersiz kategori seçildi.',

            'status.required' => 'Lütfen yayın durumunu seçin.',
            'status.in' => 'Geçersiz yayın durumu seçildi.',

        ];
    }
}