<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvisioningJob extends Model
{
    protected $fillable = ['company_id', 'target', 'action', 'status', 'attempts', 'payload', 'last_error', 'completed_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'completed_at' => 'datetime'];
    }
}
