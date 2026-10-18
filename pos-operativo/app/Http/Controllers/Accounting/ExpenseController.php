<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Models\AccountingAccount;
use App\Models\Branch;
use App\Services\AccountingPostingService;
use RuntimeException;

class ExpenseController extends Controller
{
    public function create()
    {
        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);
        $expenseAccounts = AccountingAccount::query()
            ->where('is_active', true)
            ->where('is_postable', true)
            ->where('type', AccountingAccount::TYPE_EXPENSE)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
        $defaultBranchId = auth()->user()?->branch_id ?? $branches->first()?->id;

        return view('accounting.expenses.create', compact('expenseAccounts', 'branches', 'defaultBranchId'));
    }

    public function store(ExpenseRequest $request, AccountingPostingService $postingService)
    {
        try {
            $entry = $postingService->postExpense($request->validated(), $request->user()->id);
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors([
                'expense' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('accounting.entries.show', $entry)
            ->with('status', 'Gasto registrado y contabilizado correctamente.');
    }
}
