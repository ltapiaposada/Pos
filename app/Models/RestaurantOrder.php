<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyThroughBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantOrder extends Model
{
    use HasFactory;
    use BelongsToCompanyThroughBranch;

    public const TYPE_DINE_IN = 'dine_in';
    public const TYPE_TAKEAWAY = 'takeaway';
    public const TYPE_DELIVERY = 'delivery';

    public const STATUS_OPEN = 'open';
    public const STATUS_SENT_TO_KITCHEN = 'sent_to_kitchen';
    public const STATUS_IN_PREPARATION = 'in_preparation';
    public const STATUS_READY = 'ready';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_id',
        'branch_id',
        'restaurant_table_id',
        'user_id',
        'customer_id',
        'sale_id',
        'order_number',
        'order_type',
        'status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPEN => 'Abierto',
            self::STATUS_SENT_TO_KITCHEN => 'Enviado a cocina',
            self::STATUS_IN_PREPARATION => 'En preparación',
            self::STATUS_READY => 'Listo',
            self::STATUS_DELIVERED => 'Entregado',
            self::STATUS_CLOSED => 'Cerrado',
            self::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_DINE_IN => 'Mesa',
            self::TYPE_TAKEAWAY => 'Para llevar',
            self::TYPE_DELIVERY => 'Domicilio',
        ];
    }

    public static function activeStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_SENT_TO_KITCHEN,
            self::STATUS_IN_PREPARATION,
            self::STATUS_READY,
            self::STATUS_DELIVERED,
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function items()
    {
        return $this->hasMany(RestaurantOrderItem::class);
    }
}
