<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrativePlan extends Model
{
    protected $connection = 'administracion';

    protected $table = 'plans';
}
