<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountingAccountRequest;
use App\Models\AccountingAccount;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = AccountingAccount::query()->with('parent');

        if ($search = $request->get('q')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $accounts = $query->orderBy('code')->paginate(20)->withQueryString();

        return view('accounting.accounts.index', compact('accounts'));
    }

    public function create()
    {
        $parents = AccountingAccount::query()->orderBy('code')->get(['id', 'code', 'name']);

        return view('accounting.accounts.create', compact('parents'));
    }

    public function store(AccountingAccountRequest $request)
    {
        $payload = $request->validated();
        $payload['level'] = strlen((string) $payload['code']);
        AccountingAccount::query()->create($payload);

        return redirect()->route('accounting.accounts.index')->with('status', 'Cuenta contable creada.');
    }

    public function edit(AccountingAccount $account)
    {
        $parents = AccountingAccount::query()
            ->whereKeyNot($account->id)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return view('accounting.accounts.edit', compact('account', 'parents'));
    }

    public function update(AccountingAccountRequest $request, AccountingAccount $account)
    {
        $payload = $request->validated();
        $payload['level'] = strlen((string) $payload['code']);
        $account->update($payload);

        return redirect()->route('accounting.accounts.index')->with('status', 'Cuenta contable actualizada.');
    }
}

