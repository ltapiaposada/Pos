@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Compras</h1>
                <p class="page-subtitle">Registro de compras a proveedores</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm w-full sm:w-auto">Nueva compra</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form method="GET" class="grid gap-3 md:grid-cols-3">
                <input name="q" value="{{ request('q') }}" placeholder="Buscar por proveedor, factura o nro" class="input input-bordered w-full">
                <select name="branch_id" class="select select-bordered w-full">
                    <option value="">Todas las sucursales</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-outline w-full md:w-auto">Filtrar</button>
            </form>

            <div class="overflow-x-auto mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nro</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Sucursal</th>
                            <th>Total</th>
                            <th>Saldo</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases as $purchase)
                            <tr>
                                <td>{{ $purchase->purchase_number }}</td>
                                <td>{{ $purchase->purchased_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $purchase->supplier_name }}</td>
                                <td>{{ $purchase->branch->name }}</td>
                                <td>${{ number_format($purchase->total, 2) }}</td>
                                <td>${{ number_format($purchase->balance_total, 2) }}</td>
                                <td class="text-right">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline btn-xs">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-base-content/60">Sin compras registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $purchases->links() }}
    </div>
@endsection
