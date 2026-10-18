<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformService extends Model
{
    protected $table = 'platform_services';

    protected $fillable = ['slug', 'name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'platform_service_id');
    }
}
