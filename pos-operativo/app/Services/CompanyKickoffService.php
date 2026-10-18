<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use Database\Seeders\AccountingSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TaxSeeder;
use Illuminate\Support\Facades\DB;

class CompanyKickoffService
{
    public function runIfPending(Company $company): bool
    {
        return DB::transaction(function () use ($company): bool {
            $lockedCompany = Company::query()->lockForUpdate()->findOrFail($company->id);

            if ($lockedCompany->kickoff_completed) {
                return false;
            }

            $this->createCompanyData($lockedCompany);
            $lockedCompany->forceFill(['kickoff_completed' => true])->save();
            $company->setAttribute('kickoff_completed', true);

            return true;
        });
    }

    private function createCompanyData(Company $company): void
    {
        if (! Branch::withoutGlobalScopes()->where('company_id', $company->id)->exists()) {
            Branch::withoutGlobalScopes()->create([
                'company_id' => $company->id,
                'code' => 'PRN-'.$company->id,
                'name' => 'Sucursal Principal',
                'address' => $company->address,
                'phone' => $company->phone,
            ]);
        }

        foreach ([
            ['name' => 'Bebidas', 'description' => 'Bebidas y refrescos'],
            ['name' => 'Snacks', 'description' => 'Snacks y botanas'],
        ] as $category) {
            Category::withoutGlobalScopes()->updateOrCreate(
                ['company_id' => $company->id, 'name' => $category['name']],
                ['description' => $category['description']]
            );
        }

        $customer = Customer::withoutGlobalScopes()->withTrashed()->firstOrNew([
            'company_id' => $company->id,
            'document' => '222222222222',
        ]);
        $customer->fill([
            'name' => 'Consumidor Final',
            'email' => null,
            'phone' => null,
            'address' => null,
            'contact_type' => Customer::TYPE_PERSON,
            'is_active' => true,
        ]);
        $customer->deleted_at = null;
        $customer->save();

        (new TaxSeeder)->run($company->id);
        (new SettingsSeeder)->run($company->id);
        (new AccountingSeeder)->run($company->id);
    }
}
