<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CashRegisterSession;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\CompanyType;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductModifierGroup;
use App\Models\ProductModifierOption;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\RestaurantOrderItemSelection;
use App\Models\RestaurantTable;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RestaurantModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_table_order_flow_reuses_existing_sale_stack(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $this->markUserCompanyAsRestaurant($user);
        $customerId = Customer::where('document', 'CF')->value('id');

        CashRegisterSession::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 25,
            'status' => 'open',
        ]);

        $table = RestaurantTable::create([
            'branch_id' => $user->branch_id,
            'name' => 'Mesa terraza',
            'number' => 'T-01',
            'capacity' => 4,
            'status' => RestaurantTable::STATUS_AVAILABLE,
            'location' => 'Terraza',
            'is_active' => true,
        ]);

        $product = Product::query()->findOrFail(1);
        $proteinProduct = Product::query()->findOrFail(2);
        Inventory::updateOrCreate(
            ['branch_id' => $user->branch_id, 'product_id' => $proteinProduct->id],
            ['company_id' => $user->company_id, 'stock' => 40, 'min_stock' => 0]
        );
        $proteinGroup = $product->modifierGroups()->create([
            'name' => 'Proteina',
            'selection_type' => ProductModifierGroup::TYPE_SINGLE,
            'is_required' => true,
            'min_select' => 1,
            'max_select' => 1,
        ]);
        $proteinOption = $proteinGroup->options()->create([
            'product_id' => $proteinProduct->id,
            'inventory_quantity' => 1,
            'inventory_unit' => $proteinProduct->unit,
            'inventory_unit_factor' => 1,
            'label' => 'Carne',
            'price_delta' => 0,
            'is_default' => true,
            'is_active' => true,
        ]);
        $removeGroup = $product->modifierGroups()->create([
            'name' => 'Ingredientes removibles',
            'selection_type' => ProductModifierGroup::TYPE_REMOVE,
            'is_required' => false,
            'min_select' => 0,
            'max_select' => 0,
        ]);
        $onionOption = $removeGroup->options()->create([
            'label' => 'Cebolla',
            'price_delta' => 0,
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('restaurant.orders.store'), [
                'branch_id' => $user->branch_id,
                'restaurant_table_id' => $table->id,
                'customer_id' => $customerId,
                'order_type' => RestaurantOrder::TYPE_DINE_IN,
            ])
            ->assertRedirect();

        $order = RestaurantOrder::query()->firstOrFail();

        $this->actingAs($user)
            ->put(route('restaurant.orders.update', $order), [
                'branch_id' => $user->branch_id,
                'restaurant_table_id' => $table->id,
                'customer_id' => $customerId,
                'order_type' => RestaurantOrder::TYPE_DINE_IN,
                'notes' => 'Sin hielo',
                'items' => json_encode([
                    [
                        'product_id' => 1,
                        'quantity' => 2,
                        'unit_price' => 1.20,
                        'notes' => 'Poco hielo',
                        'modifier_selections' => [
                            [
                                'group_id' => $proteinGroup->id,
                                'option_id' => $proteinOption->id,
                                'action' => 'include',
                            ],
                            [
                                'group_id' => $removeGroup->id,
                                'option_id' => $onionOption->id,
                                'action' => 'remove',
                            ],
                        ],
                    ],
                ]),
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertEquals(RestaurantTable::STATUS_OCCUPIED, $table->fresh()->status);
        $this->assertDatabaseCount('restaurant_order_items', 1);
        $this->assertDatabaseCount('restaurant_order_item_selections', 2);

        $this->assertDatabaseHas('restaurant_order_item_selections', [
            'group_name' => 'Proteina',
            'option_label' => 'Carne',
            'selection_action' => RestaurantOrderItemSelection::ACTION_INCLUDE,
        ]);
        $this->assertDatabaseHas('restaurant_order_item_selections', [
            'group_name' => 'Ingredientes removibles',
            'option_label' => 'Cebolla',
            'selection_action' => RestaurantOrderItemSelection::ACTION_REMOVE,
        ]);

        $inventoryBeforeKitchen = (float) Inventory::where('branch_id', $user->branch_id)
            ->where('product_id', 1)
            ->value('stock');
        $proteinInventoryBeforeKitchen = (float) Inventory::where('branch_id', $user->branch_id)
            ->where('product_id', $proteinProduct->id)
            ->value('stock');

        $this->actingAs($user)
            ->post(route('restaurant.orders.send-to-kitchen', $order))
            ->assertRedirect();

        $this->assertSame(RestaurantOrder::STATUS_SENT_TO_KITCHEN, $order->fresh()->status);
        $this->assertEquals($inventoryBeforeKitchen, (float) Inventory::where('branch_id', $user->branch_id)->where('product_id', 1)->value('stock'));
        $this->assertEquals($proteinInventoryBeforeKitchen, (float) Inventory::where('branch_id', $user->branch_id)->where('product_id', $proteinProduct->id)->value('stock'));

        $item = RestaurantOrderItem::query()->firstOrFail();

        $this->actingAs($user)
            ->patch(route('restaurant.kitchen.items.status', $item), [
                'kitchen_status' => RestaurantOrderItem::STATUS_IN_PREPARATION,
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->patch(route('restaurant.kitchen.items.status', $item->fresh()), [
                'kitchen_status' => RestaurantOrderItem::STATUS_READY,
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame(RestaurantOrder::STATUS_READY, $order->status);

        $this->actingAs($user)
            ->post(route('restaurant.orders.convert-to-sale', $order), [
                'customer_id' => $customerId,
                'payments' => json_encode([
                    [
                        'method' => 'cash',
                        'amount' => (float) $order->total,
                    ],
                ]),
            ])
            ->assertRedirect();

        $order->refresh();
        $sale = Sale::query()->firstOrFail();
        $inventoryAfterSale = (float) Inventory::where('branch_id', $user->branch_id)
            ->where('product_id', 1)
            ->value('stock');
        $proteinInventoryAfterSale = (float) Inventory::where('branch_id', $user->branch_id)
            ->where('product_id', $proteinProduct->id)
            ->value('stock');

        $this->assertSame(Sale::SOURCE_RESTAURANT, $sale->order_source);
        $this->assertNotNull($order->sale_id);
        $this->assertSame(RestaurantOrder::STATUS_CLOSED, $order->status);
        $this->assertEquals($inventoryBeforeKitchen - 2, $inventoryAfterSale);
        $this->assertEquals($proteinInventoryBeforeKitchen - 2, $proteinInventoryAfterSale);
        $this->assertEquals(RestaurantTable::STATUS_AVAILABLE, $table->fresh()->status);
    }

    public function test_restaurant_modifier_selection_consumes_related_inventory_with_unit_conversion(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $this->markUserCompanyAsRestaurant($user);
        $customerId = Customer::where('document', 'CF')->value('id');

        CashRegisterSession::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 25,
            'status' => 'open',
        ]);

        $menuProduct = Product::query()->findOrFail(1);
        $meatProduct = Product::query()->findOrFail(2);
        $meatProduct->update(['unit' => 'kg']);

        Inventory::updateOrCreate(
            ['branch_id' => $user->branch_id, 'product_id' => $meatProduct->id],
            ['company_id' => $user->company_id, 'stock' => 10, 'min_stock' => 0]
        );

        $group = $menuProduct->modifierGroups()->create([
            'name' => 'Porcion de carne',
            'selection_type' => ProductModifierGroup::TYPE_SINGLE,
            'is_required' => true,
            'min_select' => 1,
            'max_select' => 1,
        ]);

        $option = $group->options()->create([
            'product_id' => $meatProduct->id,
            'inventory_quantity' => 300,
            'inventory_unit' => 'g',
            'inventory_unit_factor' => 0.001,
            'label' => 'Carne 300 g',
            'price_delta' => 0,
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('restaurant.orders.store'), [
                'branch_id' => $user->branch_id,
                'customer_id' => $customerId,
                'order_type' => RestaurantOrder::TYPE_TAKEAWAY,
            ])
            ->assertRedirect();

        $order = RestaurantOrder::query()->firstOrFail();

        $this->actingAs($user)
            ->put(route('restaurant.orders.update', $order), [
                'branch_id' => $user->branch_id,
                'customer_id' => $customerId,
                'order_type' => RestaurantOrder::TYPE_TAKEAWAY,
                'items' => json_encode([
                    [
                        'product_id' => $menuProduct->id,
                        'quantity' => 2,
                        'unit_price' => 1.20,
                        'modifier_selections' => [
                            [
                                'group_id' => $group->id,
                                'option_id' => $option->id,
                                'action' => 'include',
                            ],
                        ],
                    ],
                ]),
            ])
            ->assertRedirect();

        $meatStockBeforeSale = (float) Inventory::where('branch_id', $user->branch_id)
            ->where('product_id', $meatProduct->id)
            ->value('stock');

        $this->actingAs($user)
            ->post(route('restaurant.orders.convert-to-sale', $order->fresh()), [
                'customer_id' => $customerId,
                'payments' => json_encode([
                    ['method' => 'cash', 'amount' => (float) $order->fresh()->total],
                ]),
            ])
            ->assertRedirect();

        $meatStockAfterSale = (float) Inventory::where('branch_id', $user->branch_id)
            ->where('product_id', $meatProduct->id)
            ->value('stock');

        $this->assertEquals(9.4, round($meatStockAfterSale, 3));
        $this->assertEquals(0.6, round($meatStockBeforeSale - $meatStockAfterSale, 3));
    }

    public function test_restaurant_order_is_not_converted_twice(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $this->markUserCompanyAsRestaurant($user);
        $customerId = Customer::where('document', 'CF')->value('id');

        CashRegisterSession::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 25,
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->post(route('restaurant.orders.store'), [
                'branch_id' => $user->branch_id,
                'customer_id' => $customerId,
                'order_type' => RestaurantOrder::TYPE_TAKEAWAY,
            ])
            ->assertRedirect();

        $order = RestaurantOrder::query()->firstOrFail();

        $this->actingAs($user)
            ->put(route('restaurant.orders.update', $order), [
                'branch_id' => $user->branch_id,
                'customer_id' => $customerId,
                'order_type' => RestaurantOrder::TYPE_TAKEAWAY,
                'items' => json_encode([
                    [
                        'product_id' => 1,
                        'quantity' => 1,
                        'unit_price' => 1.20,
                        'notes' => 'Sin azúcar',
                    ],
                ]),
            ])
            ->assertRedirect();

        $order->refresh();

        $this->actingAs($user)
            ->post(route('restaurant.orders.convert-to-sale', $order), [
                'customer_id' => $customerId,
                'payments' => json_encode([
                    ['method' => 'cash', 'amount' => (float) $order->total],
                ]),
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('restaurant.orders.convert-to-sale', $order->fresh()), [
                'customer_id' => $customerId,
                'payments' => json_encode([
                    ['method' => 'cash', 'amount' => (float) $order->fresh()->total],
                ]),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_takeaway_order_can_be_created_without_table(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $this->markUserCompanyAsRestaurant($user);

        $this->actingAs($user)
            ->post(route('restaurant.orders.store'), [
                'branch_id' => $user->branch_id,
                'order_type' => RestaurantOrder::TYPE_TAKEAWAY,
            ])
            ->assertRedirect();

        $order = RestaurantOrder::query()->firstOrFail();
        $this->assertNull($order->restaurant_table_id);
        $this->assertSame(RestaurantOrder::TYPE_TAKEAWAY, $order->order_type);
    }

    public function test_pos_company_cannot_access_restaurant_routes_even_with_permissions(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();

        $this->actingAs($user)
            ->get(route('restaurant.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('restaurant.kitchen.index'))
            ->assertForbidden();
    }

    public function test_restaurant_company_cannot_access_classic_pos_but_keeps_sales_returns_and_purchases(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $this->markUserCompanyAsRestaurant($user);

        $this->actingAs($user)
            ->get(route('pos.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('sales.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('returns.create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('purchases.index'))
            ->assertOk();
    }

    public function test_restaurant_module_respects_company_scope(): void
    {
        $this->seed(RolePermissionSeeder::class);
        [$companyA, $branchA] = $this->makeCompanyContext('restaurant', 'Empresa A');
        [$companyB, $branchB] = $this->makeCompanyContext('restaurant', 'Empresa B');
        $userA = $this->makeUser($companyA, $branchA, 'waiter@empresa-a.test', 'cashier');

        CompanySubscription::withoutGlobalScopes()->create([
            'company_id' => $companyA->id,
            'plan_type' => 'restaurant',
            'billing_period' => 'monthly',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(15)->toDateString(),
            'status' => CompanySubscription::STATUS_ACTIVE,
            'payment_status' => 'paid',
        ]);

        $foreignTable = RestaurantTable::withoutGlobalScopes()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'name' => 'Mesa ajena',
            'number' => 'B-01',
            'capacity' => 4,
            'status' => RestaurantTable::STATUS_AVAILABLE,
            'is_active' => true,
        ]);

        $foreignCustomer = Customer::withoutGlobalScopes()->create([
            'company_id' => $companyB->id,
            'name' => 'Cliente B',
            'document' => 'CLI-B',
            'is_active' => true,
        ]);

        $this->actingAs($userA)
            ->post(route('restaurant.orders.store'), [
                'branch_id' => $branchA->id,
                'restaurant_table_id' => $foreignTable->id,
                'customer_id' => $foreignCustomer->id,
                'order_type' => RestaurantOrder::TYPE_DINE_IN,
            ])
            ->assertSessionHasErrors(['restaurant_table_id', 'customer_id']);
    }

    public function test_restaurant_products_endpoint_only_returns_products_with_available_stock(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $this->markUserCompanyAsRestaurant($user);

        $soldOut = Product::create([
            'name' => 'Agotado restaurante',
            'sku' => 'AGOTADO-REST',
            'unit' => 'unit',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 1,
            'sale_price' => 2,
            'is_active' => true,
        ]);

        Inventory::updateOrCreate(
            ['branch_id' => $user->branch_id, 'product_id' => $soldOut->id],
            ['company_id' => $user->company_id, 'stock' => 0, 'min_stock' => 0]
        );

        $menuProduct = Product::query()->findOrFail(1);

        $response = $this->actingAs($user)->getJson(route('restaurant.products', [
            'branch_id' => $user->branch_id,
            'q' => $menuProduct->name,
        ]));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $menuProduct->id,
                'name' => $menuProduct->name,
            ])
            ->assertJsonMissing([
                'id' => $soldOut->id,
            ]);
    }

    private function makeCompanyContext(string $slug, string $name): array
    {
        $type = CompanyType::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'features' => ['sales', 'products', 'inventory', 'restaurant'],
                'is_active' => true,
            ]
        );

        $company = Company::query()->create([
            'name' => $name,
            'company_type_id' => $type->id,
            'status' => Company::STATUS_ACTIVE,
        ]);

        $branch = Branch::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'name' => 'Principal',
            'code' => 'BR-'.$company->id,
        ]);

        return [$company, $branch];
    }

    private function markUserCompanyAsRestaurant(User $user): void
    {
        $type = CompanyType::query()->firstOrCreate(
            ['slug' => 'restaurant'],
            [
                'name' => 'Restaurante',
                'features' => ['sales', 'products', 'inventory', 'restaurant'],
                'is_active' => true,
            ]
        );

        $user->company()->update([
            'company_type_id' => $type->id,
        ]);

        CompanySubscription::withoutGlobalScopes()
            ->where('company_id', $user->company_id)
            ->whereIn('status', [
                CompanySubscription::STATUS_ACTIVE,
                CompanySubscription::STATUS_PENDING_PAYMENT,
            ])
            ->update([
                'plan_type' => 'restaurant',
                'status' => CompanySubscription::STATUS_ACTIVE,
                'payment_status' => CompanySubscription::PAYMENT_STATUS_PAID,
            ]);

        $user->unsetRelation('company');
        $user->refresh();
    }

    private function makeUser(Company $company, Branch $branch, string $email, string $role): User
    {
        $user = User::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Restaurant User',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);

        $user->assignRole($role);

        return $user;
    }
}
