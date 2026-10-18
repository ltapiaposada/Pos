<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrativeTenant extends Model
{
    protected $connection = 'administracion';

    protected $table = 'companies';

    protected $fillable = ['tenant_uuid', 'name', 'status'];
}
