@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Balance general</h1>
                <p class="page-subtitle">Situacion financiera a una fecha</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('accounting.trial-balance') }}" class="btn btn-outline btn-sm">Balance de prueba</a>
                <a href="{{ route('accounting.balance-sheet.export', ['as_of' => $asOf, 'format' => 'excel']) }}" class="btn btn-outline btn-sm">Excel</a>
                <a href="{{ route('accounting.balance-sheet.export', ['as_of' => $asOf, 'format' => 'pdf']) }}" class="btn btn-outline btn-sm" target="_blank">PDF</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label class="field-label">Corte a</label>
                    <input type="date" name="as_of" value="{{ $asOf }}" class="input input-bordered w-full">
                </div>
                <div class="flex items-end gap-2 sm:col-span-2">
                    <button class="btn btn-primary btn-sm">Filtrar</button>
                    <a href="{{ route('accounting.balance-sheet') }}" class="btn btn-outline btn-sm">Hoy</a>
                </div>
            </form>

            <div class="grid gap-4 mt-5 lg:grid-cols-3">
                <div class="overflow-x-auto">
                    <h2 class="text-sm font-semibold mb-2">Activos</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th class="text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assetRows as $row)
                                <tr>
                                    <td>{{ $row['account']->code }} - {{ $row['account']->name }}</td>
                                    <td class="text-right">{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-base-content/60">Sin saldo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total activos</th>
                                <th class="text-right">{{ number_format($totalAssets, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="overflow-x-auto">
                    <h2 class="text-sm font-semibold mb-2">Pasivos</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th class="text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($liabilityRows as $row)
                                <tr>
                                    <td>{{ $row['account']->code }} - {{ $row['account']->name }}</td>
                                    <td class="text-right">{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-base-content/60">Sin saldo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total pasivos</th>
                                <th class="text-right">{{ number_format($totalLiabilities, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="overflow-x-auto">
                    <h2 class="text-sm font-semibold mb-2">Patrimonio</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th class="text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($equityRows as $row)
                                <tr>
                                    <td>{{ $row['account']->code }} - {{ $row['account']->name }}</td>
                                    <td class="text-right">{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-base-content/60">Sin saldo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total patrimonio</th>
                                <th class="text-right">{{ number_format($totalEquity, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="grid gap-4 mt-6 md:grid-cols-3">
                <div class="p-4 rounded-lg border bg-base-100">
                    <div class="text-sm text-base-content/70">Activos</div>
                    <div class="text-xl font-bold">{{ number_format($totalAssets, 2) }}</div>
                </div>
                <div class="p-4 rounded-lg border bg-base-100">
                    <div class="text-sm text-base-content/70">Pasivo + Patrimonio</div>
                    <div class="text-xl font-bold">{{ number_format($totalLiabilitiesAndEquity, 2) }}</div>
                </div>
                <div class="p-4 rounded-lg border bg-base-100">
                    <div class="text-sm text-base-content/70">Diferencia</div>
                    <div class="text-xl font-bold {{ abs($difference) < 0.01 ? 'text-success' : 'text-error' }}">
                        {{ number_format($difference, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
