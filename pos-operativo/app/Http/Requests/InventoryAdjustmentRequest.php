<?php

namespace App\Http\Requests;

use App\Models\Inventory;
use App\Models\Product;
use App\Support\CompanyRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class InventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_inventory');
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', CompanyRules::companyScoped('branches')],
            'product_id' => ['required', 'integer', CompanyRules::companyScoped('products')],
            'type' => ['required', 'in:IN,OUT'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $product = Product::query()->find($this->integer('product_id'));
            if ($product && (! $product->tracksInventory() || $product->tracksSerials() || $product->tracksLots() || $product->product_type === Product::TYPE_KIT)) {
                $validator->errors()->add(
                    'product_id',
                    'Este tipo de producto se controla desde compras o por sus componentes y no admite ajustes generales.'
                );
            }

            if ($this->input('type') !== 'OUT') {
                return;
            }

            $stock = (float) Inventory::query()
                ->where('branch_id', $this->integer('branch_id'))
                ->where('product_id', $this->integer('product_id'))
                ->value('stock');

            if ((float) $this->input('quantity') > $stock) {
                $validator->errors()->add('quantity', 'La salida no puede ser superior al stock disponible en la sucursal.');
            }
        });
    }
}
