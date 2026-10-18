<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entitlement extends Model
{
    protected $fillable = ['company_id', 'service', 'status', 'expires_at', 'payload'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'payload' => 'array'];
    }
}
