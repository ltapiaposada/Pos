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
                <div class="flex gap-2">
                    <button class="btn btn-primary w-full md:w-auto">Filtrar</button>
                    <a href="{{ route('purchases.index') }}" class="btn btn-outline w-full md:w-auto">Limpiar</a>
                </div>
            </form>

            <div class="mt-4 space-y-3 md:hidden">
                @forelse ($purchases as $purchase)
                    <article class="surface-muted rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold">{{ $purchase->purchase_number }}</p>
                                <p class="text-xs text-base-content/60">{{ $purchase->purchased_at->format('Y-m-d H:i') }}</p>
                            </div>
                            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline btn-xs">Ver</a>
                        </div>
                        <div class="mt-3 space-y-1 text-sm">
                            <p><span class="text-base-content/60">Proveedor:</span> {{ $purchase->supplier_name }}</p>
                            <p><span class="text-base-content/60">Sucursal:</span> {{ $purchase->branch->name }}</p>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                            <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2">
                                <p class="text-xs text-base-content/60">Total</p>
                                <p class="font-semibold">${{ number_format($purchase->total, 2) }}</p>
                            </div>
                            <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2">
                                <p class="text-xs text-base-content/60">Saldo</p>
                                <p class="font-semibold">${{ number_format($purchase->balance_total, 2) }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">
                        Sin compras registradas.
                    </div>
                @endforelse
            </div>

            <div class="overflow-x-auto mt-4 hidden md:block">
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
