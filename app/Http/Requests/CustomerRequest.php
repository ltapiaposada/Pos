<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_customers');
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];

        if (Customer::supportsContactType()) {
            $rules['contact_type'] = ['required', 'in:'.implode(',', [
                Customer::TYPE_PERSON,
                Customer::TYPE_COMPANY,
                Customer::TYPE_SUPPLIER,
            ])];
        }

        return $rules;
    }
}
