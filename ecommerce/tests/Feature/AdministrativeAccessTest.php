<?php

namespace Tests\Feature;

use App\Models\AdministrativeEntitlement;
use App\Models\AdministrativeSubscription;
use App\Models\AdministrativeTenant;
use App\Services\AdministrativeAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrativeAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdministrativeSchema();
    }

    public function test_storefront_requires_paid_current_pos_and_ecommerce_services(): void
    {
        $tenant = AdministrativeTenant::create([
            'tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1d',
            'name' => 'Empresa con tienda',
            'status' => 'active',
        ]);
        $this->grantStorefrontAccess($tenant);

        $access = app(AdministrativeAccessService::class);
        $this->assertTrue($access->canUseStorefront($tenant));

        AdministrativeSubscription::query()
            ->where('company_id', $tenant->id)
            ->whereHas('service', fn ($query) => $query->where('slug', 'ecommerce'))
            ->update(['payment_status' => 'pending']);
        $this->assertFalse($access->canUseStorefront($tenant));

        AdministrativeSubscription::query()
            ->where('company_id', $tenant->id)
            ->whereHas('service', fn ($query) => $query->where('slug', 'ecommerce'))
            ->update(['payment_status' => 'paid']);
        AdministrativeEntitlement::query()
            ->where('company_id', $tenant->id)
            ->where('service', 'pos')
            ->update(['status' => 'suspended']);
        $this->assertFalse($access->canUseStorefront($tenant));
    }
}
