<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\CompanyType;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductModifierGroup;
use App\Models\ProductModifierOption;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MultiCompanyAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_owner_can_access_global_company_panel(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext();
        $owner = $this->makeUser($company, $branch, 'owner@pos.test', 'system_owner');

        $this->actingAs($owner)
            ->get(route('system.companies.index'))
            ->assertOk();
    }

    public function test_normal_admin_cannot_access_global_company_panel(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext();
        $admin = $this->makeUser($company, $branch, 'admin@empresa-a.test', 'admin');

        $this->actingAs($admin)
            ->get(route('system.companies.index'))
            ->assertForbidden();
    }

    public function test_subscription_warning_is_displayed_when_four_days_are_left(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext();
        $admin = $this->makeUser($company, $branch, 'admin@warning.test', 'admin');

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => 'monthly',
            'start_date' => now()->subDays(26)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => 'paid',
            'last_payment_date' => now()->subDays(26)->toDateString(),
            'next_payment_date' => now()->addDays(4)->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tu suscripci')
            ->assertSee('4 dias', escape: false);
    }

    public function test_expired_subscription_blocks_admin_routes(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext();
        $admin = $this->makeUser($company, $branch, 'admin@expired.test', 'admin');

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => 'monthly',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
            'status' => CompanySubscription::STATUS_EXPIRED,
            'payment_status' => 'pending',
            'last_payment_date' => now()->subMonth()->toDateString(),
            'next_payment_date' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertStatus(402)
            ->assertSee('Tu suscripci')
            ->assertSee('renueva tu suscripci');
    }

    public function test_changing_billing_period_creates_a_new_subscription_and_keeps_the_new_active(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext();
        $owner = $this->makeUser($company, $branch, 'owner@subscription.test', 'system_owner');
        $admin = $this->makeUser($company, $branch, 'admin@subscription.test', 'admin');
        $newStartDate = now()->subDays(10)->toDateString();
        $newEndDate = now()->addDays(20)->toDateString();

        $subscription = CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => 'yearly',
            'start_date' => now()->subMonths(8)->toDateString(),
            'end_date' => now()->addMonths(4)->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => 'paid',
            'last_payment_date' => now()->subMonths(8)->toDateString(),
            'next_payment_date' => now()->addMonths(4)->toDateString(),
        ]);

        $this->actingAs($owner)
            ->post(route('system.companies.subscriptions.store', $company), [
                'plan_type' => 'pos',
                'billing_period' => 'monthly',
                'start_date' => $newStartDate,
                'end_date' => $newEndDate,
                'status' => CompanySubscription::STATUS_ACTIVE,
                'payment_status' => 'paid',
                'last_payment_date' => $newStartDate,
                'next_payment_date' => $newEndDate,
            ])
            ->assertRedirect(route('system.companies.edit', $company));

        $this->assertSame(
            2,
            CompanySubscription::withoutGlobalScopes()->where('company_id', $company->id)->count()
        );
        $this->assertDatabaseHas('company_subscriptions', [
            'company_id' => $company->id,
            'billing_period' => 'monthly',
            'start_date' => $newStartDate.' 00:00:00',
            'end_date' => $newEndDate.' 00:00:00',
            'status' => CompanySubscription::STATUS_ACTIVE,
        ]);
        $this->assertDatabaseHas('company_subscriptions', [
            'id' => $subscription->id,
            'company_id' => $company->id,
            'billing_period' => 'yearly',
            'status' => CompanySubscription::STATUS_CANCELLED,
        ]);

        $this->actingAs($owner)
            ->get(route('system.companies.edit', $company))
            ->assertOk()
            ->assertSee($newStartDate, escape: false)
            ->assertSee($newEndDate, escape: false);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_same_billing_period_updates_current_subscription_without_creating_a_new_one(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext();
        $owner = $this->makeUser($company, $branch, 'owner@same-period.test', 'system_owner');
        $currentStartDate = now()->subDays(40)->toDateString();
        $currentEndDate = now()->subDays(11)->toDateString();
        $updatedStartDate = now()->subDays(10)->toDateString();
        $updatedEndDate = now()->addDays(20)->toDateString();

        $subscription = CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => 'monthly',
            'start_date' => $currentStartDate,
            'end_date' => $currentEndDate,
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => 'paid',
            'last_payment_date' => $currentStartDate,
            'next_payment_date' => $currentEndDate,
        ]);

        $this->actingAs($owner)
            ->post(route('system.companies.subscriptions.store', $company), [
                'plan_type' => 'pos',
                'billing_period' => 'monthly',
                'start_date' => $updatedStartDate,
                'end_date' => $updatedEndDate,
                'status' => CompanySubscription::STATUS_ACTIVE,
                'payment_status' => 'paid',
                'last_payment_date' => $updatedStartDate,
                'next_payment_date' => $updatedEndDate,
            ])
            ->assertRedirect(route('system.companies.edit', $company));

        $this->assertSame(
            1,
            CompanySubscription::withoutGlobalScopes()->where('company_id', $company->id)->count()
        );
        $this->assertDatabaseHas('company_subscriptions', [
            'id' => $subscription->id,
            'company_id' => $company->id,
            'billing_period' => 'monthly',
            'start_date' => $updatedStartDate.' 00:00:00',
            'end_date' => $updatedEndDate.' 00:00:00',
            'status' => CompanySubscription::STATUS_ACTIVE,
        ]);
    }

    public function test_create_new_subscription_button_creates_new_record_even_for_same_period(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext();
        $owner = $this->makeUser($company, $branch, 'owner@create-new.test', 'system_owner');
        $currentStartDate = now()->subDays(40)->toDateString();
        $currentEndDate = now()->subDays(11)->toDateString();
        $newStartDate = now()->subDays(10)->toDateString();
        $newEndDate = now()->addDays(20)->toDateString();

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => 'monthly',
            'start_date' => $currentStartDate,
            'end_date' => $currentEndDate,
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
            'last_payment_date' => $currentStartDate,
            'next_payment_date' => $currentEndDate,
        ]);

        $this->actingAs($owner)
            ->post(route('system.companies.subscriptions.store', $company), [
                'action_mode' => 'create_new',
                'plan_type' => 'pos',
                'billing_period' => 'monthly',
                'start_date' => $newStartDate,
                'end_date' => $newEndDate,
                'status' => CompanySubscription::STATUS_ACTIVE,
                'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
                'last_payment_date' => $newStartDate,
                'next_payment_date' => $newEndDate,
            ])
            ->assertRedirect(route('system.companies.edit', $company));

        $this->assertSame(
            2,
            CompanySubscription::withoutGlobalScopes()->where('company_id', $company->id)->count()
        );
        $this->assertSame(
            1,
            CompanySubscription::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->where('plan_type', 'pos')
                ->where('status', CompanySubscription::STATUS_ACTIVE)
                ->count()
        );
    }

    public function test_active_subscription_is_blocked_when_payment_status_is_not_paid(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext();
        $admin = $this->makeUser($company, $branch, 'admin@not-paid.test', 'admin');

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => 'monthly',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => 'pending',
            'last_payment_date' => now()->subDays(5)->toDateString(),
            'next_payment_date' => now()->addDays(20)->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertStatus(402)
            ->assertSee('no est')
            ->assertSee('pago');
    }

    public function test_active_subscription_is_preferred_over_cancelled_subscription_with_later_end_date(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext();
        $admin = $this->makeUser($company, $branch, 'admin@effective.test', 'admin');
        $cancelledStartDate = now()->subMonths(6)->toDateString();
        $cancelledEndDate = now()->addMonths(6)->toDateString();
        $activeStartDate = now()->subDays(10)->toDateString();
        $activeEndDate = now()->addDays(20)->toDateString();

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => 'yearly',
            'start_date' => $cancelledStartDate,
            'end_date' => $cancelledEndDate,
            'status' => CompanySubscription::STATUS_CANCELLED,
            'payment_status' => 'paid',
        ]);

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => 'monthly',
            'start_date' => $activeStartDate,
            'end_date' => $activeEndDate,
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => 'paid',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('system.companies.index'))
            ->assertForbidden();
    }

    public function test_login_redirects_to_subscription_selector_when_company_has_multiple_paid_active_plans(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext();
        $admin = $this->makeUser($company, $branch, 'admin@multi-plan.test', 'admin');

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => 'monthly',
            'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
        ]);

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'restaurant',
            'billing_period' => 'monthly',
            'start_date' => now()->subDays(1)->toDateString(),
            'end_date' => now()->addDays(15)->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
        ]);

        $this->post(route('login'), [
            'email' => 'admin@multi-plan.test',
            'password' => 'password',
        ])->assertRedirect(route('subscription-context.index'));

        $this->actingAs($admin)
            ->get(route('subscription-context.index'))
            ->assertOk()
            ->assertSee('POS')
            ->assertSee('Restaurante');
    }

    public function test_company_user_cannot_view_sale_from_another_company(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$companyA, $branchA] = $this->makeCompanyContext('empresa-a', 'Empresa A');
        [$companyB, $branchB] = $this->makeCompanyContext('empresa-b', 'Empresa B');
        $userA = $this->makeUser($companyA, $branchA, 'admin@empresa-a.test', 'admin');

        $customerB = Customer::query()->create([
            'company_id' => $companyB->id,
            'name' => 'Cliente B',
            'document' => 'B-CLIENTE',
            'is_active' => true,
        ]);

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $companyA->id,
            'plan_type' => 'pos',
            'billing_period' => 'monthly',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(15)->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => 'paid',
        ]);

        $saleB = Sale::withoutGlobalScopes()->create([
            'branch_id' => $branchB->id,
            'user_id' => $userA->id,
            'customer_id' => $customerB->id,
            'sale_number' => 1,
            'status' => Sale::STATUS_PAID,
            'order_source' => Sale::SOURCE_POS,
            'subtotal' => 10,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'coupon_discount_total' => 0,
            'total' => 10,
            'paid_total' => 10,
            'change_total' => 0,
            'currency' => 'USD',
            'sold_at' => now(),
        ]);

        $this->actingAs($userA)
            ->get(route('sales.show', $saleB))
            ->assertNotFound();
    }

    public function test_sale_request_rejects_foreign_company_ids(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$companyA, $branchA] = $this->makeCompanyContext('empresa-a', 'Empresa A');
        [$companyB, $branchB] = $this->makeCompanyContext('empresa-b', 'Empresa B');
        $userA = $this->makeUser($companyA, $branchA, 'cashier@empresa-a.test', 'cashier');

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $companyA->id,
            'plan_type' => 'pos',
            'billing_period' => 'monthly',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(15)->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => 'paid',
        ]);

        $customerB = Customer::query()->create([
            'company_id' => $companyB->id,
            'name' => 'Cliente B',
            'document' => 'B-CLIENTE-2',
            'is_active' => true,
        ]);

        $productB = Product::query()->create([
            'company_id' => $companyB->id,
            'name' => 'Producto B',
            'sku' => 'B-PROD-001',
            'unit' => 'unit',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 1,
            'sale_price' => 2,
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]);

        $this->actingAs($userA)
            ->post(route('pos.checkout'), [
                'branch_id' => $branchB->id,
                'customer_id' => $customerB->id,
                'items' => [
                    [
                        'product_id' => $productB->id,
                        'quantity' => 1,
                        'unit_price' => 2,
                    ],
                ],
                'payments' => [
                    [
                        'method' => 'cash',
                        'amount' => 2,
                    ],
                ],
            ])
            ->assertSessionHasErrors(['branch_id', 'customer_id', 'items.0.product_id']);
    }

    public function test_storefront_resolves_products_by_company_domain(): void
    {
        [$companyA] = $this->makeCompanyContext('restaurant', 'Restaurante A', 'restaurante-a.test');
        [$companyB] = $this->makeCompanyContext('pos', 'Tienda B', 'tienda-b.test');

        Product::query()->create([
            'company_id' => $companyA->id,
            'name' => 'Hamburguesa A',
            'sku' => 'A-001',
            'unit' => 'und',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 5,
            'sale_price' => 10,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);

        Product::query()->create([
            'company_id' => $companyB->id,
            'name' => 'Producto B',
            'sku' => 'B-001',
            'unit' => 'und',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 5,
            'sale_price' => 10,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);

        $this->get('http://restaurante-a.test/')
            ->assertOk()
            ->assertSee('Hamburguesa A')
            ->assertDontSee('Producto B');
    }

    public function test_storefront_cart_is_isolated_per_public_domain(): void
    {
        [$companyA] = $this->makeCompanyContext('restaurant', 'Restaurante A', 'restaurante-a.test');
        [$companyB] = $this->makeCompanyContext('pos', 'Tienda B', 'tienda-b.test');

        $productA = Product::query()->create([
            'company_id' => $companyA->id,
            'name' => 'Hamburguesa A',
            'sku' => 'A-002',
            'unit' => 'und',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 5,
            'sale_price' => 10,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);

        Product::query()->create([
            'company_id' => $companyB->id,
            'name' => 'Producto B',
            'sku' => 'B-002',
            'unit' => 'und',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 5,
            'sale_price' => 10,
            'is_active' => true,
            'is_visible_ecommerce' => true,
        ]);

        $this->post('http://restaurante-a.test/carrito/items', [
                'product_id' => $productA->id,
                'quantity' => 2,
            ])
            ->assertRedirect();

        $this->get('http://restaurante-a.test/carrito')
            ->assertOk()
            ->assertSee('Hamburguesa A');

        $this->get('http://tienda-b.test/carrito')
            ->assertOk()
            ->assertDontSee('Hamburguesa A');
    }

    public function test_product_update_allows_visibility_changes_when_existing_modifier_options_are_legacy_labels(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext('restaurant', 'Restaurante Demo');
        $admin = $this->makeUser($company, $branch, 'admin@legacy-product.test', 'admin');
        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'restaurant',
            'billing_period' => 'monthly',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => 'paid',
        ]);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => 'Almuerzo corriente',
            'sku' => 'REST-LEG-001',
            'unit' => 'und',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 10,
            'sale_price' => 18,
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]);

        $group = ProductModifierGroup::query()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'name' => 'Proteina',
            'selection_type' => ProductModifierGroup::TYPE_SINGLE,
            'is_required' => true,
            'min_select' => 1,
            'max_select' => 1,
            'display_order' => 1,
        ]);

        $option = ProductModifierOption::query()->create([
            'company_id' => $company->id,
            'product_modifier_group_id' => $group->id,
            'product_id' => null,
            'label' => 'Carne',
            'inventory_quantity' => null,
            'inventory_unit' => null,
            'inventory_unit_factor' => 1,
            'price_delta' => 0,
            'is_default' => true,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->actingAs($admin)
            ->put(route('products.update', $product), [
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => '',
                'image_url' => '',
                'description' => '',
                'category_id' => '',
                'tax_id' => '',
                'unit' => $product->unit,
                'product_type' => $product->product_type,
                'parent_product_id' => '',
                'cost_price' => $product->cost_price,
                'sale_price' => $product->sale_price,
                'is_active' => 1,
                'is_visible_ecommerce' => 1,
                'modifier_groups' => [
                    [
                        'id' => $group->id,
                        'name' => $group->name,
                        'selection_type' => $group->selection_type,
                        'is_required' => 1,
                        'min_select' => 1,
                        'max_select' => 1,
                        'options' => [
                            [
                                'id' => $option->id,
                                'product_id' => '',
                                'label' => $option->label,
                                'inventory_quantity' => '',
                                'inventory_unit' => '',
                                'inventory_unit_factor' => 1,
                                'price_delta' => 0,
                                'is_default' => 1,
                                'is_active' => 1,
                            ],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_visible_ecommerce' => true,
        ]);
    }

    public function test_pos_service_cannot_access_restaurant_routes_even_with_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext('pos', 'Empresa POS');
        $admin = $this->makeUser($company, $branch, 'admin@pos-service.test', 'admin');

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => 'monthly',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
        ]);

        $this->actingAs($admin)
            ->get(route('restaurant.index'))
            ->assertForbidden();
    }

    public function test_restaurant_service_cannot_access_pos_routes_even_with_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext('restaurant', 'Empresa Restaurante');
        $admin = $this->makeUser($company, $branch, 'admin@restaurant-service.test', 'admin');

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'restaurant',
            'billing_period' => 'monthly',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
        ]);

        $this->actingAs($admin)
            ->get(route('pos.index'))
            ->assertForbidden();
    }

    public function test_product_form_offers_group_mode_when_service_is_pos(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext('pos', 'Empresa POS');
        $admin = $this->makeUser($company, $branch, 'admin@product-pos.test', 'admin');

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => 'monthly',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
        ]);

        $this->actingAs($admin)
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('¿El kit maneja grupos de componentes?')
            ->assertSee('id="modifier-fields" class="mt-6 hidden"', false);
    }

    public function test_system_owner_only_sees_kit_components_from_assigned_company(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext('pos', 'Empresa principal');
        [$otherCompany] = $this->makeCompanyContext('pos', 'Empresa externa');
        $owner = $this->makeUser($company, $branch, 'owner@kit-company.test', 'system_owner');

        $ownComponent = Product::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'name' => 'Componente propio',
            'sku' => 'KIT-OWN-COMP',
            'unit' => 'g',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 1,
            'sale_price' => 2,
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]);
        $ownCategory = \App\Models\Category::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'name' => 'Categoria propia',
        ]);
        $ownTax = \App\Models\Tax::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'name' => 'Impuesto propio',
            'rate' => 10,
            'is_active' => true,
        ]);
        \App\Models\Category::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Categoria externa',
        ]);
        \App\Models\Tax::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Impuesto externo',
            'rate' => 10,
            'is_active' => true,
        ]);
        Product::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Componente externo',
            'sku' => 'KIT-OTHER-COMP',
            'unit' => 'g',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 1,
            'sale_price' => 2,
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]);

        $this->actingAs($owner)
            ->get(route('products.create'))
            ->assertOk()
            ->assertViewHas('kitComponentCandidates', function ($candidates) use ($ownComponent): bool {
                return $candidates->pluck('id')->contains($ownComponent->id)
                    && ! $candidates->pluck('sku')->contains('KIT-OTHER-COMP');
            })
            ->assertViewHas('categories', function ($categories) use ($ownCategory): bool {
                return $categories->pluck('id')->contains($ownCategory->id)
                    && ! $categories->pluck('name')->contains('Categoria externa');
            })
            ->assertViewHas('taxes', function ($taxes) use ($ownTax): bool {
                return $taxes->pluck('id')->contains($ownTax->id)
                    && ! $taxes->pluck('name')->contains('Impuesto externo');
            });

        $this->actingAs($owner)
            ->post(route('products.store'), [
                'name' => 'Kit de empresa',
                'sku' => 'KIT-COMPANY',
                'category_id' => $ownCategory->id,
                'tax_id' => $ownTax->id,
                'unit' => 'unit',
                'product_type' => Product::TYPE_KIT,
                'cost_price' => 1,
                'sale_price' => 5,
                'is_active' => 1,
                'is_visible_ecommerce' => 0,
                'kit_items' => [[
                    'component_product_id' => $ownComponent->id,
                    'quantity' => 250,
                    'component_unit' => 'g',
                    'component_unit_factor' => 1,
                ]],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'company_id' => $company->id,
            'sku' => 'KIT-COMPANY',
            'product_type' => Product::TYPE_KIT,
        ]);
    }

    public function test_pos_service_updates_shared_product_without_deleting_existing_modifier_groups(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext('pos', 'Empresa POS');
        $admin = $this->makeUser($company, $branch, 'admin@product-preserve.test', 'admin');

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'pos',
            'billing_period' => 'monthly',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
        ]);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => 'Producto mixto',
            'sku' => 'POS-MIX-001',
            'unit' => 'und',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 5,
            'sale_price' => 10,
            'is_active' => true,
            'is_visible_ecommerce' => false,
        ]);

        $group = ProductModifierGroup::query()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'name' => 'Proteina',
            'selection_type' => ProductModifierGroup::TYPE_SINGLE,
            'is_required' => true,
            'min_select' => 1,
            'max_select' => 1,
            'display_order' => 1,
        ]);

        ProductModifierOption::query()->create([
            'company_id' => $company->id,
            'product_modifier_group_id' => $group->id,
            'product_id' => null,
            'label' => 'Cerdo',
            'inventory_quantity' => null,
            'inventory_unit' => null,
            'inventory_unit_factor' => 1,
            'price_delta' => 0,
            'is_default' => true,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->actingAs($admin)
            ->put(route('products.update', $product), [
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => '',
                'image_url' => '',
                'description' => 'Actualizado desde POS',
                'category_id' => '',
                'tax_id' => '',
                'unit' => $product->unit,
                'product_type' => $product->product_type,
                'parent_product_id' => '',
                'cost_price' => $product->cost_price,
                'sale_price' => $product->sale_price,
                'is_active' => 1,
                'is_visible_ecommerce' => 1,
            ])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_visible_ecommerce' => true,
            'description' => 'Actualizado desde POS',
        ]);
        $this->assertDatabaseHas('product_modifier_groups', [
            'id' => $group->id,
            'product_id' => $product->id,
            'name' => 'Proteina',
        ]);
    }

    public function test_product_form_offers_group_mode_when_service_is_restaurant(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$company, $branch] = $this->makeCompanyContext('restaurant', 'Empresa Restaurante');
        $admin = $this->makeUser($company, $branch, 'admin@product-restaurant.test', 'admin');

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'plan_type' => 'restaurant',
            'billing_period' => 'monthly',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
        ]);

        $this->actingAs($admin)
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('¿El kit maneja grupos de componentes?')
            ->assertSee('id="modifier-fields" class="mt-6 hidden"', false);
    }

    private function makeCompanyContext(string $slug = 'pos', string $name = 'Empresa Demo', ?string $domain = null): array
    {
        $type = CompanyType::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'features' => ['sales', 'products', 'inventory'],
                'is_active' => true,
            ]
        );

        $company = Company::query()->create([
            'name' => $name,
            'domain' => $domain,
            'company_type_id' => $type->id,
            'status' => Company::STATUS_ACTIVE,
        ]);

        $branch = Branch::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'name' => 'Principal',
            'code' => 'PRN-'.$company->id,
        ]);

        return [$company, $branch];
    }

    private function makeUser(Company $company, Branch $branch, string $email, string $role): User
    {
        $user = User::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => ucfirst($role).' User',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);

        $user->assignRole($role);

        return $user;
    }
}
