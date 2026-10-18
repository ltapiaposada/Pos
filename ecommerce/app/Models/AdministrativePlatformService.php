<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrativePlatformService extends Model
{
    protected $connection = 'administracion';

    protected $table = 'platform_services';
}
