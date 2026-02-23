<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'product_id',
        'user_id',
        'type',
        'quantity',
        'cost_price',
        'ref_type',
        'ref_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'cost_price' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
