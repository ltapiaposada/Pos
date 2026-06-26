<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

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
            'ecommerce_flat_shipping' => ['nullable', 'numeric', 'min:0'],
            'ecommerce_coupons' => ['nullable', 'array'],
            'ecommerce_coupons.*' => ['numeric', 'min:0', 'max:100'],
            'default_tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            'allow_negative_stock' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'file', 'max:2048'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'payment_qr' => ['nullable', 'file', 'max:2048'],
            'payment_qr_url' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.max' => 'Solo se permiten archivos de hasta 2MB.',
            'logo.uploaded' => 'Solo se permiten archivos de hasta 2MB.',
            'payment_qr.max' => 'Solo se permiten archivos de hasta 2MB.',
            'payment_qr.uploaded' => 'Solo se permiten archivos de hasta 2MB.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        logger()->error('Settings validation failed.', [
            'errors' => $validator->errors()->toArray(),
            'has_logo' => $this->hasFile('logo'),
            'has_payment_qr' => $this->hasFile('payment_qr'),
            'logo_name' => $this->file('logo')?->getClientOriginalName(),
            'logo_size' => $this->file('logo')?->getSize(),
            'logo_error' => $this->file('logo')?->getError(),
            'logo_error_message' => $this->file('logo')?->getErrorMessage(),
            'logo_is_valid' => $this->file('logo')?->isValid(),
            'qr_name' => $this->file('payment_qr')?->getClientOriginalName(),
            'qr_size' => $this->file('payment_qr')?->getSize(),
            'qr_error' => $this->file('payment_qr')?->getError(),
            'qr_error_message' => $this->file('payment_qr')?->getErrorMessage(),
            'qr_is_valid' => $this->file('payment_qr')?->isValid(),
            'logo_url' => $this->input('logo_url'),
            'payment_qr_url' => $this->input('payment_qr_url'),
        ]);

        parent::failedValidation($validator);
    }

    protected function prepareForValidation(): void
    {
        $coupons = $this->parseCoupons($this->input('ecommerce_coupons_text'));

        $this->merge([
            'allow_negative_stock' => $this->boolean('allow_negative_stock'),
            'ecommerce_flat_shipping' => $this->input('ecommerce_flat_shipping') !== null
                ? (float) $this->input('ecommerce_flat_shipping')
                : null,
            'ecommerce_coupons' => $coupons,
        ]);
    }

    private function parseCoupons(?string $value): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim((string) $value));
        $coupons = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            [$code, $percent] = array_pad(preg_split('/[=:]/', $line, 2), 2, null);
            $normalizedCode = strtoupper(trim((string) $code));
            $normalizedPercent = trim((string) $percent);

            if ($normalizedCode === '' || $normalizedPercent === '' || ! is_numeric($normalizedPercent)) {
                continue;
            }

            $coupons[$normalizedCode] = round((float) $normalizedPercent, 2);
        }

        return $coupons;
    }
}
