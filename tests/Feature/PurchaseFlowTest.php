<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_creates_records_updates_inventory_and_posts_accounting(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $product = Product::query()->where('is_active', true)->firstOrFail();

        $initialStock = (float) Inventory::query()
            ->where('branch_id', $user->branch_id)
            ->where('product_id', $product->id)
            ->value('stock');

        $response = $this->actingAs($user)->post(route('purchases.store'), [
            'branch_id' => $user->branch_id,
            'supplier_name' => 'Proveedor Demo',
            'supplier_document' => '900123456',
            'invoice_number' => 'FAC-001',
            'payment_method' => 'cash',
            'paid_total' => 9999,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_cost' => 7.5,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('purchase_items', 1);
        $this->assertDatabaseCount('journal_entries', 1);

        $purchase = Purchase::query()->firstOrFail();
        $this->assertEquals('Proveedor Demo', $purchase->supplier_name);

        $updatedStock = (float) Inventory::query()
            ->where('branch_id', $user->branch_id)
            ->where('product_id', $product->id)
            ->value('stock');
        $this->assertEquals($initialStock + 5.0, $updatedStock);

        $entry = JournalEntry::query()->firstOrFail();
        $this->assertStringStartsWith('CP-', $entry->entry_number);
    }

    public function test_purchase_can_be_registered_on_credit(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $product = Product::query()->where('is_active', true)->firstOrFail();

        $response = $this->actingAs($user)->post(route('purchases.store'), [
            'branch_id' => $user->branch_id,
            'supplier_name' => 'Proveedor Credito',
            'payment_method' => 'credit',
            'paid_total' => 0,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_cost' => 10,
                ],
            ],
        ]);

        $response->assertRedirect();
        $purchase = Purchase::query()->firstOrFail();

        $this->assertEquals(0.00, (float) $purchase->paid_total);
        $this->assertGreaterThan(0, (float) $purchase->balance_total);
    }

    public function test_purchase_can_use_contact_as_supplier(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $contact = Customer::query()->create([
            'name' => 'Proveedor Test',
            'document' => 'NIT-T-001',
            'contact_type' => Customer::TYPE_SUPPLIER,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('purchases.store'), [
            'branch_id' => $user->branch_id,
            'contact_id' => $contact->id,
            'supplier_name' => 'Temporal',
            'supplier_document' => 'TMP-1',
            'payment_method' => 'cash',
            'paid_total' => 999,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_cost' => 10,
                ],
            ],
        ]);

        $response->assertRedirect();
        $purchase = Purchase::query()->firstOrFail();

        $this->assertEquals($contact->name, $purchase->supplier_name);
        $this->assertEquals($contact->document, $purchase->supplier_document);
    }
}
