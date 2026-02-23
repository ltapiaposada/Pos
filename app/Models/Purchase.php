<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    public const STATUS_POSTED = 'posted';

    protected $fillable = [
        'branch_id',
        'user_id',
        'purchase_number',
        'status',
        'supplier_name',
        'supplier_document',
        'invoice_number',
        'subtotal',
        'tax_total',
        'total',
        'paid_total',
        'balance_total',
        'payment_method',
        'purchased_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'balance_total' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }
}
