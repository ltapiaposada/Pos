<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    public const STATUS_PAID = 'paid';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const SOURCE_POS = 'pos';
    public const SOURCE_ECOMMERCE = 'ecommerce';

    protected $fillable = [
        'branch_id',
        'user_id',
        'customer_id',
        'cash_register_session_id',
        'sale_number',
        'status',
        'order_source',
        'subtotal',
        'discount_total',
        'tax_total',
        'shipping_total',
        'coupon_discount_total',
        'coupon_code',
        'delivery_address',
        'customer_note',
        'total',
        'paid_total',
        'change_total',
        'currency',
        'sold_at',
        'invoiced_at',
        'invoiced_by_user_id',
        'accounted_at',
        'accounted_by_user_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'coupon_discount_total' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'change_total' => 'decimal:2',
        'sold_at' => 'datetime',
        'invoiced_at' => 'datetime',
        'accounted_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoicedBy()
    {
        return $this->belongsTo(User::class, 'invoiced_by_user_id');
    }

    public function accountedBy()
    {
        return $this->belongsTo(User::class, 'accounted_by_user_id');
    }
}
