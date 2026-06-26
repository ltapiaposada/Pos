@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">{{ $isRestaurantService ? 'Pedidos web restaurante' : 'Pedidos e-commerce' }}</h1>
                <p class="page-subtitle">{{ $isRestaurantService ? 'Gestion del flujo web del restaurante' : 'Gestion de pedidos de la tienda en linea' }}</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2">
                    <label class="field-label">Buscar</label>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        class="input input-bordered w-full"
                        placeholder="{{ $isRestaurantService ? 'Pedido, cliente o nota' : 'Pedido, cliente o direccion' }}"
                    >
                </div>
                <div>
                    <label class="field-label">Estado</label>
                    <select name="status" class="select select-bordered w-full">
                        <option value="">Todos</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                    <button class="btn btn-primary btn-sm flex-1 lg:flex-none">Filtrar</button>
                    <a href="{{ route('ecommerce-admin.orders.index') }}" class="btn btn-outline btn-sm flex-1 lg:flex-none">Limpiar</a>
                </div>
            </form>

            @php
                $paymentLabels = [
                    'card' => 'Tarjeta',
                    'transfer' => 'Transferencia por validar',
                    'qr' => 'QR por validar',
                    'contraentrega' => 'Contraentrega',
                    'other' => 'Otro',
                    'cash' => 'Efectivo',
                    'credit' => 'Credito',
                ];
            @endphp

            @if ($isRestaurantService && $restaurantOrders->isNotEmpty())
                <div class="mt-6">
                    <div class="mb-3">
                        <h2 class="text-sm font-semibold">Pedidos web restaurante en tramite</h2>
                        <p class="text-xs text-base-content/60 mt-1">Estos pedidos ya entraron al flujo de restaurante y todavia no se han convertido en venta.</p>
                    </div>

                    <div class="space-y-3 md:hidden">
                        @foreach ($restaurantOrders as $order)
                            <article class="surface-muted rounded-2xl p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold">Pedido web #{{ $order->order_number }}</p>
                                        <p class="text-xs text-base-content/60">{{ $order->opened_at?->format('Y-m-d H:i') }}</p>
                                    </div>
                                    <span class="badge badge-ghost badge-sm">{{ $restaurantStatusOptions[$order->status] ?? strtoupper($order->status) }}</span>
                                </div>
                                <div class="mt-3 space-y-1 text-sm">
                                    <p><span class="text-base-content/60">Cliente:</span> {{ $order->customer?->name ?? 'Sin cliente' }}</p>
                                    <p><span class="text-base-content/60">Tipo:</span> {{ \App\Models\RestaurantOrder::typeOptions()[$order->order_type] ?? $order->order_type }}</p>
                                    <p><span class="text-base-content/60">Sucursal:</span> {{ $order->branch?->name ?? 'Sin sucursal' }}</p>
                                </div>
                                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                    <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2">
                                        <p class="text-xs text-base-content/60">Estado</p>
                                        <p class="font-semibold">{{ $restaurantStatusOptions[$order->status] ?? strtoupper($order->status) }}</p>
                                    </div>
                                    <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2 text-right">
                                        <p class="text-xs text-base-content/60">Total</p>
                                        <p class="font-semibold">${{ number_format((float) $order->total, 2) }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 grid grid-cols-1 gap-2">
                                    <a href="{{ route('restaurant.orders.show', $order) }}" class="btn btn-outline btn-sm w-full">Abrir pedido restaurante</a>
                                    <a href="{{ route('restaurant.kitchen.index', ['branch_id' => $order->branch_id]) }}" class="btn btn-primary btn-sm w-full">Ver en cocina</a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="overflow-x-auto hidden md:block">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Pedido web</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Sucursal</th>
                                    <th class="text-right">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($restaurantOrders as $order)
                                    <tr>
                                        <td>#{{ $order->order_number }}</td>
                                        <td>{{ $order->opened_at?->format('Y-m-d H:i') }}</td>
                                        <td>{{ $order->customer?->name ?? 'Sin cliente' }}</td>
                                        <td>{{ \App\Models\RestaurantOrder::typeOptions()[$order->order_type] ?? $order->order_type }}</td>
                                        <td>{{ $restaurantStatusOptions[$order->status] ?? strtoupper($order->status) }}</td>
                                        <td>{{ $order->branch?->name ?? 'Sin sucursal' }}</td>
                                        <td class="text-right">${{ number_format((float) $order->total, 2) }}</td>
                                        <td class="text-right">
                                            <div class="actions justify-end">
                                                <a href="{{ route('restaurant.orders.show', $order) }}" class="btn btn-outline btn-xs">Abrir pedido</a>
                                                <a href="{{ route('restaurant.kitchen.index', ['branch_id' => $order->branch_id]) }}" class="btn btn-primary btn-xs">Cocina</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="mt-6">
                <div class="mb-3">
                    <h2 class="text-sm font-semibold">{{ $isRestaurantService ? 'Ventas web del restaurante' : 'Ventas e-commerce registradas' }}</h2>
                    <p class="text-xs text-base-content/60 mt-1">
                        {{ $isRestaurantService ? 'Aqui aparecen los pedidos web del restaurante que ya fueron convertidos a venta o factura.' : 'Aqui aparecen los pedidos web de la tienda y su avance desde recepcion hasta entrega.' }}
                    </p>
                </div>
            </div>

            <div class="mt-4 space-y-3 md:hidden">
                @forelse ($orders as $order)
                    <article class="surface-muted rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold">#{{ $order->sale_number }}</p>
                                <p class="text-xs text-base-content/60">{{ $order->sold_at?->format('Y-m-d H:i') }}</p>
                            </div>
                            <span class="badge badge-ghost badge-sm">{{ $statusOptions[$order->status] ?? strtoupper($order->status) }}</span>
                        </div>
                        <div class="mt-3 space-y-1 text-sm">
                            <p><span class="text-base-content/60">Cliente:</span> {{ $order->customer?->name ?? 'Sin cliente' }}</p>
                            <p><span class="text-base-content/60">Pago:</span> {{ $paymentLabels[$order->payments->first()?->method ?? ''] ?? 'Sin registrar' }}</p>
                        </div>
                        @if ($order->status === \App\Models\Sale::STATUS_PENDING)
                            <p class="mt-2 text-xs text-base-content/60">Primero valida el pago o la contraentrega antes de confirmar o facturar.</p>
                        @endif
                        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                            <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2">
                                <p class="text-xs text-base-content/60">Factura</p>
                                @if ($order->invoiced_at)
                                    <p class="font-semibold text-success">Facturada</p>
                                @else
                                    <p class="font-semibold text-base-content/70">Pendiente</p>
                                @endif
                            </div>
                            <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2 text-right">
                                <p class="text-xs text-base-content/60">Total</p>
                                <p class="font-semibold">${{ number_format((float) $order->total, 2) }}</p>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-1 gap-2">
                            <a href="{{ route('ecommerce-admin.orders.show', $order) }}" class="btn btn-outline btn-sm w-full">Ver</a>
                            @if (! $order->invoiced_at || ! $order->accounted_at)
                                <form method="POST" action="{{ route('ecommerce-admin.orders.invoice', $order) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm w-full" onclick="return confirm('Registrar venta o factura de este pedido?')">
                                        {{ ! $order->invoiced_at ? 'Registrar venta / factura' : 'Contabilizar factura' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">
                        No hay pedidos registrados para este servicio.
                    </div>
                @endforelse
            </div>

            <div class="overflow-x-auto mt-4 hidden md:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th>Factura</th>
                            <th class="text-right">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>#{{ $order->sale_number }}</td>
                                <td>{{ $order->sold_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ $order->customer?->name }}</td>
                                <td>{{ $statusOptions[$order->status] ?? strtoupper($order->status) }}</td>
                                <td>{{ $paymentLabels[$order->payments->first()?->method ?? ''] ?? 'Sin registrar' }}</td>
                                <td>
                                    @if ($order->invoiced_at)
                                        <span class="badge badge-success badge-sm">Facturada</span>
                                    @else
                                        <span class="badge badge-ghost badge-sm">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-right">${{ number_format((float) $order->total, 2) }}</td>
                                <td class="text-right">
                                    <div class="actions justify-end">
                                        <a href="{{ route('ecommerce-admin.orders.show', $order) }}" class="btn btn-outline btn-xs">Ver</a>
                                        @if (! $order->invoiced_at || ! $order->accounted_at)
                                            <form method="POST" action="{{ route('ecommerce-admin.orders.invoice', $order) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-xs" onclick="return confirm('Registrar venta o factura de este pedido?')">
                                                    {{ ! $order->invoiced_at ? 'Registrar venta / factura' : 'Contabilizar factura' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-base-content/60">No hay pedidos registrados para este servicio.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection
