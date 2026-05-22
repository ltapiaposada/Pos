<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductModifierGroup;
use App\Models\ProductModifierOption;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\RestaurantOrderItemSelection;
use App\Models\RestaurantTable;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RestaurantOrderService
{
    public function createOrder(array $payload, int $userId): RestaurantOrder
    {
        return DB::transaction(function () use ($payload, $userId) {
            $branchId = (int) $payload['branch_id'];
            $tableId = $payload['restaurant_table_id'] ?? null;

            if ($tableId) {
                $existing = RestaurantOrder::query()
                    ->where('restaurant_table_id', $tableId)
                    ->whereIn('status', RestaurantOrder::activeStatuses())
                    ->latest('id')
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            $lastNumber = RestaurantOrder::withoutGlobalScopes()
                ->where('branch_id', $branchId)
                ->orderByDesc('order_number')
                ->lockForUpdate()
                ->value('order_number');

            $order = RestaurantOrder::query()->create([
                'branch_id' => $branchId,
                'restaurant_table_id' => $tableId,
                'user_id' => $userId,
                'customer_id' => $payload['customer_id'] ?? null,
                'order_number' => ((int) $lastNumber) + 1,
                'order_type' => $payload['order_type'],
                'status' => RestaurantOrder::STATUS_OPEN,
                'subtotal' => 0,
                'tax' => 0,
                'discount' => 0,
                'total' => 0,
                'notes' => $payload['notes'] ?? null,
                'opened_at' => now(),
            ]);

            $this->syncTableOccupancy($order->table, $order->status);

            return $order->load(['table', 'customer', 'items.product']);
        });
    }

    public function updateOrder(RestaurantOrder $order, array $payload): RestaurantOrder
    {
        return DB::transaction(function () use ($order, $payload) {
            if ($order->status !== RestaurantOrder::STATUS_OPEN) {
                throw ValidationException::withMessages([
                    'order' => 'Solo puedes editar productos mientras el pedido está abierto.',
                ]);
            }

            $branchId = (int) $payload['branch_id'];
            $tableId = $payload['restaurant_table_id'] ?? null;
            $items = $payload['items'] ?? [];

            $products = Product::query()
                ->with(['tax:id,rate', 'modifierGroups.options'])
                ->whereIn('id', collect($items)->pluck('product_id'))
                ->get()
                ->keyBy('id');
            $inventoryService = app(InventoryService::class);

            $subtotal = 0.0;
            $tax = 0.0;
            $preparedItems = [];

            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'Se detectó un producto inválido en el pedido.',
                    ]);
                }

                $quantity = (float) $item['quantity'];
                $availableStock = $inventoryService->availableStockForProduct($product, $branchId);
                if ($availableStock <= 0 || $quantity > $availableStock) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$product->name}. Disponible: ".number_format($availableStock, 3).'.',
                    ]);
                }
                $unitPrice = (float) $item['unit_price'];
                $modifierSelections = collect($item['modifier_selections'] ?? []);
                $selectionPayload = app(ProductModifierSelectionService::class)
                    ->normalizeForProduct($product, $modifierSelections);
                $modifierDelta = collect($selectionPayload)->sum(fn ($selection) => (float) $selection['price_delta']);
                $lineUnitPrice = round($unitPrice + $modifierDelta, 2);
                $lineSubtotal = round($quantity * $lineUnitPrice, 2);
                $lineTax = round($lineSubtotal * (((float) ($product->tax?->rate ?? 0)) / 100), 2);

                $subtotal += $lineSubtotal;
                $tax += $lineTax;

                $preparedItems[] = [
                    'company_id' => $order->company_id ?: Branch::withoutGlobalScopes()->whereKey($branchId)->value('company_id'),
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $lineUnitPrice,
                    'subtotal' => $lineSubtotal,
                    'notes' => $item['notes'] ?? null,
                    'kitchen_status' => RestaurantOrderItem::STATUS_PENDING,
                    'modifier_selections' => $selectionPayload,
                ];
            }

            $previousTable = $order->table;
            $order->update([
                'branch_id' => $branchId,
                'restaurant_table_id' => $tableId,
                'customer_id' => $payload['customer_id'] ?? null,
                'order_type' => $payload['order_type'],
                'notes' => $payload['notes'] ?? null,
                'subtotal' => round($subtotal, 2),
                'tax' => round($tax, 2),
                'discount' => 0,
                'total' => round($subtotal + $tax, 2),
            ]);

            $order->items()->delete();

            foreach ($preparedItems as $preparedItem) {
                $selections = $preparedItem['modifier_selections'] ?? [];
                unset($preparedItem['modifier_selections']);

                /** @var RestaurantOrderItem $createdItem */
                $createdItem = $order->items()->create($preparedItem);
                $createdItem->selections()->createMany($selections);
            }

            if ($previousTable && $previousTable->id !== $order->restaurant_table_id) {
                $this->releaseTableIfFree($previousTable);
            }

            $this->syncTableOccupancy($order->table, $order->status);

            return $order->load(['table', 'customer', 'items.product']);
        });
    }

    public function sendToKitchen(RestaurantOrder $order): RestaurantOrder
    {
        return DB::transaction(function () use ($order) {
            if ($order->items()->count() === 0) {
                throw ValidationException::withMessages([
                    'order' => 'Agrega productos antes de enviar el pedido a cocina.',
                ]);
            }

            if ($order->status !== RestaurantOrder::STATUS_OPEN) {
                throw ValidationException::withMessages([
                    'order' => 'Este pedido ya no está disponible para enviar a cocina.',
                ]);
            }

            $order->update([
                'status' => RestaurantOrder::STATUS_SENT_TO_KITCHEN,
            ]);

            return $order->fresh(['table', 'customer', 'items.product']);
        });
    }

    public function updateKitchenItemStatus(RestaurantOrderItem $item, string $status): RestaurantOrderItem
    {
        return DB::transaction(function () use ($item, $status) {
            $item->update([
                'kitchen_status' => $status,
            ]);

            $order = $item->order()->with('items')->firstOrFail();
            $this->syncOrderStatusFromItems($order);

            return $item->fresh(['order.table', 'product']);
        });
    }

    public function updateOrderStatus(RestaurantOrder $order, string $status): RestaurantOrder
    {
        return DB::transaction(function () use ($order, $status) {
            $order->update([
                'status' => $status,
            ]);

            if ($status === RestaurantOrder::STATUS_CANCELLED) {
                $order->closed_at = now();
                $order->save();
                $this->releaseTableIfFree($order->table);
            }

            return $order->fresh(['table', 'customer', 'items.product']);
        });
    }

    public function convertToSale(RestaurantOrder $order, array $payload, int $userId, SaleService $saleService): Sale
    {
        return DB::transaction(function () use ($order, $payload, $userId, $saleService) {
            $order->loadMissing(['items.selections']);

            if ($order->sale_id) {
                return $order->sale()->firstOrFail();
            }

            if ($order->status === RestaurantOrder::STATUS_CANCELLED) {
                throw ValidationException::withMessages([
                    'order' => 'No puedes convertir en venta un pedido cancelado.',
                ]);
            }

            $customerId = $payload['customer_id']
                ?? $order->customer_id
                ?? Customer::query()->where('document', 'CF')->value('id')
                ?? Customer::query()->orderBy('id')->value('id');

            if (! $customerId) {
                throw ValidationException::withMessages([
                    'customer_id' => 'Debes tener al menos un cliente activo para convertir el pedido en venta.',
                ]);
            }

            $sale = $saleService->createSale([
                'branch_id' => $order->branch_id,
                'customer_id' => $customerId,
                'items' => $order->items->map(fn (RestaurantOrderItem $item) => [
                    'product_id' => $item->product_id,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'discount_type' => null,
                    'discount_value' => 0,
                    'inventory_components' => $item->selections
                        ->filter(fn (RestaurantOrderItemSelection $selection) => $selection->product_id && (float) $selection->stock_quantity !== 0.0)
                        ->map(fn (RestaurantOrderItemSelection $selection) => [
                            'product_id' => $selection->product_id,
                            'stock_quantity' => (float) $selection->stock_quantity,
                            'selection_action' => $selection->selection_action,
                            'label' => $selection->option_label,
                        ])->values()->all(),
                ])->values()->all(),
                'global_discount' => 0,
                'payments' => $payload['payments'],
                'order_source' => Sale::SOURCE_RESTAURANT,
                'customer_note' => $order->notes,
            ], $userId);

            $order->update([
                'customer_id' => $customerId,
                'sale_id' => $sale->id,
                'status' => RestaurantOrder::STATUS_CLOSED,
                'closed_at' => now(),
            ]);

            $order->items()->where('kitchen_status', '!=', RestaurantOrderItem::STATUS_DELIVERED)
                ->update(['kitchen_status' => RestaurantOrderItem::STATUS_DELIVERED]);

            $this->releaseTableIfFree($order->table);

            return $sale;
        });
    }

    public function closeOrder(RestaurantOrder $order): RestaurantOrder
    {
        return DB::transaction(function () use ($order) {
            if (! $order->sale_id) {
                throw ValidationException::withMessages([
                    'order' => 'Convierte el pedido en venta antes de cerrarlo.',
                ]);
            }

            $order->update([
                'status' => RestaurantOrder::STATUS_CLOSED,
                'closed_at' => now(),
            ]);

            $this->releaseTableIfFree($order->table);

            return $order->fresh(['table', 'customer', 'items.product', 'sale']);
        });
    }

    private function syncOrderStatusFromItems(RestaurantOrder $order): void
    {
        if (! in_array($order->status, RestaurantOrder::activeStatuses(), true)) {
            return;
        }

        $statuses = $order->items->pluck('kitchen_status')->filter()->values();
        if ($statuses->isEmpty()) {
            return;
        }

        if ($statuses->every(fn ($status) => $status === RestaurantOrderItem::STATUS_DELIVERED)) {
            $nextStatus = RestaurantOrder::STATUS_DELIVERED;
        } elseif ($statuses->every(fn ($status) => in_array($status, [RestaurantOrderItem::STATUS_READY, RestaurantOrderItem::STATUS_DELIVERED], true))) {
            $nextStatus = RestaurantOrder::STATUS_READY;
        } elseif ($statuses->contains(RestaurantOrderItem::STATUS_IN_PREPARATION) || $statuses->contains(RestaurantOrderItem::STATUS_READY)) {
            $nextStatus = RestaurantOrder::STATUS_IN_PREPARATION;
        } else {
            $nextStatus = RestaurantOrder::STATUS_SENT_TO_KITCHEN;
        }

        $order->update([
            'status' => $nextStatus,
        ]);
    }

    private function syncTableOccupancy(?RestaurantTable $table, string $orderStatus): void
    {
        if (! $table) {
            return;
        }

        if (in_array($orderStatus, RestaurantOrder::activeStatuses(), true)) {
            $table->update([
                'status' => RestaurantTable::STATUS_OCCUPIED,
                'is_active' => true,
            ]);
        }
    }

    private function releaseTableIfFree(?RestaurantTable $table): void
    {
        if (! $table) {
            return;
        }

        $hasActiveOrder = RestaurantOrder::query()
            ->where('restaurant_table_id', $table->id)
            ->whereIn('status', RestaurantOrder::activeStatuses())
            ->exists();

        if (! $hasActiveOrder && $table->status === RestaurantTable::STATUS_OCCUPIED) {
            $table->update([
                'status' => RestaurantTable::STATUS_AVAILABLE,
            ]);
        }
    }

    private function normalizeModifierSelections(Product $product, \Illuminate\Support\Collection $submittedSelections): array
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
                        'items' => 'Se detectó una selección inválida de componentes.',
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
                    'items' => "Has excedido el máximo de opciones en {$group->name}.",
                ]);
            }

            if ($group->selection_type === ProductModifierGroup::TYPE_SINGLE && $selectedCount !== 1 && $defaultCount === 0) {
                throw ValidationException::withMessages([
                    'items' => "Debes elegir una opción en {$group->name}.",
                ]);
            }
        }

        return $normalized->all();
    }
}
