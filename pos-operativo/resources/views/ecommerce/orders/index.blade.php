@extends('ecommerce.layouts.app')

@section('content')
    @php
        $statusLabels = ['pending' => 'Recibido', 'processing' => 'Confirmado', 'shipped' => 'Despachado', 'delivered' => 'Entregado', 'cancelled' => 'Cancelado'];
        $paymentLabels = ['card' => 'Tarjeta', 'transfer' => 'Transferencia por validar', 'qr' => 'QR por validar', 'contraentrega' => 'Contraentrega', 'other' => 'Otro', 'cash' => 'Efectivo', 'credit' => 'Credito'];
    @endphp
    <section class="shop-page">
        <header class="shop-page-header"><div><div class="shop-page-header__eyebrow">Tu cuenta</div><h1>Mis pedidos</h1><p>Consulta el estado, el pago registrado y el detalle de cada compra.</p></div><a href="{{ route('shop.index') }}" class="shop-action shop-action--soft">Seguir comprando</a></header>
        @if ($orders->isEmpty())
            <section class="shop-surface"><h2>Aun no tienes pedidos</h2><p class="shop-note">Cuando finalices una compra podras consultar su estado aqui.</p><a href="{{ route('shop.index') }}" class="shop-action mt-3">Ir al catalogo</a></section>
        @else
            <section class="shop-order-list">
                @foreach ($orders as $order)
                    <article class="shop-order-card">
                        <div><div class="shop-order-card__number">Pedido #{{ $order->sale_number }}</div><div class="shop-order-card__info">{{ $order->sold_at?->format('d/m/Y H:i') }} · {{ $paymentLabels[$order->payments->first()?->method ?? ''] ?? 'Sin registrar' }}</div>@if ($order->status === 'pending')<div class="shop-note">Estamos validando el pago o la modalidad de entrega.</div>@endif</div>
                        <div class="d-flex flex-column align-items-md-end gap-2"><span class="shop-status shop-status--{{ $order->status }}">{{ $statusLabels[$order->status] ?? strtoupper($order->status) }}</span><strong>COP {{ number_format((float) $order->total, 2) }}</strong><a href="{{ route('shop.orders.show', $order) }}" class="shop-action shop-action--soft">Ver detalle</a></div>
                    </article>
                @endforeach
            </section>
            <div class="mt-4">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection
