@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Devolucion #{{ $return->id }}</h1>
                <p class="page-subtitle">Detalle y resumen de reembolso</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <div class="grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <div class="text-xs text-base-content/60">Venta</div>
                    <div class="font-semibold">#{{ $return->sale_id }}</div>
                </div>
                <div>
                    <div class="text-xs text-base-content/60">Total</div>
                    <div class="font-semibold">${{ number_format($return->total, 2) }}</div>
                </div>
            </div>
            <h2 class="mt-6 text-sm font-semibold">Items</h2>
            <div class="overflow-x-auto">
                <table class="table table-sm mt-3">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($return->items as $item)
                            <tr>
                                <td>{{ $item->product_id }}</td>
                                <td>{{ number_format($item->quantity, 3) }}</td>
                                <td>${{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
