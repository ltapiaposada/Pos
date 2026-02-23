<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\CashMovement;
use App\Models\CashRegisterSession;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ReturnModel;
use App\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AccountingPostingService
{
    public function postExpense(array $payload, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($payload, $userId) {
            $expenseAccount = AccountingAccount::query()
                ->whereKey((int) $payload['expense_account_id'])
                ->where('is_active', true)
                ->where('is_postable', true)
                ->where('type', AccountingAccount::TYPE_EXPENSE)
                ->first();

            if (! $expenseAccount) {
                throw new RuntimeException('La cuenta de gasto seleccionada no es valida para contabilizar.');
            }

            $cashAccount = $this->accountByCode('1105');
            $bankAccount = $this->accountByCode('1110');
            $payableAccount = $this->accountByCode('2205');
            $amount = round((float) $payload['amount'], 2);
            $paymentMethod = (string) $payload['payment_method'];
            $branchId = isset($payload['branch_id']) ? (int) $payload['branch_id'] : null;

            $creditAccount = match ($paymentMethod) {
                'cash' => $cashAccount,
                'bank' => $bankAccount,
                'credit' => $payableAccount,
                default => null,
            };

            if (! $creditAccount) {
                throw new RuntimeException('Metodo de pago invalido para registrar el gasto.');
            }

            $description = trim((string) $payload['description']);
            $counterpartLabel = match ($paymentMethod) {
                'cash' => 'caja',
                'bank' => 'banco',
                'credit' => 'cuenta por pagar',
                default => 'contrapartida',
            };

            $lines = collect([
                $this->line($expenseAccount->id, "Registro gasto: {$description}", $amount, 0),
                $this->line($creditAccount->id, "Salida por gasto - {$counterpartLabel}", 0, $amount),
            ]);

            $this->ensureBalanced($lines->all());

            $entry = $this->createEntry(
                prefix: 'GS',
                date: (string) $payload['expense_date'],
                description: "Gasto: {$description}",
                userId: $userId,
                lines: $lines
            );

            if ($paymentMethod === 'cash') {
                if (! $branchId) {
                    throw new RuntimeException('Debes seleccionar una sucursal para registrar el gasto en efectivo.');
                }

                $session = CashRegisterSession::query()
                    ->where('branch_id', $branchId)
                    ->where('user_id', $userId)
                    ->where('status', 'open')
                    ->first();

                if (! $session) {
                    throw new RuntimeException('No hay una caja abierta para la sucursal seleccionada. Abre caja para registrar gastos en efectivo.');
                }

                CashMovement::query()->create([
                    'cash_register_session_id' => $session->id,
                    'branch_id' => $session->branch_id,
                    'user_id' => $userId,
                    'type' => 'OUT',
                    'amount' => $amount,
                    'reason' => 'Gasto: '.$description,
                ]);
            }

            return $entry;
        });
    }

    public function postSale(Sale $sale, Collection $payments, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($sale, $payments, $userId) {
            $cashAccount = $this->accountByCode('1105');
            $bankAccount = $this->accountByCode('1110');
            $receivableAccount = $this->accountByCode('1305');
            $incomeAccount = $this->accountByCode('4135');
            $taxAccount = $this->accountByCode('2408');
            $inventoryAccount = $this->accountByCode('1435');
            $costAccount = $this->accountByCode('6135');

            $cashNet = max(0.0, (float) $payments->where('method', 'cash')->sum('amount') - (float) $sale->change_total);
            $bankNet = (float) $payments
                ->whereIn('method', ['card', 'transfer', 'other'])
                ->sum('amount');
            $receivable = max(0.0, round((float) $sale->total - $cashNet - $bankNet, 2));

            $revenueBase = max(0.0, round((float) $sale->total - (float) $sale->tax_total, 2));
            $tax = max(0.0, round((float) $sale->tax_total, 2));

            $lines = [];

            if ($cashNet > 0) {
                $lines[] = $this->line($cashAccount->id, 'Ingreso por venta - efectivo', $cashNet, 0);
            }
            if ($bankNet > 0) {
                $lines[] = $this->line($bankAccount->id, 'Ingreso por venta - bancos', $bankNet, 0);
            }
            if ($receivable > 0) {
                $lines[] = $this->line($receivableAccount->id, 'Cuenta por cobrar venta', $receivable, 0);
            }

            if ($revenueBase > 0) {
                $lines[] = $this->line($incomeAccount->id, 'Ingreso operativo por venta', 0, $revenueBase);
            }
            if ($tax > 0) {
                $lines[] = $this->line($taxAccount->id, 'IVA generado en venta', 0, $tax);
            }

            $costOfSale = $this->calculateSaleCost($sale);
            if ($costOfSale > 0) {
                $lines[] = $this->line($costAccount->id, 'Costo de venta', $costOfSale, 0);
                $lines[] = $this->line($inventoryAccount->id, 'Salida de inventario por venta', 0, $costOfSale);
            }

            $this->ensureBalanced($lines);

            return $this->createEntry(
                prefix: 'VS',
                date: $sale->sold_at->format('Y-m-d'),
                description: "Venta POS #{$sale->sale_number}",
                userId: $userId,
                lines: collect($lines)
            );
        });
    }

    public function postReturn(ReturnModel $return, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($return, $userId) {
            $cashAccount = $this->accountByCode('1105');
            $incomeAccount = $this->accountByCode('4135');
            $inventoryAccount = $this->accountByCode('1435');
            $costAccount = $this->accountByCode('6135');

            $amount = round((float) $return->total, 2);

            $lines = collect([
                $this->line($incomeAccount->id, 'Reverso ingreso por devolucion', $amount, 0),
                $this->line($cashAccount->id, 'Salida de caja por devolucion', 0, $amount),
            ]);

            $returnCost = $this->calculateReturnCost($return);
            if ($returnCost > 0) {
                $lines->push($this->line($inventoryAccount->id, 'Reingreso inventario por devolucion', $returnCost, 0));
                $lines->push($this->line($costAccount->id, 'Reverso costo de venta por devolucion', 0, $returnCost));
            }

            $this->ensureBalanced($lines->all());

            return $this->createEntry(
                prefix: 'DV',
                date: $return->created_at->format('Y-m-d'),
                description: "Devolucion venta #{$return->sale_id}",
                userId: $userId,
                lines: $lines
            );
        });
    }

    public function postPurchase(Purchase $purchase, string $paymentMethod, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($purchase, $paymentMethod, $userId) {
            $inventoryAccount = $this->accountByCode('1435');
            $payableAccount = $this->accountByCode('2205');
            $cashAccount = $this->accountByCode('1105');
            $bankAccount = $this->accountByCode('1110');

            $total = round((float) $purchase->total, 2);
            $paid = round((float) $purchase->paid_total, 2);
            $balance = round((float) $purchase->balance_total, 2);

            $lines = [];
            if ($total > 0) {
                $lines[] = $this->line($inventoryAccount->id, 'Ingreso de inventario por compra', $total, 0);
            }

            if ($paid > 0) {
                if ($paymentMethod === 'cash') {
                    $lines[] = $this->line($cashAccount->id, 'Pago compra en efectivo', 0, $paid);
                } else {
                    $lines[] = $this->line($bankAccount->id, 'Pago compra por banco', 0, $paid);
                }
            }

            if ($balance > 0) {
                $lines[] = $this->line($payableAccount->id, 'Cuenta por pagar proveedor', 0, $balance);
            }

            $this->ensureBalanced($lines);

            return $this->createEntry(
                prefix: 'CP',
                date: $purchase->purchased_at->format('Y-m-d'),
                description: "Compra #{$purchase->purchase_number}",
                userId: $userId,
                lines: collect($lines)
            );
        });
    }

    public function postSaleCollection(Sale $sale, string $method, float $amount, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($sale, $method, $amount, $userId) {
            $receivableAccount = $this->accountByCode('1305');
            $cashAccount = $this->accountByCode('1105');
            $bankAccount = $this->accountByCode('1110');

            $debitAccount = $method === 'cash' ? $cashAccount : $bankAccount;
            $amount = round($amount, 2);

            $lines = collect([
                $this->line($debitAccount->id, 'Recaudo cartera venta', $amount, 0),
                $this->line($receivableAccount->id, 'Disminucion cuenta por cobrar', 0, $amount),
            ]);

            $this->ensureBalanced($lines->all());

            return $this->createEntry(
                prefix: 'RC',
                date: now()->format('Y-m-d'),
                description: "Recaudo cartera venta #{$sale->sale_number}",
                userId: $userId,
                lines: $lines
            );
        });
    }

    public function postSaleCollectionVoid(Sale $sale, string $method, float $amount, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($sale, $method, $amount, $userId) {
            $receivableAccount = $this->accountByCode('1305');
            $cashAccount = $this->accountByCode('1105');
            $bankAccount = $this->accountByCode('1110');

            $creditAccount = $method === 'cash' ? $cashAccount : $bankAccount;
            $amount = round($amount, 2);

            $lines = collect([
                $this->line($receivableAccount->id, 'Reverso disminucion cuenta por cobrar', $amount, 0),
                $this->line($creditAccount->id, 'Reverso recaudo cartera venta', 0, $amount),
            ]);

            $this->ensureBalanced($lines->all());

            return $this->createEntry(
                prefix: 'ARC',
                date: now()->format('Y-m-d'),
                description: "Anulacion recaudo cartera venta #{$sale->sale_number}",
                userId: $userId,
                lines: $lines
            );
        });
    }

    public function postPurchasePayment(Purchase $purchase, string $method, float $amount, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($purchase, $method, $amount, $userId) {
            $payableAccount = $this->accountByCode('2205');
            $cashAccount = $this->accountByCode('1105');
            $bankAccount = $this->accountByCode('1110');

            $creditAccount = $method === 'cash' ? $cashAccount : $bankAccount;
            $amount = round($amount, 2);

            $lines = collect([
                $this->line($payableAccount->id, 'Disminucion cuenta por pagar', $amount, 0),
                $this->line($creditAccount->id, 'Pago cartera proveedor', 0, $amount),
            ]);

            $this->ensureBalanced($lines->all());

            return $this->createEntry(
                prefix: 'PG',
                date: now()->format('Y-m-d'),
                description: "Pago cartera compra #{$purchase->purchase_number}",
                userId: $userId,
                lines: $lines
            );
        });
    }

    public function postPurchasePaymentVoid(Purchase $purchase, string $method, float $amount, int $userId): JournalEntry
    {
        return DB::transaction(function () use ($purchase, $method, $amount, $userId) {
            $payableAccount = $this->accountByCode('2205');
            $cashAccount = $this->accountByCode('1105');
            $bankAccount = $this->accountByCode('1110');

            $debitAccount = $method === 'cash' ? $cashAccount : $bankAccount;
            $amount = round($amount, 2);

            $lines = collect([
                $this->line($debitAccount->id, 'Reverso pago cartera proveedor', $amount, 0),
                $this->line($payableAccount->id, 'Reverso disminucion cuenta por pagar', 0, $amount),
            ]);

            $this->ensureBalanced($lines->all());

            return $this->createEntry(
                prefix: 'APG',
                date: now()->format('Y-m-d'),
                description: "Anulacion pago cartera compra #{$purchase->purchase_number}",
                userId: $userId,
                lines: $lines
            );
        });
    }

    private function createEntry(string $prefix, string $date, string $description, int $userId, Collection $lines): JournalEntry
    {
        $entry = JournalEntry::query()->create([
            'entry_number' => $this->nextEntryNumber($prefix),
            'entry_date' => $date,
            'description' => $description,
            'status' => 'posted',
            'user_id' => $userId,
        ]);

        $entryLines = $lines->map(function (array $line) use ($entry) {
            return [
                'journal_entry_id' => $entry->id,
                'accounting_account_id' => $line['accounting_account_id'],
                'description' => $line['description'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        JournalEntryLine::query()->insert($entryLines);

        return $entry;
    }

    private function nextEntryNumber(string $prefix): string
    {
        $base = $prefix.'-'.now()->format('Ymd');
        $last = JournalEntry::query()
            ->where('entry_number', 'like', $base.'-%')
            ->orderByDesc('entry_number')
            ->lockForUpdate()
            ->value('entry_number');

        if (! $last) {
            return $base.'-0001';
        }

        $current = (int) substr($last, -4);

        return sprintf('%s-%04d', $base, $current + 1);
    }

    private function accountByCode(string $code): AccountingAccount
    {
        $account = AccountingAccount::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            throw new RuntimeException("No existe la cuenta contable activa con codigo {$code}.");
        }

        return $account;
    }

    private function line(int $accountId, ?string $description, float $debit, float $credit): array
    {
        return [
            'accounting_account_id' => $accountId,
            'description' => $description,
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
        ];
    }

    private function ensureBalanced(array $lines): void
    {
        $debit = round(collect($lines)->sum('debit'), 2);
        $credit = round(collect($lines)->sum('credit'), 2);

        if (abs($debit - $credit) > 0.0001) {
            throw new RuntimeException('No se puede registrar asiento contable desbalanceado.');
        }
    }

    private function calculateSaleCost(Sale $sale): float
    {
        $sale->loadMissing('items');
        $products = Product::query()
            ->with('kitItems.componentProduct')
            ->whereIn('id', $sale->items->pluck('product_id'))
            ->get()
            ->keyBy('id');

        $total = 0.0;

        foreach ($sale->items as $item) {
            $product = $products->get($item->product_id);
            if (! $product) {
                continue;
            }

            $lineQty = (float) $item->quantity;
            if ($product->product_type === Product::TYPE_KIT) {
                foreach ($product->kitItems as $kitItem) {
                    $component = $kitItem->componentProduct;
                    if (! $component) {
                        continue;
                    }
                    $total += $lineQty * (float) $kitItem->quantity * (float) ($component->cost_price ?? 0);
                }
            } else {
                $total += $lineQty * (float) ($product->cost_price ?? 0);
            }
        }

        return round($total, 2);
    }

    private function calculateReturnCost(ReturnModel $return): float
    {
        $return->loadMissing('items');
        $products = Product::query()
            ->with('kitItems.componentProduct')
            ->whereIn('id', $return->items->pluck('product_id'))
            ->get()
            ->keyBy('id');

        $total = 0.0;

        foreach ($return->items as $item) {
            $product = $products->get($item->product_id);
            if (! $product) {
                continue;
            }

            $lineQty = (float) $item->quantity;
            if ($product->product_type === Product::TYPE_KIT) {
                foreach ($product->kitItems as $kitItem) {
                    $component = $kitItem->componentProduct;
                    if (! $component) {
                        continue;
                    }
                    $total += $lineQty * (float) $kitItem->quantity * (float) ($component->cost_price ?? 0);
                }
            } else {
                $total += $lineQty * (float) ($product->cost_price ?? 0);
            }
        }

        return round($total, 2);
    }
}
