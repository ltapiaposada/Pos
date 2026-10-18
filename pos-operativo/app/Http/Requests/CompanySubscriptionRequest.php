<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanySubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSystemAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'action_mode' => ['nullable', Rule::in(['update_current', 'create_new'])],
            'plan_type' => ['required', 'string', 'max:50', Rule::exists('company_types', 'slug')],
            'billing_period' => ['required', Rule::in(array_keys(\App\Models\CompanySubscription::billingPeriodOptions()))],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(array_keys(\App\Models\CompanySubscription::statusOptions()))],
            'payment_status' => ['required', Rule::in(array_keys(\App\Models\CompanySubscription::paymentStatusOptions()))],
            'last_payment_date' => ['nullable', 'date'],
            'next_payment_date' => ['nullable', 'date'],
        ];
    }
}
