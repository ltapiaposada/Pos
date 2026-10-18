<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyManagementRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'domain' => $this->normalizeDomain($this->input('domain')),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->isSystemAdmin() ?? false;
    }

    public function rules(): array
    {
        $adminEmailRules = $this->isMethod('post')
            ? ['required', 'email', 'max:255', 'unique:users,email']
            : ['nullable', 'email', 'max:255', 'unique:users,email'];
        $adminPasswordRules = $this->isMethod('post')
            ? ['required', 'string', 'min:8', 'confirmed']
            : ['nullable', 'string', 'min:8', 'confirmed'];

        return [
            'name' => ['required', 'string', 'max:150'],
            'domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(localhost|[a-z0-9.-]+\.[a-z0-9-]+)$/i',
                Rule::unique('companies', 'domain')->ignore($this->route('company')),
            ],
            'identification' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'company_type_id' => ['required', 'integer', Rule::exists('company_types', 'id')],
            'status' => ['required', Rule::in(['active', 'inactive', 'blocked'])],
            'admin_name' => $this->isMethod('post') ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'],
            'admin_email' => $adminEmailRules,
            'admin_password' => $adminPasswordRules,
            'admin_password_confirmation' => $this->isMethod('post') ? ['required'] : ['nullable'],
            'subscription_plan_type' => ['nullable', 'string', 'max:50'],
            'subscription_billing_period' => ['nullable', Rule::in(['monthly', 'yearly'])],
            'subscription_start_date' => ['nullable', 'date'],
            'subscription_end_date' => ['nullable', 'date', 'after_or_equal:subscription_start_date'],
            'subscription_status' => ['nullable', Rule::in(['active', 'pending_payment', 'expired', 'cancelled'])],
            'subscription_payment_status' => ['nullable', 'string', 'max:30'],
        ];
    }

    private function normalizeDomain(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $domain = trim(mb_strtolower($value));
        if ($domain === '') {
            return null;
        }

        $candidate = preg_match('#^https?://#', $domain) === 1 ? $domain : 'https://'.$domain;
        $host = parse_url($candidate, PHP_URL_HOST);

        if (is_string($host) && $host !== '') {
            return trim(mb_strtolower($host));
        }

        return trim(mb_strtolower($domain), "/ \t\n\r\0\x0B");
    }
}
