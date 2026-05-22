<?php

namespace App\Http\Requests;

use App\Models\RestaurantOrder;
use App\Models\RestaurantTable;
use App\Support\CompanyRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RestaurantOrderUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $items = $this->input('items');

        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = is_array($decoded) ? $decoded : [];
        }

        $this->merge([
            'items' => $items,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()->can('manage_restaurant');
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', CompanyRules::companyScoped('branches')],
            'restaurant_table_id' => ['nullable', 'integer', CompanyRules::companyScoped('restaurant_tables')],
            'customer_id' => ['nullable', 'integer', CompanyRules::companyScoped('customers')],
            'order_type' => ['required', 'in:'.implode(',', array_keys(RestaurantOrder::typeOptions()))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', CompanyRules::companyScoped('products')],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            'items.*.modifier_selections' => ['nullable', 'array'],
            'items.*.modifier_selections.*.group_id' => ['required', 'integer', CompanyRules::companyScoped('product_modifier_groups')],
            'items.*.modifier_selections.*.option_id' => ['required', 'integer', CompanyRules::companyScoped('product_modifier_options')],
            'items.*.modifier_selections.*.action' => ['required', 'in:include,remove'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $orderType = $this->input('order_type');
            $tableId = $this->input('restaurant_table_id');

            if ($orderType === RestaurantOrder::TYPE_DINE_IN && ! $tableId) {
                $validator->errors()->add('restaurant_table_id', 'Debes seleccionar una mesa para pedidos en salón.');
            }

            if ($orderType !== RestaurantOrder::TYPE_DINE_IN && $tableId) {
                $validator->errors()->add('restaurant_table_id', 'Solo los pedidos en salón pueden vincularse a una mesa.');
            }

            if ($tableId) {
                $table = RestaurantTable::query()->find($tableId);
                if ($table && $table->branch_id !== $this->integer('branch_id')) {
                    $validator->errors()->add('restaurant_table_id', 'La mesa seleccionada no pertenece a la sucursal indicada.');
                }
            }
        });
    }
}
