<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $current = Setting::query()->where('key', 'business')->value('value') ?? [];
        $default = [
            'name' => 'Mi Tienda POS',
            'nit' => 'NIT-000000',
            'address' => 'Calle Principal 123',
            'phone' => '555-0101',
            'currency' => 'USD',
            'ecommerce_flat_shipping' => (float) config('pos.ecommerce_flat_shipping', 0),
            'ecommerce_coupons' => (array) config('pos.ecommerce_coupons', []),
            'allow_negative_stock' => false,
            'default_tax_id' => 1,
        ];

        Setting::query()->updateOrCreate(
            ['key' => 'business'],
            ['value' => array_merge($default, is_array($current) ? $current : [])]
        );
    }
}
