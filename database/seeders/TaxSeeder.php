<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        Tax::query()->updateOrCreate(
            ['name' => 'IVA 16%'],
            [
                'rate' => 16.00,
                'is_active' => true,
            ]
        );

        Tax::query()->updateOrCreate(
            ['name' => 'Exento 0%'],
            [
                'rate' => 0.00,
                'is_active' => true,
            ]
        );
    }
}
