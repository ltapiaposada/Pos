<?php

namespace Tests\Feature;

use App\Models\AdministrativeTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalTenantApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAdministrativeSchema();
    }

    public function test_it_provisions_a_storefront_without_accessing_pos_data(): void
    {
        config()->set('integration.internal_token', 'test-token');
        $tenant = AdministrativeTenant::create([
            'tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1b',
            'name' => 'Empresa Demo',
        ]);
        $this->grantStorefrontAccess($tenant);

        $response = $this->withHeader('X-Internal-Token', 'test-token')->postJson('/api/internal/v1/tenants/provision', [
            'tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1b',
            'name' => 'Empresa Demo',
            'domain' => 'demo.test',
        ]);

        $response->assertCreated()->assertJsonPath('tenant_uuid', '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1b');
        $this->assertDatabaseHas('companies', ['name' => 'Empresa Demo'], 'administracion');
        $this->assertDatabaseHas('ecommerce_storefronts', ['company_id' => 1, 'domain' => 'demo.test']);
    }
}
