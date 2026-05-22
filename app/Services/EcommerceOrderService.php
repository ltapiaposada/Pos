<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EcommerceOrderService
{
    public function createOrder(
        array $cartItems,
        int $customerId,
        int $userId,
        string $paymentMethod,
        ?string $paymentReference = null,
        ?int $branchId = null,
        ?string $deliveryAddress = null,
        ?string $couponCode = null,
        ?string $customerNote = null
    ): Sale {
        return DB::transaction(function () use ($cartItems, $customerId, $userId, $paymentMethod, $paymentReference, $branchId, $deliveryAddress, $couponCode, $customerNote) {
            if (empty($cartItems)) {
                throw ValidationException::withMessages([
                    'cart' => 'El carrito esta vacio.',
                ]);
            }

            $branch = $branchId
                ? Branch::query()->find($branchId)
                : Branch::query()->orderBy('id')->first();

            if (! $branch) {
                throw ValidationException::withMessages([
                    'branch' => 'No existe una sucursal configurada para procesar pedidos.',
                ]);
            }

            $products = Product::query()
                ->with(['tax', 'kitItems.componentProduct'])
                ->whereIn('id', collect($cartItems)->pluck('product_id'))
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            $inventoryProductIds = $products->values()
                ->flatMap(function (Product $product) {
                    if ($product->product_type === Product::TYPE_KIT) {
                        return $product->kitItems->pluck('component_product_id');
                    }

                    return [$product->id];
                })
                ->merge(
                    collect($cartItems)
                        ->flatMap(fn (array $item) => collect($item['inventory_components'] ?? [])->pluck('product_id'))
                )
                ->unique()
                ->values();

            $inventoriesByProduct = DB::table('inventories')
                ->where('branch_id', $branch->id)
                ->whereIn('product_id', $inventoryProductIds)
                ->get(['product_id', 'stock'])
                ->keyBy('product_id');

            $lineItems = [];
            $subtotal = 0.0;
            $taxTotal = 0.0;

            foreach ($cartItems as $cartItem) {
                $product = $products->get((int) $cartItem['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'cart' => 'Uno de los productos no esta disponible.',
                    ]);
                }

                $quantity = (float) $cartItem['quantity'];
                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'cart' => 'Cantidad invalida en carrito.',
                    ]);
                }

                $this->assertStockForLine(
                    product: $product,
                    lineQuantity: $quantity,
                    inventoriesByProduct: $inventoriesByProduct,
                    extraComponents: $cartItem['inventory_components'] ?? []
                );

                $unitPrice = isset($cartItem['unit_price']) ? (float) $cartItem['unit_price'] : (float) $product->sale_price;
                $lineSubtotal = $unitPrice * $quantity;
                $taxRate = (float) ($product->tax?->rate ?? 0);
                $taxAmount = $lineSubtotal * ($taxRate / 100);
                $lineTotal = $lineSubtotal + $taxAmount;

                $subtotal += $lineSubtotal;
                $taxTotal += $taxAmount;

                $lineItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $cartItem['display_name'] ?? $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_type' => null,
                    'discount_value' => 0,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                    'inventory_components' => $cartItem['inventory_components'] ?? [],
                ];
            }

            $shippingTotal = (float) config('pos.ecommerce_flat_shipping', 0);
            [$normalizedCouponCode, $couponDiscountTotal] = $this->resolveCouponDiscount($couponCode, $subtotal);
            $total = max(0, $subtotal + $taxTotal + $shippingTotal - $couponDiscountTotal);

            $lastNumber = DB::table('sales')
                ->where('branch_id', $branch->id)
                ->orderByDesc('sale_number')
                ->lockForUpdate()
                ->value('sale_number');
            $nextNumber = ((int) $lastNumber) + 1;
            $normalizedReference = trim((string) $paymentReference);
            $normalizedReference = $normalizedReference !== '' ? $normalizedReference : null;
            $composedNote = $this->composeCustomerNote($customerNote, $normalizedReference);

            $sale = Sale::query()->create([
                'company_id' => $branch->company_id,
                'branch_id' => $branch->id,
                'user_id' => $userId,
                'customer_id' => $customerId,
                'cash_register_session_id' => null,
                'sale_number' => $nextNumber,
                'status' => Sale::STATUS_PENDING,
                'order_source' => Sale::SOURCE_ECOMMERCE,
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => $taxTotal,
                'shipping_total' => $shippingTotal,
                'coupon_discount_total' => $couponDiscountTotal,
                'coupon_code' => $normalizedCouponCode,
                'delivery_address' => $deliveryAddress,
                'customer_note' => $composedNote,
                'total' => $total,
                'paid_total' => $total,
                'change_total' => 0,
                'currency' => config('pos.default_currency', 'USD'),
                'sold_at' => now(),
            ]);

            $productsById = $products->keyBy('id');

            foreach ($lineItems as $line) {
                $inventoryComponents = $line['inventory_components'] ?? [];
                unset($line['inventory_components']);

                SaleItem::query()->create([
                    ...$line,
                    'company_id' => $branch->company_id,
                    'sale_id' => $sale->id,
                ]);

                $product = $productsById->get($line['product_id']);
                $this->applyInventoryMovementsForSaleLine(
                    product: $product,
                    branchId: $branch->id,
                    userId: $userId,
                    saleId: $sale->id,
                    lineQuantity: (float) $line['quantity'],
                    extraComponents: $inventoryComponents
                );
            }

            Payment::query()->create([
                'company_id' => $branch->company_id,
                'sale_id' => $sale->id,
                'method' => $paymentMethod,
                'amount' => $total,
                'reference' => $normalizedReference ?? 'E-COMMERCE',
                'paid_at' => now(),
            ]);

            return $sale->load(['items', 'payments', 'customer', 'branch']);
        });
    }

    private function resolveCouponDiscount(?string $couponCode, float $subtotal): array
    {
        $code = strtoupper(trim((string) $couponCode));
        if ($code === '') {
            return [null, 0.0];
        }

        $coupons = (array) config('pos.ecommerce_coupons', []);
        $discountPercent = (float) ($coupons[$code] ?? 0);

        if ($discountPercent <= 0) {
            return [null, 0.0];
        }

        $discount = min($subtotal, $subtotal * ($discountPercent / 100));

        return [$code, $discount];
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
                    'cart' => "Stock insuficiente para {$componentName}.",
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
                    ? "Venta ecommerce (kit {$product->sku})"
                    : "Venta ecommerce ({$product->sku})",
            ]);
        }
    }

    private function resolveInventoryRequirementsForLine(Product $product, float $lineQuantity, array $extraComponents = []): array
    {
        $requirements = [];

        if ($product->product_type === Product::TYPE_KIT) {
            if ($product->kitItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => "El kit {$product->name} no tiene componentes configurados.",
                ]);
            }

            foreach ($product->kitItems as $kitItem) {
                $requirements[$kitItem->component_product_id] = ($requirements[$kitItem->component_product_id] ?? 0)
                    + (((float) $kitItem->quantity * (float) ($kitItem->component_unit_factor ?? 1)) * $lineQuantity);
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

    private function composeCustomerNote(?string $customerNote, ?string $paymentReference): ?string
    {
        $base = trim((string) $customerNote);
        $reference = trim((string) $paymentReference);

        if ($reference === '') {
            return $base !== '' ? $base : null;
        }

        $referenceNote = 'Referencia de pago: '.$reference;
        if ($base === '') {
            return $referenceNote;
        }

        return trim($base.' | '.$referenceNote);
    }
}
