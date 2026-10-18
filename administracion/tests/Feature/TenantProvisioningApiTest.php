<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TenantProvisioningApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_tenant_but_keeps_provisioning_pending_until_payment_is_registered(): void
    {
        config()->set('integration.internal_token', 'test-token');
        config()->set('integration.pos_url', 'https://pos.test');
        config()->set('integration.ecommerce_url', 'https://tienda.test');
        Http::fake([
            'https://pos.test/*' => Http::response(['tenant_uuid' => 'ok']),
            'https://tienda.test/*' => Http::response(['tenant_uuid' => 'ok']),
        ]);

        $response = $this->withHeader('X-Internal-Token', 'test-token')->postJson('/api/v1/tenants', [
            'name' => 'Empresa Demo',
            'primary_admin' => ['name' => 'Ana', 'email' => 'ana@example.test'],
            'services' => ['pos', 'ecommerce'],
            'domain' => 'demo.test',
        ]);

        $response->assertCreated()
            ->assertJsonPath('provisioning_jobs.0.status', 'pending')
            ->assertJsonPath('provisioning_jobs.1.status', 'pending');
        $this->assertDatabaseHas('companies', ['name' => 'Empresa Demo']);
        $this->assertDatabaseCount('entitlements', 2);
        $this->assertDatabaseCount('provisioning_jobs', 2);
        Http::assertNothingSent();
    }
}
