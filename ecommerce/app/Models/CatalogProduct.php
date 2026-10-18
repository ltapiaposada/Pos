<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogProduct extends Model
{
    protected $table = 'ecommerce_catalog_products';
    protected $fillable = ['company_id', 'pos_product_id', 'name', 'sku', 'description', 'image_url', 'price', 'currency', 'tax_rate', 'stock', 'is_active', 'payload', 'synced_at'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'tax_rate' => 'decimal:2', 'stock' => 'decimal:3', 'is_active' => 'boolean', 'payload' => 'array', 'synced_at' => 'datetime'];
    }
}
