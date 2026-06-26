<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\StorefrontCache;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    use BelongsToCompany;

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
        'company_id',
        'category_id',
        'tax_id',
        'name',
        'sku',
        'barcode',
        'image_url',
        'description',
        'delivery_instructions',
        'unit',
        'product_type',
        'uses_component_groups',
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
        'uses_component_groups' => 'boolean',
    ];

    public const TYPE_SIMPLE = 'simple';
    public const TYPE_KIT = 'kit';
    public const TYPE_VARIANT = 'variant';
    public const TYPE_SERVICE = 'service';
    public const TYPE_DIGITAL = 'digital';
    public const TYPE_SERIALIZED = 'serialized';
    public const TYPE_BATCH = 'batch';

    public const TYPES = [
        self::TYPE_SIMPLE,
        self::TYPE_KIT,
        self::TYPE_VARIANT,
        self::TYPE_SERVICE,
        self::TYPE_DIGITAL,
        self::TYPE_SERIALIZED,
        self::TYPE_BATCH,
    ];

    public static function unitOptions(): array
    {
        return [
            'unit' => 'Unidad',
            'und' => 'Unidad (und)',
            'g' => 'Gramos (g)',
            'kg' => 'Kilogramos (kg)',
            'libra' => 'Libras (lb)',
            'ml' => 'Mililitros (ml)',
            'l' => 'Litros (l)',
            'cm' => 'Centimetros (cm)',
            'm' => 'Metros (m)',
            'porcion' => 'Porcion',
            'vaso' => 'Vaso',
            'plato' => 'Plato',
            'service' => 'Servicio',
            'license' => 'Licencia',
        ];
    }

    public function tracksInventory(): bool
    {
        return ! in_array($this->product_type, [self::TYPE_SERVICE, self::TYPE_DIGITAL], true);
    }

    public function tracksSerials(): bool
    {
        return $this->product_type === self::TYPE_SERIALIZED;
    }

    public function tracksLots(): bool
    {
        return $this->product_type === self::TYPE_BATCH;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
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

    public function modifierGroups()
    {
        return $this->hasMany(ProductModifierGroup::class)->orderBy('display_order')->orderBy('id');
    }
}
