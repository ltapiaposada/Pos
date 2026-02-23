<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $items = $this->input('items');
        $payments = $this->input('payments');

        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = is_array($decoded) ? $decoded : [];
        }

        if (is_string($payments)) {
            $decoded = json_decode($payments, true);
            $payments = is_array($decoded) ? $decoded : [];
        }

        $this->merge([
            'items' => $items,
            'payments' => $payments,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()->can('create_sale');
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'in:percent,fixed'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'global_discount' => ['nullable', 'numeric', 'min:0'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.method' => ['required', 'in:cash,card,transfer,other,credit'],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'payments.*.reference' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Debes seleccionar un cliente.',
            'customer_id.exists' => 'El cliente seleccionado no es valido.',
        ];
    }
}
