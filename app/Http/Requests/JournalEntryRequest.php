<?php

namespace App\Http\Requests;

use App\Models\AccountingAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class JournalEntryRequest extends FormRequest
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
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.accounting_account_id' => ['required', 'integer', 'exists:accounting_accounts,id'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $lines = collect($this->input('lines', []));

            $totalDebit = $lines->sum(fn ($line) => (float) ($line['debit'] ?? 0));
            $totalCredit = $lines->sum(fn ($line) => (float) ($line['credit'] ?? 0));

            if ($totalDebit <= 0 || $totalCredit <= 0) {
                $validator->errors()->add('lines', 'El asiento debe tener valores de debe y haber mayores a cero.');
            }

            if (abs($totalDebit - $totalCredit) > 0.0001) {
                $validator->errors()->add('lines', 'El asiento no esta cuadrado (debe = haber).');
            }

            foreach ($lines as $index => $line) {
                $debit = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);
                if (($debit > 0 && $credit > 0) || ($debit <= 0 && $credit <= 0)) {
                    $validator->errors()->add("lines.$index", 'Cada linea debe tener valor solo en debe o solo en haber.');
                }

                $account = AccountingAccount::query()->find($line['accounting_account_id'] ?? null);
                if ($account && ! $account->is_postable) {
                    $validator->errors()->add("lines.$index.accounting_account_id", 'Solo puedes registrar movimientos en cuentas auxiliares (movibles).');
                }
            }
        });
    }
}

