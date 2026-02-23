<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class EcommerceCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasRole('customer');
    }

    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:card,transfer,qr,contraentrega,other'],
            'payment_reference' => ['nullable', 'required_if:payment_method,transfer,qr', 'string', 'max:100'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'customer_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('payment_method') !== 'qr') {
                return;
            }

            $business = Setting::getValue('business', []);
            $qrUrl = is_array($business) ? ($business['payment_qr_url'] ?? null) : null;
            if (empty($qrUrl)) {
                $validator->errors()->add('payment_method', 'El pago por QR no esta disponible.');
            }
        });
    }
}
