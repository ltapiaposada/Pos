<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class OptometryPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_optometry_patients');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:female,male,other'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:32'],
            'allergies' => ['nullable', 'string'],
            'systemic_history' => ['nullable', 'string'],
            'ocular_history' => ['nullable', 'string'],
        ];
    }

    public function patientData(): array
    {
        return [
            ...$this->safe()->only(['name', 'document', 'email', 'phone', 'address', 'is_active']),
            'contact_type' => Customer::TYPE_PERSON,
        ];
    }

    public function profileData(): array
    {
        return $this->safe()->only([
            'birth_date',
            'gender',
            'occupation',
            'emergency_contact_name',
            'emergency_contact_phone',
            'allergies',
            'systemic_history',
            'ocular_history',
        ]);
    }
}
