<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'method',
        'amount',
        'reference',
        'paid_at',
        'voided_at',
        'voided_by_user_id',
        'void_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by_user_id');
    }
}
