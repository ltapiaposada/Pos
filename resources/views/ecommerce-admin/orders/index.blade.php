@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Pedidos e-commerce</h1>
                <p class="page-subtitle">Gestion de pedidos de la tienda en linea</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form class="grid gap-3 sm:grid-cols-4">
                <div class="sm:col-span-2">
                    <label class="field-label">Buscar</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="input input-bordered w-full" placeholder="Pedido, cliente o direccion">
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
                <div class="flex items-end gap-2">
                    <button class="btn btn-primary btn-sm">Filtrar</button>
                    <a href="{{ route('ecommerce-admin.orders.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
                </div>
            </form>

            <div class="overflow-x-auto mt-4">
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
                                                <button type="submit" class="btn btn-primary btn-xs" onclick="return confirm('¿Facturar y contabilizar este pedido?')">
                                                    {{ ! $order->invoiced_at ? 'Convertir a factura' : 'Contabilizar factura' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-base-content/60">No hay pedidos registrados.</td>
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
