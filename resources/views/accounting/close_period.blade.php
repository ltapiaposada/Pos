@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Cierre de periodo</h1>
                <p class="page-subtitle">Cancela ingresos y gastos contra patrimonio</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('accounting.entries.index') }}" class="btn btn-outline btn-sm">Libro diario</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('accounting.close-period.store') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="field-label">Desde</label>
                    <input type="date" name="from_date" value="{{ old('from_date') }}" class="input input-bordered w-full" required>
                    @error('from_date')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="field-label">Hasta</label>
                    <input type="date" name="to_date" value="{{ old('to_date') }}" class="input input-bordered w-full" required>
                    @error('to_date')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="field-label">Fecha asiento</label>
                    <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}" class="input input-bordered w-full" required>
                    @error('entry_date')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="field-label">Cuenta patrimonio (PUC)</label>
                    <input type="text" name="equity_account_code" value="{{ old('equity_account_code', '3605') }}" class="input input-bordered w-full" required>
                    @error('equity_account_code')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="field-label">Descripcion</label>
                    <input type="text" name="description" value="{{ old('description', 'Cierre contable del periodo') }}" class="input input-bordered w-full" required>
                    @error('description')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <button class="btn btn-primary">Ejecutar cierre</button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <h2 class="text-base font-semibold mb-3">Ultimos cierres</h2>
            <div class="space-y-3 md:hidden">
                @forelse($closures as $closure)
                    <article class="surface-muted rounded-2xl p-4">
                        <p class="text-sm font-semibold">{{ $closure->from_date->format('Y-m-d') }} a {{ $closure->to_date->format('Y-m-d') }}</p>
                        <p class="text-xs text-base-content/60">Fecha asiento: {{ $closure->entry_date->format('Y-m-d') }}</p>
                        <div class="mt-2 flex items-center justify-between text-sm">
                            <span>Utilidad neta</span>
                            <span class="font-semibold">{{ number_format($closure->net_income, 2) }}</span>
                        </div>
                        <p class="mt-1 text-xs text-base-content/60">Usuario: {{ $closure->user->name }}</p>
                        <a href="{{ route('accounting.entries.show', $closure->journalEntry) }}" class="btn btn-outline btn-xs mt-3">Ver asiento</a>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">Aun no hay cierres registrados.</div>
                @endforelse
            </div>

            <div class="overflow-x-auto hidden md:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Periodo</th>
                            <th>Fecha asiento</th>
                            <th>Asiento</th>
                            <th class="text-right">Utilidad neta</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($closures as $closure)
                            <tr>
                                <td>{{ $closure->from_date->format('Y-m-d') }} a {{ $closure->to_date->format('Y-m-d') }}</td>
                                <td>{{ $closure->entry_date->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('accounting.entries.show', $closure->journalEntry) }}" class="link link-primary">
                                        {{ $closure->journalEntry->entry_number }}
                                    </a>
                                </td>
                                <td class="text-right">{{ number_format($closure->net_income, 2) }}</td>
                                <td>{{ $closure->user->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-base-content/60">Aun no hay cierres registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
