<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SubscriptionAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Mockery\MockInterface;
use Tests\TestCase;

class PlatformSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_does_not_expose_the_legacy_storefront_or_platform_company_panel(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/tienda')->assertGone();
        $this->post('/carrito/items')->assertGone();

        $this->assertFalse(Route::has('system.companies.index'));
        $this->assertFalse(Route::has('system.companies.subscriptions.store'));
    }

    public function test_pos_no_longer_accepts_local_entitlement_updates(): void
    {
        config()->set('integration.internal_token', 'test-token');

        $this->withHeader('X-Internal-Token', 'test-token')
            ->patchJson('/api/v1/tenants/018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1a/entitlements', [
                'entitlements' => [],
            ])
            ->assertNotFound();
    }

    public function test_ecommerce_menu_and_routes_are_hidden_when_the_service_is_not_enabled(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@pos.test')->firstOrFail();

        $this->partialMock(SubscriptionAccessService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('canUseService')
                ->withArgs(fn ($company, string $service): bool => $service === 'ecommerce')
                ->andReturnFalse();
        });

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Pedidos web');

        $this->actingAs($admin)
            ->get(route('ecommerce-admin.orders.index'))
            ->assertForbidden();
    }
}
