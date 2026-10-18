<?php

namespace App\Console\Commands;

use App\Models\Entitlement;
use App\Models\Plan;
use App\Models\PlatformService;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPosSubscriptions extends Command
{
    protected $signature = 'platform:import-pos-subscriptions {--dry-run : Solo muestra lo que se importaría}';

    protected $description = 'Importa al maestro administrativo las suscripciones históricas del POS sin duplicarlas.';

    public function handle(): int
    {
        if (! DB::connection('pos')->getSchemaBuilder()->hasTable('company_subscriptions')) {
            $this->error('No existe la tabla company_subscriptions en la conexión POS.');

            return self::FAILURE;
        }

        $legacySubscriptions = DB::connection('pos')->table('company_subscriptions')->orderBy('id')->get();
        $imported = 0;

        foreach ($legacySubscriptions as $legacy) {
            $tenant = Tenant::find($legacy->company_id);
            if (! $tenant) {
                $this->warn("Se omitió la suscripción {$legacy->id}: la compañía {$legacy->company_id} no existe en Administración.");
                continue;
            }

            $serviceSlug = $this->serviceSlug((string) $legacy->plan_type);
            if ($this->option('dry-run')) {
                $this->line("Importaría suscripción {$legacy->id}: {$tenant->name} · {$serviceSlug} · {$legacy->status}/{$legacy->payment_status}");
                $imported++;
                continue;
            }

            $service = PlatformService::firstOrCreate(
                ['slug' => $serviceSlug],
                ['name' => $this->serviceName($serviceSlug), 'is_active' => true]
            );
            $plan = Plan::firstOrCreate(
                ['slug' => "{$serviceSlug}-".($legacy->billing_period ?: 'monthly')],
                [
                    'platform_service_id' => $service->id,
                    'name' => $this->serviceName($serviceSlug).' '.($legacy->billing_period ?: 'monthly'),
                    'billing_period' => $legacy->billing_period ?: 'monthly',
                    'price' => 0,
                    'currency' => 'COP',
                    'is_active' => true,
                ]
            );

            // Restaurante y óptica son módulos del POS operativo: su licencia
            // habilita el acceso al POS, sin exponerlos como tiendas públicas.
            Entitlement::firstOrCreate(
                ['company_id' => $tenant->id, 'service' => 'pos'],
                ['status' => 'enabled']
            );

            Subscription::updateOrCreate(
                ['legacy_pos_subscription_id' => $legacy->id],
                [
                    'company_id' => $tenant->id,
                    'platform_service_id' => $service->id,
                    'plan_id' => $plan->id,
                    'status' => $this->subscriptionStatus((string) $legacy->status),
                    'payment_status' => $this->paymentStatus((string) $legacy->payment_status),
                    'starts_at' => $legacy->start_date,
                    'ends_at' => $legacy->end_date,
                ]
            );

            $imported++;
        }

        $this->info(($this->option('dry-run') ? 'Se importarían ' : 'Se importaron ').$imported.' suscripciones.');

        return self::SUCCESS;
    }

    private function serviceSlug(string $legacyPlan): string
    {
        return match ($legacyPlan) {
            'restaurant' => 'restaurant',
            'optic' => 'optic',
            default => 'pos',
        };
    }

    private function serviceName(string $service): string
    {
        return match ($service) {
            'restaurant' => 'Restaurante',
            'optic' => 'Óptica',
            default => 'POS operativo',
        };
    }

    private function subscriptionStatus(string $status): string
    {
        return match ($status) {
            'pending_payment' => 'suspended',
            default => in_array($status, ['active', 'suspended', 'cancelled', 'expired'], true) ? $status : 'suspended',
        };
    }

    private function paymentStatus(string $status): string
    {
        return match ($status) {
            'partial' => 'pending',
            default => in_array($status, ['paid', 'pending', 'overdue', 'waived'], true) ? $status : 'pending',
        };
    }
}
