<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class CompanyEntitlement extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'service',
        'status',
        'expires_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'payload' => 'array',
        ];
    }
}
