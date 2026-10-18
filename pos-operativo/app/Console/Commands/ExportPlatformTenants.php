<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportPlatformTenants extends Command
{
    protected $signature = 'platform:export-tenants {path : Archivo JSON de salida}';

    protected $description = 'Exporta empresas y suscripciones del POS para importarlas en administración.';

    public function handle(): int
    {
        $companies = Company::withoutGlobalScopes()
            ->with(['companyType:id,name,slug', 'subscriptions'])
            ->orderBy('id')
            ->get()
            ->map(fn (Company $company) => [
                'tenant_uuid' => $company->tenant_uuid,
                'name' => $company->name,
                'identification' => $company->identification,
                'email' => $company->email,
                'phone' => $company->phone,
                'address' => $company->address,
                'status' => $company->status,
                'company_type' => $company->companyType?->slug,
                'subscriptions' => $company->subscriptions->map(fn ($subscription) => [
                    'plan_type' => $subscription->plan_type,
                    'billing_period' => $subscription->billing_period,
                    'status' => $subscription->status,
                    'payment_status' => $subscription->payment_status,
                    'start_date' => $subscription->start_date?->toDateString(),
                    'end_date' => $subscription->end_date?->toDateString(),
                ])->values(),
            ])->values();

        $path = $this->argument('path');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode(['version' => 1, 'tenants' => $companies], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        $this->info("Empresas exportadas: {$companies->count()}");

        return self::SUCCESS;
    }
}
