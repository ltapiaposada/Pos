<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlatformService;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInPlatformAdministrator();
    }

    public function test_a_paid_client_payment_enables_its_subscription_payment_status(): void
    {
        $company = Tenant::create([
            'tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1c',
            'name' => 'Empresa de pagos',
            'status' => 'active',
        ]);
        $service = PlatformService::create(['slug' => 'pos', 'name' => 'POS operativo', 'is_active' => true]);
        $plan = Plan::create([
            'platform_service_id' => $service->id,
            'name' => 'POS mensual',
            'slug' => 'pos-mensual',
            'billing_period' => 'monthly',
            'price' => 100000,
            'currency' => 'COP',
            'is_active' => true,
        ]);
        $subscription = Subscription::create([
            'company_id' => $company->id,
            'platform_service_id' => $service->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'payment_status' => 'pending',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
        ]);

        $this->post(route('companies.payments.store', $company), [
            'subscription_id' => $subscription->id,
            'amount' => 100000,
            'currency' => 'COP',
            'status' => 'paid',
            'method' => 'transfer',
            'reference' => 'TRX-100',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('client_payments', [
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'status' => 'paid',
            'reference' => 'TRX-100',
        ]);
        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'payment_status' => 'paid']);
    }

    public function test_voiding_the_only_paid_payment_revokes_the_payment_status(): void
    {
        $company = Tenant::create([
            'tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1e',
            'name' => 'Empresa con pago anulado',
            'status' => 'active',
        ]);
        $service = PlatformService::create(['slug' => 'pos', 'name' => 'POS operativo', 'is_active' => true]);
        $plan = Plan::create([
            'platform_service_id' => $service->id,
            'name' => 'POS mensual',
            'slug' => 'pos-mensual',
            'billing_period' => 'monthly',
            'price' => 100000,
            'currency' => 'COP',
            'is_active' => true,
        ]);
        $subscription = Subscription::create([
            'company_id' => $company->id,
            'platform_service_id' => $service->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'payment_status' => 'pending',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
        ]);

        $this->post(route('companies.payments.store', $company), [
            'subscription_id' => $subscription->id,
            'amount' => 100000,
            'currency' => 'COP',
            'status' => 'paid',
        ]);
        $payment = $subscription->payments()->firstOrFail();
        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'payment_status' => 'paid']);

        $this->put(route('companies.payments.update', [$company, $payment]), [
            'subscription_id' => $subscription->id,
            'amount' => 100000,
            'currency' => 'COP',
            'status' => 'void',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'payment_status' => 'pending']);
    }
}
