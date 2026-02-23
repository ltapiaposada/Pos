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
            'logo' => ['required', 'image', 'max:5120'],
        ];
    }
}
