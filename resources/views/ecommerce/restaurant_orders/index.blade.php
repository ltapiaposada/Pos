@extends('ecommerce.layouts.app')

@section('content')
    @php
        $statusMeta = [
            \App\Models\RestaurantOrder::STATUS_OPEN => [
                'label' => 'Pendiente',
                'hint' => 'Tu pedido fue recibido y está esperando revisión del restaurante.',
                'badge' => 'bg-slate-100 text-slate-700',
            ],
            \App\Models\RestaurantOrder::STATUS_SENT_TO_KITCHEN => [
                'label' => 'Enviado a cocina',
                'hint' => 'El restaurante ya envió tu pedido a cocina.',
                'badge' => 'bg-amber-100 text-amber-700',
            ],
            \App\Models\RestaurantOrder::STATUS_IN_PREPARATION => [
                'label' => 'En preparación',
                'hint' => 'Tu pedido se está preparando en cocina.',
                'badge' => 'bg-sky-100 text-sky-700',
            ],
            \App\Models\RestaurantOrder::STATUS_READY => [
                'label' => 'Listo',
                'hint' => 'Tu pedido está listo para entrega o despacho.',
                'badge' => 'bg-emerald-100 text-emerald-700',
            ],
            \App\Models\RestaurantOrder::STATUS_DELIVERED => [
                'label' => 'Entregado',
                'hint' => 'Tu pedido ya fue entregado.',
                'badge' => 'bg-emerald-200 text-emerald-800',
            ],
            \App\Models\RestaurantOrder::STATUS_CLOSED => [
                'label' => 'Cerrado',
                'hint' => 'El pedido ya fue cerrado en caja.',
                'badge' => 'bg-slate-200 text-slate-800',
            ],
            \App\Models\RestaurantOrder::STATUS_CANCELLED => [
                'label' => 'Cancelado',
                'hint' => 'El pedido fue cancelado.',
                'badge' => 'bg-rose-100 text-rose-700',
            ],
        ];
    @endphp
    <section class="shop-mini-hero mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-1 text-white">Mis pedidos del restaurante</h1>
                <p class="mb-0 text-white-50">Sigue el estado de preparacion y entrega de tus pedidos.</p>
            </div>
            <a href="{{ route('shop.index') }}" class="btn btn-light btn-sm">Seguir comprando</a>
        </div>
    </section>

    @if ($orders->isEmpty())
        <div class="alert alert-secondary">Aun no tienes pedidos registrados.</div>
        <a href="{{ route('shop.index') }}" class="btn btn-primary">Ir al menu</a>
    @else
        <div class="row g-3">
            @foreach ($orders as $order)
                @php($meta = $statusMeta[$order->status] ?? ['label' => \App\Models\RestaurantOrder::statusOptions()[$order->status] ?? $order->status, 'hint' => 'Consulta el detalle del pedido para ver más información.', 'badge' => 'bg-slate-100 text-slate-700'])
                <div class="col-12">
                    <article class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <div class="small text-muted">Pedido #{{ $order->order_number }}</div>
                                    <h2 class="h5 mb-1">{{ \App\Models\RestaurantOrder::typeOptions()[$order->order_type] ?? $order->order_type }}</h2>
                                    <div class="small mt-2">
                                        <span class="badge rounded-pill {{ $meta['badge'] }}">{{ $meta['label'] }}</span>
                                    </div>
                                    <div class="small text-muted mt-2">
                                        {{ $meta['hint'] }}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold">${{ number_format((float) $order->total, 2) }}</div>
                                    <div class="small text-muted">{{ optional($order->opened_at)->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>

                            <div class="mt-3 small text-muted">
                                {{ $order->items->count() }} item(s)
                                @if ($order->sale_id)
                                    • Facturado
                                @else
                                    • Pendiente de cierre en caja
                                @endif
                            </div>

                            <a href="{{ route('shop.orders.show', $order->id) }}" class="btn btn-outline-secondary btn-sm mt-3">Ver detalle</a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $orders->links() }}
        </div>
    @endif
@endsection
