<?php

namespace App\Services;

use App\Models\CatalogProduct;
use App\Models\AdministrativeTenant;

class CatalogSynchronizer
{
    public function __construct(private readonly PosApiClient $pos)
    {
    }

    public function sync(AdministrativeTenant $tenant): void
    {
        if (! app(AdministrativeAccessService::class)->canUseService($tenant, 'pos')) {
            return;
        }

        $catalog = $this->pos->catalog($tenant->tenant_uuid)['products'] ?? [];
        $availability = collect($this->pos->availability($tenant->tenant_uuid)['availability'] ?? [])
            ->keyBy('product_id');

        foreach ($catalog as $product) {
            $requiresInventory = ! in_array($product['product_type'] ?? 'simple', ['service', 'digital'], true);
            $availabilityItem = $availability->get($product['id']);

            CatalogProduct::updateOrCreate(
                ['company_id' => $tenant->id, 'pos_product_id' => $product['id']],
                [
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'description' => $product['description'],
                    'image_url' => $product['image_url'],
                    'price' => $product['price'],
                    'currency' => $product['currency'],
                    'tax_rate' => $product['tax_rate'],
                    // Un producto físico sin inventario publicado no se puede
                    // comprar. Servicios y productos digitales no requieren stock.
                    'stock' => $availabilityItem['stock'] ?? ($requiresInventory ? 0 : null),
                    'is_active' => true,
                    'payload' => $product,
                    'synced_at' => now(),
                ]
            );
        }
    }
}
