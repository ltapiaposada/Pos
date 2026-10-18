<?php

namespace Tests\Feature;

use App\Models\AccountingAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingJournalEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_manage_accounting_can_create_balanced_entry(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $cashAccount = AccountingAccount::where('code', '1105')->firstOrFail();
        $incomeAccount = AccountingAccount::where('code', '4135')->firstOrFail();

        $response = $this->actingAs($user)->post(route('accounting.entries.store'), [
            'entry_date' => now()->toDateString(),
            'description' => 'Asiento de prueba',
            'lines' => [
                [
                    'accounting_account_id' => $cashAccount->id,
                    'description' => 'Debe',
                    'debit' => 100,
                    'credit' => 0,
                ],
                [
                    'accounting_account_id' => $incomeAccount->id,
                    'description' => 'Haber',
                    'debit' => 0,
                    'credit' => 100,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('journal_entries', 1);
        $this->assertDatabaseCount('journal_entry_lines', 2);
    }

    public function test_cannot_create_unbalanced_entry(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $cashAccount = AccountingAccount::where('code', '1105')->firstOrFail();
        $incomeAccount = AccountingAccount::where('code', '4135')->firstOrFail();

        $response = $this->from(route('accounting.entries.create'))
            ->actingAs($user)
            ->post(route('accounting.entries.store'), [
                'entry_date' => now()->toDateString(),
                'description' => 'Asiento descuadrado',
                'lines' => [
                    [
                        'accounting_account_id' => $cashAccount->id,
                        'description' => 'Debe',
                        'debit' => 100,
                        'credit' => 0,
                    ],
                    [
                        'accounting_account_id' => $incomeAccount->id,
                        'description' => 'Haber',
                        'debit' => 0,
                        'credit' => 80,
                    ],
                ],
            ]);

        $response->assertRedirect(route('accounting.entries.create'));
        $response->assertSessionHasErrors('lines');
        $this->assertDatabaseCount('journal_entries', 0);
        $this->assertDatabaseCount('journal_entry_lines', 0);
    }
}
