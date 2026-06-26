<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class InventorySerial extends Model
{
    use BelongsToCompany;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_SOLD = 'sold';

    protected $fillable = [
        'company_id',
        'branch_id',
        'product_id',
        'purchase_item_id',
        'sale_item_id',
        'serial_number',
        'status',
        'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }
}
