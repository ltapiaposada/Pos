<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['platform_service_id', 'name', 'slug', 'billing_period', 'price', 'currency', 'is_active'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function service()
    {
        return $this->belongsTo(PlatformService::class, 'platform_service_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
