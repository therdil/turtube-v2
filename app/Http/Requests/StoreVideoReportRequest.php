<?php

namespace App\Http\Requests;

use App\Models\VideoReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVideoReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(array_keys(VideoReport::REASONS))],
            'details' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
