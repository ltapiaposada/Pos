@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Reportes</h1>
                <p class="page-subtitle">Filtros por fecha, cajero y sucursal</p>
            </div>
            <div class="page-actions">
                <span class="chip">Resumen ejecutivo</span>
            </div>
        </div>
    </div>

    <form method="GET" class="mt-4 panel">
        <div class="panel-body grid gap-3 sm:grid-cols-4">
        <div>
            <label class="field-label">Desde</label>
            <input type="date" name="from" value="{{ $dateFrom }}" class="input input-bordered w-full">
        </div>
        <div>
            <label class="field-label">Hasta</label>
            <input type="date" name="to" value="{{ $dateTo }}" class="input input-bordered w-full">
        </div>
        <div>
            <label class="field-label">Sucursal</label>
            <select name="branch_id" class="select select-bordered w-full">
                <option value="">Todas</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="field-label">Cajero</label>
            <select name="user_id" class="select select-bordered w-full">
                <option value="">Todos</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected($userId == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-4">
            <button class="btn btn-primary">Aplicar filtros</button>
        </div>
        </div>
    </form>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="panel">
            <div class="panel-body">
                <h2 class="text-sm font-semibold">Ventas por dia</h2>
                <div class="overflow-x-auto">
                    <table class="table table-sm mt-3">
                        <thead>
                            <tr>
                                <th>Dia</th>
                                <th>Ventas</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salesByDay as $row)
                                <tr>
                                    <td>{{ $row->day }}</td>
                                    <td>{{ $row->sales_count }}</td>
                                    <td>${{ number_format($row->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-base-content/60">Sin datos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-body">
                <h2 class="text-sm font-semibold">Ventas por cajero</h2>
                <div class="overflow-x-auto">
                    <table class="table table-sm mt-3">
                        <thead>
                            <tr>
                                <th>Cajero</th>
                                <th>Ventas</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salesByCashier as $row)
                                <tr>
                                    <td>{{ $row->user?->name ?? 'N/A' }}</td>
                                    <td>{{ $row->sales_count }}</td>
                                    <td>${{ number_format($row->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-base-content/60">Sin datos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-body">
                <h2 class="text-sm font-semibold">Top productos</h2>
                <div class="overflow-x-auto">
                    <table class="table table-sm mt-3">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salesByProduct as $row)
                                <tr>
                                    <td>{{ $row->product_name }}</td>
                                    <td>{{ number_format($row->qty, 3) }}</td>
                                    <td>${{ number_format($row->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-base-content/60">Sin datos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-body">
                <h2 class="text-sm font-semibold">Pagos por metodo</h2>
                <div class="overflow-x-auto">
                    <table class="table table-sm mt-3">
                        <thead>
                            <tr>
                                <th>Metodo</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salesByPayment as $row)
                                <tr>
                                    <td>{{ strtoupper($row->method) }}</td>
                                    <td>${{ number_format($row->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-base-content/60">Sin datos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
