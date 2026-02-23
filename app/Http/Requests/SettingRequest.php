<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_settings');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'currency' => ['required', 'string', 'max:10'],
            'default_tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            'allow_negative_stock' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'logo_url' => ['nullable', 'url', 'max:500'],
            'payment_qr' => ['nullable', 'image', 'max:2048'],
            'payment_qr_url' => ['nullable', 'url', 'max:500'],
        ];
    }
}
