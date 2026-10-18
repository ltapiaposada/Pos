<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entitlement;
use App\Models\ProvisioningJob;
use App\Models\Tenant;
use App\Services\ProvisioningClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function store(Request $request, ProvisioningClient $provisioning): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'identification' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'primary_admin' => ['nullable', 'array'],
            'primary_admin.name' => ['nullable', 'string', 'max:255'],
            'primary_admin.email' => ['nullable', 'email', 'max:255'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['required', 'in:pos,ecommerce'],
            'domain' => ['nullable', 'string', 'max:255'],
        ]);

        if (in_array('ecommerce', $data['services'], true) && ! in_array('pos', $data['services'], true)) {
            abort(422, 'E-commerce requiere que el servicio POS este habilitado.');
        }

        [$tenant, $jobs] = DB::transaction(function () use ($data): array {
            $tenant = Tenant::create([
                'tenant_uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'identification' => $data['identification'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'primary_admin_name' => $data['primary_admin']['name'] ?? null,
                'primary_admin_email' => $data['primary_admin']['email'] ?? null,
            ]);

            $jobs = collect($data['services'])->unique()->map(function (string $service) use ($tenant, $data): ProvisioningJob {
                Entitlement::create([
                    'company_id' => $tenant->id,
                    'service' => $service,
                    'status' => 'enabled',
                ]);

                $payload = [
                    'tenant_uuid' => $tenant->tenant_uuid,
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                    'address' => $tenant->address,
                    'admin' => $data['primary_admin'] ?? [],
                ];

                if ($service === 'ecommerce') {
                    $payload['domain'] = $data['domain'] ?? null;
                }

                return ProvisioningJob::create([
                    'company_id' => $tenant->id,
                    'target' => $service,
                    'action' => 'provision',
                    'status' => 'pending',
                    'payload' => $payload,
                ]);
            });

            return [$tenant, $jobs];
        });

        // Crear una empresa no concede acceso operativo. Los trabajos quedan
        // pendientes hasta que Administración registre una suscripción pagada
        // y habilite el aprovisionamiento desde su flujo controlado.
        // Esto evita crear recursos de POS o e-commerce para clientes impagos.

        return response()->json([
            'tenant_uuid' => $tenant->tenant_uuid,
            'provisioning_jobs' => $jobs->map(fn (ProvisioningJob $job) => [
                'id' => $job->id,
                'target' => $job->target,
                'status' => $job->status,
                'last_error' => $job->last_error,
            ])->values(),
        ], 201);
    }

    public function retryProvisioning(string $tenantUuid, int $jobId, ProvisioningClient $provisioning): JsonResponse
    {
        $tenant = Tenant::where('tenant_uuid', $tenantUuid)->firstOrFail();
        $job = ProvisioningJob::where('company_id', $tenant->id)->findOrFail($jobId);
        abort_unless($this->canProvisionService($tenant, $job->target), 422, 'El servicio debe estar habilitado y tener una suscripción activa y pagada antes de aprovisionar.');

        $job = $this->runProvisioning($job, $provisioning);

        return response()->json([
            'id' => $job->id,
            'target' => $job->target,
            'status' => $job->status,
            'last_error' => $job->last_error,
        ]);
    }

    public function updateEntitlements(Request $request, string $tenantUuid): JsonResponse
    {
        $data = $request->validate([
            'entitlements' => ['required', 'array', 'min:1'],
            'entitlements.*.service' => ['required', 'in:pos,ecommerce'],
            'entitlements.*.status' => ['required', 'in:enabled,suspended,expired'],
            'entitlements.*.expires_at' => ['nullable', 'date'],
            'entitlements.*.payload' => ['nullable', 'array'],
        ]);
        $tenant = Tenant::where('tenant_uuid', $tenantUuid)->firstOrFail();

        foreach ($data['entitlements'] as $item) {
            Entitlement::updateOrCreate(
                ['company_id' => $tenant->id, 'service' => $item['service']],
                ['status' => $item['status'], 'expires_at' => $item['expires_at'] ?? null, 'payload' => $item['payload'] ?? null]
            );
        }

        return response()->json(['tenant_uuid' => $tenant->tenant_uuid, 'updated' => count($data['entitlements'])]);
    }

    private function runProvisioning(ProvisioningJob $job, ProvisioningClient $provisioning): ProvisioningJob
    {
        $job->increment('attempts');
        $job->update(['status' => 'running', 'last_error' => null]);

        try {
            $provisioning->execute($job);
            $job->update(['status' => 'completed', 'completed_at' => now()]);
        } catch (\Throwable $exception) {
            report($exception);
            $job->update(['status' => 'failed', 'last_error' => $exception->getMessage()]);
        }

        return $job->fresh();
    }

    private function canProvisionService(Tenant $tenant, string $service): bool
    {
        if (! in_array($service, ['pos', 'ecommerce'], true)
            || ! $tenant->entitlements()->where('service', $service)->where('status', 'enabled')->exists()) {
            return false;
        }

        $isPaidAndActive = function (array $slugs) use ($tenant): bool {
            return $tenant->subscriptions()
                ->where('status', 'active')
                ->where('payment_status', 'paid')
                ->whereDate('starts_at', '<=', now()->toDateString())
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', now()->toDateString()))
                ->whereHas('service', fn ($query) => $query->whereIn('slug', $slugs))
                ->exists();
        };

        return $service === 'ecommerce'
            ? $isPaidAndActive(['ecommerce']) && $isPaidAndActive(['pos', 'restaurant', 'optic'])
            : $isPaidAndActive(['pos', 'restaurant', 'optic']);
    }
}
