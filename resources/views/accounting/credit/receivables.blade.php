@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Cuentas por cobrar</h1>
                <p class="page-subtitle">Ventas con saldo pendiente y registro de abonos</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('sales.index') }}" class="btn btn-outline btn-sm">Ver facturas</a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mt-4">
            <ul class="space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 panel">
        <div class="panel-body">
            <div class="space-y-3 md:hidden">
                @forelse ($sales as $sale)
                    @php
                        $balance = max(0, (float) $sale->total - (float) $sale->paid_total);
                    @endphp
                    <article class="surface-muted rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold">#{{ $sale->sale_number }}</p>
                                <p class="text-xs text-base-content/60">{{ $sale->sold_at?->format('Y-m-d H:i') }}</p>
                            </div>
                            <span class="text-sm font-semibold">${{ number_format($balance, 2) }}</span>
                        </div>
                        <div class="mt-2 space-y-1 text-sm">
                            <p><span class="text-base-content/60">Cliente:</span> {{ $sale->customer?->name ?? 'Consumidor final' }}</p>
                            <p><span class="text-base-content/60">Sucursal:</span> {{ $sale->branch?->name }}</p>
                            <p><span class="text-base-content/60">Total:</span> ${{ number_format((float) $sale->total, 2) }}</p>
                            <p><span class="text-base-content/60">Pagado:</span> ${{ number_format((float) $sale->paid_total, 2) }}</p>
                        </div>
                        <form method="POST" action="{{ route('accounting.receivables.collect', $sale) }}" class="mt-3 grid grid-cols-1 gap-2">
                            @csrf
                            <input name="amount" type="number" step="0.01" min="0.01" max="{{ number_format($balance, 2, '.', '') }}" class="input input-bordered input-sm w-full" placeholder="Monto" required>
                            <select name="method" class="select select-bordered select-sm w-full">
                                <option value="cash">Efectivo</option>
                                <option value="card">Tarjeta</option>
                                <option value="transfer">Transferencia</option>
                            </select>
                            <input name="reference" class="input input-bordered input-sm w-full" placeholder="Ref">
                            <button class="btn btn-primary btn-sm w-full">Registrar</button>
                        </form>
                        @if ($sale->payments->isNotEmpty())
                            <div class="mt-3 space-y-1">
                                @foreach ($sale->payments as $payment)
                                    <div class="flex items-center justify-between gap-2 text-xs">
                                        <span>
                                            {{ $payment->paid_at?->format('Y-m-d H:i') }}
                                            - {{ strtoupper($payment->method) }}
                                            - ${{ number_format((float) $payment->amount, 2) }}
                                        </span>
                                        @if ($payment->voided_at)
                                            <span class="badge badge-error badge-xs">Anulado</span>
                                        @else
                                            <form method="POST" action="{{ route('accounting.receivables.payments.void', [$sale, $payment]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline btn-xs" data-confirm="Anular este abono?">Anular</button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">No hay cuentas por cobrar pendientes.</div>
                @endforelse
            </div>

            <div class="overflow-x-auto hidden md:block">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Venta</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Sucursal</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Pagado</th>
                            <th class="text-right">Saldo</th>
                            <th>Abono</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            @php
                                $balance = max(0, (float) $sale->total - (float) $sale->paid_total);
                            @endphp
                            <tr>
                                <td>#{{ $sale->sale_number }}</td>
                                <td>{{ $sale->sold_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ $sale->customer?->name ?? 'Consumidor final' }}</td>
                                <td>{{ $sale->branch?->name }}</td>
                                <td class="text-right">${{ number_format((float) $sale->total, 2) }}</td>
                                <td class="text-right">${{ number_format((float) $sale->paid_total, 2) }}</td>
                                <td class="text-right font-semibold">${{ number_format($balance, 2) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('accounting.receivables.collect', $sale) }}" class="flex gap-2 items-center flex-wrap">
                                        @csrf
                                        <input name="amount" type="number" step="0.01" min="0.01" max="{{ number_format($balance, 2, '.', '') }}" class="input input-bordered input-xs w-24" placeholder="Monto" required>
                                        <select name="method" class="select select-bordered select-xs">
                                            <option value="cash">Efectivo</option>
                                            <option value="card">Tarjeta</option>
                                            <option value="transfer">Transferencia</option>
                                        </select>
                                        <input name="reference" class="input input-bordered input-xs w-28" placeholder="Ref">
                                        <button class="btn btn-primary btn-xs">Registrar</button>
                                    </form>

                                    @if ($sale->payments->isNotEmpty())
                                        <div class="mt-2 space-y-1">
                                            @foreach ($sale->payments as $payment)
                                                <div class="flex items-center justify-between gap-2 text-xs">
                                                    <span>
                                                        {{ $payment->paid_at?->format('Y-m-d H:i') }}
                                                        - {{ strtoupper($payment->method) }}
                                                        - ${{ number_format((float) $payment->amount, 2) }}
                                                    </span>
                                                    @if ($payment->voided_at)
                                                        <span class="badge badge-error badge-xs">Anulado</span>
                                                    @else
                                                        <form method="POST" action="{{ route('accounting.receivables.payments.void', [$sale, $payment]) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline btn-xs" data-confirm="¿Anular este abono?">Anular</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-base-content/60">No hay cuentas por cobrar pendientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $sales->links() }}
    </div>
@endsection
