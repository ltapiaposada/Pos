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

            <div class="mt-4 space-y-3 md:hidden">
                @forelse ($rows as $row)
                    <article class="surface-muted rounded-2xl p-4">
                        <p class="text-sm font-semibold">{{ $row['account']->code }} - {{ $row['account']->name }}</p>
                        <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                            <div class="rounded-xl border border-base-300 bg-base-100 px-2 py-2 text-center">
                                <p class="text-base-content/60">Debe</p>
                                <p class="font-semibold">{{ number_format($row['debit'], 2) }}</p>
                            </div>
                            <div class="rounded-xl border border-base-300 bg-base-100 px-2 py-2 text-center">
                                <p class="text-base-content/60">Haber</p>
                                <p class="font-semibold">{{ number_format($row['credit'], 2) }}</p>
                            </div>
                            <div class="rounded-xl border border-base-300 bg-base-100 px-2 py-2 text-center">
                                <p class="text-base-content/60">Saldo</p>
                                <p class="font-semibold">{{ number_format($row['balance'], 2) }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">
                        Sin movimientos para el filtro seleccionado.
                    </div>
                @endforelse
                @if (count($rows) > 0)
                    <article class="rounded-2xl border border-base-300 bg-base-100 p-4 text-sm">
                        <div class="flex justify-between"><span>Debe total</span><span class="font-semibold">{{ number_format($totalDebit, 2) }}</span></div>
                        <div class="mt-1 flex justify-between"><span>Haber total</span><span class="font-semibold">{{ number_format($totalCredit, 2) }}</span></div>
                        <div class="mt-1 flex justify-between"><span>Saldo</span><span class="font-semibold">{{ number_format($totalDebit - $totalCredit, 2) }}</span></div>
                    </article>
                @endif
            </div>

            <div class="overflow-x-auto mt-4 hidden md:block">
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
