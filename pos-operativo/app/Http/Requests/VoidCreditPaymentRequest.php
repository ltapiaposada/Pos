<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidCreditPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_accounting');
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
