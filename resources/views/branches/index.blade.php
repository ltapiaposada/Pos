@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Sucursales</h1>
                <p class="page-subtitle">Administra los puntos de venta</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm">Nueva sucursal</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <div class="space-y-3 md:hidden">
                @forelse ($branches as $branch)
                    <article class="surface-muted rounded-2xl p-4">
                        <p class="text-sm font-semibold">{{ $branch->name }}</p>
                        <div class="mt-2 space-y-1 text-sm">
                            <p><span class="text-base-content/60">Codigo:</span> {{ $branch->code }}</p>
                            <p><span class="text-base-content/60">Telefono:</span> {{ $branch->phone ?: '-' }}</p>
                            <p><span class="text-base-content/60">Direccion:</span> {{ $branch->address ?: '-' }}</p>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-outline btn-xs">Editar</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">Sin sucursales registradas.</div>
                @endforelse
            </div>

            <div class="overflow-x-auto hidden md:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Codigo</th>
                            <th>Telefono</th>
                            <th>Direccion</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($branches as $branch)
                            <tr>
                                <td>{{ $branch->name }}</td>
                                <td>{{ $branch->code }}</td>
                                <td>{{ $branch->phone ?: '-' }}</td>
                                <td>{{ $branch->address ?: '-' }}</td>
                                <td class="text-right">
                                    <a href="{{ route('branches.edit', $branch) }}" class="btn btn-outline btn-xs">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-base-content/60">Sin sucursales registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $branches->links() }}
    </div>
@endsection
