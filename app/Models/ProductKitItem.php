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
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
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
