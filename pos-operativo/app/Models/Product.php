<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Support\StorefrontCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToCompany;
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

        static::creating(function (Product $product): void {
            static::releaseDeletedIdentifierConflicts(
                (int) $product->company_id,
                (string) $product->sku,
                $product->barcode
            );
        });

        static::deleting(function (Product $product): void {
            if ($product->isForceDeleting()) {
                return;
            }

            $product->archiveUniqueIdentifiers();
        });
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
        'variant_attributes',
        'cost_price',
        'sale_price',
        'is_active',
        'is_visible_ecommerce',
    ];

    public static function releaseDeletedIdentifierConflicts(int $companyId, string $sku, ?string $barcode): void
    {
        static::withoutGlobalScopes()
            ->onlyTrashed()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($sku, $barcode): void {
                $query->where('sku', $sku);

                if (filled($barcode)) {
                    $query->orWhere('barcode', $barcode);
                }
            })
            ->get()
            ->each(fn (Product $product) => $product->archiveUniqueIdentifiers($sku, $barcode));
    }

    public function archiveUniqueIdentifiers(?string $matchingSku = null, ?string $matchingBarcode = null): void
    {
        $updates = [];

        if ($matchingSku === null || $this->sku === $matchingSku) {
            $updates['sku'] = $this->archivedIdentifier($this->sku);
        }

        if (filled($this->barcode) && ($matchingBarcode === null || $this->barcode === $matchingBarcode)) {
            $updates['barcode'] = $this->archivedIdentifier($this->barcode);
        }

        if ($updates !== []) {
            $this->forceFill($updates)->saveQuietly();
        }
    }

    private function archivedIdentifier(string $identifier): string
    {
        $suffix = '~deleted-'.$this->getKey();

        return mb_substr($identifier, 0, max(0, 64 - mb_strlen($suffix))).$suffix;
    }

    public function displaySku(): string
    {
        return $this->trashed()
            ? (string) preg_replace('/~deleted-\d+$/', '', $this->sku)
            : $this->sku;
    }

    public function displayBarcode(): ?string
    {
        if (! filled($this->barcode)) {
            return null;
        }

        return $this->trashed()
            ? (string) preg_replace('/~deleted-\d+$/', '', $this->barcode)
            : $this->barcode;
    }

    protected $casts = [
        'is_active' => 'boolean',
        'is_visible_ecommerce' => 'boolean',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'uses_component_groups' => 'boolean',
        'variant_attributes' => 'array',
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
