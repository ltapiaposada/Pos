<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanySubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalTenantApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_requires_an_internal_token_to_provision_a_tenant(): void
    {
        config()->set('integration.internal_token', 'test-token');

        $this->postJson('/api/v1/tenants/provision', [
            'tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1c',
            'name' => 'Empresa API',
        ])->assertUnauthorized();
    }

    public function test_it_provisions_an_isolated_pos_company_for_a_platform_tenant(): void
    {
        config()->set('integration.internal_token', 'test-token');
        $company = Company::create([
            'tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1c',
            'name' => 'Empresa API',
            'status' => Company::STATUS_ACTIVE,
        ]);
        CompanySubscription::create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => CompanySubscription::PERIOD_MONTHLY,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
        ]);

        $response = $this->withHeader('X-Internal-Token', 'test-token')->postJson('/api/v1/tenants/provision', [
            'tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1c',
            'name' => 'Empresa API',
            'email' => 'empresa@example.test',
        ]);

        $response->assertCreated()->assertJsonPath('tenant_uuid', '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1c');
        $this->assertDatabaseHas('companies', ['tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1c']);
        $this->assertDatabaseCount('branches', 1);
    }

    public function test_it_does_not_provision_a_tenant_without_a_paid_subscription(): void
    {
        config()->set('integration.internal_token', 'test-token');

        $this->withHeader('X-Internal-Token', 'test-token')->postJson('/api/v1/tenants/provision', [
            'tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1e',
            'name' => 'Empresa impaga',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('branches', 0);
    }
}
