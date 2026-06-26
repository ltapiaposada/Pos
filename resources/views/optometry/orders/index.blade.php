@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Ordenes medicas</h1>
                <p class="page-subtitle">Formulas, indicaciones e integracion con caja</p>
            </div>
            <div class="page-actions">
                <form method="GET" class="join">
                    <input name="q" value="{{ request('q') }}" placeholder="Buscar paciente" class="input input-bordered join-item input-sm">
                    <button class="btn btn-outline btn-sm join-item">Buscar</button>
                </form>
                <a href="{{ route('optometry.orders.create') }}" class="btn btn-primary btn-sm">Nueva orden</a>
            </div>
        </div>
    </div>

    <div class="panel mt-6">
        <div class="panel-body overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Paciente</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->customer?->name }}</td>
                            <td>{{ $order->ordered_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge badge-info">{{ $statusOptions[$order->status] ?? $order->status }}</span></td>
                            <td class="text-right">
                                <div class="actions justify-end">
                                    <a href="{{ route('optometry.orders.show', $order) }}" class="btn btn-outline-primary btn-xs">Ver</a>
                                    <a href="{{ route('optometry.orders.edit', $order) }}" class="btn btn-outline btn-xs">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-base-content/60">No hay ordenes medicas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
