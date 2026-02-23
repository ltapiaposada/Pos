<?php

namespace App\Services;

use App\Models\CashRegisterSession;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Services\AccountingPostingService;
use App\Services\InventoryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function createSale(array $payload, int $userId): Sale
    {
        return DB::transaction(function () use ($payload, $userId) {
            $branchId = (int) $payload['branch_id'];
            $cashSession = CashRegisterSession::query()
                ->where('branch_id', $branchId)
                ->where('user_id', $userId)
                ->where('status', 'open')
                ->first();

            if (!$cashSession) {
                throw ValidationException::withMessages([
                    'cash_register' => 'No hay caja abierta para este cajero en la sucursal seleccionada.',
                ]);
            }

            $items = $payload['items'];
            if (count($items) === 0) {
                throw ValidationException::withMessages([
                    'items' => 'La venta debe tener al menos un producto.',
                ]);
            }

            $allowNegative = (bool) (Setting::getValue('business')['allow_negative_stock'] ?? false);
            $products = Product::query()
                ->with(['tax', 'kitItems.componentProduct'])
                ->whereIn('id', collect($items)->pluck('product_id'))
                ->get()
                ->keyBy('id');

            $inventoryProductIds = $products->values()
                ->flatMap(function (Product $product) {
                    if ($product->product_type === Product::TYPE_KIT) {
                        return $product->kitItems->pluck('component_product_id');
                    }

                    return [$product->id];
                })
                ->unique()
                ->values();

            $inventoriesByProduct = DB::table('inventories')
                ->where('branch_id', $branchId)
                ->whereIn('product_id', $inventoryProductIds)
                ->get(['product_id', 'stock'])
                ->keyBy('product_id');

            $subtotal = 0.0;
            $taxTotal = 0.0;
            $discountTotal = 0.0;
            $lineItems = [];

            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => 'Producto inválido en la venta.',
                    ]);
                }

                $quantity = (float) $item['quantity'];
                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Cantidad inválida.',
                    ]);
                }

                $unitPrice = (float) $item['unit_price'];
                $lineSubtotal = $unitPrice * $quantity;
                $discountValue = (float) ($item['discount_value'] ?? 0);
                $discountType = $item['discount_type'] ?? null;
                $lineDiscount = 0.0;

                if ($discountType === 'percent') {
                    $lineDiscount = $lineSubtotal * ($discountValue / 100);
                } elseif ($discountType === 'fixed') {
                    $lineDiscount = $discountValue;
                }

                $lineDiscount = round(max(0, min($lineDiscount, $lineSubtotal)), 2);
                $taxRate = $product->tax?->rate ?? 0;
                $taxAmount = round(($lineSubtotal - $lineDiscount) * ($taxRate / 100), 2);
                $lineTotal = round($lineSubtotal - $lineDiscount + $taxAmount, 2);

                $subtotal += $lineSubtotal;
                $discountTotal += $lineDiscount;
                $taxTotal += $taxAmount;

                $lineItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ];

                if (!$allowNegative) {
                    $this->assertStockForLine($product, $quantity, $inventoriesByProduct);
                }
            }

            $subtotal = round($subtotal, 2);
            $taxTotal = round($taxTotal, 2);
            $discountTotal = round($discountTotal, 2);

            $globalDiscount = (float) ($payload['global_discount'] ?? 0);
            $discountTotal = round($discountTotal + min($globalDiscount, $subtotal), 2);
            $total = round(max(0, $subtotal - $discountTotal + $taxTotal), 2);

            $payments = collect($payload['payments']);
            $creditTotal = round((float) $payments->where('method', 'credit')->sum('amount'), 2);
            $nonCreditPaidTotal = round((float) $payments->where('method', '!=', 'credit')->sum('amount'), 2);
            $coveredTotal = round($nonCreditPaidTotal + $creditTotal, 2);

            if (($coveredTotal + 0.0001) < $total) {
                throw ValidationException::withMessages([
                    'payments' => 'El pago mas credito es insuficiente.',
                ]);
            }
            if ($coveredTotal > ($total + 0.0001)) {
                throw ValidationException::withMessages([
                    'payments' => 'El pago mas credito no puede superar el total.',
                ]);
            }

            $paidTotal = min($nonCreditPaidTotal, $total);
            $changeTotal = max(0, $nonCreditPaidTotal - $total);
            $balanceTotal = round(max(0, $total - $paidTotal), 2);

            $lastNumber = DB::table('sales')
                ->where('branch_id', $branchId)
                ->orderByDesc('sale_number')
                ->lockForUpdate()
                ->value('sale_number');
            $nextNumber = ((int) $lastNumber) + 1;

            $sale = Sale::query()->create([
                'branch_id' => $branchId,
                'user_id' => $userId,
                'customer_id' => $payload['customer_id'] ?? null,
                'cash_register_session_id' => $cashSession->id,
                'sale_number' => $nextNumber,
                'status' => $balanceTotal > 0 ? Sale::STATUS_PENDING : Sale::STATUS_PAID,
                'order_source' => Sale::SOURCE_POS,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'shipping_total' => 0,
                'coupon_discount_total' => 0,
                'coupon_code' => null,
                'delivery_address' => null,
                'customer_note' => null,
                'total' => $total,
                'paid_total' => $paidTotal,
                'change_total' => $changeTotal,
                'currency' => $payload['currency'] ?? config('pos.default_currency', 'USD'),
                'sold_at' => now(),
            ]);

            foreach ($lineItems as $line) {
                $line['sale_id'] = $sale->id;
                SaleItem::query()->create($line);

                $product = $products->get($line['product_id']);
                $this->applyInventoryMovementsForSaleLine(
                    product: $product,
                    branchId: $branchId,
                    userId: $userId,
                    saleId: $sale->id,
                    lineQuantity: (float) $line['quantity']
                );
            }

            foreach ($payments as $payment) {
                Payment::query()->create([
                    'sale_id' => $sale->id,
                    'method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'reference' => $payment['reference'] ?? null,
                    'paid_at' => now(),
                ]);
            }

            app(AccountingPostingService::class)->postSale(
                sale: $sale,
                payments: $payments,
                userId: $userId
            );

            $cashAmount = (float) $payments
                ->where('method', 'cash')
                ->sum('amount');
            $cashNet = max(0, $cashAmount - $changeTotal);

            if ($cashNet > 0) {
                $notes = 'Venta Punto de venta - efectivo';

                \App\Models\CashMovement::query()->create([
                    'cash_register_session_id' => $cashSession->id,
                    'branch_id' => $branchId,
                    'user_id' => $userId,
                    'type' => 'IN',
                    'amount' => $cashNet,
                    'reason' => $notes,
                ]);
            }

            return $sale->load(['items', 'payments', 'customer', 'user', 'branch']);
        });
    }

    private function assertStockForLine(Product $product, float $lineQuantity, Collection $inventoriesByProduct): void
    {
        if ($product->product_type === Product::TYPE_KIT) {
            if ($product->kitItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => "El kit {$product->name} no tiene componentes configurados.",
                ]);
            }

            foreach ($product->kitItems as $kitItem) {
                $required = (float) $kitItem->quantity * $lineQuantity;
                $componentStock = (float) ($inventoriesByProduct->get($kitItem->component_product_id)->stock ?? 0);

                if ($componentStock - $required < 0) {
                    $componentName = $kitItem->componentProduct?->name ?? 'componente';
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$componentName} (kit {$product->name}).",
                    ]);
                }
            }

            return;
        }

        $stock = (float) ($inventoriesByProduct->get($product->id)->stock ?? 0);
        if ($stock - $lineQuantity < 0) {
            throw ValidationException::withMessages([
                'items' => "Stock insuficiente para {$product->name}.",
            ]);
        }
    }

    private function applyInventoryMovementsForSaleLine(Product $product, int $branchId, int $userId, int $saleId, float $lineQuantity): void
    {
        $inventoryService = app(InventoryService::class);

        if ($product->product_type === Product::TYPE_KIT) {
            foreach ($product->kitItems as $kitItem) {
                $component = $kitItem->componentProduct;
                if (! $component) {
                    continue;
                }

                $inventoryService->adjust([
                    'branch_id' => $branchId,
                    'product_id' => $component->id,
                    'user_id' => $userId,
                    'type' => 'OUT',
                    'quantity' => (float) $kitItem->quantity * $lineQuantity,
                    'cost_price' => $component->cost_price ?? 0,
                    'ref_type' => 'sale',
                    'ref_id' => $saleId,
                    'notes' => "Venta Punto de venta (kit {$product->sku})",
                ]);
            }

            return;
        }

        $inventoryService->adjust([
            'branch_id' => $branchId,
            'product_id' => $product->id,
            'user_id' => $userId,
            'type' => 'OUT',
            'quantity' => $lineQuantity,
            'cost_price' => $product->cost_price ?? 0,
            'ref_type' => 'sale',
            'ref_id' => $saleId,
            'notes' => 'Venta Punto de venta',
        ]);
    }
}
