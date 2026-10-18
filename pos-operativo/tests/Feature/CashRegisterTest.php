<?php

namespace Tests\Feature;

use App\Models\CashRegisterSession;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use App\Services\CashRegisterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
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

    public function test_cash_register_only_lists_branches_from_the_users_company(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@pos.test')->firstOrFail();

        $otherCompany = Company::query()->create([
            'name' => 'Empresa que no debe verse en caja',
            'status' => Company::STATUS_ACTIVE,
        ]);
        Branch::withoutGlobalScopes()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Sucursal externa no permitida',
            'code' => 'EXT-CAJA',
        ]);

        $this->actingAs($user)
            ->get(route('cash-register.index'))
            ->assertOk()
            ->assertDontSee('Sucursal externa no permitida');
    }

    public function test_cash_sale_is_counted_once_in_the_expected_closing_amount(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $cash = app(CashRegisterService::class);
        $session = $cash->open($user->branch_id, $user->id, 100);

        $sale = Sale::query()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'cash_register_session_id' => $session->id,
            'sale_number' => 900001,
            'status' => Sale::STATUS_PAID,
            'order_source' => Sale::SOURCE_POS,
            'subtotal' => 50,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'coupon_discount_total' => 0,
            'total' => 50,
            'paid_total' => 50,
            'change_total' => 0,
            'currency' => 'COP',
            'sold_at' => now(),
        ]);
        Payment::query()->create([
            'company_id' => $user->company_id,
            'sale_id' => $sale->id,
            'method' => 'cash',
            'amount' => 50,
            'paid_at' => now(),
        ]);
        $cash->recordSystemMovement(
            session: $session,
            userId: $user->id,
            type: 'IN',
            amount: 50,
            category: 'sale_cash',
            reason: 'Venta de prueba',
            sourceType: Sale::class,
            sourceId: $sale->id,
        );

        $closed = $cash->close($session, 150);

        $this->assertSame('closed', $closed->status);
        $this->assertEquals(150.0, (float) $closed->expected_amount);
        $this->assertEquals(0.0, (float) $closed->difference);
    }

    public function test_cash_difference_requires_a_reason_and_is_audited_on_closing(): void
    {
        $this->seed();
        $user = User::where('email', 'cashier@pos.test')->firstOrFail();
        $session = CashRegisterSession::query()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_amount' => 100,
            'status' => 'open',
            'open_marker' => 1,
        ]);

        try {
            app(CashRegisterService::class)->close($session, 90);
            $this->fail('El cierre con diferencia debe exigir un motivo.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('difference_reason', $exception->errors());
        }

        $closed = app(CashRegisterService::class)->close($session, 90, 'Faltante identificado al contar billetes.');

        $this->assertEquals(-10.0, (float) $closed->difference);
        $this->assertSame('Faltante identificado al contar billetes.', $closed->difference_reason);
    }
}
