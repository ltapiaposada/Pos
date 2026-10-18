<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Maestro de habilitaciones: vive exclusivamente en Administración. */
class AdministrativeEntitlement extends Model
{
    protected $connection = 'administracion';

    protected $table = 'entitlements';

    protected $casts = ['expires_at' => 'datetime'];
}
