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
                    <table class="table">
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
                    <table class="table">
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
