<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function createPurchase(array $payload, int $userId): Purchase
    {
        return DB::transaction(function () use ($payload, $userId) {
            $branchId = (int) $payload['branch_id'];
            $items = $payload['items'];

            if (count($items) === 0) {
                throw ValidationException::withMessages([
                    'items' => 'La compra debe tener al menos un producto.',
                ]);
            }

            $products = Product::query()
                ->with('tax:id,rate')
                ->whereIn('id', collect($items)->pluck('product_id'))
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;
            $taxTotal = 0.0;
            $lineItems = [];

            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'Producto inválido en la compra.',
                    ]);
                }

                $quantity = (float) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Cantidad inválida en la compra.',
                    ]);
                }

                $lineSubtotal = $quantity * $unitCost;
                $taxRate = (float) ($product->tax?->rate ?? 0);
                $taxAmount = round($lineSubtotal * ($taxRate / 100), 2);
                $lineTotal = round($lineSubtotal + $taxAmount, 2);

                $subtotal += $lineSubtotal;
                $taxTotal += $taxAmount;

                $lineItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $taxTotal = round($taxTotal, 2);
            $total = round($subtotal + $taxTotal, 2);
            $paidTotal = round((float) ($payload['paid_total'] ?? 0), 2);

            if (($payload['payment_method'] ?? 'credit') !== 'credit' && ($paidTotal + 0.0001) < $total) {
                throw ValidationException::withMessages([
                    'paid_total' => 'Para compras pagadas, el valor pagado debe cubrir el total.',
                ]);
            }

            $paidTotal = min($paidTotal, $total);
            $balanceTotal = round(max(0, $total - $paidTotal), 2);

            $lastNumber = DB::table('purchases')
                ->where('branch_id', $branchId)
                ->orderByDesc('purchase_number')
                ->lockForUpdate()
                ->value('purchase_number');
            $nextNumber = ((int) $lastNumber) + 1;

            $purchase = Purchase::query()->create([
                'branch_id' => $branchId,
                'user_id' => $userId,
                'purchase_number' => $nextNumber,
                'status' => Purchase::STATUS_POSTED,
                'supplier_name' => $payload['supplier_name'],
                'supplier_document' => $payload['supplier_document'] ?? null,
                'invoice_number' => $payload['invoice_number'] ?? null,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'total' => $total,
                'paid_total' => $paidTotal,
                'balance_total' => $balanceTotal,
                'payment_method' => $payload['payment_method'],
                'purchased_at' => now(),
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($lineItems as $line) {
                PurchaseItem::query()->create([
                    ...$line,
                    'purchase_id' => $purchase->id,
                ]);

                app(InventoryService::class)->adjust([
                    'branch_id' => $branchId,
                    'product_id' => $line['product_id'],
                    'user_id' => $userId,
                    'type' => 'IN',
                    'quantity' => $line['quantity'],
                    'cost_price' => $line['unit_cost'],
                    'ref_type' => 'purchase',
                    'ref_id' => $purchase->id,
                    'notes' => 'Ingreso por compra',
                ]);

                Product::query()->whereKey($line['product_id'])->update([
                    'cost_price' => $line['unit_cost'],
                ]);
            }

            app(AccountingPostingService::class)->postPurchase(
                purchase: $purchase,
                paymentMethod: $payload['payment_method'],
                userId: $userId
            );

            return $purchase->load(['items', 'branch', 'user']);
        });
    }
}
