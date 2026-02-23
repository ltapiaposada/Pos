@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Balance de prueba</h1>
                <p class="page-subtitle">Resumen por cuenta PUC</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('accounting.entries.index') }}" class="btn btn-outline btn-sm">Libro diario</a>
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
                    <a href="{{ route('accounting.trial-balance') }}" class="btn btn-outline btn-sm">Limpiar</a>
                </div>
            </form>

            <div class="overflow-x-auto mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th class="text-right">Debe</th>
                            <th class="text-right">Haber</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ $row['account']->code }} - {{ $row['account']->name }}</td>
                                <td class="text-right">{{ number_format($row['debit'], 2) }}</td>
                                <td class="text-right">{{ number_format($row['credit'], 2) }}</td>
                                <td class="text-right">{{ number_format($row['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-base-content/60">Sin movimientos para el filtro seleccionado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-right">Totales</th>
                            <th class="text-right">{{ number_format($totalDebit, 2) }}</th>
                            <th class="text-right">{{ number_format($totalCredit, 2) }}</th>
                            <th class="text-right">{{ number_format($totalDebit - $totalCredit, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

