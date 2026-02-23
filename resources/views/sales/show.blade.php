@extends('layouts.admin')

@section('content')
    @php
        $business = \App\Models\Setting::getValue('business', []);
        $logoUrl = $business['logo_url'] ?? null;
    @endphp

    <div class="page-header">
        <div class="page-header-row">
            <div>
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 ring-1 ring-primary/20">
                    <x-application-logo :logo-url="$logoUrl" :size="36" class="max-h-9 w-auto object-contain" />
                </div>
                <h1 class="page-title">Venta #{{ $sale->sale_number }}</h1>
                <p class="page-subtitle">Detalle de venta y pagos</p>
            </div>
            <div class="page-actions">
                <a
                    href="{{ route('sales.ticket', $sale) }}"
                    class="btn btn-primary"
                    onclick="window.open(this.href, 'pos_invoice_popup', 'width=420,height=760,scrollbars=yes,resizable=yes'); return false;"
                >
                    Imprimir ticket
                </a>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 panel">
            <div class="panel-body">
                <h2 class="text-sm font-semibold">Items</h2>
                <div class="overflow-x-auto">
                    <table class="table table-sm mt-3">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ number_format($item->quantity, 3) }}</td>
                                    <td>${{ number_format($item->unit_price, 2) }}</td>
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
                    <div class="flex justify-between"><span>Subtotal</span><span>${{ number_format($sale->subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span>Descuentos</span><span>${{ number_format($sale->discount_total, 2) }}</span></div>
                    <div class="flex justify-between"><span>Impuestos</span><span>${{ number_format($sale->tax_total, 2) }}</span></div>
                    <div class="flex justify-between font-semibold"><span>Total</span><span>${{ number_format($sale->total, 2) }}</span></div>
                    <div class="flex justify-between"><span>Pagado</span><span>${{ number_format($sale->paid_total, 2) }}</span></div>
                    <div class="flex justify-between"><span>Saldo</span><span>${{ number_format(max(0, (float) $sale->total - (float) $sale->paid_total), 2) }}</span></div>
                    <div class="flex justify-between"><span>Cambio</span><span>${{ number_format($sale->change_total, 2) }}</span></div>
                </div>
                <div class="mt-4">
                    <h3 class="text-xs font-semibold uppercase text-base-content/60">Pagos</h3>
                    <ul class="mt-2 text-sm space-y-1">
                        @foreach ($sale->payments as $payment)
                            <li class="flex justify-between">
                                <span>{{ $payment->method === 'cash' ? 'Efectivo' : ($payment->method === 'card' ? 'Tarjeta' : ($payment->method === 'transfer' ? 'Transferencia' : ($payment->method === 'credit' ? 'Credito' : strtoupper($payment->method)))) }}</span>
                                <span>${{ number_format($payment->amount, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
