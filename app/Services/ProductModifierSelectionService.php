<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductModifierGroup;
use App\Models\ProductModifierOption;
use App\Models\RestaurantOrderItemSelection;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProductModifierSelectionService
{
    public function normalizeForProduct(Product $product, Collection $submittedSelections): array
    {
        if ($submittedSelections->isEmpty() && $product->modifierGroups->isEmpty()) {
            return [];
        }

        $groups = $product->modifierGroups->keyBy('id');
        $options = $product->modifierGroups
            ->flatMap(fn (ProductModifierGroup $group) => $group->options)
            ->keyBy('id');

        $normalized = $submittedSelections
            ->map(function (array $selection) use ($groups, $options, $product) {
                $group = $groups->get((int) ($selection['group_id'] ?? 0));
                $option = $options->get((int) ($selection['option_id'] ?? 0));

                if (! $group || ! $option || (int) $option->product_modifier_group_id !== (int) $group->id) {
                    throw ValidationException::withMessages([
                        'items' => 'Se detecto una seleccion invalida de componentes.',
                    ]);
                }

                $action = $selection['action'] ?? RestaurantOrderItemSelection::ACTION_INCLUDE;
                if ($group->selection_type === ProductModifierGroup::TYPE_REMOVE) {
                    $action = RestaurantOrderItemSelection::ACTION_REMOVE;
                }

                return [
                    'company_id' => $product->company_id,
                    'product_modifier_group_id' => $group->id,
                    'product_modifier_option_id' => $option->id,
                    'product_id' => $option->product_id,
                    'group_name' => $group->name,
                    'option_label' => $option->label,
                    'selection_action' => $action,
                    'price_delta' => $action === RestaurantOrderItemSelection::ACTION_INCLUDE ? (float) $option->price_delta : 0,
                    'inventory_quantity' => (float) ($option->inventory_quantity ?? 0),
                    'inventory_unit' => $option->inventory_unit ?: $option->product?->unit,
                    'inventory_unit_factor' => (float) ($option->inventory_unit_factor ?? 1),
                    'stock_quantity' => round((float) ($option->inventory_quantity ?? 0) * (float) ($option->inventory_unit_factor ?? 1), 6),
                ];
            })
            ->values();

        foreach ($product->modifierGroups as $group) {
            $groupSelections = $normalized->where('product_modifier_group_id', $group->id)->values();
            $selectedCount = $groupSelections->count();
            $defaultCount = $group->options->where('is_default', true)->where('is_active', true)->count();

            if (in_array($group->selection_type, [ProductModifierGroup::TYPE_SINGLE, ProductModifierGroup::TYPE_MULTIPLE], true)) {
                $groupSelections->each(function (array $selection) use ($group): void {
                    $isLegacySelection = empty($selection['product_id']);

                    if ($isLegacySelection) {
                        return;
                    }

                    if (empty($selection['product_id'])) {
                        throw ValidationException::withMessages([
                            'items' => "Las opciones de {$group->name} deben estar ligadas a productos existentes.",
                        ]);
                    }

                    if ((float) ($selection['stock_quantity'] ?? 0) <= 0) {
                        throw ValidationException::withMessages([
                            'items' => "Las opciones de {$group->name} deben tener consumo de inventario configurado.",
                        ]);
                    }
                });
            }

            if ($group->selection_type === ProductModifierGroup::TYPE_REMOVE) {
                continue;
            }

            $minRequired = (int) $group->min_select;
            $maxAllowed = (int) $group->max_select;

            if ($group->is_required && $selectedCount < max(1, $minRequired)) {
                throw ValidationException::withMessages([
                    'items' => "Debes seleccionar opciones en {$group->name}.",
                ]);
            }

            if ($selectedCount < $minRequired) {
                throw ValidationException::withMessages([
                    'items' => "Faltan selecciones en {$group->name}.",
                ]);
            }

            if ($maxAllowed > 0 && $selectedCount > $maxAllowed) {
                throw ValidationException::withMessages([
                    'items' => "Has excedido el maximo de opciones en {$group->name}.",
                ]);
            }

            if ($group->selection_type === ProductModifierGroup::TYPE_SINGLE && $selectedCount !== 1 && $defaultCount === 0) {
                throw ValidationException::withMessages([
                    'items' => "Debes elegir una opcion en {$group->name}.",
                ]);
            }
        }

        return $normalized->all();
    }

    public function normalizeStorefrontInput(Product $product, array $rawInput): array
    {
        $submittedSelections = collect();

        foreach ($rawInput as $groupId => $value) {
            if (is_array($value)) {
                foreach ($value as $optionId) {
                    if ($optionId === null || $optionId === '') {
                        continue;
                    }

                    $submittedSelections->push([
                        'group_id' => (int) $groupId,
                        'option_id' => (int) $optionId,
                        'action' => RestaurantOrderItemSelection::ACTION_INCLUDE,
                    ]);
                }

                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $submittedSelections->push([
                'group_id' => (int) $groupId,
                'option_id' => (int) $value,
                'action' => RestaurantOrderItemSelection::ACTION_INCLUDE,
            ]);
        }

        return $this->normalizeForProduct($product, $submittedSelections);
    }

    public function priceDelta(array $normalizedSelections): float
    {
        return round(collect($normalizedSelections)->sum(fn (array $selection) => (float) ($selection['price_delta'] ?? 0)), 2);
    }

    public function summaryLines(array $normalizedSelections): array
    {
        return collect($normalizedSelections)
            ->groupBy('group_name')
            ->map(function (Collection $groupSelections, string $groupName) {
                $labels = $groupSelections
                    ->map(function (array $selection) {
                        $label = (string) ($selection['option_label'] ?? '');

                        return ($selection['selection_action'] ?? RestaurantOrderItemSelection::ACTION_INCLUDE) === RestaurantOrderItemSelection::ACTION_REMOVE
                            ? 'Sin '.$label
                            : $label;
                    })
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'group' => $groupName,
                    'labels' => $labels,
                ];
            })
            ->values()
            ->all();
    }

    public function displayName(Product $product, array $normalizedSelections): string
    {
        $summary = collect($this->summaryLines($normalizedSelections))
            ->flatMap(fn (array $line) => $line['labels'] ?? [])
            ->filter()
            ->values();

        if ($summary->isEmpty()) {
            return $product->name;
        }

        return $product->name.' ('.$summary->implode(', ').')';
    }
}
