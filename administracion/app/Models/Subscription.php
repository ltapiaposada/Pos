<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = ['legacy_pos_subscription_id', 'company_id', 'platform_service_id', 'plan_id', 'status', 'payment_status', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return ['starts_at' => 'date', 'ends_at' => 'date'];
    }

    public function service()
    {
        return $this->belongsTo(PlatformService::class, 'platform_service_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(ClientPayment::class);
    }
}
