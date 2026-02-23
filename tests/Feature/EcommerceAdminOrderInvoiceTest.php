<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceAdminOrderInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_convert_ecommerce_order_to_invoice_and_see_it_in_sales_index(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@pos.test')->firstOrFail();
        $branch = Branch::query()->firstOrFail();
        $customer = Customer::query()->firstOrFail();

        $order = Sale::query()->create([
            'branch_id' => $branch->id,
            'user_id' => $admin->id,
            'customer_id' => $customer->id,
            'cash_register_session_id' => null,
            'sale_number' => 999001,
            'status' => Sale::STATUS_PENDING,
            'order_source' => Sale::SOURCE_ECOMMERCE,
            'subtotal' => 100,
            'discount_total' => 0,
            'tax_total' => 19,
            'shipping_total' => 0,
            'coupon_discount_total' => 0,
            'coupon_code' => null,
            'delivery_address' => 'Direccion demo',
            'customer_note' => 'Pedido web',
            'total' => 119,
            'paid_total' => 119,
            'change_total' => 0,
            'currency' => 'USD',
            'sold_at' => now(),
        ]);
        $order->payments()->create([
            'method' => 'transfer',
            'amount' => 119,
            'reference' => 'TRX-INV-001',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('ecommerce-admin.orders.invoice', $order))
            ->assertRedirect(route('ecommerce-admin.orders.index'));

        $order->refresh();
        $this->assertNotNull($order->invoiced_at);
        $this->assertSame($admin->id, (int) $order->invoiced_by_user_id);
        $this->assertNotNull($order->accounted_at);
        $this->assertSame($admin->id, (int) $order->accounted_by_user_id);
        $this->assertDatabaseCount('journal_entries', 1);
        $entry = JournalEntry::query()->first();
        $this->assertNotNull($entry);
        $this->assertStringStartsWith('VS-', $entry->entry_number);

        $this->actingAs($admin)
            ->post(route('ecommerce-admin.orders.invoice', $order))
            ->assertRedirect(route('ecommerce-admin.orders.index'));
        $this->assertDatabaseCount('journal_entries', 1);

        $this->actingAs($admin)
            ->get(route('sales.index'))
            ->assertOk()
            ->assertSee((string) $order->sale_number);
    }

    public function test_admin_can_account_an_already_invoiced_order_without_duplicate_entries(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@pos.test')->firstOrFail();
        $branch = Branch::query()->firstOrFail();
        $customer = Customer::query()->firstOrFail();

        $order = Sale::query()->create([
            'branch_id' => $branch->id,
            'user_id' => $admin->id,
            'customer_id' => $customer->id,
            'cash_register_session_id' => null,
            'sale_number' => 999002,
            'status' => Sale::STATUS_PENDING,
            'order_source' => Sale::SOURCE_ECOMMERCE,
            'subtotal' => 100,
            'discount_total' => 0,
            'tax_total' => 19,
            'shipping_total' => 0,
            'coupon_discount_total' => 0,
            'coupon_code' => null,
            'delivery_address' => 'Direccion demo 2',
            'customer_note' => 'Pedido web 2',
            'total' => 119,
            'paid_total' => 119,
            'change_total' => 0,
            'currency' => 'USD',
            'sold_at' => now(),
            'invoiced_at' => now(),
            'invoiced_by_user_id' => $admin->id,
        ]);
        $order->payments()->create([
            'method' => 'transfer',
            'amount' => 119,
            'reference' => 'TRX-INV-002',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('ecommerce-admin.orders.invoice', $order))
            ->assertRedirect(route('ecommerce-admin.orders.index'));

        $order->refresh();
        $this->assertNotNull($order->accounted_at);
        $this->assertSame($admin->id, (int) $order->accounted_by_user_id);
        $this->assertDatabaseCount('journal_entries', 1);
    }
}
