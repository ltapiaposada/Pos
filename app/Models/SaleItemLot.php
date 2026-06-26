<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class SaleItemLot extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'sale_item_id',
        'inventory_lot_id',
        'quantity',
        'returned_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'returned_quantity' => 'decimal:3',
    ];

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function lot()
    {
        return $this->belongsTo(InventoryLot::class, 'inventory_lot_id');
    }
}
