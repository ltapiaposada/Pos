<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Storefront extends Model
{
    protected $table = 'ecommerce_storefronts';
    protected $fillable = ['company_id', 'domain', 'status', 'flat_shipping', 'coupons'];

    protected function casts(): array
    {
        return ['coupons' => 'array', 'flat_shipping' => 'decimal:2'];
    }

}
