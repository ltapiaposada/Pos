@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Restaurante</h1>
                <p class="page-subtitle">Mesas, pedidos abiertos, cocina e historial del salón</p>
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
                        @foreach ($tableStatuses as $value => $label)
                            <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('restaurant.tables.index') }}" class="btn btn-outline btn-sm">Configurar mesas</a>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success mt-4">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mt-4">
            <ul class="space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-12">
        <div class="space-y-6 xl:col-span-8">
            <div class="panel">
                <div class="panel-body">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold">Mapa de mesas</h2>
                            <p class="text-xs text-base-content/60">Abre una mesa disponible o entra rápido al pedido activo.</p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs">
                            @foreach ($tableStatuses as $value => $label)
                                <span class="badge {{ $value === 'occupied' ? 'badge-warning' : ($value === 'available' ? 'badge-success' : 'badge-info') }}">{{ $label }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 restaurant-floor-grid">
                        @forelse ($tables as $table)
                            @php
                                $order = $table->activeOrder;
                                $statusMeta = match ($table->status) {
                                    'available' => ['card' => 'restaurant-map-card--available', 'dot' => 'restaurant-map-card__dot--available'],
                                    'occupied' => ['card' => 'restaurant-map-card--occupied', 'dot' => 'restaurant-map-card__dot--occupied'],
                                    'reserved' => ['card' => 'restaurant-map-card--reserved', 'dot' => 'restaurant-map-card__dot--reserved'],
                                    'cleaning' => ['card' => 'restaurant-map-card--cleaning', 'dot' => 'restaurant-map-card__dot--cleaning'],
                                    default => ['card' => 'restaurant-map-card--inactive', 'dot' => 'restaurant-map-card__dot--inactive'],
                                };
                            @endphp

                            <article class="restaurant-map-card {{ $statusMeta['card'] }}">
                                <div class="restaurant-map-card__top">
                                    <div class="restaurant-map-card__identity">
                                        <span class="restaurant-map-card__number">#{{ $table->number }}</span>
                                        <div class="restaurant-map-card__labels">
                                            <p class="restaurant-map-card__name">{{ $table->name }}</p>
                                            <p class="restaurant-map-card__zone">{{ $table->location ?: 'Sin zona' }}</p>
                                        </div>
                                    </div>
                                    <span class="restaurant-map-card__status">
                                        <span class="restaurant-map-card__dot {{ $statusMeta['dot'] }}"></span>
                                        {{ $tableStatuses[$table->status] ?? $table->status }}
                                    </span>
                                </div>

                                <div class="restaurant-map-card__meta">
                                    <span><i class="fa-solid fa-users"></i> {{ $table->capacity }} puestos</span>
                                    <span><i class="fa-solid fa-table-cells"></i> {{ $table->is_active ? 'Activa' : 'Inactiva' }}</span>
                                </div>

                                @if ($order)
                                    <div class="restaurant-map-card__order">
                                        <div>
                                            <p class="restaurant-map-card__order-id">Pedido #{{ $order->order_number }}</p>
                                            <p class="restaurant-map-card__order-status">{{ \App\Models\RestaurantOrder::statusOptions()[$order->status] ?? $order->status }}</p>
                                            @if ($order->customer)
                                                <p class="restaurant-map-card__order-customer">{{ $order->customer->name }}</p>
                                            @endif
                                        </div>
                                        <a href="{{ route('restaurant.orders.show', $order) }}" class="btn btn-primary btn-xs">Abrir</a>
                                    </div>
                                @elseif ($table->is_active && $table->status !== \App\Models\RestaurantTable::STATUS_INACTIVE)
                                    <form method="POST" action="{{ route('restaurant.orders.store') }}" class="restaurant-map-card__quick-open">
                                        @csrf
                                        <input type="hidden" name="branch_id" value="{{ $branchId }}">
                                        <input type="hidden" name="restaurant_table_id" value="{{ $table->id }}">
                                        <input type="hidden" name="order_type" value="{{ \App\Models\RestaurantOrder::TYPE_DINE_IN }}">
                                        <button class="btn btn-primary btn-xs w-full">Abrir mesa</button>
                                    </form>
                                @else
                                    <div class="restaurant-map-card__disabled">Mesa no disponible para abrir pedidos.</div>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60 md:col-span-2 2xl:col-span-3 xl:col-span-full">
                                No hay mesas configuradas para esta sucursal.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body">
                    <h2 class="text-sm font-semibold">Historial reciente</h2>
                    <div class="mt-4 overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Tipo</th>
                                    <th>Cliente</th>
                                    <th>Venta</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($historyOrders as $order)
                                    <tr>
                                        <td><a href="{{ route('restaurant.orders.show', $order) }}" class="link link-hover">#{{ $order->order_number }}</a></td>
                                        <td>{{ $orderTypes[$order->order_type] ?? $order->order_type }}</td>
                                        <td>{{ $order->customer?->name ?? 'Cliente mostrador' }}</td>
                                        <td>
                                            @if ($order->sale)
                                                <a href="{{ route('sales.show', $order->sale) }}" class="link link-hover">#{{ $order->sale->sale_number }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ \App\Models\RestaurantOrder::statusOptions()[$order->status] ?? $order->status }}</td>
                                        <td>${{ number_format((float) $order->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-base-content/60">Sin historial reciente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6 xl:col-span-4">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="text-sm font-semibold">Pedidos rápidos</h2>
                    <p class="mt-1 text-xs text-base-content/60">Úsalo para llevar o domicilio sin pasar por una mesa.</p>

                    <div class="mt-4 grid gap-3">
                        <form method="POST" action="{{ route('restaurant.orders.store') }}">
                            @csrf
                            <input type="hidden" name="branch_id" value="{{ $branchId }}">
                            <input type="hidden" name="order_type" value="{{ \App\Models\RestaurantOrder::TYPE_TAKEAWAY }}">
                            <button class="btn btn-outline w-full">Nuevo pedido para llevar</button>
                        </form>
                        <form method="POST" action="{{ route('restaurant.orders.store') }}">
                            @csrf
                            <input type="hidden" name="branch_id" value="{{ $branchId }}">
                            <input type="hidden" name="order_type" value="{{ \App\Models\RestaurantOrder::TYPE_DELIVERY }}">
                            <button class="btn btn-outline w-full">Nuevo pedido a domicilio</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body">
                    <h2 class="text-sm font-semibold">Pendientes</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($activeOrders as $order)
                            <a href="{{ route('restaurant.orders.show', $order) }}" class="block rounded-2xl border border-base-300 bg-base-100 p-4 transition hover:border-primary/50">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold">Pedido #{{ $order->order_number }}</p>
                                        <p class="text-xs text-base-content/60">
                                            {{ $order->table?->name ?? ($orderTypes[$order->order_type] ?? $order->order_type) }}
                                        </p>
                                    </div>
                                    <span class="badge badge-info">{{ \App\Models\RestaurantOrder::statusOptions()[$order->status] ?? $order->status }}</span>
                                </div>
                                <div class="mt-2 text-sm">
                                    <p>Cliente: {{ $order->customer?->name ?? 'Cliente mostrador' }}</p>
                                    <p>Total: ${{ number_format((float) $order->total, 2) }}</p>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-base-300 bg-base-100 p-4 text-sm text-base-content/60">No hay pedidos pendientes en esta sucursal.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
