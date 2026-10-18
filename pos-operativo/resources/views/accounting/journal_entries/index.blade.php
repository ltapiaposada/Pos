@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Libro diario</h1>
                <p class="page-subtitle">Asientos contables registrados</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('accounting.entries.create') }}" class="btn btn-primary btn-sm">Nuevo asiento</a>
                <a href="{{ route('accounting.entries.movements') }}" class="btn btn-outline btn-sm">Todos los movimientos</a>
                <a href="{{ route('accounting.opening-balances.form') }}" class="btn btn-outline btn-sm">Saldos iniciales</a>
                <a href="{{ route('accounting.income-statement') }}" class="btn btn-outline btn-sm">Estado de resultados</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <div class="space-y-3 md:hidden">
                @forelse ($entries as $entry)
                    <article class="surface-muted rounded-2xl p-4">
                        <p class="text-sm font-semibold">{{ $entry->entry_number }}</p>
                        <div class="mt-2 space-y-1 text-sm">
                            <p><span class="text-base-content/60">Fecha:</span> {{ $entry->entry_date->format('Y-m-d') }}</p>
                            <p><span class="text-base-content/60">Descripcion:</span> {{ $entry->description }}</p>
                            <p><span class="text-base-content/60">Usuario:</span> {{ $entry->user->name }}</p>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('accounting.entries.show', $entry) }}" class="btn btn-outline-primary btn-xs">Ver</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">Sin asientos registrados.</div>
                @endforelse
            </div>

            <div class="overflow-x-auto hidden md:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nro</th>
                            <th>Fecha</th>
                            <th>Descripcion</th>
                            <th>Usuario</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            <tr>
                                <td>{{ $entry->entry_number }}</td>
                                <td>{{ $entry->entry_date->format('Y-m-d') }}</td>
                                <td>{{ $entry->description }}</td>
                                <td>{{ $entry->user->name }}</td>
                                <td class="text-right">
                                    <a href="{{ route('accounting.entries.show', $entry) }}" class="btn btn-outline-primary btn-xs">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-base-content/60">Sin asientos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $entries->links() }}
    </div>
@endsection
