<?php

namespace Tests\Feature;

use App\Models\AccountingAccount;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingOpeningBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_opening_balances_and_system_adds_counterpart(): void
    {
        $this->seed();

        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $cash = AccountingAccount::where('code', '1105')->firstOrFail();
        $inventory = AccountingAccount::where('code', '1435')->firstOrFail();
        $equity = AccountingAccount::where('code', '31')->firstOrFail();

        $response = $this->actingAs($user)->post(route('accounting.opening-balances.store'), [
            'entry_date' => now()->toDateString(),
            'description' => 'Apertura inicial',
            'equity_account_id' => $equity->id,
            'lines' => [
                [
                    'accounting_account_id' => $cash->id,
                    'balance' => 1200.00,
                ],
                [
                    'accounting_account_id' => $inventory->id,
                    'balance' => 800.00,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('journal_entries', 1);

        $entry = JournalEntry::query()->firstOrFail();
        $this->assertStringStartsWith('SI-', $entry->entry_number);
        $this->assertDatabaseCount('journal_entry_lines', 3);

        $debit = (float) $entry->lines()->sum('debit');
        $credit = (float) $entry->lines()->sum('credit');
        $this->assertEqualsWithDelta($debit, $credit, 0.0001);
    }
}
