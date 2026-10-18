@extends('ecommerce.layouts.app')

@section('content')
    @php
        $statusLabels = ['pending' => 'Recibido', 'processing' => 'Confirmado', 'shipped' => 'Despachado', 'delivered' => 'Entregado', 'cancelled' => 'Cancelado'];
        $paymentLabels = ['card' => 'Tarjeta', 'transfer' => 'Transferencia por validar', 'qr' => 'QR por validar', 'contraentrega' => 'Contraentrega', 'other' => 'Otro', 'cash' => 'Efectivo', 'credit' => 'Credito'];
    @endphp
    <style>.order-detail-layout{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(280px,.7fr);gap:24px;align-items:start}.order-detail-summary{position:sticky;top:96px}@media(max-width:900px){.order-detail-layout{grid-template-columns:1fr}.order-detail-summary{position:static}}</style>
    <section class="shop-page">
        <header class="shop-page-header"><div><div class="shop-page-header__eyebrow">Seguimiento</div><h1>Pedido #{{ $order->sale_number }}</h1><p>Revisa el detalle y el estado actual de tu compra.</p></div><a href="{{ route('shop.orders.index') }}" class="shop-action shop-action--soft">Volver a pedidos</a></header>
        @if ($order->status === 'pending')<div class="shop-notice">Recibimos tu pedido. Si pagaste por transferencia o QR, revisaremos la referencia antes de confirmarlo.</div>@endif
        <div class="order-detail-layout mt-4">
            <section class="shop-surface"><h2>Productos del pedido</h2>
                @foreach ($order->items as $item)
                    <article class="shop-line-item"><div><div class="shop-line-item__name">{{ $item->product_name }}</div><div class="shop-line-item__meta">Cantidad {{ number_format((float) $item->quantity, 2) }} · Precio COP {{ number_format((float) $item->unit_price, 2) }} · Impuesto COP {{ number_format((float) $item->tax_amount, 2) }}</div>@if ($item->serials->isNotEmpty())<div class="shop-note">Seriales: {{ $item->serials->pluck('serial_number')->join(', ') }}</div>@endif @if ($item->lots->isNotEmpty())<div class="shop-note">Lotes: {{ $item->lots->map(fn ($allocation) => $allocation->lot?->lot_number)->filter()->join(', ') }}</div>@endif @if ($item->delivery_instructions)<div class="shop-note">{!! nl2br(e($item->delivery_instructions)) !!}</div>@endif</div><div class="shop-line-item__price">COP {{ number_format((float) $item->line_total, 2) }}</div></article>
                @endforeach
            </section>
            <aside class="shop-summary order-detail-summary"><h2>Resumen del pedido</h2><div class="shop-totals"><div class="shop-total-row"><span>Fecha</span><strong>{{ $order->sold_at?->format('d/m/Y H:i') }}</strong></div><div class="shop-total-row"><span>Estado</span><span class="shop-status shop-status--{{ $order->status }}">{{ $statusLabels[$order->status] ?? strtoupper($order->status) }}</span></div><div class="shop-total-row"><span>Pago</span><strong>{{ $paymentLabels[$order->payments->first()?->method ?? ''] ?? 'Sin registrar' }}</strong></div><div class="shop-total-row"><span>Entrega</span><strong>{{ $order->delivery_address ?: 'Sin direccion' }}</strong></div><div class="shop-total-row"><span>Subtotal</span><strong>COP {{ number_format((float) $order->subtotal, 2) }}</strong></div><div class="shop-total-row"><span>Impuestos</span><strong>COP {{ number_format((float) $order->tax_total, 2) }}</strong></div><div class="shop-total-row"><span>Envio</span><strong>COP {{ number_format((float) $order->shipping_total, 2) }}</strong></div><div class="shop-total-row"><span>Descuento</span><strong>COP {{ number_format((float) $order->coupon_discount_total, 2) }}</strong></div><div class="shop-total-row shop-total-row--final"><span>Total</span><strong>COP {{ number_format((float) $order->total, 2) }}</strong></div></div></aside>
        </div>
    </section>
@endsection
