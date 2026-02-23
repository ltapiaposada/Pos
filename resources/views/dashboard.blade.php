@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Resumen rapido de ventas y operacion</p>
            </div>
            <div class="page-actions">
                <span class="chip">Hoy</span>
                <span class="chip">Sucursal principal</span>
            </div>
        </div>
    </div>

    <div class="mt-6 kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Ventas del dia</div>
            <div class="kpi-value">${{ number_format($salesTotal, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Transacciones</div>
            <div class="kpi-value">{{ $salesCount }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Ticket promedio</div>
            <div class="kpi-value">${{ number_format($avgTicket, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Stock bajo</div>
            <div class="kpi-value">{{ $lowStock }}</div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-header">
            <h2 class="text-sm font-semibold text-base-content/80">Ventas recientes</h2>
            <span class="chip">Ultimos movimientos</span>
        </div>
        <div class="panel-body">
            <div class="space-y-3 md:hidden">
                @forelse ($recentSales as $sale)
                    <article class="surface-muted rounded-2xl p-4">
                        <p class="text-sm font-semibold">#{{ $sale->sale_number }}</p>
                        <p class="text-xs text-base-content/60">{{ $sale->sold_at->format('d/m/Y H:i') }}</p>
                        <p class="mt-2 text-sm font-semibold">${{ number_format($sale->total, 2) }}</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">Sin ventas aun.</div>
                @endforelse
            </div>

            <div class="overflow-x-auto hidden md:block">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Fecha</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentSales as $sale)
                            <tr>
                                <td>#{{ $sale->sale_number }}</td>
                                <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                                <td>${{ number_format($sale->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-base-content/60">Sin ventas aun.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
