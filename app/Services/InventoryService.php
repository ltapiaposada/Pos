<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductKitItem;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function currentStock(int $branchId, int $productId): float
    {
        return (float) Inventory::query()
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->value('stock');
    }

    public function availableStockForProduct(Product $product, int $branchId): float
    {
        return $this->availableStockForProducts(collect([$product]), $branchId)[$product->id] ?? 0.0;
    }

    public function availableStockForProducts(Collection $products, int $branchId): array
    {
        $products = $products->values();
        if ($products->isEmpty()) {
            return [];
        }

        $products->each(function (Product $product): void {
            $product->loadMissing('kitItems.componentProduct');
        });

        $inventoryIds = $products->flatMap(function (Product $product) {
            if ($product->product_type === Product::TYPE_KIT) {
                return $product->kitItems->pluck('component_product_id');
            }

            return [$product->id];
        })->unique()->values();

        $stockByProduct = Inventory::query()
            ->where('branch_id', $branchId)
            ->whereIn('product_id', $inventoryIds)
            ->pluck('stock', 'product_id');

        $available = [];

        foreach ($products as $product) {
            if ($product->product_type !== Product::TYPE_KIT) {
                $available[$product->id] = max(0, round((float) ($stockByProduct[$product->id] ?? 0), 6));
                continue;
            }

            if ($product->kitItems->isEmpty()) {
                $available[$product->id] = 0.0;
                continue;
            }

            $componentAvailability = $product->kitItems->map(function (ProductKitItem $item) use ($stockByProduct) {
                $requiredInStockUnit = (float) $item->quantity * (float) ($item->component_unit_factor ?? 1);
                if ($requiredInStockUnit <= 0) {
                    return 0.0;
                }

                $componentStock = (float) ($stockByProduct[$item->component_product_id] ?? 0);

                return max(0, $componentStock / $requiredInStockUnit);
            });

            $available[$product->id] = max(0, round((float) $componentAvailability->min(), 6));
        }

        return $available;
    }

    public function adjust(array $data): Inventory
    {
        return DB::transaction(function () use ($data) {
            $companyId = Branch::withoutGlobalScopes()
                ->whereKey($data['branch_id'])
                ->value('company_id');

            $inventory = Inventory::query()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $data['branch_id'],
                    'product_id' => $data['product_id'],
                ],
                [
                    'stock' => 0,
                    'min_stock' => 0,
                ]
            );

            $allowNegative = (bool) (Setting::getValue('business')['allow_negative_stock'] ?? false);
            $quantity = (float) $data['quantity'];
            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'La cantidad debe ser mayor a cero.',
                ]);
            }

            $delta = $data['type'] === 'IN' ? $quantity : -$quantity;
            $newStock = $inventory->stock + $delta;

            if ($newStock < 0 && !$allowNegative) {
                throw ValidationException::withMessages([
                    'quantity' => 'No se permite stock negativo.',
                ]);
            }

            $payload = [
                'stock' => $newStock,
            ];

            if (array_key_exists('min_stock', $data) && $data['min_stock'] !== null && $data['min_stock'] !== '') {
                $payload['min_stock'] = (float) $data['min_stock'];
            }

            $inventory->update($payload);

            InventoryMovement::query()->create([
                'company_id' => $companyId,
                'branch_id' => $data['branch_id'],
                'product_id' => $data['product_id'],
                'user_id' => $data['user_id'],
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'cost_price' => $data['cost_price'] ?? 0,
                'ref_type' => $data['ref_type'] ?? 'manual',
                'ref_id' => $data['ref_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $inventory;
        });
    }
}
