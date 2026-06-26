<?php

namespace App\Services;

use App\Models\InventoryLot;
use App\Models\InventorySerial;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\SaleItemLot;
use App\Models\Sale;
use Illuminate\Validation\ValidationException;

class InventoryTrackingService
{
    public function consume(Product $product, int $branchId, SaleItem $saleItem, float $quantity): void
    {
        if ($product->tracksSerials()) {
            $this->consumeSerials($product, $branchId, $saleItem, $quantity);
        }

        if ($product->tracksLots()) {
            $this->consumeLots($product, $branchId, $saleItem, $quantity);
        }
    }

    public function restore(Product $product, Sale $sale, float $quantity): void
    {
        if ($product->tracksSerials()) {
            $serials = InventorySerial::query()
                ->where('product_id', $product->id)
                ->whereHas('saleItem', fn ($query) => $query->where('sale_id', $sale->id))
                ->where('status', InventorySerial::STATUS_SOLD)
                ->orderByDesc('sold_at')
                ->limit((int) $quantity)
                ->lockForUpdate()
                ->get();

            if (floor($quantity) !== $quantity || $serials->count() !== (int) $quantity) {
                throw ValidationException::withMessages([
                    'items' => "No fue posible restaurar los seriales de {$product->name}.",
                ]);
            }

            InventorySerial::query()->whereKey($serials->modelKeys())->update([
                'sale_item_id' => null,
                'status' => InventorySerial::STATUS_AVAILABLE,
                'sold_at' => null,
            ]);
        }

        if ($product->tracksLots()) {
            $remaining = $quantity;
            $allocations = SaleItemLot::query()
                ->whereHas('saleItem', fn ($query) => $query
                    ->where('sale_id', $sale->id)
                    ->where('product_id', $product->id))
                ->with('lot')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->get();

            foreach ($allocations as $allocation) {
                if ($remaining <= 0) {
                    break;
                }

                $availableToRestore = (float) $allocation->quantity - (float) $allocation->returned_quantity;
                if ($availableToRestore <= 0) {
                    continue;
                }

                $restored = min($remaining, $availableToRestore);
                $allocation->lot->increment('remaining_quantity', $restored);
                $allocation->increment('returned_quantity', $restored);
                $remaining = round($remaining - $restored, 3);
            }

            if ($remaining > 0) {
                throw ValidationException::withMessages([
                    'items' => "No fue posible restaurar los lotes de {$product->name}.",
                ]);
            }
        }
    }

    private function consumeSerials(Product $product, int $branchId, SaleItem $saleItem, float $quantity): void
    {
        if (floor($quantity) !== $quantity) {
            throw ValidationException::withMessages([
                'items' => "El producto serializado {$product->name} solo admite cantidades enteras.",
            ]);
        }

        $serials = InventorySerial::query()
            ->where('branch_id', $branchId)
            ->where('product_id', $product->id)
            ->where('status', InventorySerial::STATUS_AVAILABLE)
            ->orderBy('id')
            ->lockForUpdate()
            ->limit((int) $quantity)
            ->get();

        if ($serials->count() !== (int) $quantity) {
            throw ValidationException::withMessages([
                'items' => "No hay suficientes seriales disponibles para {$product->name}.",
            ]);
        }

        InventorySerial::query()
            ->whereKey($serials->modelKeys())
            ->update([
                'sale_item_id' => $saleItem->id,
                'status' => InventorySerial::STATUS_SOLD,
                'sold_at' => now(),
            ]);
    }

    private function consumeLots(Product $product, int $branchId, SaleItem $saleItem, float $quantity): void
    {
        $remaining = $quantity;
        $lots = InventoryLot::query()
            ->where('branch_id', $branchId)
            ->where('product_id', $product->id)
            ->where('remaining_quantity', '>', 0)
            ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expires_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($lots as $lot) {
            if ($remaining <= 0) {
                break;
            }

            $taken = min($remaining, (float) $lot->remaining_quantity);
            $lot->update([
                'remaining_quantity' => round((float) $lot->remaining_quantity - $taken, 3),
            ]);
            SaleItemLot::query()->create([
                'company_id' => $saleItem->company_id,
                'sale_item_id' => $saleItem->id,
                'inventory_lot_id' => $lot->id,
                'quantity' => $taken,
            ]);
            $remaining = round($remaining - $taken, 3);
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'items' => "No hay saldo suficiente por lotes para {$product->name}.",
            ]);
        }
    }
}
