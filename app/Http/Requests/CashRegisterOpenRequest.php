<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashRegisterOpenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('open_cash_register');
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'opening_amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
