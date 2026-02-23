@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Saldos iniciales</h1>
                <p class="page-subtitle">Carga de apertura contable por cuenta</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            @if ($errors->any())
                <div class="alert alert-error mb-4">
                    <ul class="space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('accounting.opening-balances.store') }}">
                @csrf
                <div class="form-grid">
                    <div>
                        <label class="field-label">Fecha de apertura</label>
                        <input type="date" name="entry_date" value="{{ old('entry_date', now()->format('Y-m-d')) }}" class="input input-bordered w-full" required>
                    </div>
                    <div>
                        <label class="field-label">Cuenta contrapartida (patrimonio)</label>
                        <select name="equity_account_id" class="select select-bordered w-full" required>
                            <option value="">Selecciona una cuenta</option>
                            @foreach ($equityAccounts as $account)
                                <option value="{{ $account->id }}" @selected((string) old('equity_account_id', $equityAccounts->first()?->id) === (string) $account->id)>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">Descripcion</label>
                        <input name="description" value="{{ old('description', 'Registro de saldos iniciales') }}" class="input input-bordered w-full" required>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <h2 class="text-sm font-semibold">Cuentas con saldo</h2>
                    <button type="button" class="btn btn-outline btn-xs" id="add-line">Agregar cuenta</button>
                </div>

                @error('lines')
                    <p class="text-xs text-error mt-2">{{ $message }}</p>
                @enderror

                <div id="lines-wrapper" class="mt-3 space-y-2"></div>

                <div class="mt-4 text-sm space-y-1">
                    <div>Total Debe estimado: <strong id="total-debit">0.00</strong></div>
                    <div>Total Haber estimado: <strong id="total-credit">0.00</strong></div>
                    <div>Diferencia (a contrapartida): <strong id="difference">0.00</strong></div>
                </div>

                <div class="mt-6 flex gap-2">
                    <button class="btn btn-primary">Registrar saldos iniciales</button>
                    <a href="{{ route('accounting.entries.index') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <template id="line-template">
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-12 opening-line">
            <div class="sm:col-span-7">
                <select class="select select-bordered w-full account-id"></select>
            </div>
            <div class="sm:col-span-4">
                <input type="number" min="0" step="0.01" class="input input-bordered w-full line-balance" placeholder="Saldo inicial">
            </div>
            <div class="sm:col-span-1">
                <button type="button" class="btn btn-outline-danger btn-xs remove-line w-full">X</button>
            </div>
        </div>
    </template>

    <script>
        (function () {
            const accounts = @json($accounts);
            const oldLines = @json(old('lines', [['accounting_account_id' => '', 'balance' => '']]));
            const wrapper = document.getElementById('lines-wrapper');
            const template = document.getElementById('line-template');
            const addBtn = document.getElementById('add-line');
            const totalDebitEl = document.getElementById('total-debit');
            const totalCreditEl = document.getElementById('total-credit');
            const differenceEl = document.getElementById('difference');

            const accountMap = new Map(accounts.map((account) => [String(account.id), account]));

            function updateNames() {
                const rows = wrapper.querySelectorAll('.opening-line');
                rows.forEach((row, index) => {
                    row.querySelector('.account-id').name = `lines[${index}][accounting_account_id]`;
                    row.querySelector('.line-balance').name = `lines[${index}][balance]`;
                });
            }

            function updateTotals() {
                let debit = 0;
                let credit = 0;

                wrapper.querySelectorAll('.opening-line').forEach((row) => {
                    const accountId = row.querySelector('.account-id').value;
                    const amount = parseFloat(row.querySelector('.line-balance').value || 0);
                    const account = accountMap.get(String(accountId));
                    if (!account || amount <= 0) {
                        return;
                    }

                    if (account.nature === 'debit') {
                        debit += amount;
                    } else {
                        credit += amount;
                    }
                });

                totalDebitEl.textContent = debit.toFixed(2);
                totalCreditEl.textContent = credit.toFixed(2);
                differenceEl.textContent = (debit - credit).toFixed(2);
            }

            function createRow(values = {}) {
                const row = template.content.firstElementChild.cloneNode(true);
                const select = row.querySelector('.account-id');
                select.innerHTML = '<option value="">Cuenta</option>' + accounts
                    .map((account) => `<option value="${account.id}">${account.code} - ${account.name}</option>`)
                    .join('');

                select.value = values.accounting_account_id || '';
                row.querySelector('.line-balance').value = values.balance || '';

                row.querySelector('.remove-line').addEventListener('click', function () {
                    row.remove();
                    updateNames();
                    updateTotals();
                });

                select.addEventListener('change', updateTotals);
                row.querySelector('.line-balance').addEventListener('input', updateTotals);

                wrapper.appendChild(row);
                updateNames();
                updateTotals();
            }

            addBtn.addEventListener('click', function () {
                createRow();
            });

            oldLines.forEach((line) => createRow(line));
            if (wrapper.children.length === 0) {
                createRow();
            }
        })();
    </script>
@endsection
