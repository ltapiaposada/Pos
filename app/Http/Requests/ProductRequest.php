<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductModifierOption;
use App\Support\CompanyRules;
use App\Support\CompanyContext;
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
        $companyId = CompanyContext::authenticatedCompanyId();

        return [
            'category_id' => ['nullable', 'integer', CompanyRules::companyScoped('categories')],
            'tax_id' => ['nullable', 'integer', CompanyRules::companyScoped('taxes')],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->where(fn ($query) => $query->where('company_id', $companyId))->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:64', Rule::unique('products', 'barcode')->where(fn ($query) => $query->where('company_id', $companyId))->ignore($productId)],
            'image_url' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'description' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:32'],
            'product_type' => ['required', Rule::in([Product::TYPE_SIMPLE, Product::TYPE_KIT, Product::TYPE_VARIANT])],
            'parent_product_id' => ['nullable', 'integer', CompanyRules::companyScoped('products')],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_visible_ecommerce' => ['required', 'boolean'],
            'kit_items' => ['nullable', 'array'],
            'kit_items.*.component_product_id' => ['required_with:kit_items.*.quantity', 'integer', CompanyRules::companyScoped('products')],
            'kit_items.*.quantity' => ['required_with:kit_items.*.component_product_id', 'numeric', 'gt:0'],
            'kit_items.*.component_unit' => ['nullable', 'string', 'max:32'],
            'kit_items.*.component_unit_factor' => ['nullable', 'numeric', 'gt:0'],
            'modifier_groups' => ['nullable', 'array'],
            'modifier_groups.*.name' => ['required_with:modifier_groups.*.selection_type', 'string', 'max:255'],
            'modifier_groups.*.selection_type' => ['required_with:modifier_groups.*.name', Rule::in(array_keys(\App\Models\ProductModifierGroup::selectionTypeOptions()))],
            'modifier_groups.*.is_required' => ['nullable', 'boolean'],
            'modifier_groups.*.min_select' => ['nullable', 'integer', 'min:0', 'max:20'],
            'modifier_groups.*.max_select' => ['nullable', 'integer', 'min:0', 'max:20'],
            'modifier_groups.*.options' => ['nullable', 'array'],
            'modifier_groups.*.options.*.product_id' => ['nullable', 'integer', CompanyRules::companyScoped('products')],
            'modifier_groups.*.options.*.label' => ['required_with:modifier_groups.*.options.*.product_id', 'nullable', 'string', 'max:255'],
            'modifier_groups.*.options.*.price_delta' => ['nullable', 'numeric', 'min:0'],
            'modifier_groups.*.options.*.inventory_quantity' => ['nullable', 'numeric', 'gt:0'],
            'modifier_groups.*.options.*.inventory_unit' => ['nullable', 'string', 'max:32'],
            'modifier_groups.*.options.*.inventory_unit_factor' => ['nullable', 'numeric', 'gt:0'],
            'modifier_groups.*.options.*.is_default' => ['nullable', 'boolean'],
            'modifier_groups.*.options.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->input('product_type');
            $productId = $this->route('product')?->id;
            $parentProductId = $this->input('parent_product_id');
            $legacyModifierOptionIds = ProductModifierOption::query()
                ->whereIn('id', collect($this->input('modifier_groups', []))
                    ->flatMap(fn ($group) => collect($group['options'] ?? [])->pluck('id'))
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->all())
                ->whereNull('product_id')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
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

                $kitItems->each(function (array $item, int $index) use ($validator): void {
                    if (empty($item['component_unit_factor']) || (float) $item['component_unit_factor'] <= 0) {
                        $validator->errors()->add("kit_items.{$index}.component_unit_factor", 'Debes indicar el factor de conversion hacia la unidad de stock.');
                    }
                });
            }

            collect($this->input('modifier_groups', []))
                ->filter(fn ($group) => ! empty($group['name']))
                ->each(function (array $group, int $groupIndex) use ($validator, $productId, $legacyModifierOptionIds): void {
                    $type = $group['selection_type'] ?? null;
                    $minSelect = (int) ($group['min_select'] ?? 0);
                    $maxSelect = (int) ($group['max_select'] ?? 0);
                    $options = collect($group['options'] ?? [])
                        ->filter(fn ($option) => ! empty($option['label']) || ! empty($option['product_id']))
                        ->values();

                    if ($options->isEmpty()) {
                        $validator->errors()->add("modifier_groups.{$groupIndex}.options", 'Cada grupo de componentes debe tener al menos una opción.');
                        return;
                    }

                    $optionKeys = $options->map(fn ($option) => (string) ($option['product_id'] ?? '').'|'.mb_strtolower(trim((string) ($option['label'] ?? ''))));
                    if ($optionKeys->count() !== $optionKeys->unique()->count()) {
                        $validator->errors()->add("modifier_groups.{$groupIndex}.options", 'No puedes repetir opciones dentro del mismo grupo.');
                    }

                    if ($productId && $options->pluck('product_id')->filter()->contains((int) $productId)) {
                        $validator->errors()->add("modifier_groups.{$groupIndex}.options", 'Un producto no puede referenciarse a si mismo como opción.');
                    }

                    if ($type === \App\Models\ProductModifierGroup::TYPE_SINGLE) {
                        if ($maxSelect === 0) {
                            $maxSelect = 1;
                        }
                        if ($maxSelect !== 1) {
                            $validator->errors()->add("modifier_groups.{$groupIndex}.max_select", 'Los grupos de selección única deben permitir exactamente una opción.');
                        }
                        if ($group['is_required'] ?? false) {
                            if ($minSelect !== 1) {
                                $validator->errors()->add("modifier_groups.{$groupIndex}.min_select", 'Si el grupo es obligatorio debes exigir una opción.');
                            }
                        }
                    }

                    if (in_array($type, [
                        \App\Models\ProductModifierGroup::TYPE_SINGLE,
                        \App\Models\ProductModifierGroup::TYPE_MULTIPLE,
                    ], true)) {
                        $options->each(function (array $option, int $optionIndex) use ($validator, $groupIndex, $legacyModifierOptionIds): void {
                            $isLegacyOption = ! empty($option['id'])
                                && empty($option['product_id'])
                                && in_array((int) $option['id'], $legacyModifierOptionIds, true);

                            if ($isLegacyOption) {
                                return;
                            }

                            if (empty($option['product_id'])) {
                                $validator->errors()->add(
                                    "modifier_groups.{$groupIndex}.options.{$optionIndex}.product_id",
                                    'Las opciones de seleccion deben apuntar a un producto existente.'
                                );
                            }

                            if (empty($option['inventory_quantity']) || (float) $option['inventory_quantity'] <= 0) {
                                $validator->errors()->add(
                                    "modifier_groups.{$groupIndex}.options.{$optionIndex}.inventory_quantity",
                                    'Debes indicar la cantidad consumida por esta opcion.'
                                );
                            }

                            if (empty($option['inventory_unit_factor']) || (float) $option['inventory_unit_factor'] <= 0) {
                                $validator->errors()->add(
                                    "modifier_groups.{$groupIndex}.options.{$optionIndex}.inventory_unit_factor",
                                    'Debes indicar el factor de conversion hacia la unidad de stock.'
                                );
                            }
                        });
                    }

                    if ($type === \App\Models\ProductModifierGroup::TYPE_REMOVE) {
                        if (! $options->every(fn ($option) => (bool) ($option['is_default'] ?? false))) {
                            $validator->errors()->add("modifier_groups.{$groupIndex}.options", 'En grupos para quitar ingredientes, todas las opciones deben venir marcadas por defecto.');
                        }
                    }

                    if ($maxSelect > 0 && $minSelect > $maxSelect) {
                        $validator->errors()->add("modifier_groups.{$groupIndex}.min_select", 'El mínimo no puede ser mayor que el máximo.');
                    }
                });
        });
    }
}
