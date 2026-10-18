<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutOrder extends Model
{
    protected $table = 'ecommerce_checkout_orders';
    protected $fillable = ['company_id', 'external_order_uuid', 'external_customer_uuid', 'pos_sale_id', 'status', 'total', 'currency', 'request_payload', 'pos_response'];

    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'request_payload' => 'array', 'pos_response' => 'array'];
    }
}
