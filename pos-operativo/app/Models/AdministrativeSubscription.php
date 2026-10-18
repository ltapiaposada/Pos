<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Adaptador de lectura para la suscripción administrada centralmente.
 * Los alias mantienen compatible el contexto de módulos del POS.
 */
class AdministrativeSubscription extends Model
{
    protected $connection = 'administracion';

    protected $table = 'subscriptions';

    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date'];

    public function service()
    {
        return $this->belongsTo(AdministrativePlatformService::class, 'platform_service_id');
    }

    public function plan()
    {
        return $this->belongsTo(AdministrativePlan::class);
    }

    protected function planType(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->service?->slug);
    }

    protected function billingPeriod(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->plan?->billing_period);
    }

    protected function startDate(): Attribute
    {
        return Attribute::get(fn () => $this->starts_at);
    }

    protected function endDate(): Attribute
    {
        return Attribute::get(fn () => $this->ends_at);
    }
}
