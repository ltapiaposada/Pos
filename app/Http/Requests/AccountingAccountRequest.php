<?php

namespace App\Http\Requests;

use App\Models\AccountingAccount;
use App\Support\CompanyContext;
use App\Support\CompanyRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AccountingAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_accounting');
    }

    public function rules(): array
    {
        $accountId = $this->route('account')?->id;
        $companyId = CompanyContext::authenticatedCompanyId();

        return [
            'code' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/', Rule::unique('accounting_accounts', 'code')->where(fn ($query) => $query->where('company_id', $companyId))->ignore($accountId)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in([
                AccountingAccount::TYPE_ASSET,
                AccountingAccount::TYPE_LIABILITY,
                AccountingAccount::TYPE_EQUITY,
                AccountingAccount::TYPE_INCOME,
                AccountingAccount::TYPE_EXPENSE,
            ])],
            'nature' => ['required', Rule::in([
                AccountingAccount::NATURE_DEBIT,
                AccountingAccount::NATURE_CREDIT,
            ])],
            'parent_account_id' => ['nullable', 'integer', CompanyRules::companyScoped('accounting_accounts')],
            'is_postable' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $accountId = $this->route('account')?->id;
            $code = (string) $this->input('code');
            $parentId = $this->input('parent_account_id');

            if ($accountId && (int) $parentId === (int) $accountId) {
                $validator->errors()->add('parent_account_id', 'La cuenta padre no puede ser la misma cuenta.');
            }

            if ($parentId) {
                $parent = AccountingAccount::query()->find($parentId);
                if ($parent && ! str_starts_with($code, $parent->code)) {
                    $validator->errors()->add('code', 'El codigo debe iniciar con el codigo de la cuenta padre.');
                }

                if ($parent && strlen($code) <= strlen($parent->code)) {
                    $validator->errors()->add('code', 'El codigo debe tener mayor nivel que la cuenta padre.');
                }
            }
        });
    }
}
