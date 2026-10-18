<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrativeEntitlement extends Model
{
    protected $connection = 'administracion';

    protected $table = 'entitlements';

    protected $casts = ['expires_at' => 'datetime'];
}
