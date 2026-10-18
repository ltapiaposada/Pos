<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPayment extends Model
{
    protected $fillable = ['company_id', 'subscription_id', 'amount', 'currency', 'status', 'due_date', 'paid_at', 'method', 'reference', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'due_date' => 'date', 'paid_at' => 'datetime'];
    }

    public function company() { return $this->belongsTo(Tenant::class, 'company_id'); }
    public function subscription() { return $this->belongsTo(Subscription::class); }
}
