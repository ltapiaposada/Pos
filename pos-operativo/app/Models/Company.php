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
        'tenant_uuid',
        'name',
        'domain',
        'identification',
        'email',
        'phone',
        'address',
        'company_type_id',
        'status',
        'kickoff_completed',
    ];

    protected $casts = [
        'kickoff_completed' => 'boolean',
    ];

    public function getConnectionName(): ?string
    {
        // Los tests conservan SQLite local. En ejecución real la compañía vive
        // únicamente en Administración.
        return app()->environment('testing') ? config('database.default') : 'administracion';
    }

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
        return app()->environment('testing')
            ? $this->hasMany(CompanySubscription::class)
            : $this->hasMany(AdministrativeSubscription::class, 'company_id');
    }

    public function latestSubscription()
    {
        return app()->environment('testing')
            ? $this->hasOne(CompanySubscription::class)->latestOfMany('id')
            : $this->hasOne(AdministrativeSubscription::class, 'company_id')->latestOfMany('id');
    }

    public function activeSubscription()
    {
        return $this->hasOne(app()->environment('testing') ? CompanySubscription::class : AdministrativeSubscription::class, 'company_id')
            ->where('status', CompanySubscription::STATUS_ACTIVE)
            ->latestOfMany('id');
    }

    public function effectiveSubscription()
    {
        return $this->hasOne(app()->environment('testing') ? CompanySubscription::class : AdministrativeSubscription::class, 'company_id')
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
