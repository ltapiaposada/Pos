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
                    'amount' => 3.00,
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
                    'amount' => 10.00,
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
}
