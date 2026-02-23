<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('record_cash_movement');
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'type' => ['required', 'in:IN,OUT'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
