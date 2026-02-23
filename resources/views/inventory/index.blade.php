@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Inventario</h1>
                <p class="page-subtitle">Stock por sucursal y ajustes</p>
            </div>
            <div class="page-actions">
                <form method="GET" class="join">
                    <select name="branch_id" class="select select-bordered join-item select-sm">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <input name="q" value="{{ request('q') }}" placeholder="Buscar producto" class="input input-bordered join-item input-sm">
                    <button class="btn btn-outline btn-sm join-item">Filtrar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 min-w-0 space-y-4">
            <div class="panel">
                <div class="panel-body">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Sucursal</th>
                                    <th>Stock</th>
                                    <th>Minimo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inventories as $inventory)
                                    @php
                                        $isLowStock = $inventory->min_stock > 0 && $inventory->stock <= $inventory->min_stock;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="font-medium">{{ $inventory->product->name }}</div>
                                            <div class="text-xs text-base-content/60">{{ $inventory->product->sku }}</div>
                                        </td>
                                        <td>{{ $inventory->branch->name }}</td>
                                        <td>{{ number_format($inventory->stock, 3) }}</td>
                                        <td>{{ number_format($inventory->min_stock, 3) }}</td>
                                        <td>
                                            <span class="badge {{ $isLowStock ? 'badge-danger' : 'badge-success' }}">
                                                {{ $isLowStock ? 'Minimo' : 'OK' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-base-content/60">Sin registros</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                {{ $inventories->links() }}
            </div>
        </div>

        <div class="space-y-4">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="text-sm font-semibold">Ajuste de inventario</h2>
                    <form action="{{ route('inventory.adjust') }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label class="field-label">Sucursal</label>
                            <select name="branch_id" class="select select-bordered w-full">
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <p class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="field-label">Producto</label>
                            <select name="product_id" class="select select-bordered w-full">
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <p class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="field-label">Tipo</label>
                                <select name="type" class="select select-bordered w-full">
                                    <option value="IN">Entrada</option>
                                    <option value="OUT">Salida</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Cantidad</label>
                                <input name="quantity" type="number" step="0.001" class="input input-bordered w-full" required>
                                @error('quantity')
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Stock minimo (opcional)</label>
                            <input name="min_stock" type="number" step="0.001" min="0" class="input input-bordered w-full" placeholder="Ej: 10">
                            @error('min_stock')
                                <p class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="field-label">Costo (opcional)</label>
                            <input name="cost_price" type="number" step="0.01" class="input input-bordered w-full">
                        </div>
                        <div>
                            <label class="field-label">Notas</label>
                            <input name="notes" class="input input-bordered w-full">
                        </div>
                        <button class="btn btn-primary w-full">Guardar ajuste</button>
                    </form>
                </div>
            </div>

            <div class="panel">
                <div class="panel-body">
                    <h2 class="text-sm font-semibold">Ultimos movimientos</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        @forelse ($movements as $movement)
                            <div class="surface-muted p-3">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                    <span class="font-medium">{{ $movement->product->name }}</span>
                                    <span class="text-xs text-base-content/60">{{ $movement->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="text-xs text-base-content/60">{{ $movement->branch->name }}</div>
                                <div class="mt-1 text-xs">
                                    <span class="font-semibold">{{ $movement->type === 'IN' ? 'Entrada' : 'Salida' }}</span>
                                    <span>{{ number_format($movement->quantity, 3) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-base-content/60">Sin movimientos recientes.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
