<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\StorefrontCache;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        $invalidate = static function (): void {
            StorefrontCache::bumpProductsVersion();
        };

        static::saved($invalidate);
        static::deleted($invalidate);
        static::restored($invalidate);
        static::forceDeleted($invalidate);
    }

    protected $fillable = [
        'category_id',
        'tax_id',
        'name',
        'sku',
        'barcode',
        'image_url',
        'description',
        'unit',
        'product_type',
        'parent_product_id',
        'cost_price',
        'sale_price',
        'is_active',
        'is_visible_ecommerce',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_visible_ecommerce' => 'boolean',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public const TYPE_SIMPLE = 'simple';
    public const TYPE_KIT = 'kit';
    public const TYPE_VARIANT = 'variant';

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function parentProduct()
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    public function variants()
    {
        return $this->hasMany(Product::class, 'parent_product_id');
    }

    public function kitItems()
    {
        return $this->hasMany(ProductKitItem::class, 'kit_product_id');
    }

    public function kitComponents()
    {
        return $this->belongsToMany(Product::class, 'product_kit_items', 'kit_product_id', 'component_product_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function usedInKits()
    {
        return $this->belongsToMany(Product::class, 'product_kit_items', 'component_product_id', 'kit_product_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
