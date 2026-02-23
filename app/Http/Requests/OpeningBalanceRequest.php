<?php

namespace App\Http\Requests;

use App\Models\AccountingAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class OpeningBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_accounting');
    }

    public function rules(): array
    {
        return [
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'equity_account_id' => ['required', 'integer', 'exists:accounting_accounts,id'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.accounting_account_id' => ['required', 'integer', 'exists:accounting_accounts,id'],
            'lines.*.balance' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $equityAccount = AccountingAccount::query()->find($this->input('equity_account_id'));
            if (! $equityAccount || ! $equityAccount->is_active || ! $equityAccount->is_postable || $equityAccount->type !== AccountingAccount::TYPE_EQUITY) {
                $validator->errors()->add('equity_account_id', 'Selecciona una cuenta de patrimonio activa y movible.');
            }

            foreach ((array) $this->input('lines', []) as $index => $line) {
                $account = AccountingAccount::query()->find($line['accounting_account_id'] ?? null);
                if (! $account || ! $account->is_active || ! $account->is_postable) {
                    $validator->errors()->add("lines.$index.accounting_account_id", 'Solo puedes usar cuentas activas y movibles.');
                }
            }
        });
    }
}
