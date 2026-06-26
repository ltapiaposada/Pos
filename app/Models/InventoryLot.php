<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class InventoryLot extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'branch_id',
        'product_id',
        'purchase_item_id',
        'lot_number',
        'expires_at',
        'quantity',
        'remaining_quantity',
        'unit_cost',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'quantity' => 'decimal:3',
        'remaining_quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
