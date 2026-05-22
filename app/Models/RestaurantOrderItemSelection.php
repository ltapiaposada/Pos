<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantOrderItemSelection extends Model
{
    use HasFactory;
    use BelongsToCompany;

    public const ACTION_INCLUDE = 'include';
    public const ACTION_REMOVE = 'remove';

    protected $fillable = [
        'company_id',
        'restaurant_order_item_id',
        'product_modifier_group_id',
        'product_modifier_option_id',
        'product_id',
        'group_name',
        'option_label',
        'selection_action',
        'price_delta',
        'inventory_quantity',
        'inventory_unit',
        'inventory_unit_factor',
        'stock_quantity',
    ];

    protected $casts = [
        'price_delta' => 'decimal:2',
        'inventory_quantity' => 'decimal:3',
        'inventory_unit_factor' => 'decimal:6',
        'stock_quantity' => 'decimal:6',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(RestaurantOrderItem::class, 'restaurant_order_item_id');
    }

    public function modifierGroup()
    {
        return $this->belongsTo(ProductModifierGroup::class, 'product_modifier_group_id');
    }

    public function modifierOption()
    {
        return $this->belongsTo(ProductModifierOption::class, 'product_modifier_option_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
