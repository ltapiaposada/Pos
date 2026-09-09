<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Company;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(?int $companyId = null): void
    {
        $companyId ??= Company::query()->orderBy('id')->value('id');
        if ($companyId === null) {
            return;
        }

        $current = Setting::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('key', 'business')
            ->first()?->value ?? [];
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
            ['company_id' => $companyId, 'key' => 'business'],
            ['value' => array_merge($default, is_array($current) ? $current : [])]
        );
    }
}
