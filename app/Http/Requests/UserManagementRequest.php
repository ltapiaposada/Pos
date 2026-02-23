<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserManagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_users');
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $passwordRules = $this->isMethod('post')
            ? ['required', 'string', 'min:8', 'confirmed']
            : ['nullable', 'string', 'min:8', 'confirmed'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'password' => $passwordRules,
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
