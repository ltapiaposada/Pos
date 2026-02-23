<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ReturnItem;
use App\Models\ReturnModel;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    public function createReturn(int $saleId, int $userId, array $items, ?string $reason = null): ReturnModel
    {
        return DB::transaction(function () use ($saleId, $userId, $items, $reason) {
            $sale = Sale::query()->with('items')->findOrFail($saleId);
            $items = collect($items)
                ->filter(fn ($item) => (float) ($item['quantity'] ?? 0) > 0)
                ->values()
                ->all();

            if (count($items) === 0) {
                throw ValidationException::withMessages([
                    'items' => 'Debes indicar cantidad mayor a cero en al menos un item.',
                ]);
            }

            $saleItems = $sale->items->keyBy('product_id');
            $alreadyReturnedQtyByProduct = ReturnItem::query()
                ->select('product_id', DB::raw('SUM(quantity) as returned_qty'))
                ->whereHas('returnModel', function ($query) use ($sale) {
                    $query->where('sale_id', $sale->id)
                        ->where('status', 'completed');
                })
                ->groupBy('product_id')
                ->pluck('returned_qty', 'product_id');

            $products = Product::query()
                ->with('kitItems.componentProduct')
                ->whereIn('id', collect($items)->pluck('product_id'))
                ->get()
                ->keyBy('id');

            $total = 0;
            $return = ReturnModel::query()->create([
                'sale_id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'user_id' => $userId,
                'reason' => $reason,
                'total' => 0,
                'status' => 'completed',
            ]);

            $inventoryService = app(InventoryService::class);

            foreach ($items as $item) {
                $saleItem = $saleItems->get((int) $item['product_id']);
                if (! $saleItem) {
                    throw ValidationException::withMessages([
                        'items' => 'Producto invalido en la devolucion.',
                    ]);
                }

                $quantity = (float) $item['quantity'];
                $alreadyReturned = (float) ($alreadyReturnedQtyByProduct->get((int) $item['product_id']) ?? 0);
                $availableToReturn = (float) $saleItem->quantity - $alreadyReturned;

                if ($quantity <= 0 || $quantity > $availableToReturn) {
                    throw ValidationException::withMessages([
                        'items' => "Cantidad invalida para devolucion. Disponible: {$availableToReturn}.",
                    ]);
                }

                $lineTotal = $saleItem->unit_price * $quantity;
                $total += $lineTotal;
                $alreadyReturnedQtyByProduct->put((int) $item['product_id'], $alreadyReturned + $quantity);

                ReturnItem::query()->create([
                    'return_id' => $return->id,
                    'product_id' => $saleItem->product_id,
                    'quantity' => $quantity,
                    'unit_price' => $saleItem->unit_price,
                    'tax_amount' => 0,
                    'line_total' => $lineTotal,
                ]);

                $product = $products->get($saleItem->product_id);
                $this->applyInventoryMovementsForReturnLine(
                    product: $product,
                    sale: $sale,
                    userId: $userId,
                    returnId: $return->id,
                    lineQuantity: $quantity,
                    inventoryService: $inventoryService
                );
            }

            $return->update(['total' => $total]);
            app(AccountingPostingService::class)->postReturn($return, $userId);

            return $return->load('items');
        });
    }

    private function applyInventoryMovementsForReturnLine(
        Product $product,
        Sale $sale,
        int $userId,
        int $returnId,
        float $lineQuantity,
        InventoryService $inventoryService
    ): void {
        if ($product->product_type === Product::TYPE_KIT) {
            foreach ($product->kitItems as $kitItem) {
                $component = $kitItem->componentProduct;
                if (! $component) {
                    continue;
                }

                $inventoryService->adjust([
                    'branch_id' => $sale->branch_id,
                    'product_id' => $component->id,
                    'user_id' => $userId,
                    'type' => 'IN',
                    'quantity' => (float) $kitItem->quantity * $lineQuantity,
                    'cost_price' => $component->cost_price ?? 0,
                    'ref_type' => 'return',
                    'ref_id' => $returnId,
                    'notes' => "Devolucion de kit {$product->sku}",
                ]);
            }

            return;
        }

        $inventoryService->adjust([
            'branch_id' => $sale->branch_id,
            'product_id' => $product->id,
            'user_id' => $userId,
            'type' => 'IN',
            'quantity' => $lineQuantity,
            'cost_price' => $product->cost_price ?? 0,
            'ref_type' => 'return',
            'ref_id' => $returnId,
            'notes' => 'Devolucion',
        ]);
    }
}
