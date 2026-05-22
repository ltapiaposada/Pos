<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanyType;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductModifierGroup;
use App\Models\ProductModifierOption;
use App\Models\RestaurantOrder;
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

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'customer']);
        Role::firstOrCreate(['name' => 'admin']);

        $companyType = CompanyType::query()->firstOrCreate(
            ['slug' => 'pos'],
            [
                'name' => 'POS',
                'features' => ['sales', 'products', 'inventory'],
                'is_active' => true,
            ]
        );

        $this->company = Company::query()->create([
            'name' => 'Tienda Demo',
            'domain' => 'localhost',
            'company_type_id' => $companyType->id,
            'status' => Company::STATUS_ACTIVE,
        ]);
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

    public function test_customer_from_other_store_is_logged_out_and_redirected_to_current_storefront(): void
    {
        $otherType = CompanyType::query()->firstOrCreate(
            ['slug' => 'restaurant'],
            [
                'name' => 'Restaurante',
                'features' => ['ecommerce', 'restaurant'],
                'is_active' => true,
            ]
        );

        $otherCompany = Company::query()->create([
            'name' => 'Otra tienda',
            'domain' => 'otra-tienda.test',
            'company_type_id' => $otherType->id,
            'status' => Company::STATUS_ACTIVE,
        ]);

        Branch::query()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Principal',
            'code' => 'MAIN',
        ]);

        $user = User::factory()->create([
            'company_id' => $otherCompany->id,
            'email' => 'cross-store@test.com',
            'password' => 'password',
        ]);
        $user->assignRole('customer');

        $response = $this->actingAs($user)->get('http://localhost/');

        $response->assertRedirect('http://localhost');
        $response->assertSessionHas('status');
        $this->assertGuest();
    }

    public function test_storefront_can_add_product_with_legacy_modifier_options_to_cart(): void
    {
        $restaurantType = CompanyType::query()->firstOrCreate(
            ['slug' => 'restaurant'],
            [
                'name' => 'Restaurante',
                'features' => ['ecommerce', 'restaurant'],
                'is_active' => true,
            ]
        );

        $restaurantCompany = Company::query()->create([
            'name' => 'Restaurante Legacy',
            'domain' => 'restaurant-legacy.test',
            'company_type_id' => $restaurantType->id,
            'status' => Company::STATUS_ACTIVE,
        ]);

        $branch = Branch::query()->create([
            'company_id' => $restaurantCompany->id,
            'name' => 'Principal',
            'code' => 'MAIN',
        ]);

        $product = Product::query()->create([
            'company_id' => $restaurantCompany->id,
            'name' => 'Almuerzo corriente',
            'sku' => 'LEG-REST-001',
            'unit' => 'und',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 12,
            'sale_price' => 20,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);

        Inventory::query()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'stock' => 8,
            'min_stock' => 1,
        ]);

        $proteinGroup = ProductModifierGroup::query()->create([
            'company_id' => $restaurantCompany->id,
            'product_id' => $product->id,
            'name' => 'Proteina',
            'selection_type' => ProductModifierGroup::TYPE_SINGLE,
            'is_required' => true,
            'min_select' => 1,
            'max_select' => 1,
            'display_order' => 1,
        ]);

        $cerdoOption = ProductModifierOption::query()->create([
            'company_id' => $restaurantCompany->id,
            'product_modifier_group_id' => $proteinGroup->id,
            'product_id' => null,
            'label' => 'Cerdo',
            'inventory_quantity' => null,
            'inventory_unit' => null,
            'inventory_unit_factor' => 1,
            'price_delta' => 0,
            'is_default' => false,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $removeGroup = ProductModifierGroup::query()->create([
            'company_id' => $restaurantCompany->id,
            'product_id' => $product->id,
            'name' => 'Quitar ingredientes',
            'selection_type' => ProductModifierGroup::TYPE_REMOVE,
            'is_required' => false,
            'min_select' => 0,
            'max_select' => 0,
            'display_order' => 2,
        ]);

        $ensaladaOption = ProductModifierOption::query()->create([
            'company_id' => $restaurantCompany->id,
            'product_modifier_group_id' => $removeGroup->id,
            'product_id' => null,
            'label' => 'Ensalada',
            'inventory_quantity' => null,
            'inventory_unit' => null,
            'inventory_unit_factor' => 1,
            'price_delta' => 0,
            'is_default' => true,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->post('http://restaurant-legacy.test/carrito/items', [
                'product_id' => $product->id,
                'quantity' => 1,
                'modifier_groups' => [
                    $proteinGroup->id => $cerdoOption->id,
                    $removeGroup->id => [$ensaladaOption->id],
                ],
            ])
            ->assertRedirect();

        $this->get('http://restaurant-legacy.test/carrito')
            ->assertOk()
            ->assertSee('Almuerzo corriente (Cerdo, Sin Ensalada)')
            ->assertSee('Proteina: Cerdo')
            ->assertSee('Quitar ingredientes: Sin Ensalada');
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
            'company_id' => $this->company->id,
            'name' => 'Principal',
            'code' => 'MAIN',
        ]);

        $tax = Tax::query()->create([
            'company_id' => $this->company->id,
            'name' => 'IVA',
            'rate' => 19,
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'company_id' => $this->company->id,
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
            'company_id' => $this->company->id,
            'name' => 'Cliente Demo',
            'email' => 'cliente@demo.com',
            'password' => 'password',
        ]);
        $user->assignRole('customer');

        Customer::query()->create([
            'company_id' => $this->company->id,
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
            'company_id' => $this->company->id,
            'name' => 'Principal',
            'code' => 'MAIN',
        ]);

        $tax = Tax::query()->create([
            'company_id' => $this->company->id,
            'name' => 'IVA',
            'rate' => 19,
            'is_active' => true,
        ]);

        $parent = Product::query()->create([
            'company_id' => $this->company->id,
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
            'company_id' => $this->company->id,
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
            'company_id' => $this->company->id,
            'name' => 'IVA',
            'rate' => 19,
            'is_active' => true,
        ]);

        $parent = Product::query()->create([
            'company_id' => $this->company->id,
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
            'company_id' => $this->company->id,
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
            'company_id' => $this->company->id,
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

    public function test_restaurant_storefront_creates_restaurant_order_and_keeps_inventory_until_sale(): void
    {
        $restaurantType = CompanyType::query()->firstOrCreate(
            ['slug' => 'restaurant'],
            [
                'name' => 'Restaurante',
                'features' => ['tables', 'orders', 'kitchen', 'menu'],
                'is_active' => true,
            ]
        );

        $restaurantCompany = Company::query()->create([
            'name' => 'Restaurante Demo',
            'domain' => 'restaurant.local',
            'company_type_id' => $restaurantType->id,
            'status' => Company::STATUS_ACTIVE,
        ]);

        $branch = Branch::query()->create([
            'company_id' => $restaurantCompany->id,
            'name' => 'Principal',
            'code' => 'REST-001',
        ]);

        $tax = Tax::query()->create([
            'company_id' => $restaurantCompany->id,
            'name' => 'IVA',
            'rate' => 0,
            'is_active' => true,
        ]);

        $dish = Product::query()->create([
            'company_id' => $restaurantCompany->id,
            'name' => 'Almuerzo corriente',
            'sku' => 'ALM-001',
            'tax_id' => $tax->id,
            'unit' => 'und',
            'sale_price' => 20,
            'cost_price' => 10,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);

        $protein = Product::query()->create([
            'company_id' => $restaurantCompany->id,
            'name' => 'Pechuga de pollo',
            'sku' => 'PROT-001',
            'tax_id' => $tax->id,
            'unit' => 'kg',
            'sale_price' => 0,
            'cost_price' => 8,
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]);

        $group = ProductModifierGroup::query()->create([
            'company_id' => $restaurantCompany->id,
            'product_id' => $dish->id,
            'name' => 'Proteina',
            'selection_type' => ProductModifierGroup::TYPE_SINGLE,
            'is_required' => true,
            'min_select' => 1,
            'max_select' => 1,
            'display_order' => 1,
        ]);

        $option = ProductModifierOption::query()->create([
            'company_id' => $restaurantCompany->id,
            'product_modifier_group_id' => $group->id,
            'product_id' => $protein->id,
            'inventory_quantity' => 250,
            'inventory_unit' => 'g',
            'inventory_unit_factor' => 0.001,
            'label' => 'Pechuga',
            'price_delta' => 3,
            'is_default' => true,
            'is_active' => true,
            'display_order' => 1,
        ]);

        Inventory::query()->create([
            'branch_id' => $branch->id,
            'product_id' => $dish->id,
            'stock' => 10,
            'min_stock' => 1,
        ]);

        Inventory::query()->create([
            'branch_id' => $branch->id,
            'product_id' => $protein->id,
            'stock' => 1,
            'min_stock' => 0,
        ]);

        $user = User::factory()->create([
            'company_id' => $restaurantCompany->id,
            'name' => 'Cliente Restaurante',
            'email' => 'cliente@restaurant.local',
            'password' => 'password',
        ]);
        $user->assignRole('customer');

        Customer::query()->create([
            'company_id' => $restaurantCompany->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post('http://restaurant.local/carrito/items', [
                'product_id' => $dish->id,
                'quantity' => 2,
                'modifier_groups' => [
                    $group->id => $option->id,
                ],
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->get('http://restaurant.local/carrito')
            ->assertOk()
            ->assertSee('Almuerzo corriente (Pechuga)')
            ->assertSee('Proteina: Pechuga')
            ->assertSee('$46.00', false);

        $response = $this->actingAs($user)
            ->post('http://restaurant.local/checkout', [
                'address' => 'Calle 123',
                'phone' => '5551234',
                'fulfillment_type' => 'delivery',
                'payment_method' => 'card',
                'payment_reference' => 'TRX-REST-001',
                'customer_note' => 'Sin demoras',
            ]);

        $response->assertRedirect();

        $order = RestaurantOrder::query()->first();
        $this->assertNotNull($order);
        $this->assertSame(RestaurantOrder::STATUS_OPEN, $order->status);
        $this->assertSame(RestaurantOrder::TYPE_DELIVERY, $order->order_type);
        $this->assertStringContainsString('Origen: Pedido web restaurante', (string) $order->notes);
        $this->assertStringContainsString('Referencia de pago: TRX-REST-001', (string) $order->notes);
        $this->assertSame(1, $order->items()->count());
        $this->assertDatabaseHas('restaurant_order_items', [
            'restaurant_order_id' => $order->id,
            'product_id' => $dish->id,
            'unit_price' => 23,
        ]);
        $this->assertDatabaseHas('restaurant_order_item_selections', [
            'restaurant_order_item_id' => $order->items()->first()->id,
            'option_label' => 'Pechuga',
        ]);
        $this->assertSame(10.0, (float) Inventory::query()->where('product_id', $dish->id)->value('stock'));
        $this->assertSame(1.0, (float) Inventory::query()->where('product_id', $protein->id)->value('stock'));
        $this->assertNull(Sale::query()->first());
    }
}
