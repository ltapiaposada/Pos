<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboundEvent extends Model
{
    protected $table = 'ecommerce_inbound_events';
    protected $fillable = ['event_uuid', 'event_type', 'payload', 'processed_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'processed_at' => 'datetime'];
    }
}
