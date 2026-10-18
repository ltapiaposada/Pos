<?php

namespace Tests\Feature;

use App\Models\AdministrativeTenant;
use App\Models\Storefront;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAdministrativeSchema();
    }
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Http::fake([
            '*' => Http::response(['products' => [], 'availability' => []]),
        ]);
        $tenant = AdministrativeTenant::create(['tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1a', 'name' => 'Tienda Demo']);
        $this->grantStorefrontAccess($tenant);
        Storefront::create(['company_id' => $tenant->id, 'domain' => 'localhost']);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
