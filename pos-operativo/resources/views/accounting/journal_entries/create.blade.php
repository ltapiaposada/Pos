@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Nuevo asiento contable</h1>
                <p class="page-subtitle">Debe y haber deben cuadrar</p>
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

            <form method="POST" action="{{ route('accounting.entries.store') }}">
                @csrf
                <div class="form-grid">
                    <div>
                        <label class="field-label">Fecha</label>
                        <input type="date" name="entry_date" value="{{ old('entry_date', now()->format('Y-m-d')) }}" class="input input-bordered w-full" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">Descripcion</label>
                        <input name="description" value="{{ old('description') }}" class="input input-bordered w-full" required>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <h2 class="text-sm font-semibold">Lineas</h2>
                    <button type="button" class="btn btn-outline btn-xs" id="add-line">Agregar linea</button>
                </div>
                @error('lines')
                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                @enderror

                <div id="lines-wrapper" class="mt-3 space-y-2"></div>

                <div class="mt-3 text-sm">
                    <span>Total Debe: <strong id="total-debit">0.00</strong></span>
                    <span class="ml-4">Total Haber: <strong id="total-credit">0.00</strong></span>
                </div>

                <div class="mt-6 flex gap-2">
                    <button class="btn btn-primary">Registrar asiento</button>
                    <a href="{{ route('accounting.entries.index') }}" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <template id="line-template">
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-12 entry-line">
            <div class="sm:col-span-5">
                <select class="select select-bordered w-full account-id"></select>
            </div>
            <div class="sm:col-span-3">
                <input type="text" class="input input-bordered w-full line-description" placeholder="Detalle">
            </div>
            <div class="sm:col-span-2">
                <input type="number" min="0" step="0.01" class="input input-bordered w-full line-debit" placeholder="Debe">
            </div>
            <div class="sm:col-span-2">
                <div class="flex gap-2">
                    <input type="number" min="0" step="0.01" class="input input-bordered w-full line-credit" placeholder="Haber">
                    <button type="button" class="btn btn-outline-danger btn-xs remove-line">X</button>
                </div>
            </div>
        </div>
    </template>

    <script>
        (function () {
            const accounts = @json($accounts);
            const oldLines = @json(old('lines', [[], []]));
            const wrapper = document.getElementById('lines-wrapper');
            const template = document.getElementById('line-template');
            const addBtn = document.getElementById('add-line');
            const totalDebitEl = document.getElementById('total-debit');
            const totalCreditEl = document.getElementById('total-credit');

            function updateNames() {
                const rows = wrapper.querySelectorAll('.entry-line');
                rows.forEach((row, i) => {
                    row.querySelector('.account-id').name = `lines[${i}][accounting_account_id]`;
                    row.querySelector('.line-description').name = `lines[${i}][description]`;
                    row.querySelector('.line-debit').name = `lines[${i}][debit]`;
                    row.querySelector('.line-credit').name = `lines[${i}][credit]`;
                });
            }

            function updateTotals() {
                let debit = 0;
                let credit = 0;
                wrapper.querySelectorAll('.line-debit').forEach((input) => debit += parseFloat(input.value || 0));
                wrapper.querySelectorAll('.line-credit').forEach((input) => credit += parseFloat(input.value || 0));
                totalDebitEl.textContent = debit.toFixed(2);
                totalCreditEl.textContent = credit.toFixed(2);
            }

            function createRow(values = {}) {
                const row = template.content.firstElementChild.cloneNode(true);
                const select = row.querySelector('.account-id');
                select.innerHTML = '<option value="">Cuenta</option>' + accounts
                    .filter((account) => account.is_postable)
                    .map((account) => `<option value="${account.id}">${account.code} - ${account.name}</option>`)
                    .join('');

                select.value = values.accounting_account_id || '';
                row.querySelector('.line-description').value = values.description || '';
                row.querySelector('.line-debit').value = values.debit || '';
                row.querySelector('.line-credit').value = values.credit || '';

                row.querySelector('.remove-line').addEventListener('click', function () {
                    row.remove();
                    updateNames();
                    updateTotals();
                });
                row.querySelector('.line-debit').addEventListener('input', updateTotals);
                row.querySelector('.line-credit').addEventListener('input', updateTotals);

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
                createRow();
            }
        })();
    </script>
@endsection
