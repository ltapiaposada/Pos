<?php

namespace App\Http\Controllers;

use App\Models\Entitlement;
use App\Models\Plan;
use App\Models\PlatformService;
use App\Models\ProvisioningJob;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\ProvisioningClient;
use App\Support\SpanishLabel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $this->ensureBaseCatalog();

        return view('dashboard', [
            'companiesCount' => Tenant::count(),
            'activeCompanies' => Tenant::where('status', 'active')->count(),
            'enabledServices' => Entitlement::query()->where('status', 'enabled')->count(),
            'activeSubscriptions' => Subscription::query()->where('status', 'active')->count(),
        ]);
    }

    public function companies(): View
    {
        $this->ensureBaseCatalog();

        return view('companies.index', [
            'companies' => Tenant::query()->with(['entitlements', 'subscriptions'])->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->ensureBaseCatalog();

        return view('companies.create', ['services' => PlatformService::query()->where('is_active', true)->orderBy('name')->get()]);
    }

    public function show(Tenant $company): RedirectResponse
    {
        return to_route('companies.activation', $company);
    }

    public function activation(Tenant $company): View
    {
        $this->ensureBaseCatalog();
        $company->load(['entitlements', 'subscriptions.service', 'subscriptions.plan', 'payments', 'provisioningJobs']);
        $services = PlatformService::query()->whereIn('slug', ['pos', 'ecommerce'])->orderBy('name')->get();

        return view('companies.activation', [
            'company' => $company,
            'services' => $services,
            'steps' => $services->mapWithKeys(fn (PlatformService $service) => [$service->slug => $this->activationState($company, $service->slug)]),
        ]);
    }

    public function edit(Tenant $company): View
    {
        return view('companies.edit', ['company' => $company]);
    }

    public function applications(Tenant $company): View
    {
        $this->ensureBaseCatalog();
        $company->load(['entitlements', 'provisioningJobs' => fn ($query) => $query->latest()->limit(20)]);

        return view('companies.applications', [
            'company' => $company,
            'services' => PlatformService::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function subscriptions(Tenant $company): View
    {
        $this->ensureBaseCatalog();
        $company->load(['subscriptions.service', 'subscriptions.plan']);

        return view('companies.subscriptions', [
            'company' => $company,
            'services' => PlatformService::query()->where('is_active', true)->orderBy('name')->get(),
            'plans' => Plan::query()->where('is_active', true)->with('service')->orderBy('name')->get(),
        ]);
    }

    public function provisioning(Tenant $company): View
    {
        $this->ensureBaseCatalog();
        $company->load(['entitlements', 'subscriptions.service', 'subscriptions.plan', 'provisioningJobs' => fn ($query) => $query->latest()->limit(30)]);

        return view('companies.provisioning', [
            'company' => $company,
            'services' => PlatformService::query()->whereIn('slug', ['pos', 'ecommerce'])->orderBy('name')->get(),
        ]);
    }

    public function catalog(): View
    {
        $this->ensureBaseCatalog();

        return view('catalog.index', [
            'services' => PlatformService::query()->withCount('plans')->orderBy('name')->get(),
            'plans' => Plan::query()->with('service')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->companyData($request);
        $company = DB::transaction(function () use ($data): Tenant {
            $company = Tenant::create([...collect($data)->except('services')->all(), 'tenant_uuid' => (string) Str::uuid(), 'status' => 'active']);
            $this->saveEntitlements($company, $data['services'] ?? ['pos']);

            return $company;
        });

        return to_route('companies.activation', $company)->with('success', 'Compañía creada. Completa las etapas de activación para poner sus aplicaciones en funcionamiento.');
    }

    public function update(Request $request, Tenant $company): RedirectResponse
    {
        $data = $this->companyData($request, $company);
        $company->update(collect($data)->except('services')->all());
        if ($request->has('services')) {
            $this->saveEntitlements($company, $data['services'] ?? []);
        }

        return back()->with('success', 'Datos de la compañía actualizados.');
    }

    public function updateEntitlements(Request $request, Tenant $company): RedirectResponse
    {
        $data = $request->validate(['services' => ['nullable', 'array'], 'services.*' => [Rule::in(PlatformService::query()->pluck('slug')->all())]]);
        $this->saveEntitlements($company, $data['services'] ?? []);

        return back()->with('success', 'Habilitaciones actualizadas.');
    }

    public function storeSubscription(Request $request, Tenant $company): RedirectResponse
    {
        $data = $request->validate([
            'platform_service_id' => ['required', 'exists:platform_services,id'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(['active', 'suspended', 'cancelled', 'expired'])],
        ]);
        if ($data['plan_id'] && Plan::findOrFail($data['plan_id'])->platform_service_id !== (int) $data['platform_service_id']) {
            return back()->withErrors(['plan_id' => 'El plan seleccionado no corresponde al servicio.']);
        }
        // Una suscripción nueva no puede habilitar una aplicación por sí sola.
        // El estado pasa a pagado exclusivamente al registrar un cobro válido.
        Subscription::create([...$data, 'company_id' => $company->id, 'payment_status' => 'pending']);

        return back()->with('success', 'Suscripción registrada.');
    }

    public function updateSubscription(Request $request, Tenant $company, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->company_id === $company->id, 404);
        $subscription->update($request->validate([
            'status' => ['required', Rule::in(['active', 'suspended', 'cancelled', 'expired'])],
            'ends_at' => ['nullable', 'date'],
        ]));

        return back()->with('success', 'Suscripción actualizada.');
    }

    public function destroySubscription(Tenant $company, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->company_id === $company->id, 404);
        $subscription->delete();

        return back()->with('success', 'Suscripción eliminada.');
    }

    public function storeService(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'alpha_dash', 'max:50', 'unique:platform_services,slug']]);
        PlatformService::create([...$data, 'is_active' => true]);

        return to_route('dashboard')->with('success', 'Servicio de plataforma creado.');
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'platform_service_id' => ['required', 'exists:platform_services,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:80', 'unique:plans,slug'],
            'billing_period' => ['required', Rule::in(['monthly', 'quarterly', 'annual', 'one_time'])],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
        ]);
        Plan::create([...$data, 'currency' => strtoupper($data['currency']), 'is_active' => true]);

        return to_route('dashboard')->with('success', 'Plan creado.');
    }

    public function editService(PlatformService $service): View
    {
        return view('catalog.edit-service', compact('service'));
    }

    public function updateService(Request $request, PlatformService $service): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:50', Rule::unique('platform_services', 'slug')->ignore($service->id)],
            'is_active' => ['required', 'boolean'],
        ]);

        if (in_array($service->slug, ['pos', 'ecommerce'], true) && $data['slug'] !== $service->slug) {
            return back()->withErrors(['slug' => 'No puedes cambiar el identificador de un servicio base.']);
        }
        if ($data['slug'] !== $service->slug && ($service->subscriptions()->exists() || Entitlement::where('service', $service->slug)->exists())) {
            return back()->withErrors(['slug' => 'No puedes cambiar el identificador de un servicio que ya está asignado a empresas o suscripciones.']);
        }

        $service->update($data);

        return to_route('catalog.index')->with('success', 'Servicio actualizado.');
    }

    public function editPlan(Plan $plan): View
    {
        return view('catalog.edit-plan', [
            'plan' => $plan,
            'services' => PlatformService::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function updatePlan(Request $request, Plan $plan): RedirectResponse
    {
        $data = $request->validate([
            'platform_service_id' => ['required', 'exists:platform_services,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:80', Rule::unique('plans', 'slug')->ignore($plan->id)],
            'billing_period' => ['required', Rule::in(['monthly', 'quarterly', 'annual', 'one_time'])],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($plan->subscriptions()->exists() && (int) $data['platform_service_id'] !== (int) $plan->platform_service_id) {
            return back()->withErrors(['platform_service_id' => 'No puedes cambiar de servicio un plan que ya tiene suscripciones.']);
        }

        $plan->update([...$data, 'currency' => strtoupper($data['currency'])]);

        return to_route('catalog.index')->with('success', 'Plan actualizado.');
    }

    public function destroyPlan(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'No puedes eliminar este plan porque tiene suscripciones registradas. Conserva el historial o crea un plan nuevo.');
        }

        $plan->delete();

        return back()->with('success', 'Plan eliminado.');
    }

    public function destroyService(PlatformService $service): RedirectResponse
    {
        if (in_array($service->slug, ['pos', 'ecommerce'], true)) {
            return back()->with('error', 'POS y E-commerce son servicios base y no se pueden eliminar.');
        }

        if ($service->subscriptions()->exists() || $service->plans()->exists()) {
            return back()->with('error', 'No puedes eliminar este servicio porque tiene planes o suscripciones asociadas. Elimina primero los planes sin uso.');
        }

        DB::transaction(function () use ($service): void {
            Entitlement::where('service', $service->slug)->delete();
            $service->delete();
        });

        return back()->with('success', 'Servicio eliminado.');
    }

    public function provision(Tenant $company, string $service, ProvisioningClient $provisioning): RedirectResponse
    {
        abort_unless(in_array($service, ['pos', 'ecommerce'], true), 404);
        abort_unless($this->canProvisionService($company, $service), 422, 'El servicio debe estar habilitado y tener una suscripción activa y pagada antes de aprovisionar.');
        $job = ProvisioningJob::create(['company_id' => $company->id, 'target' => $service, 'action' => 'provision', 'payload' => $this->provisioningPayload($company, $service)]);
        $this->runProvisioning($job, $provisioning);

        return to_route('companies.provisioning', $company)->with('success', sprintf(
            'Aprovisionamiento de %s: %s.',
            SpanishLabel::service($service),
            SpanishLabel::provisioningStatus($job->fresh()->status),
        ));
    }

    public function retryProvisioning(Tenant $company, ProvisioningJob $job, ProvisioningClient $provisioning): RedirectResponse
    {
        abort_unless($job->company_id === $company->id, 404);
        abort_unless($this->canProvisionService($company, $job->target), 422, 'El servicio debe estar habilitado y tener una suscripción activa y pagada antes de aprovisionar.');
        $this->runProvisioning($job, $provisioning);

        return back()->with('success', sprintf(
            'Reintento de %s: %s.',
            SpanishLabel::service($job->target),
            SpanishLabel::provisioningStatus($job->fresh()->status),
        ));
    }

    private function companyData(Request $request, ?Tenant $company = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('companies', 'domain')->ignore($company?->id)],
            'identification' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'blocked'])],
            'services' => ['nullable', 'array'],
            'services.*' => [Rule::in(PlatformService::query()->pluck('slug')->all())],
        ]);
    }

    private function saveEntitlements(Tenant $company, array $enabledServices): void
    {
        foreach (PlatformService::query()->pluck('slug') as $slug) {
            Entitlement::updateOrCreate(['company_id' => $company->id, 'service' => $slug], ['status' => in_array($slug, $enabledServices, true) ? 'enabled' : 'suspended']);
        }
    }

    private function provisioningPayload(Tenant $company, string $service): array
    {
        return array_filter(['tenant_uuid' => $company->tenant_uuid, 'name' => $company->name, 'email' => $company->email, 'phone' => $company->phone, 'address' => $company->address, 'domain' => $service === 'ecommerce' ? $company->domain : null], fn ($value) => $value !== null);
    }

    private function runProvisioning(ProvisioningJob $job, ProvisioningClient $provisioning): void
    {
        $job->increment('attempts');
        $job->update(['status' => 'running', 'last_error' => null]);
        try {
            $provisioning->execute($job);
            $job->update(['status' => 'completed', 'completed_at' => now()]);
        } catch (\Throwable $exception) {
            report($exception);
            $job->update(['status' => 'failed', 'last_error' => Str::limit($exception->getMessage(), 1000)]);
        }
    }

    private function activationState(Tenant $company, string $service): array
    {
        $enabled = $company->entitlements->where('service', $service)->first()?->status === 'enabled';
        $paid = $this->serviceIsPaidAndActive($company, $service);
        $job = $company->provisioningJobs->where('target', $service)->sortByDesc('id')->first();

        return [
            'enabled' => $enabled,
            'paid' => $paid,
            'provisioned' => $job?->status === 'completed',
            'job_status' => $job?->status,
        ];
    }

    private function serviceIsPaidAndActive(Tenant $company, string $service): bool
    {
        $activePaidFor = function (array $serviceSlugs) use ($company): bool {
            return $company->subscriptions()
                ->where('status', 'active')
                ->where('payment_status', 'paid')
                ->whereDate('starts_at', '<=', now()->toDateString())
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', now()->toDateString()))
                ->whereHas('service', fn ($query) => $query->whereIn('slug', $serviceSlugs))
                ->exists();
        };

        if ($service === 'ecommerce') {
            return $activePaidFor(['ecommerce']) && $activePaidFor(['pos', 'restaurant', 'optic']);
        }

        return $activePaidFor(['pos', 'restaurant', 'optic']);
    }

    private function canProvisionService(Tenant $company, string $service): bool
    {
        return $company->entitlements()
            ->where('service', $service)
            ->where('status', 'enabled')
            ->exists()
            && $this->serviceIsPaidAndActive($company, $service);
    }

    private function ensureBaseCatalog(): void
    {
        foreach (['pos' => 'POS operativo', 'ecommerce' => 'E-commerce'] as $slug => $name) {
            $service = PlatformService::firstOrCreate(['slug' => $slug], ['name' => $name, 'is_active' => true]);
            Plan::firstOrCreate(['slug' => "{$slug}-mensual"], ['platform_service_id' => $service->id, 'name' => ucfirst($slug).' mensual', 'billing_period' => 'monthly', 'price' => 0, 'currency' => 'COP', 'is_active' => true]);
        }
    }
}
