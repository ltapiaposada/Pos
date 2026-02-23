<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\CashRegisterSession;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashRegisterService
{
    public function open(int $branchId, int $userId, float $openingAmount): CashRegisterSession
    {
        $existing = CashRegisterSession::query()
            ->where('branch_id', $branchId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'cash_register' => 'Ya existe una caja abierta para este usuario.',
            ]);
        }

        return CashRegisterSession::query()->create([
            'branch_id' => $branchId,
            'user_id' => $userId,
            'opened_at' => now(),
            'opening_amount' => $openingAmount,
            'status' => 'open',
        ]);
    }

    public function close(CashRegisterSession $session, float $closingAmount): CashRegisterSession
    {
        return DB::transaction(function () use ($session, $closingAmount) {
            $payments = Payment::query()
                ->whereHas('sale', function ($builder) use ($session) {
                    $builder->where('cash_register_session_id', $session->id);
                })
                ->get()
                ->groupBy('method')
                ->map(fn ($items) => $items->sum('amount'));

            $cashMovements = CashMovement::query()
                ->where('cash_register_session_id', $session->id)
                ->get();

            $cashIn = $cashMovements->where('type', 'IN')->sum('amount');
            $cashOut = $cashMovements->where('type', 'OUT')->sum('amount');

            $cashPayments = (float) ($payments['cash'] ?? 0);
            $expected = $session->opening_amount + $cashPayments + $cashIn - $cashOut;

            $session->update([
                'closed_at' => now(),
                'closing_amount' => $closingAmount,
                'expected_amount' => $expected,
                'difference' => $closingAmount - $expected,
                'status' => 'closed',
            ]);

            return $session;
        });
    }

    public function recordMovement(CashRegisterSession $session, int $userId, array $payload): CashMovement
    {
        if ($session->status !== 'open') {
            throw ValidationException::withMessages([
                'cash_register' => 'La caja debe estar abierta.',
            ]);
        }

        return CashMovement::query()->create([
            'cash_register_session_id' => $session->id,
            'branch_id' => $session->branch_id,
            'user_id' => $userId,
            'type' => $payload['type'],
            'amount' => $payload['amount'],
            'reason' => $payload['reason'],
        ]);
    }
}
