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
            <form class="grid gap-3 sm:grid-cols-4">
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
                <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-end">
                    <button class="btn btn-primary btn-sm w-full sm:w-auto">Filtrar</button>
                    <a href="{{ route('sales.index') }}" class="btn btn-outline btn-sm w-full sm:w-auto">Limpiar</a>
                </div>
            </form>

            <div class="overflow-x-auto mt-4">
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
