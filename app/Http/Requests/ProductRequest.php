<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_products');
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:64', Rule::unique('products', 'barcode')->ignore($productId)],
            'image_url' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'description' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:32'],
            'product_type' => ['required', Rule::in([Product::TYPE_SIMPLE, Product::TYPE_KIT, Product::TYPE_VARIANT])],
            'parent_product_id' => ['nullable', 'integer', 'exists:products,id'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_visible_ecommerce' => ['required', 'boolean'],
            'kit_items' => ['nullable', 'array'],
            'kit_items.*.component_product_id' => ['required_with:kit_items.*.quantity', 'integer', 'exists:products,id'],
            'kit_items.*.quantity' => ['required_with:kit_items.*.component_product_id', 'numeric', 'gt:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->input('product_type');
            $productId = $this->route('product')?->id;
            $parentProductId = $this->input('parent_product_id');
            $kitItems = collect($this->input('kit_items', []))
                ->filter(fn ($item) => ! empty($item['component_product_id']));

            if ($type === Product::TYPE_VARIANT) {
                if (! $parentProductId) {
                    $validator->errors()->add('parent_product_id', 'Debes seleccionar un producto base para la variante.');
                }

                if ($productId && (int) $parentProductId === (int) $productId) {
                    $validator->errors()->add('parent_product_id', 'Una variante no puede apuntarse a si misma.');
                }

                $parentType = Product::query()->whereKey($parentProductId)->value('product_type');
                if ($parentType === Product::TYPE_VARIANT) {
                    $validator->errors()->add('parent_product_id', 'El producto base no puede ser otra variante.');
                }
            }

            if ($type !== Product::TYPE_VARIANT && $parentProductId) {
                $validator->errors()->add('parent_product_id', 'Solo las variantes pueden tener producto base.');
            }

            if ($type === Product::TYPE_KIT) {
                if ($kitItems->isEmpty()) {
                    $validator->errors()->add('kit_items', 'Debes agregar al menos un componente al kit.');
                }

                $componentIds = $kitItems->pluck('component_product_id');
                if ($componentIds->count() !== $componentIds->unique()->count()) {
                    $validator->errors()->add('kit_items', 'No puedes repetir componentes dentro del kit.');
                }

                if ($productId && $componentIds->contains((int) $productId)) {
                    $validator->errors()->add('kit_items', 'Un kit no puede incluirse a si mismo como componente.');
                }
            }
        });
    }
}
