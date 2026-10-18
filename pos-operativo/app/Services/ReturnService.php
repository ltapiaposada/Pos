<?php

namespace App\Services;

use App\Models\CashRegisterSession;
use App\Models\Product;
use App\Models\ReturnItem;
use App\Models\ReturnModel;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    public function createReturn(int $saleId, int $userId, array $items, ?string $reason = null, string $refundMethod = 'cash'): ReturnModel
    {
        return DB::transaction(function () use ($saleId, $userId, $items, $reason, $refundMethod) {
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
                'company_id' => $sale->company_id,
                'sale_id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'user_id' => $userId,
                'reason' => $reason,
                'refund_method' => $refundMethod,
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

                $lineTotal = round((float) $saleItem->line_total * ($quantity / (float) $saleItem->quantity), 2);
                $taxAmount = round((float) $saleItem->tax_amount * ($quantity / (float) $saleItem->quantity), 2);
                $total += $lineTotal;
                $alreadyReturnedQtyByProduct->put((int) $item['product_id'], $alreadyReturned + $quantity);

                ReturnItem::query()->create([
                    'company_id' => $return->company_id,
                    'return_id' => $return->id,
                    'product_id' => $saleItem->product_id,
                    'quantity' => $quantity,
                    'unit_price' => $saleItem->unit_price,
                    'tax_amount' => $taxAmount,
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
                app(InventoryTrackingService::class)->restore($product, $sale, $quantity);
            }

            $previousRefunds = (float) ReturnModel::query()
                ->where('sale_id', $sale->id)
                ->where('status', 'completed')
                ->whereKeyNot($return->id)
                ->sum('total');
            $availableToRefund = round(max(0, (float) $sale->paid_total - $previousRefunds), 2);

            if ($total > $availableToRefund + 0.0001) {
                throw ValidationException::withMessages([
                    'items' => 'El reembolso supera el valor que el cliente ha pagado y aún está disponible para devolver.',
                ]);
            }

            $cashSession = null;
            if ($refundMethod === 'cash') {
                // El reembolso debe salir de la misma caja física que recibió la
                // venta. Si esa caja ya se cerró, el usuario que lo procesa debe
                // tener una caja abierta en la sucursal.
                $cashSession = CashRegisterSession::query()
                    ->whereKey($sale->cash_register_session_id)
                    ->where('status', 'open')
                    ->first();

                $cashSession ??= CashRegisterSession::query()
                    ->where('branch_id', $sale->branch_id)
                    ->where('user_id', $userId)
                    ->where('status', 'open')
                    ->first();

                if (! $cashSession) {
                    throw ValidationException::withMessages([
                        'cash_register' => 'Debes tener una caja abierta en la sucursal para reembolsar en efectivo.',
                    ]);
                }
            }

            $return->update(['total' => $total]);
            app(AccountingPostingService::class)->postReturn($return, $userId);

            if ($cashSession) {
                app(CashRegisterService::class)->recordSystemMovement(
                    session: $cashSession,
                    userId: $userId,
                    type: 'OUT',
                    amount: $total,
                    category: 'refund_cash',
                    reason: "Reembolso devolución venta #{$sale->sale_number}",
                    sourceType: ReturnModel::class,
                    sourceId: $return->id,
                );
            }

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
        if (! $product->tracksInventory()) {
            return;
        }

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
