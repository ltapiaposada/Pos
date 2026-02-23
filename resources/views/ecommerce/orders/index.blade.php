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
        .orders-shell {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
            overflow: hidden;
        }
    </style>

    <section class="shop-mini-hero">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-1 text-white">Mis pedidos</h1>
                <p class="mb-0 text-white-50">Consulta el estado y detalle de tus compras.</p>
            </div>
            <a href="{{ route('shop.index') }}" class="btn btn-light btn-sm">Seguir comprando</a>
        </div>
    </section>

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
        $statusClasses = [
            'pending' => 'text-bg-warning',
            'processing' => 'text-bg-info',
            'shipped' => 'text-bg-primary',
            'delivered' => 'text-bg-success',
            'cancelled' => 'text-bg-danger',
        ];
    @endphp

    @if ($orders->isEmpty())
        <div class="alert alert-secondary">Aun no tienes pedidos.</div>
        <a href="{{ route('shop.index') }}" class="btn btn-primary">Ir a comprar</a>
    @else
        <div class="orders-shell">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pedido</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="fw-semibold">#{{ $order->sale_number }}</td>
                                <td>{{ $order->sold_at?->format('d/m/Y H:i') }}</td>
                                <td><span class="badge {{ $statusClasses[$order->status] ?? 'text-bg-secondary' }}">{{ $statusLabels[$order->status] ?? strtoupper($order->status) }}</span></td>
                                <td>{{ $paymentLabels[$order->payments->first()?->method ?? ''] ?? 'Sin registrar' }}</td>
                                <td class="fw-semibold">${{ number_format((float) $order->total, 2) }}</td>
                                <td>
                                    <a href="{{ route('shop.orders.show', $order) }}" class="btn btn-outline-secondary btn-sm">Ver detalle</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $orders->links() }}
        </div>
    @endif
@endsection
