<?php

namespace Tests\Feature;

use App\Models\CashRegisterSession;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingCreditControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_receivable_collection_updates_sale_balance_and_posts_accounting(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $customerId = Customer::where('document', 'CF')->value('id');

        CashRegisterSession::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 20,
            'status' => 'open',
        ]);

        $this->actingAs($user)->post(route('pos.checkout'), [
            'branch_id' => $user->branch_id,
            'customer_id' => $customerId,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                    'unit_price' => 10,
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 2],
                ['method' => 'credit', 'amount' => 20],
            ],
        ])->assertRedirect();

        $sale = Sale::query()->firstOrFail();
        $balance = round((float) $sale->total - (float) $sale->paid_total, 2);
        $this->assertGreaterThan(0, $balance);

        $this->actingAs($user)->post(route('accounting.receivables.collect', $sale), [
            'amount' => $balance,
            'method' => 'transfer',
            'reference' => 'TRX-100',
        ])->assertRedirect(route('accounting.receivables.index'));

        $sale->refresh();
        $this->assertSame(Sale::STATUS_PAID, $sale->status);
        $this->assertEquals(round((float) $sale->total, 2), round((float) $sale->paid_total, 2));
        $this->assertDatabaseHas('payments', [
            'sale_id' => $sale->id,
            'method' => 'transfer',
            'reference' => 'TRX-100',
        ]);
        $this->assertDatabaseCount('journal_entries', 2);

        $collectionEntry = JournalEntry::query()
            ->where('entry_number', 'like', 'RC-%')
            ->first();
        $this->assertNotNull($collectionEntry);
        $this->assertTrue(JournalEntryLine::query()
            ->where('journal_entry_id', $collectionEntry->id)
            ->whereHas('account', fn ($q) => $q->where('code', '1110'))
            ->where('debit', $balance)
            ->exists());
        $this->assertTrue(JournalEntryLine::query()
            ->where('journal_entry_id', $collectionEntry->id)
            ->whereHas('account', fn ($q) => $q->where('code', '1305'))
            ->where('credit', $balance)
            ->exists());
    }

    public function test_payable_payment_updates_purchase_balance_and_posts_accounting(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();

        $this->actingAs($user)->post(route('purchases.store'), [
            'branch_id' => $user->branch_id,
            'supplier_name' => 'Proveedor CxP',
            'payment_method' => 'credit',
            'paid_total' => 0,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                    'unit_cost' => 20,
                ],
            ],
        ])->assertRedirect();

        $purchase = Purchase::query()->firstOrFail();
        $initialBalance = (float) $purchase->balance_total;
        $this->assertGreaterThan(0, $initialBalance);

        $this->actingAs($user)->post(route('accounting.payables.pay', $purchase), [
            'amount' => 5,
            'method' => 'transfer',
            'reference' => 'PAGO-9',
        ])->assertRedirect(route('accounting.payables.index'));

        $purchase->refresh();
        $this->assertEquals(round($initialBalance - 5, 2), round((float) $purchase->balance_total, 2));
        $this->assertDatabaseHas('purchase_payments', [
            'purchase_id' => $purchase->id,
            'method' => 'transfer',
            'reference' => 'PAGO-9',
        ]);
        $this->assertEquals(1, PurchasePayment::query()->count());
        $this->assertDatabaseCount('journal_entries', 2);

        $paymentEntry = JournalEntry::query()
            ->where('entry_number', 'like', 'PG-%')
            ->first();
        $this->assertNotNull($paymentEntry);
        $this->assertTrue(JournalEntryLine::query()
            ->where('journal_entry_id', $paymentEntry->id)
            ->whereHas('account', fn ($q) => $q->where('code', '2205'))
            ->where('debit', 5.00)
            ->exists());
        $this->assertTrue(JournalEntryLine::query()
            ->where('journal_entry_id', $paymentEntry->id)
            ->whereHas('account', fn ($q) => $q->where('code', '1110'))
            ->where('credit', 5.00)
            ->exists());
    }

    public function test_receivable_collection_can_be_voided_and_reopens_balance(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $customerId = Customer::where('document', 'CF')->value('id');

        CashRegisterSession::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 20,
            'status' => 'open',
        ]);

        $this->actingAs($user)->post(route('pos.checkout'), [
            'branch_id' => $user->branch_id,
            'customer_id' => $customerId,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                    'unit_price' => 10,
                ],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 2],
                ['method' => 'credit', 'amount' => 20],
            ],
        ])->assertRedirect();

        $sale = Sale::query()->firstOrFail();
        $amount = 5.00;
        $this->actingAs($user)->post(route('accounting.receivables.collect', $sale), [
            'amount' => $amount,
            'method' => 'transfer',
            'reference' => 'TRX-VOID-1',
        ])->assertRedirect(route('accounting.receivables.index'));

        $payment = $sale->payments()->where('reference', 'TRX-VOID-1')->firstOrFail();

        $this->actingAs($user)->post(route('accounting.receivables.payments.void', [$sale, $payment]), [
            'reason' => 'Error de recaudo',
        ])->assertRedirect(route('accounting.receivables.index'));

        $sale->refresh();
        $payment->refresh();

        $this->assertNotNull($payment->voided_at);
        $this->assertEquals($user->id, $payment->voided_by_user_id);
        $this->assertSame('Error de recaudo', $payment->void_reason);

        $expectedPaid = (float) $sale->payments()->whereNull('voided_at')->sum('amount');
        $this->assertEquals(round($expectedPaid, 2), round((float) $sale->paid_total, 2));
        $expectedStatus = round((float) $sale->total - $expectedPaid, 2) > 0
            ? Sale::STATUS_PENDING
            : Sale::STATUS_PAID;
        $this->assertSame($expectedStatus, $sale->status);

        $voidEntry = JournalEntry::query()
            ->where('entry_number', 'like', 'ARC-%')
            ->first();
        $this->assertNotNull($voidEntry);
        $this->assertTrue(JournalEntryLine::query()
            ->where('journal_entry_id', $voidEntry->id)
            ->whereHas('account', fn ($q) => $q->where('code', '1305'))
            ->where('debit', $amount)
            ->exists());
        $this->assertTrue(JournalEntryLine::query()
            ->where('journal_entry_id', $voidEntry->id)
            ->whereHas('account', fn ($q) => $q->where('code', '1110'))
            ->where('credit', $amount)
            ->exists());
    }

    public function test_payable_payment_can_be_voided_and_reopens_balance(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();

        $this->actingAs($user)->post(route('purchases.store'), [
            'branch_id' => $user->branch_id,
            'supplier_name' => 'Proveedor CxP',
            'payment_method' => 'credit',
            'paid_total' => 0,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                    'unit_cost' => 20,
                ],
            ],
        ])->assertRedirect();

        $purchase = Purchase::query()->firstOrFail();
        $amount = 5.00;
        $this->actingAs($user)->post(route('accounting.payables.pay', $purchase), [
            'amount' => $amount,
            'method' => 'transfer',
            'reference' => 'PAGO-VOID-1',
        ])->assertRedirect(route('accounting.payables.index'));

        $payment = PurchasePayment::query()->where('reference', 'PAGO-VOID-1')->firstOrFail();

        $this->actingAs($user)->post(route('accounting.payables.payments.void', [$purchase, $payment]), [
            'reason' => 'Pago duplicado',
        ])->assertRedirect(route('accounting.payables.index'));

        $purchase->refresh();
        $payment->refresh();

        $this->assertNotNull($payment->voided_at);
        $this->assertEquals($user->id, $payment->voided_by_user_id);
        $this->assertSame('Pago duplicado', $payment->void_reason);

        $expectedPaid = (float) PurchasePayment::query()
            ->where('purchase_id', $purchase->id)
            ->whereNull('voided_at')
            ->sum('amount');
        $this->assertEquals(round($expectedPaid, 2), round((float) $purchase->paid_total, 2));
        $this->assertEquals(round((float) $purchase->total - $expectedPaid, 2), round((float) $purchase->balance_total, 2));

        $voidEntry = JournalEntry::query()
            ->where('entry_number', 'like', 'APG-%')
            ->first();
        $this->assertNotNull($voidEntry);
        $this->assertTrue(JournalEntryLine::query()
            ->where('journal_entry_id', $voidEntry->id)
            ->whereHas('account', fn ($q) => $q->where('code', '1110'))
            ->where('debit', $amount)
            ->exists());
        $this->assertTrue(JournalEntryLine::query()
            ->where('journal_entry_id', $voidEntry->id)
            ->whereHas('account', fn ($q) => $q->where('code', '2205'))
            ->where('credit', $amount)
            ->exists());
    }
}
