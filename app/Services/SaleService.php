<?php

namespace App\Services;

use App\Models\CashRegisterSession;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\MedicalOrder;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Services\AccountingPostingService;
use App\Services\InventoryService;
use App\Support\UnitConverter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function createSale(array $payload, int $userId): Sale
    {
        return DB::transaction(function () use ($payload, $userId) {
            $branchId = (int) $payload['branch_id'];
            $companyId = Branch::withoutGlobalScopes()->whereKey($branchId)->value('company_id');
            $medicalOrder = null;
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
                ->with(['tax', 'kitItems.componentProduct', 'modifierGroups.options.product'])
                ->whereIn('id', collect($items)->pluck('product_id'))
                ->get()
                ->keyBy('id');

            $items = collect($items)->map(function (array $item) use ($products): array {
                $product = $products->get((int) ($item['product_id'] ?? 0));
                if (! $product?->uses_component_groups) {
                    return $item;
                }

                $item['inventory_components'] = app(ProductModifierSelectionService::class)
                    ->normalizeForProduct($product, collect($item['modifier_selections'] ?? []));

                return $item;
            })->all();

            $inventoryProductIds = $products->values()
                ->flatMap(function (Product $product) {
                    if ($product->product_type === Product::TYPE_KIT) {
                        return $product->kitItems->pluck('component_product_id');
                    }

                    return [$product->id];
                })
                ->merge(
                    collect($items)
                        ->flatMap(fn (array $item) => collect($item['inventory_components'] ?? [])->pluck('product_id'))
                )
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
                    'delivery_instructions' => $product->product_type === Product::TYPE_DIGITAL
                        ? $product->delivery_instructions
                        : null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                    'inventory_components' => $item['inventory_components'] ?? [],
                ];

                if (!$allowNegative) {
                    $this->assertStockForLine(
                        product: $product,
                        lineQuantity: $quantity,
                        inventoriesByProduct: $inventoriesByProduct,
                        extraComponents: $item['inventory_components'] ?? []
                    );
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
            $currency = $payload['currency'] ?? $this->resolveCurrency($companyId);

            if (! empty($payload['medical_order_id'])) {
                $medicalOrder = MedicalOrder::query()
                    ->whereKey((int) $payload['medical_order_id'])
                    ->first();

                if (! $medicalOrder) {
                    throw ValidationException::withMessages([
                        'medical_order_id' => 'La orden medica seleccionada no existe.',
                    ]);
                }

                if ($medicalOrder->status !== MedicalOrder::STATUS_ACTIVE) {
                    throw ValidationException::withMessages([
                        'medical_order_id' => 'Solo puedes usar ordenes medicas activas en una venta.',
                    ]);
                }

                if ((int) $medicalOrder->customer_id !== (int) $payload['customer_id']) {
                    throw ValidationException::withMessages([
                        'medical_order_id' => 'La orden medica no corresponde al cliente seleccionado.',
                    ]);
                }
            }

            $sale = Sale::query()->create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'user_id' => $userId,
                'customer_id' => $payload['customer_id'] ?? null,
                'medical_order_id' => $medicalOrder?->id,
                'cash_register_session_id' => $cashSession->id,
                'sale_number' => $nextNumber,
                'status' => $balanceTotal > 0 ? Sale::STATUS_PENDING : Sale::STATUS_PAID,
                'order_source' => $payload['order_source'] ?? Sale::SOURCE_POS,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'shipping_total' => 0,
                'coupon_discount_total' => 0,
                'coupon_code' => null,
                'delivery_address' => $payload['delivery_address'] ?? null,
                'customer_note' => $payload['customer_note'] ?? null,
                'total' => $total,
                'paid_total' => $paidTotal,
                'change_total' => $changeTotal,
                'currency' => $currency,
                'sold_at' => now(),
            ]);

            foreach ($lineItems as $line) {
                $inventoryComponents = $line['inventory_components'] ?? [];
                unset($line['inventory_components']);
                $line['sale_id'] = $sale->id;
                $line['company_id'] = $companyId;
                $saleItem = SaleItem::query()->create($line);

                $product = $products->get($line['product_id']);
                $this->applyInventoryMovementsForSaleLine(
                    product: $product,
                    branchId: $branchId,
                    userId: $userId,
                    saleId: $sale->id,
                    lineQuantity: (float) $line['quantity'],
                    extraComponents: $inventoryComponents
                );
                app(InventoryTrackingService::class)->consume(
                    product: $product,
                    branchId: $branchId,
                    saleItem: $saleItem,
                    quantity: (float) $line['quantity']
                );
            }

            foreach ($payments as $payment) {
                Payment::query()->create([
                    'company_id' => $companyId,
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
                    'company_id' => $companyId,
                    'cash_register_session_id' => $cashSession->id,
                    'branch_id' => $branchId,
                    'user_id' => $userId,
                    'type' => 'IN',
                    'amount' => $cashNet,
                    'reason' => $notes,
                ]);
            }

            if ($medicalOrder) {
                $medicalOrder->update([
                    'status' => MedicalOrder::STATUS_USED,
                ]);
            }

            return $sale->load(['items', 'payments', 'customer', 'user', 'branch']);
        });
    }

    private function resolveCurrency(?int $companyId): string
    {
        $business = Setting::getValue('business', [], $companyId);
        $currency = is_array($business) ? trim((string) ($business['currency'] ?? '')) : '';

        return $currency !== '' ? $currency : config('pos.default_currency', 'USD');
    }

    private function assertStockForLine(Product $product, float $lineQuantity, Collection $inventoriesByProduct, array $extraComponents = []): void
    {
        $requiredByProduct = $this->resolveInventoryRequirementsForLine($product, $lineQuantity, $extraComponents);

        foreach ($requiredByProduct as $productId => $required) {
            if ($required <= 0) {
                continue;
            }

            $componentStock = (float) ($inventoriesByProduct->get($productId)->stock ?? 0);
            if ($componentStock - $required < 0) {
                $componentName = $productId === $product->id
                    ? $product->name
                    : (Product::query()->find($productId)?->name ?? 'componente');

                throw ValidationException::withMessages([
                    'items' => "Stock insuficiente para {$componentName}.",
                ]);
            }
        }
    }

    private function applyInventoryMovementsForSaleLine(Product $product, int $branchId, int $userId, int $saleId, float $lineQuantity, array $extraComponents = []): void
    {
        $inventoryService = app(InventoryService::class);
        $requiredByProduct = $this->resolveInventoryRequirementsForLine($product, $lineQuantity, $extraComponents);

        foreach ($requiredByProduct as $productId => $required) {
            if ($required <= 0) {
                continue;
            }

            $component = $productId === $product->id ? $product : Product::query()->find($productId);
            if (! $component) {
                continue;
            }

            $inventoryService->adjust([
                'branch_id' => $branchId,
                'product_id' => $component->id,
                'user_id' => $userId,
                'type' => 'OUT',
                'quantity' => $required,
                'cost_price' => $component->cost_price ?? 0,
                'ref_type' => 'sale',
                'ref_id' => $saleId,
                'notes' => $product->product_type === Product::TYPE_KIT
                    ? "Venta Punto de venta (kit {$product->sku})"
                    : "Venta Punto de venta ({$product->sku})",
            ]);
        }
    }

    private function resolveInventoryRequirementsForLine(Product $product, float $lineQuantity, array $extraComponents = []): array
    {
        $requirements = [];

        if (! $product->tracksInventory()) {
            $requirements = [];
        } elseif ($product->product_type === Product::TYPE_KIT) {
            if ($product->kitItems->isEmpty() && empty($extraComponents)) {
                throw ValidationException::withMessages([
                    'items' => "El kit {$product->name} no tiene componentes configurados.",
                ]);
            }

            foreach ($product->kitItems as $kitItem) {
                $factor = UnitConverter::resolveFactor(
                    $kitItem->component_unit,
                    $kitItem->componentProduct?->unit,
                    (float) ($kitItem->component_unit_factor ?? 1)
                );
                $requirements[$kitItem->component_product_id] = ($requirements[$kitItem->component_product_id] ?? 0)
                    + (((float) $kitItem->quantity * $factor) * $lineQuantity);
            }
        } else {
            $requirements[$product->id] = ($requirements[$product->id] ?? 0) + $lineQuantity;
        }

        foreach ($extraComponents as $component) {
            $componentProductId = (int) ($component['product_id'] ?? 0);
            $componentQuantity = (float) ($component['stock_quantity'] ?? 0) * $lineQuantity;
            if ($componentProductId <= 0 || $componentQuantity === 0.0) {
                continue;
            }

            $sign = ($component['selection_action'] ?? 'include') === 'remove' ? -1 : 1;
            $requirements[$componentProductId] = ($requirements[$componentProductId] ?? 0) + ($componentQuantity * $sign);
        }

        return collect($requirements)
            ->map(fn (float $required) => max(0, round($required, 6)))
            ->all();
    }
}
