<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayablePaymentRequest;
use App\Http\Requests\ReceivableCollectionRequest;
use App\Http\Requests\VoidCreditPaymentRequest;
use App\Models\CashMovement;
use App\Models\CashRegisterSession;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Sale;
use App\Services\AccountingPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreditControlController extends Controller
{
    public function receivablesIndex()
    {
        $sales = Sale::query()
            ->with([
                'branch:id,name',
                'customer:id,name',
                'user:id,name',
                'payments' => fn ($q) => $q->orderByDesc('paid_at')->orderByDesc('id'),
                'payments.voidedBy:id,name',
            ])
            ->where('order_source', Sale::SOURCE_POS)
            ->whereRaw('total > paid_total')
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('accounting.credit.receivables', compact('sales'));
    }

    public function collectReceivable(ReceivableCollectionRequest $request, Sale $sale, AccountingPostingService $postingService)
    {
        DB::transaction(function () use ($request, $sale, $postingService) {
            $sale->refresh();
            $amount = round((float) $request->input('amount'), 2);
            $outstanding = round(max(0, (float) $sale->total - (float) $sale->paid_total), 2);

            if ($outstanding <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'La venta seleccionada no tiene saldo pendiente.',
                ]);
            }
            if ($amount > $outstanding + 0.0001) {
                throw ValidationException::withMessages([
                    'amount' => 'El abono supera el saldo pendiente.',
                ]);
            }

            $method = (string) $request->input('method');

            Payment::query()->create([
                'sale_id' => $sale->id,
                'method' => $method,
                'amount' => $amount,
                'reference' => $request->input('reference'),
                'paid_at' => now(),
            ]);

            $newPaidTotal = round((float) $sale->paid_total + $amount, 2);
            $newBalance = round(max(0, (float) $sale->total - $newPaidTotal), 2);

            $sale->update([
                'paid_total' => $newPaidTotal,
                'status' => $newBalance > 0 ? Sale::STATUS_PENDING : Sale::STATUS_PAID,
            ]);

            $postingService->postSaleCollection($sale, $method, $amount, $request->user()->id);

            if ($method === 'cash') {
                $session = $this->openSessionForBranch($sale->branch_id, $request->user()->id);
                CashMovement::query()->create([
                    'cash_register_session_id' => $session->id,
                    'branch_id' => $sale->branch_id,
                    'user_id' => $request->user()->id,
                    'type' => 'IN',
                    'amount' => $amount,
                    'reason' => "Abono cartera venta #{$sale->sale_number}",
                ]);
            }
        });

        return redirect()->route('accounting.receivables.index')->with('status', 'Abono registrado correctamente.');
    }

    public function payablesIndex()
    {
        $purchases = Purchase::query()
            ->with([
                'branch:id,name',
                'user:id,name',
                'payments' => fn ($q) => $q->orderByDesc('paid_at')->orderByDesc('id'),
                'payments.voidedBy:id,name',
            ])
            ->where('balance_total', '>', 0)
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('accounting.credit.payables', compact('purchases'));
    }

    public function payPayable(PayablePaymentRequest $request, Purchase $purchase, AccountingPostingService $postingService)
    {
        DB::transaction(function () use ($request, $purchase, $postingService) {
            $purchase->refresh();
            $amount = round((float) $request->input('amount'), 2);
            $outstanding = round((float) $purchase->balance_total, 2);

            if ($outstanding <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'La compra seleccionada no tiene saldo pendiente.',
                ]);
            }
            if ($amount > $outstanding + 0.0001) {
                throw ValidationException::withMessages([
                    'amount' => 'El pago supera el saldo pendiente.',
                ]);
            }

            $method = (string) $request->input('method');

            PurchasePayment::query()->create([
                'purchase_id' => $purchase->id,
                'user_id' => $request->user()->id,
                'method' => $method,
                'amount' => $amount,
                'reference' => $request->input('reference'),
                'paid_at' => now(),
            ]);

            $newPaidTotal = round((float) $purchase->paid_total + $amount, 2);
            $newBalance = round(max(0, (float) $purchase->total - $newPaidTotal), 2);

            $purchase->update([
                'paid_total' => $newPaidTotal,
                'balance_total' => $newBalance,
            ]);

            $postingService->postPurchasePayment($purchase, $method, $amount, $request->user()->id);

            if ($method === 'cash') {
                $session = $this->openSessionForBranch($purchase->branch_id, $request->user()->id);
                CashMovement::query()->create([
                    'cash_register_session_id' => $session->id,
                    'branch_id' => $purchase->branch_id,
                    'user_id' => $request->user()->id,
                    'type' => 'OUT',
                    'amount' => $amount,
                    'reason' => "Pago cartera compra #{$purchase->purchase_number}",
                ]);
            }
        });

        return redirect()->route('accounting.payables.index')->with('status', 'Pago registrado correctamente.');
    }

    public function voidReceivablePayment(
        VoidCreditPaymentRequest $request,
        Sale $sale,
        Payment $payment,
        AccountingPostingService $postingService
    ) {
        if ((int) $payment->sale_id !== (int) $sale->id) {
            abort(404);
        }

        DB::transaction(function () use ($request, $sale, $payment, $postingService) {
            $sale->refresh();
            $payment->refresh();

            if ($payment->voided_at) {
                throw ValidationException::withMessages([
                    'reason' => 'El abono ya fue anulado previamente.',
                ]);
            }

            $payment->update([
                'voided_at' => now(),
                'voided_by_user_id' => $request->user()->id,
                'void_reason' => $request->input('reason'),
            ]);

            $paidTotal = (float) Payment::query()
                ->where('sale_id', $sale->id)
                ->whereNull('voided_at')
                ->sum('amount');

            $newPaidTotal = round($paidTotal, 2);
            $newBalance = round(max(0, (float) $sale->total - $newPaidTotal), 2);

            $sale->update([
                'paid_total' => $newPaidTotal,
                'status' => $newBalance > 0 ? Sale::STATUS_PENDING : Sale::STATUS_PAID,
            ]);

            $postingService->postSaleCollectionVoid($sale, (string) $payment->method, (float) $payment->amount, $request->user()->id);

            if ($payment->method === 'cash') {
                $session = $this->openSessionForBranch($sale->branch_id, $request->user()->id);
                CashMovement::query()->create([
                    'cash_register_session_id' => $session->id,
                    'branch_id' => $sale->branch_id,
                    'user_id' => $request->user()->id,
                    'type' => 'OUT',
                    'amount' => $payment->amount,
                    'reason' => "Anulacion abono cartera venta #{$sale->sale_number}",
                ]);
            }
        });

        return redirect()->route('accounting.receivables.index')->with('status', 'Abono anulado correctamente.');
    }

    public function voidPayablePayment(
        VoidCreditPaymentRequest $request,
        Purchase $purchase,
        PurchasePayment $purchasePayment,
        AccountingPostingService $postingService
    ) {
        if ((int) $purchasePayment->purchase_id !== (int) $purchase->id) {
            abort(404);
        }

        DB::transaction(function () use ($request, $purchase, $purchasePayment, $postingService) {
            $purchase->refresh();
            $purchasePayment->refresh();

            if ($purchasePayment->voided_at) {
                throw ValidationException::withMessages([
                    'reason' => 'El pago ya fue anulado previamente.',
                ]);
            }

            $purchasePayment->update([
                'voided_at' => now(),
                'voided_by_user_id' => $request->user()->id,
                'void_reason' => $request->input('reason'),
            ]);

            $paidTotal = (float) PurchasePayment::query()
                ->where('purchase_id', $purchase->id)
                ->whereNull('voided_at')
                ->sum('amount');

            $newPaidTotal = round($paidTotal, 2);
            $newBalance = round(max(0, (float) $purchase->total - $newPaidTotal), 2);

            $purchase->update([
                'paid_total' => $newPaidTotal,
                'balance_total' => $newBalance,
            ]);

            $postingService->postPurchasePaymentVoid($purchase, (string) $purchasePayment->method, (float) $purchasePayment->amount, $request->user()->id);

            if ($purchasePayment->method === 'cash') {
                $session = $this->openSessionForBranch($purchase->branch_id, $request->user()->id);
                CashMovement::query()->create([
                    'cash_register_session_id' => $session->id,
                    'branch_id' => $purchase->branch_id,
                    'user_id' => $request->user()->id,
                    'type' => 'IN',
                    'amount' => $purchasePayment->amount,
                    'reason' => "Anulacion pago cartera compra #{$purchase->purchase_number}",
                ]);
            }
        });

        return redirect()->route('accounting.payables.index')->with('status', 'Pago anulado correctamente.');
    }

    private function openSessionForBranch(int $branchId, int $userId): CashRegisterSession
    {
        $session = CashRegisterSession::query()
            ->where('branch_id', $branchId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if (! $session) {
            throw ValidationException::withMessages([
                'amount' => 'Debes tener una caja abierta en la sucursal para registrar pagos en efectivo.',
            ]);
        }

        return $session;
    }
}
