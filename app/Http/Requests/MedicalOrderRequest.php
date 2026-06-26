<?php

namespace App\Http\Requests;

use App\Models\MedicalOrder;
use App\Support\CompanyRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicalOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_optometry_orders');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', CompanyRules::companyScoped('customers')],
            'clinical_record_id' => ['nullable', 'integer', CompanyRules::companyScoped('clinical_records')],
            'ordered_at' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(MedicalOrder::statusOptions()))],
            'prescription_details' => ['required', 'string'],
            'usage_instructions' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'professional_name' => ['nullable', 'string', 'max:255'],
            'professional_license' => ['nullable', 'string', 'max:255'],
        ];
    }
}
