<?php

namespace Tests\Feature;

use App\Models\AccountingAccount;
use App\Models\CashRegisterSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_expense_creates_journal_entry_and_cash_movement(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $expenseAccount = AccountingAccount::where('code', '5135')->firstOrFail();

        CashRegisterSession::query()->create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 50,
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->post(route('accounting.expenses.store'), [
            'expense_date' => now()->toDateString(),
            'expense_account_id' => $expenseAccount->id,
            'payment_method' => 'cash',
            'branch_id' => $user->branch_id,
            'amount' => 25.40,
            'description' => 'Pago servicio internet',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('journal_entries', 1);
        $this->assertDatabaseCount('journal_entry_lines', 2);
        $this->assertDatabaseHas('cash_movements', [
            'type' => 'OUT',
            'amount' => 25.40,
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'reason' => 'Gasto: Pago servicio internet',
        ]);
    }

    public function test_cash_expense_requires_open_cash_register_session(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();
        $expenseAccount = AccountingAccount::where('code', '5135')->firstOrFail();

        $response = $this->from(route('accounting.expenses.create'))
            ->actingAs($user)
            ->post(route('accounting.expenses.store'), [
                'expense_date' => now()->toDateString(),
                'expense_account_id' => $expenseAccount->id,
                'payment_method' => 'cash',
                'branch_id' => $user->branch_id,
                'amount' => 10.00,
                'description' => 'Papeleria',
            ]);

        $response->assertRedirect(route('accounting.expenses.create'));
        $response->assertSessionHasErrors('expense');
        $this->assertDatabaseCount('journal_entries', 0);
        $this->assertDatabaseCount('cash_movements', 0);
    }
}
