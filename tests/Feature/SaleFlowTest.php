<?php

namespace Tests\Feature;

use App\Models\CashRegisterSession;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductKitItem;
use App\Models\ProductModifierGroup;
use App\Models\ProductModifierOption;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_requires_open_cash_register(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->first();
        $customerId = Customer::where('document', 'CF')->value('id');
        $this->actingAs($user);

        $payload = [
            'branch_id' => $user->branch_id,
            'customer_id' => $customerId,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                    'unit_price' => 1.20,
                ],
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => 1.20,
                ],
            ],
        ];

        $response = $this->post(route('pos.checkout'), $payload);
        $response->assertSessionHasErrors('cash_register');
    }

    public function test_sale_creates_records_and_updates_stock(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->first();
        $customerId = Customer::where('document', 'CF')->value('id');
        CashRegisterSession::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 10,
            'status' => 'open',
        ]);

        $this->actingAs($user);

        $payload = [
            'branch_id' => $user->branch_id,
            'customer_id' => $customerId,
            'items' => json_encode([
                [
                    'product_id' => 1,
                    'quantity' => 2,
                    'unit_price' => 1.20,
                ],
            ]),
            'payments' => json_encode([
                [
                    'method' => 'cash',
                    'amount' => 2.78,
                ],
            ]),
        ];

        $response = $this->post(route('pos.checkout'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseCount('sale_items', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('journal_entries', 1);

        $inventory = Inventory::where('branch_id', $user->branch_id)->where('product_id', 1)->first();
        $this->assertNotNull($inventory);
        $this->assertEquals(98.000, (float) $inventory->stock);

        $entry = JournalEntry::first();
        $this->assertNotNull($entry);
        $this->assertStringStartsWith('VS-', $entry->entry_number);

        $sale = Sale::query()->firstOrFail();
        $this->get(route('sales.show', $sale))
            ->assertOk()
            ->assertSee('Detalle de venta y pagos');
    }

    public function test_sale_of_kit_decreases_component_stock(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->first();
        $customerId = Customer::where('document', 'CF')->value('id');
        CashRegisterSession::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 10,
            'status' => 'open',
        ]);

        $component = Product::create([
            'name' => 'Componente Kit Test',
            'sku' => 'COMP-KIT-TEST',
            'unit' => 'unit',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 2,
            'sale_price' => 4,
            'is_active' => true,
        ]);

        $kit = Product::create([
            'name' => 'Kit Test',
            'sku' => 'KIT-TEST',
            'unit' => 'unit',
            'product_type' => Product::TYPE_KIT,
            'cost_price' => 0,
            'sale_price' => 15,
            'is_active' => true,
        ]);

        ProductKitItem::create([
            'kit_product_id' => $kit->id,
            'component_product_id' => $component->id,
            'quantity' => 2,
        ]);

        Inventory::updateOrCreate(
            ['branch_id' => $user->branch_id, 'product_id' => $component->id],
            ['stock' => 20, 'min_stock' => 0]
        );

        $this->actingAs($user);

        $payload = [
            'branch_id' => $user->branch_id,
            'customer_id' => $customerId,
            'items' => [
                [
                    'product_id' => $kit->id,
                    'quantity' => 3,
                    'unit_price' => 15,
                ],
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => 45,
                ],
            ],
        ];

        $response = $this->post(route('pos.checkout'), $payload);
        $response->assertRedirect();

        $componentInventory = Inventory::where('branch_id', $user->branch_id)->where('product_id', $component->id)->first();
        $this->assertNotNull($componentInventory);
        $this->assertEquals(14.000, (float) $componentInventory->stock);
    }

    public function test_sale_of_grouped_kit_decreases_selected_component_stock(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $customerId = Customer::where('document', 'CF')->value('id');
        CashRegisterSession::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 10,
            'status' => 'open',
        ]);

        $component = Product::create([
            'name' => 'Carne seleccionable',
            'sku' => 'GROUP-SALE-COMP',
            'unit' => 'g',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 2,
            'sale_price' => 4,
            'is_active' => true,
        ]);
        $kit = Product::create([
            'name' => 'Kit seleccionable',
            'sku' => 'GROUP-SALE-KIT',
            'unit' => 'unit',
            'product_type' => Product::TYPE_KIT,
            'uses_component_groups' => true,
            'cost_price' => 0,
            'sale_price' => 15,
            'is_active' => true,
        ]);
        $group = ProductModifierGroup::create([
            'product_id' => $kit->id,
            'name' => 'Proteina',
            'selection_type' => ProductModifierGroup::TYPE_SINGLE,
            'is_required' => true,
            'min_select' => 1,
            'max_select' => 1,
        ]);
        $option = ProductModifierOption::create([
            'product_modifier_group_id' => $group->id,
            'product_id' => $component->id,
            'inventory_quantity' => 250,
            'inventory_unit' => 'g',
            'inventory_unit_factor' => 1,
            'label' => 'Carne',
            'price_delta' => 0,
            'is_default' => true,
            'is_active' => true,
        ]);
        Inventory::updateOrCreate(
            ['branch_id' => $user->branch_id, 'product_id' => $component->id],
            ['stock' => 1000, 'min_stock' => 0]
        );

        $this->actingAs($user)
            ->post(route('pos.checkout'), [
                'branch_id' => $user->branch_id,
                'customer_id' => $customerId,
                'items' => [[
                    'product_id' => $kit->id,
                    'quantity' => 2,
                    'unit_price' => 15,
                    'modifier_selections' => [[
                        'group_id' => $group->id,
                        'option_id' => $option->id,
                        'action' => 'include',
                    ]],
                ]],
                'payments' => [['method' => 'cash', 'amount' => 30]],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertEquals(
            500.0,
            (float) Inventory::where('branch_id', $user->branch_id)
                ->where('product_id', $component->id)
                ->value('stock')
        );
    }

    public function test_sale_can_be_registered_on_credit(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $customerId = Customer::where('document', 'CF')->value('id');

        CashRegisterSession::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 10,
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->post(route('pos.checkout'), [
            'branch_id' => $user->branch_id,
            'customer_id' => $customerId,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 2,
                    'unit_price' => 1.20,
                ],
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => 1.00,
                ],
                [
                    'method' => 'credit',
                    'amount' => 1.78,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('sales', 1);

        $sale = Sale::query()->firstOrFail();
        $this->assertSame(Sale::STATUS_PENDING, $sale->status);
        $this->assertEquals(1.00, (float) $sale->paid_total);
        $this->assertEquals(0.00, (float) $sale->change_total);
        $this->assertDatabaseCount('payments', 2);

        $entry = JournalEntry::query()->firstOrFail();
        $receivableLine = JournalEntryLine::query()
            ->where('journal_entry_id', $entry->id)
            ->whereHas('account', fn ($q) => $q->where('code', '1305'))
            ->first();

        $this->assertNotNull($receivableLine);
        $this->assertEquals(round((float) $sale->total - 1.00, 2), round((float) $receivableLine->debit, 2));
    }

    public function test_pos_products_endpoint_only_returns_products_with_available_stock(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();

        $soldOut = Product::create([
            'name' => 'Agotado POS',
            'sku' => 'AGOTADO-POS',
            'unit' => 'unit',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 1,
            'sale_price' => 2,
            'is_active' => true,
        ]);

        $rawChicken = Product::create([
            'name' => 'Pollo crudo',
            'sku' => 'POLLO-CRUDO',
            'unit' => 'kg',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 10,
            'sale_price' => 12,
            'is_active' => true,
        ]);

        $dish = Product::create([
            'name' => 'Plato de pollo 250g',
            'sku' => 'PLATO-POLLO-250',
            'unit' => 'plato',
            'product_type' => Product::TYPE_KIT,
            'cost_price' => 0,
            'sale_price' => 18,
            'is_active' => true,
        ]);

        ProductKitItem::create([
            'kit_product_id' => $dish->id,
            'component_product_id' => $rawChicken->id,
            'quantity' => 250,
            'component_unit' => 'g',
            'component_unit_factor' => 0.001,
        ]);

        Inventory::updateOrCreate(
            ['branch_id' => $user->branch_id, 'product_id' => $rawChicken->id],
            ['stock' => 1, 'min_stock' => 0]
        );

        Inventory::updateOrCreate(
            ['branch_id' => $user->branch_id, 'product_id' => $soldOut->id],
            ['stock' => 0, 'min_stock' => 0]
        );

        $response = $this->actingAs($user)->getJson(route('pos.products', [
            'branch_id' => $user->branch_id,
            'q' => 'PLATO',
        ]));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $dish->id,
                'name' => 'Plato de pollo 250g',
                'available_stock' => 4.0,
            ]);

        $response->assertJsonMissing([
            'id' => $soldOut->id,
        ]);
    }

    public function test_commercial_pound_automatically_converts_grams_for_kit_sales(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $customerId = Customer::where('document', 'CF')->value('id');

        CashRegisterSession::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 10,
            'status' => 'open',
        ]);

        $ingredient = Product::create([
            'name' => 'Harina por libra',
            'sku' => 'HARINA-LB',
            'unit' => 'libra',
            'product_type' => Product::TYPE_SIMPLE,
            'cost_price' => 2,
            'sale_price' => 3,
            'is_active' => true,
        ]);

        $kit = Product::create([
            'name' => 'Producto de harina 250 g',
            'sku' => 'KIT-HARINA-250',
            'unit' => 'unit',
            'product_type' => Product::TYPE_KIT,
            'cost_price' => 0,
            'sale_price' => 5,
            'is_active' => true,
        ]);

        ProductKitItem::create([
            'kit_product_id' => $kit->id,
            'component_product_id' => $ingredient->id,
            'quantity' => 250,
            'component_unit' => 'g',
            'component_unit_factor' => 1,
        ]);

        Inventory::updateOrCreate(
            ['branch_id' => $user->branch_id, 'product_id' => $ingredient->id],
            ['stock' => 1, 'min_stock' => 0]
        );

        $this->actingAs($user)
            ->post(route('pos.checkout'), [
                'branch_id' => $user->branch_id,
                'customer_id' => $customerId,
                'items' => [[
                    'product_id' => $kit->id,
                    'quantity' => 1,
                    'unit_price' => 5,
                ]],
                'payments' => [[
                    'method' => 'cash',
                    'amount' => 5,
                ]],
            ])
            ->assertRedirect();

        $this->assertEquals(
            0.5,
            (float) Inventory::where('branch_id', $user->branch_id)
                ->where('product_id', $ingredient->id)
                ->value('stock')
        );
    }

    public function test_inventory_adjustment_cannot_remove_more_than_available_stock(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();

        Inventory::updateOrCreate(
            ['branch_id' => $user->branch_id, 'product_id' => 1],
            ['stock' => 2, 'min_stock' => 0]
        );

        $response = $this->actingAs($user)->post(route('inventory.adjust'), [
            'branch_id' => $user->branch_id,
            'product_id' => 1,
            'type' => 'OUT',
            'quantity' => 3,
        ]);

        $response->assertSessionHasErrors('quantity');
    }
}
