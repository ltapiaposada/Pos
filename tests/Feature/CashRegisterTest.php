<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_cash_register(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->first();
        $this->actingAs($user);

        $response = $this->post(route('cash-register.open'), [
            'branch_id' => $user->branch_id,
            'opening_amount' => 20,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('cash_register_sessions', 1);
    }
}
