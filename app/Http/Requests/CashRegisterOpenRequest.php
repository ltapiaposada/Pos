<?php

namespace App\Http\Requests;

use App\Support\CompanyRules;
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
            'branch_id' => ['required', 'integer', CompanyRules::companyScoped('branches')],
            'opening_amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
