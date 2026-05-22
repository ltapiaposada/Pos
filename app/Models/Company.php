<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'name',
        'domain',
        'identification',
        'email',
        'phone',
        'address',
        'company_type_id',
        'status',
    ];

    public function companyType()
    {
        return $this->belongsTo(CompanyType::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(CompanySubscription::class);
    }

    public function latestSubscription()
    {
        return $this->hasOne(CompanySubscription::class)->latestOfMany('id');
    }

    public function activeSubscription()
    {
        return $this->hasOne(CompanySubscription::class)
            ->where('status', CompanySubscription::STATUS_ACTIVE)
            ->latestOfMany('id');
    }

    public function effectiveSubscription()
    {
        return $this->hasOne(CompanySubscription::class)
            ->where('status', CompanySubscription::STATUS_ACTIVE)
            ->latestOfMany('id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activa',
            self::STATUS_INACTIVE => 'Inactiva',
            self::STATUS_BLOCKED => 'Bloqueada',
        ];
    }
}
