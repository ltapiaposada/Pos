<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrativeSubscription extends Model
{
    protected $connection = 'administracion';

    protected $table = 'subscriptions';

    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date'];

    public function service()
    {
        return $this->belongsTo(AdministrativePlatformService::class, 'platform_service_id');
    }
}
