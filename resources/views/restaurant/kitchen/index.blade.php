@extends('layouts.admin')

@section('content')
    @php
        $totalOrders = $orders->count();
        $readyOrders = $orders->where('status', \App\Models\RestaurantOrder::STATUS_READY)->count();
        $prepOrders = $orders->where('status', \App\Models\RestaurantOrder::STATUS_IN_PREPARATION)->count();
        $pendingItems = $orders->sum(fn ($order) => $order->items->where('kitchen_status', \App\Models\RestaurantOrderItem::STATUS_PENDING)->count());
    @endphp

    <div class="space-y-6">
        <div class="page-header restaurant-module-header">
            <div class="page-header-row">
                <div>
                    <h1 class="page-title">Cocina</h1>
                    <p class="page-subtitle">Control operativo de pedidos enviados a preparacion y platos listos para entregar.</p>
                </div>
                <div class="page-actions">
                    <form method="GET" class="join">
                        <select name="branch_id" class="select select-bordered join-item select-sm" onchange="this.form.submit()">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected($branchId === $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="select select-bordered join-item select-sm" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            <div class="restaurant-header-chips">
                <span class="chip">Pedidos {{ $totalOrders }}</span>
                <span class="chip">En preparacion {{ $prepOrders }}</span>
                <span class="chip">Listos {{ $readyOrders }}</span>
                <span class="chip">Items pendientes {{ $pendingItems }}</span>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="restaurant-kitchen-board">
            <div class="restaurant-kitchen-board__card">
                <p class="restaurant-kitchen-board__eyebrow">Carga activa</p>
                <p class="restaurant-kitchen-board__value">{{ $totalOrders }}</p>
                <p class="mt-2 text-sm text-base-content/60">Pedidos visibles en esta sucursal con filtros aplicados.</p>
            </div>
            <div class="restaurant-kitchen-board__card">
                <p class="restaurant-kitchen-board__eyebrow">Produccion</p>
                <p class="restaurant-kitchen-board__value">{{ $prepOrders }}</p>
                <p class="mt-2 text-sm text-base-content/60">Pedidos actualmente en preparacion por el equipo de cocina.</p>
            </div>
            <div class="restaurant-kitchen-board__card">
                <p class="restaurant-kitchen-board__eyebrow">Despacho</p>
                <p class="restaurant-kitchen-board__value">{{ $readyOrders }}</p>
                <p class="mt-2 text-sm text-base-content/60">Pedidos listos para pasar a entrega o servicio de mesa.</p>
            </div>
        </div>

        <div class="restaurant-kitchen-grid">
            @forelse ($orders as $order)
                <div class="panel restaurant-kitchen-card">
                    <div class="panel-body">
                        <div class="restaurant-kitchen-card__header">
                            <div>
                                <h2 class="text-base font-semibold">Pedido #{{ $order->order_number }}</h2>
                                <p class="mt-1 text-sm text-base-content/60">
                                    {{ $order->table?->name ?? ($order->order_type === 'delivery' ? 'Domicilio' : 'Para llevar') }}
                                    · {{ $order->customer?->name ?? 'Cliente mostrador' }}
                                </p>
                            </div>
                            <span class="badge badge-info">{{ $statusOptions[$order->status] ?? $order->status }}</span>
                        </div>

                        <div class="restaurant-kitchen-card__meta">
                            <div class="restaurant-kitchen-meta-box">
                                <p class="restaurant-kitchen-meta-box__label">Sucursal</p>
                                <p class="restaurant-kitchen-meta-box__value">{{ $order->branch?->name ?? 'Actual' }}</p>
                            </div>
                            <div class="restaurant-kitchen-meta-box">
                                <p class="restaurant-kitchen-meta-box__label">Tipo</p>
                                <p class="restaurant-kitchen-meta-box__value">
                                    {{ $order->order_type === 'delivery' ? 'Domicilio' : ($order->order_type === 'takeaway' ? 'Para llevar' : 'Mesa') }}
                                </p>
                            </div>
                            <div class="restaurant-kitchen-meta-box">
                                <p class="restaurant-kitchen-meta-box__label">Abierto</p>
                                <p class="restaurant-kitchen-meta-box__value">{{ optional($order->opened_at)->format('H:i') ?: 'Sin hora' }}</p>
                            </div>
                        </div>

                        <div class="restaurant-kitchen-items">
                            @foreach ($order->items as $item)
                                <div class="restaurant-kitchen-item">
                                    <div class="restaurant-kitchen-item__top">
                                        <div>
                                            <p class="text-sm font-semibold">{{ $item->product?->name ?? 'Producto' }}</p>
                                            <p class="mt-1 text-xs text-base-content/60">Cantidad {{ number_format((float) $item->quantity, 3) }}</p>

                                            @if ($item->selections->isNotEmpty() || $item->notes)
                                                <div class="restaurant-kitchen-item__notes">
                                                    @foreach ($item->selections as $selection)
                                                        <div class="restaurant-kitchen-note">
                                                            {{ $selection->group_name }}:
                                                            {{ $selection->selection_action === 'remove' ? 'Sin' : 'Con' }}
                                                            {{ $selection->option_label }}
                                                        </div>
                                                    @endforeach

                                                    @if ($item->notes)
                                                        <div class="restaurant-kitchen-note">Nota: {{ $item->notes }}</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <span class="badge badge-outline">{{ $itemStatusOptions[$item->kitchen_status] ?? $item->kitchen_status }}</span>
                                    </div>

                                    <form method="POST" action="{{ route('restaurant.kitchen.items.status', $item) }}" class="restaurant-kitchen-item__form">
                                        @csrf
                                        @method('PATCH')
                                        <select name="kitchen_status" class="select select-bordered select-sm flex-1">
                                            @foreach ($itemStatusOptions as $value => $label)
                                                @if ($value !== 'pending')
                                                    <option value="{{ $value }}" @selected($item->kitchen_status === $value)>{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button class="btn btn-outline btn-sm">Actualizar</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="restaurant-empty-state md:col-span-2 xl:col-span-3">
                    <div class="restaurant-empty-state__icon">
                        <i class="fa-solid fa-fire-burner"></i>
                    </div>
                    <h3>Sin pedidos en cocina</h3>
                    <p>No hay pedidos activos para esta sucursal con el filtro actual.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
