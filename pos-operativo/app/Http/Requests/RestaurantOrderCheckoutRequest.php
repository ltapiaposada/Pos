<?php

namespace App\Http\Requests;

use App\Support\CompanyRules;
use Illuminate\Foundation\Http\FormRequest;

class RestaurantOrderCheckoutRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $payments = $this->input('payments');

        if (is_string($payments)) {
            $decoded = json_decode($payments, true);
            $payments = is_array($decoded) ? $decoded : [];
        }

        $this->merge([
            'payments' => $payments,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()->can('create_sale') && $this->user()->can('manage_restaurant');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', CompanyRules::companyScoped('customers')],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.method' => ['required', 'in:cash,card,transfer,other,credit'],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'payments.*.reference' => ['nullable', 'string', 'max:100'],
        ];
    }
}
