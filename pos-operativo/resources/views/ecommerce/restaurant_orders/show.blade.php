@extends('ecommerce.layouts.app')

@section('content')
    @php
        $statusMeta = [
            \App\Models\RestaurantOrder::STATUS_OPEN => [
                'label' => 'Pendiente',
                'hint' => 'Tu pedido fue recibido y está esperando revisión del restaurante.',
                'badge' => 'bg-slate-100 text-slate-700',
                'step' => 1,
            ],
            \App\Models\RestaurantOrder::STATUS_SENT_TO_KITCHEN => [
                'label' => 'Enviado a cocina',
                'hint' => 'El restaurante ya confirmó el pedido y lo envió a cocina.',
                'badge' => 'bg-amber-100 text-amber-700',
                'step' => 2,
            ],
            \App\Models\RestaurantOrder::STATUS_IN_PREPARATION => [
                'label' => 'En preparación',
                'hint' => 'Tu pedido se está preparando ahora mismo.',
                'badge' => 'bg-sky-100 text-sky-700',
                'step' => 3,
            ],
            \App\Models\RestaurantOrder::STATUS_READY => [
                'label' => 'Listo',
                'hint' => 'Tu pedido está listo para entrega o despacho.',
                'badge' => 'bg-emerald-100 text-emerald-700',
                'step' => 4,
            ],
            \App\Models\RestaurantOrder::STATUS_DELIVERED => [
                'label' => 'Entregado',
                'hint' => 'Tu pedido ya fue entregado.',
                'badge' => 'bg-emerald-200 text-emerald-800',
                'step' => 5,
            ],
            \App\Models\RestaurantOrder::STATUS_CLOSED => [
                'label' => 'Cerrado',
                'hint' => 'El pedido ya fue cerrado en caja.',
                'badge' => 'bg-slate-200 text-slate-800',
                'step' => 5,
            ],
            \App\Models\RestaurantOrder::STATUS_CANCELLED => [
                'label' => 'Cancelado',
                'hint' => 'El pedido fue cancelado por el restaurante.',
                'badge' => 'bg-rose-100 text-rose-700',
                'step' => 0,
            ],
        ];
        $currentStatus = $statusMeta[$order->status] ?? [
            'label' => \App\Models\RestaurantOrder::statusOptions()[$order->status] ?? $order->status,
            'hint' => 'Consulta con el restaurante para más detalles.',
            'badge' => 'bg-slate-100 text-slate-700',
            'step' => 0,
        ];
        $progressSteps = [
            1 => 'Pendiente',
            2 => 'Enviado a cocina',
            3 => 'En preparación',
            4 => 'Listo',
            5 => 'Entregado',
        ];
    @endphp
    <section class="shop-mini-hero mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-1 text-white">Pedido #{{ $order->order_number }}</h1>
                <p class="mb-0 text-white-50">{{ $currentStatus['hint'] }}</p>
            </div>
            <a href="{{ route('shop.orders.index') }}" class="btn btn-light btn-sm">Volver</a>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <div class="small text-muted">Estado actual</div>
                            <div class="mt-2">
                                <span class="badge rounded-pill {{ $currentStatus['badge'] }}">{{ $currentStatus['label'] }}</span>
                            </div>
                        </div>
                        <div class="small text-muted text-end">
                            Actualizado con base en el flujo del restaurante
                        </div>
                    </div>

                    @if ($order->status !== \App\Models\RestaurantOrder::STATUS_CANCELLED)
                        <div class="mt-4">
                            <div class="row g-2">
                                @foreach ($progressSteps as $stepNumber => $stepLabel)
                                    @php($isComplete = $currentStatus['step'] >= $stepNumber)
                                    <div class="col-6 col-md">
                                        <div class="rounded-4 border px-3 py-3 h-100 {{ $isComplete ? 'border-primary bg-primary-subtle text-primary-emphasis' : 'border-secondary-subtle bg-light text-muted' }}">
                                            <div class="fw-semibold small">{{ $stepNumber }}. {{ $stepLabel }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">Platos del pedido</h2>
                    <div class="d-grid gap-3">
                        @foreach ($order->items as $item)
                            <article class="border rounded-4 p-3">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $item->product?->name ?? 'Producto eliminado' }}</div>
                                        <div class="small text-muted">Cantidad: {{ number_format((float) $item->quantity, 3) }}</div>
                                        @if ($item->selections->isNotEmpty())
                                            <div class="small text-muted mt-2">
                                                @foreach ($item->selections->groupBy('group_name') as $groupName => $selections)
                                                    <div>
                                                        {{ $groupName }}:
                                                        {{ $selections->map(fn ($selection) => $selection->selection_action === \App\Models\RestaurantOrderItemSelection::ACTION_REMOVE ? 'Sin '.$selection->option_label : $selection->option_label)->implode(', ') }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold">${{ number_format((float) $item->subtotal, 2) }}</div>
                                        <div class="small text-muted">{{ \App\Models\RestaurantOrderItem::kitchenStatusOptions()[$item->kitchen_status] ?? $item->kitchen_status }}</div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">Resumen</h2>
                    <div class="d-flex justify-content-between"><span>Tipo</span><strong>{{ \App\Models\RestaurantOrder::typeOptions()[$order->order_type] ?? $order->order_type }}</strong></div>
                    <div class="d-flex justify-content-between mt-2"><span>Subtotal</span><strong>${{ number_format((float) $order->subtotal, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mt-2"><span>Impuestos</span><strong>${{ number_format((float) $order->tax, 2) }}</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between"><span>Total</span><strong>${{ number_format((float) $order->total, 2) }}</strong></div>
                    <div class="small text-muted mt-3">
                        Apertura: {{ optional($order->opened_at)->format('d/m/Y H:i') }}
                    </div>
                    @if ($order->notes)
                        <div class="small text-muted mt-3" style="white-space: pre-line;">{{ $order->notes }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
