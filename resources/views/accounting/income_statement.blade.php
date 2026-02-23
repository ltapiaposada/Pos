@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Estado de resultados</h1>
                <p class="page-subtitle">Ingresos, gastos y utilidad del periodo</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('accounting.income-statement.export', array_filter(['from' => $from, 'to' => $to, 'format' => 'excel'])) }}" class="btn btn-outline btn-sm">Excel</a>
                <a href="{{ route('accounting.income-statement.export', array_filter(['from' => $from, 'to' => $to, 'format' => 'pdf'])) }}" class="btn btn-outline btn-sm" target="_blank">PDF</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label class="field-label">Desde</label>
                    <input type="date" name="from" value="{{ $from }}" class="input input-bordered w-full">
                </div>
                <div>
                    <label class="field-label">Hasta</label>
                    <input type="date" name="to" value="{{ $to }}" class="input input-bordered w-full">
                </div>
                <div class="flex items-end gap-2">
                    <button class="btn btn-primary btn-sm">Filtrar</button>
                    <a href="{{ route('accounting.income-statement') }}" class="btn btn-outline btn-sm">Limpiar</a>
                </div>
            </form>

            <div class="grid gap-4 mt-5 lg:grid-cols-2">
                <div class="overflow-x-auto">
                    <h2 class="text-sm font-semibold mb-2">Ingresos</h2>
                    <div class="space-y-2 md:hidden">
                        @forelse ($incomeRows as $row)
                            <article class="surface-muted rounded-xl p-3">
                                <p class="text-sm">{{ $row['account']->code }} - {{ $row['account']->name }}</p>
                                <p class="text-sm font-semibold mt-1">{{ number_format($row['balance'], 2) }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-base-content/60">Sin ingresos para el periodo.</p>
                        @endforelse
                        <article class="rounded-xl border border-base-300 bg-base-100 p-3">
                            <p class="text-xs text-base-content/60">Total ingresos</p>
                            <p class="font-semibold">{{ number_format($totalIncome, 2) }}</p>
                        </article>
                    </div>
                    <table class="table hidden md:table">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th class="text-right">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($incomeRows as $row)
                                <tr>
                                    <td>{{ $row['account']->code }} - {{ $row['account']->name }}</td>
                                    <td class="text-right">{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-base-content/60">Sin ingresos para el periodo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total ingresos</th>
                                <th class="text-right">{{ number_format($totalIncome, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="overflow-x-auto">
                    <h2 class="text-sm font-semibold mb-2">Gastos</h2>
                    <div class="space-y-2 md:hidden">
                        @forelse ($expenseRows as $row)
                            <article class="surface-muted rounded-xl p-3">
                                <p class="text-sm">{{ $row['account']->code }} - {{ $row['account']->name }}</p>
                                <p class="text-sm font-semibold mt-1">{{ number_format($row['balance'], 2) }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-base-content/60">Sin gastos para el periodo.</p>
                        @endforelse
                        <article class="rounded-xl border border-base-300 bg-base-100 p-3">
                            <p class="text-xs text-base-content/60">Total gastos</p>
                            <p class="font-semibold">{{ number_format($totalExpense, 2) }}</p>
                        </article>
                    </div>
                    <table class="table hidden md:table">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th class="text-right">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expenseRows as $row)
                                <tr>
                                    <td>{{ $row['account']->code }} - {{ $row['account']->name }}</td>
                                    <td class="text-right">{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-base-content/60">Sin gastos para el periodo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total gastos</th>
                                <th class="text-right">{{ number_format($totalExpense, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="mt-6 p-4 rounded-lg border bg-base-100">
                <div class="text-sm text-base-content/70">Utilidad neta</div>
                <div class="text-2xl font-bold {{ $netIncome >= 0 ? 'text-success' : 'text-error' }}">
                    {{ number_format($netIncome, 2) }}
                </div>
            </div>
        </div>
    </div>
@endsection
