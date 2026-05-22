<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantOrderItem extends Model
{
    use HasFactory;
    use BelongsToCompany;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PREPARATION = 'in_preparation';
    public const STATUS_READY = 'ready';
    public const STATUS_DELIVERED = 'delivered';

    protected $fillable = [
        'company_id',
        'restaurant_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
        'kitchen_status',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public static function kitchenStatusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_IN_PREPARATION => 'En preparación',
            self::STATUS_READY => 'Listo',
            self::STATUS_DELIVERED => 'Entregado',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function order()
    {
        return $this->belongsTo(RestaurantOrder::class, 'restaurant_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function selections()
    {
        return $this->hasMany(RestaurantOrderItemSelection::class)->orderBy('id');
    }
}
