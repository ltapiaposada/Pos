<?php

namespace App\Models;

use App\Support\StorefrontCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductKitItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        $invalidate = static function (): void {
            StorefrontCache::bumpProductsVersion();
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    protected $fillable = [
        'kit_product_id',
        'component_product_id',
        'quantity',
        'component_unit',
        'component_unit_factor',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'component_unit_factor' => 'decimal:6',
    ];

    public function kitProduct()
    {
        return $this->belongsTo(Product::class, 'kit_product_id');
    }

    public function componentProduct()
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }
}
