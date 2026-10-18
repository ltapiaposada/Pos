<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\PlatformService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_maintenance_command_expires_old_active_subscriptions(): void
    {
        $service = PlatformService::create(['slug' => 'pos', 'name' => 'POS', 'is_active' => true]);
        $subscription = Subscription::create([
            'company_id' => 1,
            'platform_service_id' => $service->id,
            'status' => 'active',
            'payment_status' => 'paid',
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => now()->subDay()->toDateString(),
        ]);

        $this->artisan('platform:refresh-subscriptions')
            ->expectsOutput('Suscripciones vencidas actualizadas: 1')
            ->assertSuccessful();

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'status' => 'expired']);
    }
}
