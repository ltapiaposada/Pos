<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\JournalEntryRequest;
use App\Http\Requests\OpeningBalanceRequest;
use App\Http\Requests\PeriodCloseRequest;
use App\Models\AccountingAccount;
use App\Models\AccountingPeriodClosure;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $entries = JournalEntry::query()
            ->with('user')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('accounting.journal_entries.index', compact('entries'));
    }

    public function create()
    {
        $accounts = AccountingAccount::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'is_postable']);

        return view('accounting.journal_entries.create', compact('accounts'));
    }

    public function openingBalancesForm()
    {
        $accounts = AccountingAccount::query()
            ->where('is_active', true)
            ->where('is_postable', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type', 'nature']);

        $equityAccounts = $accounts
            ->where('type', AccountingAccount::TYPE_EQUITY)
            ->values();

        return view('accounting.opening_balances', compact('accounts', 'equityAccounts'));
    }

    public function store(JournalEntryRequest $request)
    {
        $payload = $request->validated();

        $entry = DB::transaction(function () use ($payload, $request) {
            $entry = JournalEntry::query()->create([
                'entry_number' => $this->nextEntryNumber(),
                'entry_date' => $payload['entry_date'],
                'description' => $payload['description'],
                'status' => 'posted',
                'user_id' => $request->user()->id,
            ]);

            $lines = collect($payload['lines'])
                ->map(function (array $line) use ($entry) {
                    return [
                        'journal_entry_id' => $entry->id,
                        'accounting_account_id' => (int) $line['accounting_account_id'],
                        'description' => $line['description'] ?? null,
                        'debit' => (float) $line['debit'],
                        'credit' => (float) $line['credit'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->all();

            JournalEntryLine::query()->insert($lines);

            return $entry;
        });

        return redirect()->route('accounting.entries.show', $entry)->with('status', 'Asiento contable registrado.');
    }

    public function show(JournalEntry $entry)
    {
        $entry->load(['user', 'lines.account']);

        return view('accounting.journal_entries.show', compact('entry'));
    }

    public function movements(Request $request)
    {
        $lines = JournalEntryLine::query()
            ->select('journal_entry_lines.*')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->with([
                'journalEntry:id,entry_number,entry_date,description,user_id',
                'journalEntry.user:id,name',
                'account:id,code,name',
            ])
            ->orderByDesc('journal_entries.entry_date')
            ->orderByDesc('journal_entries.id')
            ->orderByDesc('journal_entry_lines.id')
            ->paginate(100)
            ->withQueryString();

        $pageDebit = (float) $lines->getCollection()->sum(fn (JournalEntryLine $line) => (float) $line->debit);
        $pageCredit = (float) $lines->getCollection()->sum(fn (JournalEntryLine $line) => (float) $line->credit);

        return view('accounting.journal_entries.movements', compact('lines', 'pageDebit', 'pageCredit'));
    }

    public function storeOpeningBalances(OpeningBalanceRequest $request)
    {
        $payload = $request->validated();

        $entry = DB::transaction(function () use ($payload, $request) {
            $lines = collect($payload['lines'])
                ->map(function (array $line) {
                    $account = AccountingAccount::query()->findOrFail((int) $line['accounting_account_id']);
                    $balance = round((float) $line['balance'], 2);

                    return [
                        'accounting_account_id' => $account->id,
                        'description' => 'Saldo inicial',
                        'debit' => $account->nature === AccountingAccount::NATURE_DEBIT ? $balance : 0.0,
                        'credit' => $account->nature === AccountingAccount::NATURE_CREDIT ? $balance : 0.0,
                    ];
                })
                ->filter(fn (array $line) => $line['debit'] > 0 || $line['credit'] > 0)
                ->values();

            if ($lines->isEmpty()) {
                throw ValidationException::withMessages([
                    'lines' => 'Debes ingresar al menos una cuenta con saldo inicial.',
                ]);
            }

            $totalDebit = round((float) $lines->sum('debit'), 2);
            $totalCredit = round((float) $lines->sum('credit'), 2);
            $difference = round($totalDebit - $totalCredit, 2);

            if (abs($difference) > 0.0001) {
                $lines->push([
                    'accounting_account_id' => (int) $payload['equity_account_id'],
                    'description' => 'Contrapartida saldos iniciales',
                    'debit' => $difference < 0 ? abs($difference) : 0.0,
                    'credit' => $difference > 0 ? $difference : 0.0,
                ]);
            }

            $this->ensureBalanced($lines);

            $entry = JournalEntry::query()->create([
                'entry_number' => $this->nextEntryNumber('SI'),
                'entry_date' => $payload['entry_date'],
                'description' => $payload['description'],
                'status' => 'posted',
                'user_id' => $request->user()->id,
            ]);

            $entryLines = $lines->map(function (array $line) use ($entry) {
                return [
                    'journal_entry_id' => $entry->id,
                    'accounting_account_id' => $line['accounting_account_id'],
                    'description' => $line['description'],
                    'debit' => round((float) $line['debit'], 2),
                    'credit' => round((float) $line['credit'], 2),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            JournalEntryLine::query()->insert($entryLines);

            return $entry;
        });

        return redirect()
            ->route('accounting.entries.show', $entry)
            ->with('status', 'Saldos iniciales registrados correctamente.');
    }

    public function trialBalance(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');

        $totalsByAccount = $this->summarizedLines($from, $to);
        $accounts = AccountingAccount::query()->orderBy('code')->get();

        $rows = $accounts->map(function (AccountingAccount $account) use ($totalsByAccount) {
            $summary = $totalsByAccount->get($account->id);
            $debit = (float) ($summary->debit_total ?? 0);
            $credit = (float) ($summary->credit_total ?? 0);
            $balance = $debit - $credit;

            return compact('account', 'debit', 'credit', 'balance');
        })->filter(fn ($row) => $row['debit'] != 0.0 || $row['credit'] != 0.0 || $row['balance'] != 0.0)->values();

        $totalDebit = $rows->sum('debit');
        $totalCredit = $rows->sum('credit');

        return view('accounting.trial_balance', compact('rows', 'totalDebit', 'totalCredit', 'from', 'to'));
    }

    public function incomeStatement(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $data = $this->buildIncomeStatementData($from, $to);
        $incomeRows = $data['incomeRows'];
        $expenseRows = $data['expenseRows'];
        $totalIncome = $data['totalIncome'];
        $totalExpense = $data['totalExpense'];
        $netIncome = $data['netIncome'];

        return view('accounting.income_statement', compact(
            'incomeRows',
            'expenseRows',
            'totalIncome',
            'totalExpense',
            'netIncome',
            'from',
            'to'
        ));
    }

    public function balanceSheet(Request $request)
    {
        $asOf = $request->get('as_of', now()->toDateString());
        $data = $this->buildBalanceSheetData($asOf);
        $assetRows = $data['assetRows'];
        $liabilityRows = $data['liabilityRows'];
        $equityRows = $data['equityRows'];
        $totalAssets = $data['totalAssets'];
        $totalLiabilities = $data['totalLiabilities'];
        $totalEquity = $data['totalEquity'];
        $totalLiabilitiesAndEquity = $data['totalLiabilitiesAndEquity'];
        $difference = $data['difference'];

        return view('accounting.balance_sheet', compact(
            'assetRows',
            'liabilityRows',
            'equityRows',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'totalLiabilitiesAndEquity',
            'difference',
            'asOf'
        ));
    }

    public function exportIncomeStatement(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $format = $request->get('format', 'excel');

        $data = $this->buildIncomeStatementData($from, $to);
        $payload = [
            'from' => $from,
            'to' => $to,
            ...$data,
        ];

        if ($format === 'pdf') {
            return view('accounting.exports.income_statement_print', $payload);
        }

        return $this->downloadCsv(
            filename: 'estado_resultados_'.now()->format('Ymd_His').'.csv',
            headers: ['Tipo', 'Codigo', 'Cuenta', 'Valor'],
            rows: collect($data['incomeRows'])->map(fn ($row) => [
                'Ingreso',
                $row['account']->code,
                $row['account']->name,
                $row['balance'],
            ])->merge(
                collect($data['expenseRows'])->map(fn ($row) => [
                    'Gasto',
                    $row['account']->code,
                    $row['account']->name,
                    $row['balance'],
                ])
            )->push(['Totales', '', 'Total ingresos', $data['totalIncome']])
                ->push(['Totales', '', 'Total gastos', $data['totalExpense']])
                ->push(['Totales', '', 'Utilidad neta', $data['netIncome']])
                ->all()
        );
    }

    public function exportBalanceSheet(Request $request)
    {
        $asOf = $request->get('as_of', now()->toDateString());
        $format = $request->get('format', 'excel');
        $data = $this->buildBalanceSheetData($asOf);

        if ($format === 'pdf') {
            return view('accounting.exports.balance_sheet_print', [
                'asOf' => $asOf,
                ...$data,
            ]);
        }

        return $this->downloadCsv(
            filename: 'balance_general_'.now()->format('Ymd_His').'.csv',
            headers: ['Grupo', 'Codigo', 'Cuenta', 'Saldo'],
            rows: collect($data['assetRows'])->map(fn ($row) => [
                'Activo',
                $row['account']->code,
                $row['account']->name,
                $row['balance'],
            ])->merge(
                collect($data['liabilityRows'])->map(fn ($row) => [
                    'Pasivo',
                    $row['account']->code,
                    $row['account']->name,
                    $row['balance'],
                ])
            )->merge(
                collect($data['equityRows'])->map(fn ($row) => [
                    'Patrimonio',
                    $row['account']->code,
                    $row['account']->name,
                    $row['balance'],
                ])
            )->push(['Totales', '', 'Total activos', $data['totalAssets']])
                ->push(['Totales', '', 'Total pasivos', $data['totalLiabilities']])
                ->push(['Totales', '', 'Total patrimonio', $data['totalEquity']])
                ->push(['Totales', '', 'Pasivo + patrimonio', $data['totalLiabilitiesAndEquity']])
                ->push(['Totales', '', 'Diferencia', $data['difference']])
                ->all()
        );
    }

    public function closePeriodForm()
    {
        $closures = AccountingPeriodClosure::query()
            ->with(['journalEntry', 'user'])
            ->orderByDesc('to_date')
            ->limit(12)
            ->get();

        return view('accounting.close_period', compact('closures'));
    }

    public function closePeriod(PeriodCloseRequest $request)
    {
        $payload = $request->validated();

        $exists = AccountingPeriodClosure::query()
            ->whereDate('from_date', '<=', $payload['to_date'])
            ->whereDate('to_date', '>=', $payload['from_date'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'to_date' => 'El rango se cruza con un cierre ya existente.',
            ])->withInput();
        }

        $result = DB::transaction(function () use ($payload, $request) {
            $totalsByAccount = $this->summarizedLines($payload['from_date'], $payload['to_date']);
            $incomeRows = $this->statementRows([AccountingAccount::TYPE_INCOME], $totalsByAccount);
            $expenseRows = $this->statementRows([AccountingAccount::TYPE_EXPENSE], $totalsByAccount);

            $netIncome = round((float) $incomeRows->sum('balance') - (float) $expenseRows->sum('balance'), 2);
            $equityAccount = $this->findPostableAccountByCode($payload['equity_account_code']);

            $lines = collect();

            foreach ($incomeRows as $row) {
                if ($row['balance'] <= 0) {
                    continue;
                }
                $lines->push([
                    'accounting_account_id' => $row['account']->id,
                    'description' => 'Cierre de ingresos',
                    'debit' => round((float) $row['balance'], 2),
                    'credit' => 0.0,
                ]);
            }

            foreach ($expenseRows as $row) {
                if ($row['balance'] <= 0) {
                    continue;
                }
                $lines->push([
                    'accounting_account_id' => $row['account']->id,
                    'description' => 'Cierre de gastos',
                    'debit' => 0.0,
                    'credit' => round((float) $row['balance'], 2),
                ]);
            }

            if ($netIncome > 0) {
                $lines->push([
                    'accounting_account_id' => $equityAccount->id,
                    'description' => 'Utilidad del periodo',
                    'debit' => 0.0,
                    'credit' => $netIncome,
                ]);
            } elseif ($netIncome < 0) {
                $lines->push([
                    'accounting_account_id' => $equityAccount->id,
                    'description' => 'Perdida del periodo',
                    'debit' => abs($netIncome),
                    'credit' => 0.0,
                ]);
            }

            if ($lines->isEmpty()) {
                throw ValidationException::withMessages([
                    'to_date' => 'No hay saldos de ingresos o gastos para cerrar en el periodo.',
                ]);
            }

            $this->ensureBalanced($lines);

            $entry = JournalEntry::query()->create([
                'entry_number' => $this->nextEntryNumber('CI'),
                'entry_date' => $payload['entry_date'],
                'description' => $payload['description'],
                'status' => 'posted',
                'user_id' => $request->user()->id,
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

            $closure = AccountingPeriodClosure::query()->create([
                'from_date' => $payload['from_date'],
                'to_date' => $payload['to_date'],
                'entry_date' => $payload['entry_date'],
                'description' => $payload['description'],
                'net_income' => $netIncome,
                'journal_entry_id' => $entry->id,
                'user_id' => $request->user()->id,
            ]);

            return [$closure, $entry];
        });

        return redirect()
            ->route('accounting.entries.show', $result[1])
            ->with('status', 'Cierre de periodo registrado correctamente.');
    }

    private function summarizedLines(?string $from, ?string $to): Collection
    {
        return JournalEntryLine::query()
            ->selectRaw('accounting_account_id, SUM(debit) as debit_total, SUM(credit) as credit_total')
            ->whereHas('journalEntry', function ($builder) use ($from, $to) {
                $builder->where('status', 'posted');

                if ($from) {
                    $builder->whereDate('entry_date', '>=', $from);
                }
                if ($to) {
                    $builder->whereDate('entry_date', '<=', $to);
                }
            })
            ->groupBy('accounting_account_id')
            ->get()
            ->keyBy('accounting_account_id');
    }

    private function buildIncomeStatementData(?string $from, ?string $to): array
    {
        $totalsByAccount = $this->summarizedLines($from, $to);
        $incomeRows = $this->statementRows([AccountingAccount::TYPE_INCOME], $totalsByAccount);
        $expenseRows = $this->statementRows([AccountingAccount::TYPE_EXPENSE], $totalsByAccount);
        $totalIncome = (float) $incomeRows->sum('balance');
        $totalExpense = (float) $expenseRows->sum('balance');

        return [
            'incomeRows' => $incomeRows,
            'expenseRows' => $expenseRows,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netIncome' => $totalIncome - $totalExpense,
        ];
    }

    private function buildBalanceSheetData(string $asOf): array
    {
        $totalsByAccount = $this->summarizedLines(null, $asOf);
        $assetRows = $this->statementRows([AccountingAccount::TYPE_ASSET], $totalsByAccount);
        $liabilityRows = $this->statementRows([AccountingAccount::TYPE_LIABILITY], $totalsByAccount);
        $equityRows = $this->statementRows([AccountingAccount::TYPE_EQUITY], $totalsByAccount);
        $totalAssets = (float) $assetRows->sum('balance');
        $totalLiabilities = (float) $liabilityRows->sum('balance');
        $totalEquity = (float) $equityRows->sum('balance');

        return [
            'assetRows' => $assetRows,
            'liabilityRows' => $liabilityRows,
            'equityRows' => $equityRows,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'totalLiabilitiesAndEquity' => $totalLiabilities + $totalEquity,
            'difference' => $totalAssets - ($totalLiabilities + $totalEquity),
        ];
    }

    private function statementRows(array $types, Collection $totalsByAccount): Collection
    {
        return AccountingAccount::query()
            ->where('is_active', true)
            ->where('is_postable', true)
            ->whereIn('type', $types)
            ->orderBy('code')
            ->get()
            ->map(function (AccountingAccount $account) use ($totalsByAccount) {
                $summary = $totalsByAccount->get($account->id);
                $debit = (float) ($summary->debit_total ?? 0);
                $credit = (float) ($summary->credit_total ?? 0);
                $balance = $account->nature === AccountingAccount::NATURE_DEBIT
                    ? $debit - $credit
                    : $credit - $debit;

                return compact('account', 'debit', 'credit', 'balance');
            })
            ->filter(fn ($row) => abs((float) $row['balance']) > 0.0001)
            ->values();
    }

    private function nextEntryNumber(string $prefix = 'AS'): string
    {
        $prefix = $prefix.'-'.now()->format('Ymd');
        $last = JournalEntry::query()
            ->where('entry_number', 'like', $prefix.'-%')
            ->orderByDesc('entry_number')
            ->lockForUpdate()
            ->value('entry_number');

        if (! $last) {
            return $prefix.'-0001';
        }

        $current = (int) substr($last, -4);

        return sprintf('%s-%04d', $prefix, $current + 1);
    }

    private function findPostableAccountByCode(string $code): AccountingAccount
    {
        $account = AccountingAccount::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->where('is_postable', true)
            ->first();

        if (! $account) {
            throw ValidationException::withMessages([
                'equity_account_code' => "No existe una cuenta postable activa con codigo {$code}.",
            ]);
        }

        return $account;
    }

    private function ensureBalanced(Collection $lines): void
    {
        $debit = round((float) $lines->sum('debit'), 2);
        $credit = round((float) $lines->sum('credit'), 2);

        if (abs($debit - $credit) > 0.0001) {
            throw ValidationException::withMessages([
                'to_date' => 'El cierre genero un asiento desbalanceado.',
            ]);
        }
    }

    private function downloadCsv(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, chr(239).chr(187).chr(191));
            fputcsv($handle, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
