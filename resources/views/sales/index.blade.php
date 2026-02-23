@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Facturas de venta</h1>
                <p class="page-subtitle">Listado de facturas del POS y pedidos e-commerce facturados</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('pos.index') }}" class="btn btn-primary btn-sm w-full sm:w-auto">Nueva venta</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2">
                    <label class="field-label">Buscar</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="input input-bordered w-full" placeholder="Factura, cliente o sucursal">
                </div>
                <div>
                    <label class="field-label">Sucursal</label>
                    <select name="branch_id" class="select select-bordered w-full">
                        <option value="">Todas</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                    <button class="btn btn-primary btn-sm flex-1 lg:flex-none">Filtrar</button>
                    <a href="{{ route('sales.index') }}" class="btn btn-outline btn-sm flex-1 lg:flex-none">Limpiar</a>
                </div>
            </form>

            <div class="mt-4 space-y-3 md:hidden">
                @forelse ($sales as $sale)
                    <article class="surface-muted rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold">#{{ $sale->sale_number }}</p>
                                <p class="text-xs text-base-content/60">{{ $sale->sold_at?->format('Y-m-d H:i') }}</p>
                            </div>
                            <div>
                                @if ($sale->order_source === \App\Models\Sale::SOURCE_ECOMMERCE)
                                    <span class="badge badge-info badge-sm">E-commerce</span>
                                @else
                                    <span class="badge badge-ghost badge-sm">POS</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3 space-y-1 text-sm">
                            <p><span class="text-base-content/60">Sucursal:</span> {{ $sale->branch?->name }}</p>
                            <p><span class="text-base-content/60">Cliente:</span> {{ $sale->customer?->name ?? 'Consumidor final' }}</p>
                            <p><span class="text-base-content/60">Vendedor:</span> {{ $sale->user?->name }}</p>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-xs text-base-content/60">Total</span>
                            <span class="text-base font-semibold">${{ number_format((float) $sale->total, 2) }}</span>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline btn-sm w-full">Ver</a>
                            <a
                                href="{{ route('sales.ticket', $sale) }}"
                                class="btn btn-outline btn-sm w-full"
                                onclick="window.open(this.href, 'pos_invoice_popup', 'width=420,height=760,scrollbars=yes,resizable=yes'); return false;"
                            >
                                Ticket
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">
                        No hay facturas registradas.
                    </div>
                @endforelse
            </div>

            <div class="overflow-x-auto mt-4 hidden md:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Factura</th>
                            <th>Fecha</th>
                            <th>Sucursal</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th>Origen</th>
                            <th class="text-right">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td>#{{ $sale->sale_number }}</td>
                                <td>{{ $sale->sold_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ $sale->branch?->name }}</td>
                                <td>{{ $sale->customer?->name ?? 'Consumidor final' }}</td>
                                <td>{{ $sale->user?->name }}</td>
                                <td>
                                    @if ($sale->order_source === \App\Models\Sale::SOURCE_ECOMMERCE)
                                        <span class="badge badge-info badge-sm">E-commerce</span>
                                    @else
                                        <span class="badge badge-ghost badge-sm">POS</span>
                                    @endif
                                </td>
                                <td class="text-right">${{ number_format((float) $sale->total, 2) }}</td>
                                <td class="text-right">
                                    <div class="actions">
                                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline btn-xs">Ver</a>
                                        <a
                                            href="{{ route('sales.ticket', $sale) }}"
                                            class="btn btn-outline btn-xs"
                                            onclick="window.open(this.href, 'pos_invoice_popup', 'width=420,height=760,scrollbars=yes,resizable=yes'); return false;"
                                        >
                                            Ticket
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-base-content/60">No hay facturas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
@endsection
