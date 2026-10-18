<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QrUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_settings');
    }

    public function rules(): array
    {
        return [
            'qr' => ['required', 'file', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'qr.max' => 'Solo se permiten archivos de hasta 2MB.',
        ];
    }
}
