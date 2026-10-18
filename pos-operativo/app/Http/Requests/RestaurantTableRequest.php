<?php

namespace App\Http\Requests;

use App\Models\RestaurantTable;
use App\Support\CompanyRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RestaurantTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_restaurant');
    }

    public function rules(): array
    {
        $tableId = $this->route('table')?->id;

        return [
            'branch_id' => ['required', 'integer', CompanyRules::companyScoped('branches')],
            'name' => ['required', 'string', 'max:255'],
            'number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('restaurant_tables', 'number')
                    ->ignore($tableId)
                    ->where(fn ($query) => $query->where('branch_id', $this->integer('branch_id'))),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', Rule::in(array_keys(RestaurantTable::statusOptions()))],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
