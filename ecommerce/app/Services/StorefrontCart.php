<?php

namespace App\Services;

use App\Models\CatalogProduct;
use App\Models\Storefront;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** Carrito de sesión. Nunca consulta ni modifica tablas del POS. */
class StorefrontCart
{
    public function items(int $companyId): Collection
    {
        return collect(session($this->key($companyId), []));
    }

    public function count(int $companyId): int
    {
        return (int) $this->items($companyId)->sum('quantity');
    }

    public function add(CatalogProduct $product, int|float $quantity, ?int $variantId = null): void
    {
        abort_unless($product->is_active, 422, 'Este producto ya no está disponible.');

        $payload = $product->payload ?? [];
        $variant = collect($payload['variants'] ?? [])->firstWhere('id', $variantId);
        $lineKey = $product->id.':'.($variant['id'] ?? 'base');
        $items = $this->items($product->company_id)->all();
        $unitPrice = (float) ($variant['price'] ?? $product->price);
        $stock = $product->stock === null ? null : (float) $product->stock;
        $newQuantity = min(999, max(1, (float) (($items[$lineKey]['quantity'] ?? 0) + $quantity)));

        if ($stock !== null && $newQuantity > $stock) {
            abort(422, 'La cantidad solicitada supera la disponibilidad publicada.');
        }

        $items[$lineKey] = [
            'line_key' => $lineKey,
            'catalog_product_id' => $product->id,
            'pos_product_id' => $variant['id'] ?? $product->pos_product_id,
            'name' => $product->name,
            'variant_name' => $variant['name'] ?? null,
            'sku' => $variant['sku'] ?? $product->sku,
            'quantity' => $newQuantity,
            'unit_price' => $unitPrice,
            'tax_rate' => (float) $product->tax_rate,
            'currency' => $product->currency,
        ];

        session([$this->key($product->company_id) => $items]);
    }

    public function update(int $companyId, string $lineKey, int|float $quantity): void
    {
        $items = $this->items($companyId)->all();
        abort_unless(isset($items[$lineKey]), 404);
        $items[$lineKey]['quantity'] = min(999, max(1, (float) $quantity));
        session([$this->key($companyId) => $items]);
    }

    public function remove(int $companyId, string $lineKey): void
    {
        $items = $this->items($companyId)->all();
        unset($items[$lineKey]);
        session([$this->key($companyId) => $items]);
    }

    public function clear(int $companyId): void
    {
        session()->forget($this->key($companyId));
    }

    /** @return array{subtotal: float, tax: float, shipping: float, total: float, currency: string} */
    public function summary(int $companyId, ?Storefront $storefront = null): array
    {
        $items = $this->items($companyId);
        $subtotal = $items->sum(fn (array $item) => $item['unit_price'] * $item['quantity']);
        $tax = $items->sum(fn (array $item) => $item['unit_price'] * $item['quantity'] * $item['tax_rate'] / 100);
        $shipping = $items->isEmpty() ? 0 : (float) ($storefront?->flat_shipping ?? 0);

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'total' => round($subtotal + $tax + $shipping, 2),
            'currency' => $items->first()['currency'] ?? 'COP',
        ];
    }

    public function customerUuid(int $companyId): string
    {
        $customer = app(StorefrontCustomerSession::class)->get($companyId);
        if ($customer) {
            return $customer['external_customer_uuid'];
        }

        $key = 'ecommerce.customer.'.$companyId;
        if (! session()->has($key)) {
            session([$key => (string) Str::uuid()]);
        }

        return session($key);
    }

    private function key(int $companyId): string
    {
        return 'ecommerce.cart.'.$companyId;
    }
}
