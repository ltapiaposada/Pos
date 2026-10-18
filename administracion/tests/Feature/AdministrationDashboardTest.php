<?php

namespace Tests\Feature;

use App\Models\Entitlement;
use App\Models\Plan;
use App\Models\PlatformService;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInPlatformAdministrator();
    }

    public function test_it_displays_and_creates_companies_from_the_administration_panel(): void
    {
        $this->get('/')->assertOk()->assertSee('Administración');

        $response = $this->post('/companies', [
            'name' => 'Empresa Visual',
            'domain' => 'visual.test',
            'email' => 'admin@visual.test',
            'services' => ['pos', 'ecommerce'],
        ]);

        $company = Tenant::where('name', 'Empresa Visual')->firstOrFail();
        $response->assertRedirect(route('companies.activation', $company));
        $this->assertDatabaseHas('companies', ['id' => $company->id, 'domain' => 'visual.test']);
        $this->assertDatabaseCount('entitlements', 2);
        $this->get(route('companies.edit', $company))->assertOk()->assertSee('Datos generales');
        $this->get(route('companies.activation', $company))->assertOk()->assertSee('Activar servicios');
        $this->get(route('companies.applications', $company))->assertOk()->assertSee('Aplicaciones habilitadas');
        $this->get(route('companies.subscriptions', $company))->assertOk()->assertSee('Suscripciones vigentes e históricas');
        $this->get(route('companies.payments', $company))->assertOk()->assertSee('Pagos de cliente');
        $this->get(route('companies.provisioning', $company))->assertOk()->assertSee('Aprovisionamiento');
        $this->assertTrue(Entitlement::where('company_id', $company->id)->where('service', 'pos')->where('status', 'enabled')->exists());
    }

    public function test_it_edits_and_deletes_unused_catalog_items(): void
    {
        $service = PlatformService::create(['name' => 'Servicio temporal', 'slug' => 'temporal', 'is_active' => true]);
        $plan = Plan::create([
            'platform_service_id' => $service->id,
            'name' => 'Plan temporal',
            'slug' => 'temporal-mensual',
            'billing_period' => 'monthly',
            'price' => 25000,
            'currency' => 'COP',
            'is_active' => true,
        ]);

        $this->get(route('services.edit', $service))->assertOk()->assertSee('Editar servicio');
        $this->put(route('services.update', $service), ['name' => 'Servicio actualizado', 'slug' => 'temporal', 'is_active' => 1])->assertRedirect(route('catalog.index'));
        $this->get(route('plans.edit', $plan))->assertOk()->assertSee('Editar plan');
        $this->put(route('plans.update', $plan), ['platform_service_id' => $service->id, 'name' => 'Plan actualizado', 'slug' => 'temporal-mensual', 'billing_period' => 'annual', 'price' => 30000, 'currency' => 'cop', 'is_active' => 1])->assertRedirect(route('catalog.index'));
        $this->delete(route('plans.destroy', $plan))->assertSessionHas('success');
        $this->delete(route('services.destroy', $service))->assertSessionHas('success');

        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
        $this->assertDatabaseMissing('platform_services', ['id' => $service->id]);
    }

    public function test_validation_messages_are_shown_in_spanish(): void
    {
        $this->from(route('companies.create'))
            ->post(route('companies.store'), [])
            ->assertRedirect(route('companies.create'))
            ->assertSessionHasErrors([
                'name' => 'El campo nombre es obligatorio.',
            ]);
    }
}
