<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashRegisterCloseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('close_cash_register');
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'closing_amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
