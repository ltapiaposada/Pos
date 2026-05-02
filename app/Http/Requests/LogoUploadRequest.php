<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogoUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_settings');
    }

    public function rules(): array
    {
        return [
            'logo' => ['required', 'file', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.max' => 'Solo se permiten archivos de hasta 2MB.',
            'logo.uploaded' => 'Solo se permiten archivos de hasta 2MB.',
        ];
    }
}
