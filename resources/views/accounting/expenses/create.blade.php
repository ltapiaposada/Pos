@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Registrar gasto</h1>
                <p class="page-subtitle">Registra el gasto y crea el asiento contable automaticamente</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('accounting.entries.index') }}" class="btn btn-outline btn-sm">Libro diario</a>
            </div>
        </div>
    </div>

    @if ($errors->has('expense'))
        <div class="alert alert-error mt-4">{{ $errors->first('expense') }}</div>
    @endif

    <div class="mt-6 panel max-w-3xl">
        <div class="panel-body">
            <form method="POST" action="{{ route('accounting.expenses.store') }}" class="grid gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="field-label">Fecha</label>
                    <input
                        name="expense_date"
                        type="date"
                        value="{{ old('expense_date', now()->toDateString()) }}"
                        class="input input-bordered w-full @error('expense_date') input-error @enderror"
                        required
                    >
                    @error('expense_date')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="field-label">Monto</label>
                    <input
                        name="amount"
                        type="number"
                        min="0.01"
                        step="0.01"
                        value="{{ old('amount') }}"
                        class="input input-bordered w-full @error('amount') input-error @enderror"
                        placeholder="0.00"
                        required
                    >
                    @error('amount')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="field-label">Cuenta de gasto</label>
                    <select
                        name="expense_account_id"
                        class="select select-bordered w-full @error('expense_account_id') select-error @enderror"
                        required
                    >
                        <option value="">Selecciona una cuenta</option>
                        @foreach ($expenseAccounts as $account)
                            <option value="{{ $account->id }}" @selected((string) old('expense_account_id') === (string) $account->id)>
                                {{ $account->code }} - {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('expense_account_id')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="field-label">Forma de pago</label>
                    <select
                        name="payment_method"
                        class="select select-bordered w-full @error('payment_method') select-error @enderror"
                        required
                    >
                        <option value="cash" @selected(old('payment_method') === 'cash')>Efectivo</option>
                        <option value="bank" @selected(old('payment_method') === 'bank')>Banco</option>
                        <option value="credit" @selected(old('payment_method') === 'credit')>Credito / CxP</option>
                    </select>
                    @error('payment_method')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="field-label">Sucursal (efectivo)</label>
                    <select
                        name="branch_id"
                        class="select select-bordered w-full @error('branch_id') select-error @enderror"
                    >
                        <option value="">Selecciona sucursal</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $defaultBranchId) === (string) $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-base-content/60">Se usa para crear salida de caja cuando el pago es en efectivo.</p>
                    @error('branch_id')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="field-label">Descripcion</label>
                    <input
                        name="description"
                        value="{{ old('description') }}"
                        class="input input-bordered w-full @error('description') input-error @enderror"
                        placeholder="Ej: pago de internet oficina"
                        required
                    >
                    @error('description')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 flex justify-end gap-2">
                    <a href="{{ route('accounting.entries.index') }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar gasto</button>
                </div>
            </form>
        </div>
    </div>
@endsection
