<?php

namespace Tests\Feature;

use App\Models\AdministrativeTenant;
use App\Models\CatalogProduct;
use App\Models\Storefront;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontCartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdministrativeSchema();
    }

    public function test_a_storefront_keeps_its_cart_isolated_and_shows_the_legacy_purchase_flow(): void
    {
        $tenant = AdministrativeTenant::create([
            'tenant_uuid' => '018f0a50-7ec4-7e72-b7d1-0c4f2a8f4f1d',
            'name' => 'Tienda de prueba',
            'status' => 'active',
        ]);
        $this->grantStorefrontAccess($tenant);
        Storefront::create(['company_id' => $tenant->id, 'domain' => 'localhost']);
        $product = CatalogProduct::create([
            'company_id' => $tenant->id,
            'pos_product_id' => 87,
            'name' => 'Café de prueba',
            'price' => 12000,
            'currency' => 'COP',
            'tax_rate' => 19,
            'stock' => 8,
            'is_active' => true,
            'payload' => ['category' => 'Bebidas'],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Café de prueba')
            ->assertSee('Productos destacados');

        $this->post('/carrito', ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect();

        $this->get('/carrito')
            ->assertOk()
            ->assertSee('Productos seleccionados')
            ->assertSee('28,560.00');

        $this->get('/checkout')
            ->assertRedirect('/ingresar');
    }
}
