<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayablePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_accounting');
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,transfer'],
            'reference' => ['nullable', 'string', 'max:100'],
        ];
    }
}
