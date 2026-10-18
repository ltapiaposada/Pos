<?php

namespace App\Http\Requests;

use App\Support\CompanyRules;
use Illuminate\Foundation\Http\FormRequest;

class ClinicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_optometry_records');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', CompanyRules::companyScoped('customers')],
            'examined_at' => ['required', 'date'],
            'reason_for_consultation' => ['required', 'string'],
            'medical_history' => ['nullable', 'string'],
            'ocular_history' => ['nullable', 'string'],
            'examination' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'treatment_plan' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'professional_name' => ['nullable', 'string', 'max:255'],
            'professional_license' => ['nullable', 'string', 'max:255'],
        ];
    }
}
