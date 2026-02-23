<?php

namespace App\Http\Requests;

use App\Models\AccountingAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_accounting');
    }

    public function rules(): array
    {
        return [
            'expense_date' => ['required', 'date'],
            'expense_account_id' => ['required', 'integer', 'exists:accounting_accounts,id'],
            'payment_method' => ['required', 'in:cash,bank,credit'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id', 'required_if:payment_method,cash'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $account = AccountingAccount::query()->find((int) $this->input('expense_account_id'));
            if (! $account || ! $account->is_active || ! $account->is_postable || $account->type !== AccountingAccount::TYPE_EXPENSE) {
                $validator->errors()->add('expense_account_id', 'Selecciona una cuenta de gasto activa y movible.');
            }
        });
    }
}
