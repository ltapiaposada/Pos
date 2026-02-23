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
            <div class="overflow-x-auto">
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
