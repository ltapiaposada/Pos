<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyThroughBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    use HasFactory;
    use BelongsToCompanyThroughBranch;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_OCCUPIED = 'occupied';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_CLEANING = 'cleaning';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'number',
        'capacity',
        'status',
        'location',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_AVAILABLE => 'Disponible',
            self::STATUS_OCCUPIED => 'Ocupada',
            self::STATUS_RESERVED => 'Reservada',
            self::STATUS_CLEANING => 'En limpieza',
            self::STATUS_INACTIVE => 'Inactiva',
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

    public function orders()
    {
        return $this->hasMany(RestaurantOrder::class);
    }

    public function activeOrder()
    {
        return $this->hasOne(RestaurantOrder::class)
            ->whereIn('status', RestaurantOrder::activeStatuses())
            ->latestOfMany();
    }
}
