<?php

namespace App\Console\Commands;

use App\Models\Entitlement;
use App\Models\Plan;
use App\Models\PlatformService;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportPosTenants extends Command
{
    protected $signature = 'platform:import-pos-tenants {path : Archivo JSON creado por platform:export-tenants}';

    protected $description = 'Importa empresas y suscripciones históricas desde un export del POS.';

    public function handle(): int
    {
        $document = json_decode((string) file_get_contents($this->argument('path')), true, flags: JSON_THROW_ON_ERROR);
        $tenants = collect($document['tenants'] ?? []);
        $services = collect(['pos', 'ecommerce'])->mapWithKeys(fn (string $slug) => [$slug => PlatformService::firstOrCreate(['slug' => $slug], ['name' => strtoupper($slug)])]);

        DB::transaction(function () use ($tenants, $services): void {
            foreach ($tenants as $record) {
                $tenant = Tenant::updateOrCreate(
                    ['tenant_uuid' => $record['tenant_uuid'] ?: (string) Str::uuid()],
                    [
                        'name' => $record['name'],
                        'identification' => $record['identification'] ?? null,
                        'email' => $record['email'] ?? null,
                        'phone' => $record['phone'] ?? null,
                        'address' => $record['address'] ?? null,
                        'status' => $record['status'] ?? 'active',
                    ]
                );

                foreach ($record['subscriptions'] ?? [] as $legacy) {
                    $serviceSlug = $legacy['plan_type'] === 'ecommerce' ? 'ecommerce' : 'pos';
                    $service = $services->get($serviceSlug);
                    $plan = Plan::firstOrCreate(
                        ['slug' => $serviceSlug.'-'.$legacy['billing_period']],
                        [
                            'platform_service_id' => $service->id,
                            'name' => ucfirst($serviceSlug).' '.ucfirst($legacy['billing_period']),
                            'billing_period' => $legacy['billing_period'],
                            'price' => 0,
                            'currency' => 'USD',
                        ]
                    );

                    Subscription::updateOrCreate(
                        [
                            'company_id' => $tenant->id,
                            'platform_service_id' => $service->id,
                            'starts_at' => $legacy['start_date'],
                        ],
                        [
                            'plan_id' => $plan->id,
                            'status' => $legacy['status'],
                            'payment_status' => $legacy['payment_status'],
                            'ends_at' => $legacy['end_date'],
                        ]
                    );

                    Entitlement::updateOrCreate(
                        ['company_id' => $tenant->id, 'service' => $serviceSlug],
                        [
                            'status' => ($legacy['status'] === 'active' && $legacy['payment_status'] === 'paid') ? 'enabled' : 'suspended',
                            'expires_at' => $legacy['end_date'],
                        ]
                    );
                }
            }
        });

        $this->info("Empresas importadas: {$tenants->count()}");
        return self::SUCCESS;
    }
}
