<?php

namespace Tests;

use App\Models\AdministrativeEntitlement;
use App\Models\AdministrativePlatformService;
use App\Models\AdministrativeSubscription;
use App\Models\AdministrativeTenant;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function createAdministrativeSchema(): void
    {
        config()->set('database.connections.administracion', config('database.connections.sqlite'));

        Schema::connection('administracion')->create('companies', function ($table): void {
            $table->id();
            $table->uuid('tenant_uuid')->unique();
            $table->string('name');
            $table->string('status')->default('active');
            $table->timestamps();
        });
        Schema::connection('administracion')->create('platform_services', function ($table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->timestamps();
        });
        Schema::connection('administracion')->create('entitlements', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('service');
            $table->string('status');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::connection('administracion')->create('subscriptions', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('platform_service_id');
            $table->string('status');
            $table->string('payment_status');
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->timestamps();
        });
    }

    protected function grantStorefrontAccess(AdministrativeTenant $tenant): void
    {
        foreach (['pos' => 'POS', 'ecommerce' => 'E-commerce'] as $slug => $name) {
            $service = AdministrativePlatformService::forceCreate(['slug' => $slug, 'name' => $name]);
            AdministrativeEntitlement::forceCreate(['company_id' => $tenant->id, 'service' => $slug, 'status' => 'enabled']);
            AdministrativeSubscription::forceCreate([
                'company_id' => $tenant->id,
                'platform_service_id' => $service->id,
                'status' => 'active',
                'payment_status' => 'paid',
                'starts_at' => now()->subDay()->toDateString(),
                'ends_at' => now()->addDay()->toDateString(),
            ]);
        }
    }
}
