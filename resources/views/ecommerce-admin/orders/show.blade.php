@extends('layouts.admin')

@section('content')
    @php
        $paymentLabels = [
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
            'qr' => 'Pago QR',
            'contraentrega' => 'Contraentrega',
            'other' => 'Otro',
            'cash' => 'Efectivo',
            'credit' => 'Credito',
        ];
    @endphp
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Pedido #{{ $order->sale_number }}</h1>
                <p class="page-subtitle">Detalle operativo del pedido e-commerce</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('ecommerce-admin.orders.index') }}" class="btn btn-outline btn-sm">Volver</a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3 mt-6">
        <div class="lg:col-span-2 panel">
            <div class="panel-body">
                <h2 class="text-sm font-semibold mb-3">Items del pedido</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cant.</th>
                                <th>Precio</th>
                                <th>Impuesto</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td>${{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td>${{ number_format((float) $item->tax_amount, 2) }}</td>
                                    <td>${{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="text-sm font-semibold mb-3">Gestion del pedido</h2>
                    <form method="POST" action="{{ route('ecommerce-admin.orders.status', $order) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="field-label">Estado</label>
                            <select name="status" class="select select-bordered w-full">
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary btn-sm w-full">Actualizar estado</button>
                    </form>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body text-sm space-y-2">
                    <div><strong>Cliente:</strong> {{ $order->customer?->name }}</div>
                    <div><strong>Email:</strong> {{ $order->customer?->email }}</div>
                    <div><strong>Pago:</strong> {{ $paymentLabels[$order->payments->first()?->method ?? ''] ?? 'Sin registrar' }}</div>
                    <div><strong>Direccion:</strong> {{ $order->delivery_address ?: 'Sin direccion' }}</div>
                    <div><strong>Nota:</strong> {{ $order->customer_note ?: 'Sin nota' }}</div>
                    <div><strong>Cupon:</strong> {{ $order->coupon_code ?: 'N/A' }}</div>
                    <hr>
                    <div class="flex justify-between"><span>Subtotal</span><strong>${{ number_format((float) $order->subtotal, 2) }}</strong></div>
                    <div class="flex justify-between"><span>Impuestos</span><strong>${{ number_format((float) $order->tax_total, 2) }}</strong></div>
                    <div class="flex justify-between"><span>Envio</span><strong>${{ number_format((float) $order->shipping_total, 2) }}</strong></div>
                    <div class="flex justify-between"><span>Descuento cupon</span><strong>${{ number_format((float) $order->coupon_discount_total, 2) }}</strong></div>
                    <div class="flex justify-between"><span>Total</span><strong>${{ number_format((float) $order->total, 2) }}</strong></div>
                </div>
            </div>
        </div>
    </div>
@endsection
