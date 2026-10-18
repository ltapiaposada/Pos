<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    public function run(?int $companyId = null): void
    {
        $companyId ??= Company::query()->orderBy('id')->value('id');
        if ($companyId === null) {
            return;
        }

        Tax::query()->updateOrCreate(
            ['company_id' => $companyId, 'name' => 'IVA 16%'],
            [
                'rate' => 16.00,
                'is_active' => true,
            ]
        );

        Tax::query()->updateOrCreate(
            ['company_id' => $companyId, 'name' => 'Exento 0%'],
            [
                'rate' => 0.00,
                'is_active' => true,
            ]
        );
    }
}
