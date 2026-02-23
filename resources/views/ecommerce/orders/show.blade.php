@extends('ecommerce.layouts.app')

@section('content')
    <style>
        .shop-mini-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #0ea5e9 100%);
            border-radius: 1rem;
            color: #fff;
            padding: 1.4rem 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .18);
        }
        .order-info-card {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
        }
        .order-summary-card {
            border: 1px solid #dbeafe;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 10px 24px rgba(14, 116, 144, 0.09);
        }
    </style>
    @php
        $statusLabels = [
            'pending' => 'Pendiente',
            'processing' => 'Procesando',
            'shipped' => 'Enviado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        ];
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

    <section class="shop-mini-hero">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-1 text-white">Pedido #{{ $order->sale_number }}</h1>
                <p class="mb-0 text-white-50">Detalle completo de tu compra.</p>
            </div>
            <a href="{{ route('shop.orders.index') }}" class="btn btn-light btn-sm">Volver</a>
        </div>
    </section>

    <div class="order-info-card mb-3">
        <div class="card-body d-flex flex-wrap gap-4">
            <div><strong>Fecha:</strong> {{ $order->sold_at?->format('d/m/Y H:i') }}</div>
            <div><strong>Estado:</strong> {{ $statusLabels[$order->status] ?? strtoupper($order->status) }}</div>
            <div><strong>Pago:</strong> {{ $paymentLabels[$order->payments->first()?->method ?? ''] ?? 'Sin registrar' }}</div>
            <div><strong>Sucursal:</strong> {{ $order->branch?->name }}</div>
            <div><strong>Direccion:</strong> {{ $order->delivery_address ?: 'Sin direccion' }}</div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="order-info-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
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
        </div>
        <div class="col-xl-4">
            <div class="order-summary-card">
                <div class="card-body">
                    <h2 class="h6 mb-3">Resumen del pedido</h2>
                    <div class="d-flex justify-content-between"><span>Subtotal</span><strong>${{ number_format((float) $order->subtotal, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mt-2"><span>Impuestos</span><strong>${{ number_format((float) $order->tax_total, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mt-2"><span>Envio</span><strong>${{ number_format((float) $order->shipping_total, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mt-2"><span>Descuento cupon</span><strong>${{ number_format((float) $order->coupon_discount_total, 2) }}</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between"><span>Total</span><strong>${{ number_format((float) $order->total, 2) }}</strong></div>
                </div>
            </div>
        </div>
    </div>
@endsection
