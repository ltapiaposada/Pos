<?php

namespace Tests\Feature;

use App\Models\AdministrativeTenant;
use App\Models\Storefront;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StorefrontCustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    private AdministrativeTenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdministrativeSchema();
        $this->tenant = AdministrativeTenant::create([
            'tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1d',
            'name' => 'Tienda de prueba',
            'status' => 'active',
        ]);
        $this->grantStorefrontAccess($this->tenant);
        Storefront::create(['company_id' => $this->tenant->id, 'domain' => 'localhost']);
    }

    public function test_a_new_contact_is_created_only_in_the_pos_and_its_identity_is_kept_in_session(): void
    {
        Http::fake(function (Request $request) {
            $this->assertStringEndsWith('/contacts', $request->url());
            $this->assertSame('ana@example.test', $request['email']);

            return Http::response(['customer' => [
                'external_customer_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1e',
                'name' => 'Ana Compradora',
                'email' => 'ana@example.test',
                'phone' => '3001234567',
                'address' => null,
            ]], 201);
        });

        $response = $this->post('/registrate', [
            'name' => 'Ana Compradora',
            'email' => 'ana@example.test',
            'phone' => '3001234567',
            'password' => 'compras-seguras',
            'password_confirmation' => 'compras-seguras',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('ecommerce.contact.'.$this->tenant->id.'.email', 'ana@example.test');
    }

    public function test_an_existing_pos_contact_is_sent_to_password_recovery_without_creating_a_duplicate(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Contacto existente'], 409)]);

        $this->post('/registrate', [
            'name' => 'Ana Compradora',
            'email' => 'ana@example.test',
            'password' => 'compras-seguras',
            'password_confirmation' => 'compras-seguras',
        ])->assertRedirect('/recuperar-contrasena?email=ana%40example.test');
    }
}
