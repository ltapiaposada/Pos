<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CashMovement;
use App\Models\CashRegisterSession;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashRegisterService
{
    public function open(int $branchId, int $userId, float $openingAmount): CashRegisterSession
    {
        try {
            return DB::transaction(function () use ($branchId, $userId, $openingAmount) {
                $companyId = Branch::withoutGlobalScopes()->whereKey($branchId)->value('company_id');
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
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'user_id' => $userId,
                    'opened_at' => now(),
                    'opening_amount' => $openingAmount,
                    'status' => 'open',
                    'open_marker' => 1,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'cash_register' => 'Ya existe una caja abierta para este usuario.',
            ]);
        }
    }

    public function summary(CashRegisterSession $session): array
    {
        $movements = CashMovement::query()
            ->where('cash_register_session_id', $session->id)
            ->with('user:id,name')
            ->orderByDesc('id')
            ->get();

        $cashIn = (float) $movements->where('type', 'IN')->sum('amount');
        $cashOut = (float) $movements->where('type', 'OUT')->sum('amount');

        return [
            'movements' => $movements,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'expected_amount' => round((float) $session->opening_amount + $cashIn - $cashOut, 2),
            'by_category' => $movements
                ->groupBy('category')
                ->map(fn ($items) => round((float) $items->sum(fn (CashMovement $movement) => $movement->type === 'IN' ? $movement->amount : -$movement->amount), 2)),
        ];
    }

    public function close(CashRegisterSession $session, float $closingAmount, ?string $differenceReason = null): CashRegisterSession
    {
        return DB::transaction(function () use ($session, $closingAmount, $differenceReason) {
            $session = CashRegisterSession::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($session->id);

            if ($session->status !== 'open') {
                throw ValidationException::withMessages([
                    'cash_register' => 'La caja ya fue cerrada.',
                ]);
            }

            $summary = $this->summary($session);
            $expected = $summary['expected_amount'];
            $difference = round($closingAmount - $expected, 2);

            if (abs($difference) >= 0.01 && trim((string) $differenceReason) === '') {
                throw ValidationException::withMessages([
                    'difference_reason' => 'Debes indicar el motivo del sobrante o faltante de caja.',
                ]);
            }

            $session->update([
                'closed_at' => now(),
                'closing_amount' => $closingAmount,
                'expected_amount' => $expected,
                'difference' => $difference,
                'difference_reason' => $difference === 0.0 ? null : trim((string) $differenceReason),
                'status' => 'closed',
                'open_marker' => null,
            ]);

            return $session;
        });
    }

    public function recordMovement(CashRegisterSession $session, int $userId, array $payload): CashMovement
    {
        return $this->createMovement($session, $userId, [
            'type' => $payload['type'],
            'category' => $payload['category'],
            'amount' => $payload['amount'],
            'reason' => $payload['reason'],
        ]);
    }

    public function recordSystemMovement(
        CashRegisterSession $session,
        int $userId,
        string $type,
        float $amount,
        string $category,
        string $reason,
        ?string $sourceType = null,
        ?int $sourceId = null,
    ): CashMovement {
        return $this->createMovement($session, $userId, [
            'type' => $type,
            'category' => $category,
            'amount' => $amount,
            'reason' => $reason,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);
    }

    private function createMovement(CashRegisterSession $session, int $userId, array $attributes): CashMovement
    {
        return DB::transaction(function () use ($session, $userId, $attributes) {
            $session = CashRegisterSession::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($session->id);

            if ($session->status !== 'open') {
                throw ValidationException::withMessages([
                    'cash_register' => 'La caja debe estar abierta.',
                ]);
            }

            return CashMovement::query()->create([
                'company_id' => $session->company_id,
                'cash_register_session_id' => $session->id,
                'branch_id' => $session->branch_id,
                'user_id' => $userId,
                ...$attributes,
            ]);
        });
    }
}
