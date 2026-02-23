<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_branches');
    }

    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:20', Rule::unique('branches', 'code')->ignore($branchId)],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ];
    }
}
