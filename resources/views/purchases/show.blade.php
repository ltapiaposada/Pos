@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Compra #{{ $purchase->purchase_number }}</h1>
                <p class="page-subtitle">Detalle de compra y costos</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('purchases.index') }}" class="btn btn-outline">Volver</a>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 panel">
            <div class="panel-body">
                <h2 class="text-sm font-semibold">Items</h2>
                <div class="overflow-x-auto mt-3">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Costo unit.</th>
                                <th>Imp %</th>
                                <th>Total línea</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchase->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ number_format($item->quantity, 3) }}</td>
                                    <td>${{ number_format($item->unit_cost, 2) }}</td>
                                    <td>{{ number_format($item->tax_rate, 2) }}</td>
                                    <td>${{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-body">
                <h2 class="text-sm font-semibold">Resumen</h2>
                <div class="mt-4 text-sm space-y-2">
                    <div class="flex justify-between"><span>Proveedor</span><span>{{ $purchase->supplier_name }}</span></div>
                    <div class="flex justify-between"><span>Sucursal</span><span>{{ $purchase->branch->name }}</span></div>
                    <div class="flex justify-between"><span>Factura</span><span>{{ $purchase->invoice_number ?: 'N/A' }}</span></div>
                    <div class="flex justify-between"><span>Fecha</span><span>{{ $purchase->purchased_at->format('Y-m-d H:i') }}</span></div>
                    <div class="flex justify-between"><span>Subtotal</span><span>${{ number_format($purchase->subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span>Impuestos</span><span>${{ number_format($purchase->tax_total, 2) }}</span></div>
                    <div class="flex justify-between font-semibold"><span>Total</span><span>${{ number_format($purchase->total, 2) }}</span></div>
                    <div class="flex justify-between"><span>Pagado</span><span>${{ number_format($purchase->paid_total, 2) }}</span></div>
                    <div class="flex justify-between"><span>Saldo</span><span>${{ number_format($purchase->balance_total, 2) }}</span></div>
                </div>
                <div class="mt-4">
                    <h3 class="text-xs font-semibold uppercase text-base-content/60">Pagos</h3>
                    <ul class="mt-2 text-sm space-y-1">
                        @forelse ($purchase->payments as $payment)
                            <li class="flex justify-between">
                                <span>{{ $payment->method === 'cash' ? 'Efectivo' : 'Transferencia' }}</span>
                                <span>${{ number_format($payment->amount, 2) }}</span>
                            </li>
                        @empty
                            <li class="text-base-content/60">Sin pagos registrados.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
