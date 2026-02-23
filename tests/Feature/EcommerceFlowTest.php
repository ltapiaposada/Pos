<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EcommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'customer']);
        Role::firstOrCreate(['name' => 'admin']);
    }

    public function test_customer_login_redirects_to_storefront(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@test.com',
            'password' => 'password',
        ]);
        $user->assignRole('customer');

        $response = $this->post('/login', [
            'email' => 'customer@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
    }

    public function test_admin_login_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);
        $user->assignRole('admin');

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
    }

    public function test_customer_can_place_order_from_checkout(): void
    {
        $branch = Branch::query()->create([
            'name' => 'Principal',
            'code' => 'MAIN',
        ]);

        $tax = Tax::query()->create([
            'name' => 'IVA',
            'rate' => 19,
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'name' => 'Producto web',
            'sku' => 'WEB-001',
            'tax_id' => $tax->id,
            'sale_price' => 100,
            'cost_price' => 50,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);

        Inventory::query()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'stock' => 10,
            'min_stock' => 1,
        ]);

        $user = User::factory()->create([
            'name' => 'Cliente Demo',
            'email' => 'cliente@demo.com',
            'password' => 'password',
        ]);
        $user->assignRole('customer');

        Customer::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['shop.cart' => [$product->id => 2]])
            ->post('/checkout', [
                'address' => 'Calle 123',
                'phone' => '5551234',
                'payment_method' => 'card',
                'payment_reference' => 'TRX-ECOM-001',
                'customer_note' => 'Dejar en recepcion',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $sale = Sale::query()->first();
        $this->assertNotNull($sale);
        $response->assertRedirect(route('shop.orders.show', ['sale' => $sale->id]));
        $this->assertSame(1, $sale->items()->count());
        $this->assertSame('pending', $sale->status);
        $this->assertSame('ecommerce', $sale->order_source);
        $this->assertStringContainsString('Dejar en recepcion', (string) $sale->customer_note);
        $this->assertStringContainsString('Referencia de pago: TRX-ECOM-001', (string) $sale->customer_note);
        $this->assertDatabaseHas('payments', [
            'sale_id' => $sale->id,
            'method' => 'card',
            'reference' => 'TRX-ECOM-001',
        ]);
        $this->assertSame(8.0, (float) Inventory::query()->where('product_id', $product->id)->value('stock'));
    }

    public function test_storefront_shows_variant_selector_for_parent_product(): void
    {
        $branch = Branch::query()->create([
            'name' => 'Principal',
            'code' => 'MAIN',
        ]);

        $tax = Tax::query()->create([
            'name' => 'IVA',
            'rate' => 19,
            'is_active' => true,
        ]);

        $parent = Product::query()->create([
            'name' => 'Camiseta deportiva',
            'sku' => 'CAM-BASE',
            'tax_id' => $tax->id,
            'sale_price' => 90,
            'cost_price' => 50,
            'is_active' => true,
            'is_visible_ecommerce' => true,
            'product_type' => Product::TYPE_SIMPLE,
        ]);

        $variantL = Product::query()->create([
            'name' => 'Camiseta deportiva talla L',
            'sku' => 'CAM-L',
            'tax_id' => $tax->id,
            'sale_price' => 95,
            'cost_price' => 52,
            'is_active' => true,
            'is_visible_ecommerce' => true,
            'product_type' => Product::TYPE_VARIANT,
            'parent_product_id' => $parent->id,
        ]);

        Inventory::query()->create([
            'branch_id' => $branch->id,
            'product_id' => $variantL->id,
            'stock' => 10,
            'min_stock' => 1,
        ]);

        $response = $this->get(route('shop.index'));

        $response->assertOk();
        $response->assertSee('Camiseta deportiva');
        $response->assertSee('Selecciona talla o presentacion antes de agregar.');
        $response->assertSee('value="'.$variantL->id.'"', false);
        $response->assertSee('name="product_id"', false);
    }

    public function test_storefront_shows_variant_selector_when_parent_is_hidden(): void
    {
        $tax = Tax::query()->create([
            'name' => 'IVA',
            'rate' => 19,
            'is_active' => true,
        ]);

        $parent = Product::query()->create([
            'name' => 'Camiseta deportiva',
            'sku' => 'CAM-BASE-HIDDEN',
            'tax_id' => $tax->id,
            'sale_price' => 90,
            'cost_price' => 50,
            'is_active' => true,
            'is_visible_ecommerce' => false,
            'product_type' => Product::TYPE_SIMPLE,
        ]);

        $variantM = Product::query()->create([
            'name' => 'Camiseta deportiva talla M',
            'sku' => 'CAM-H-M',
            'tax_id' => $tax->id,
            'sale_price' => 95,
            'cost_price' => 52,
            'is_active' => true,
            'is_visible_ecommerce' => true,
            'product_type' => Product::TYPE_VARIANT,
            'parent_product_id' => $parent->id,
        ]);

        $variantL = Product::query()->create([
            'name' => 'Camiseta deportiva talla L',
            'sku' => 'CAM-H-L',
            'tax_id' => $tax->id,
            'sale_price' => 97,
            'cost_price' => 53,
            'is_active' => true,
            'is_visible_ecommerce' => true,
            'product_type' => Product::TYPE_VARIANT,
            'parent_product_id' => $parent->id,
        ]);

        $response = $this->get(route('shop.index'));

        $response->assertOk();
        $response->assertSee('Camiseta deportiva');
        $response->assertSee('Selecciona talla o presentacion antes de agregar.');
        $response->assertSee('value="'.$variantM->id.'"', false);
        $response->assertSee('value="'.$variantL->id.'"', false);
        $response->assertSee('name="product_id"', false);
    }
}
