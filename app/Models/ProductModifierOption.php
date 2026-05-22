<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModifierOption extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'product_modifier_group_id',
        'product_id',
        'inventory_quantity',
        'inventory_unit',
        'inventory_unit_factor',
        'label',
        'price_delta',
        'is_default',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'price_delta' => 'decimal:2',
        'inventory_quantity' => 'decimal:3',
        'inventory_unit_factor' => 'decimal:6',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function group()
    {
        return $this->belongsTo(ProductModifierGroup::class, 'product_modifier_group_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockQuantityPerSelection(): float
    {
        return round((float) ($this->inventory_quantity ?? 0) * (float) ($this->inventory_unit_factor ?? 1), 6);
    }
}
